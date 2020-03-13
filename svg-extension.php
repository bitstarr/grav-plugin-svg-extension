<?php

namespace Grav\Plugin;

use DOMDocument;
use DOMElement;
use Exception;
use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;
use UnexpectedValueException;

/**
 * Class SVGExtensionPlugin
 * @package Grav\Plugin
 */
class SVGExtensionPlugin extends Plugin
{
    /** @var string[] */
    protected $processedSvg = [];

    protected $defaults = [
        'id' => null,
        'title' => null,
        'preserveAspectRatio' => 'xMinYMin',
    ];

    protected $options = [];

    /**
     * @return array
     * //     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main event we are interested in
        $this->enable([
            'onTwigInitialized' => ['onTwigInitialized', 0]
        ]);
    }

    /**
     * Do some work for this event, full details of events can be found
     * on the learn site: http://learn.getgrav.org/plugins/event-hooks
     *
     * @param Event $e
     */
    public function onTwigInitialized(Event $e)
    {
        $this->grav['twig']->twig()->addFunction(
            new \Twig_SimpleFunction('svg', [$this, 'getSvg'])
        );
    }

    /**
     * Gibt den korrekt formatierten und manipulierten String der SVG zurück, die via Pfad/String übergeben wurde
     *
     * @param string $svgPath Pfad/String der SVG
     * @param string|null $class
     * @param array|null $options
     * @return string
     */
    public function getSvg(?string $svgPath, ?string $class = null, ?array $options = []): string
    {
        foreach ($this->defaults as $key => $value) {
            $this->options[$key] = array_key_exists($key, $options) ? $options[$key] : $value;
        }
        $processedKey = $this->getKeyForProcessed($svgPath, $class);
        if ($this->isProcessed($processedKey)) {
            return $this->getFromProcessed($processedKey) ?? '';
        }

        $this->assetPath = ($this->config->get('plugins.svg-extension.path')) ? $this->config->get('plugins.svg-extension.path') : 'theme://dist/icons/';

        if (! preg_match('~[\./]~', $svgPath)) {
            $svgPath = $this->grav['locator']->findResource($this->assetPath . $svgPath . '.svg');
        }

        $svgString = $svgPath;

        if ($svgPath !== null && $this->isSvgFromFile($svgPath)) {
            $svgString = file_get_contents($svgPath);
        }

        if ($svgString && $this->isValidSvg($svgString)) {
            if ($class || $this->options['id'] || $this->options['title']) {
                $svgString = $this->createHtml($svgString, $class);
            }

            $this->pushToProcessed($processedKey, $svgString);

            // Auf Standardwerte zurücksetzen, da sie sonst ins nächste Icon bluten
            $this->options = [];

            return $svgString;
        }
        return '';
    }

    /**
     * Gibt einen Key für den In-Memory Zwischenspeicher zurück
     * @param string|null $graphic
     * @param string|null $class
     * @return string
     */
    protected function getKeyForProcessed(?string $graphic, ?string $class): string
    {
        $optionsstring = implode('-', $this->options);
        return md5(implode('-', [$graphic, $class, $optionsstring]));
    }

    /**
     * Gibt den Inhalt des In-Memory Caches für den angegebenen Key zurück
     * @param string $key
     * @return string|null
     */
    protected function getFromProcessed(string $key): ?string
    {
        if ($this->isProcessed($key)) {
            return $this->processedSvg[$key];
        }

        return null;
    }

    /**
     * Gibt zurück, ob der Key bereits verarbeitet wurde
     * @param string $key
     * @return bool
     */
    protected function isProcessed(string $key): bool
    {
        return array_key_exists($key, $this->processedSvg);
    }

    /**
     * Speichert den Key und den dazugehörigen Wert im In-Memory Cache
     * @param string $key
     * @param string $value
     */
    protected function pushToProcessed(string $key, string $value): void
    {
        $this->processedSvg[$key] = $value;
    }

    /**
     * Prüfen, ob es sich um eine SVG handelt
     *
     * Nur grobe Prüfung!
     * @param string|null $svgString
     * @return bool
     */
    protected function isValidSvg(?string $svgString): bool
    {
        if (!$svgString) {
            return false;
        }

        return !(strpos($svgString, '</svg>') === false);
    }

    protected function isSvgFromFile(?string $svgString): bool
    {
        if (!$svgString) {
            return false;
        }

        return strpos($svgString, '.svg') && file_exists($svgString);
    }

    /**
     * Überschreibt die (CSS-)ID der SVG mit der angegebenen und ergänzt die (CSS-)Klassen um die angegebenen
     *
     * @param string $svgString String der SVG Source
     * @param string|null $classes String der Klassen in der Form 'foo bar baz'
     * @return string manipulierter Source der SVG
     */
    protected function createHtml(string $svgString, ?string $classes): string
    {
        // TODO: Set title tag
        $svgDomDoc = new DOMDocument();
        try {
            $svgDomDoc->loadXML($svgString);
        } catch (Exception $e) {
            return '';
        }

        $svgNodeInDocument = $svgDomDoc->getElementsByTagName('svg')->item(0);
        if (!$svgNodeInDocument instanceof DOMElement) {
            throw new UnexpectedValueException('Could not get DOMElement from SVG.');
        }
        $id = $this->options['id'];
        if ($id !== null) {
            $svgNodeInDocument->setAttribute('id', $id);
        }

        if ($classes) {
            $oldClasses = explode(' ', $svgNodeInDocument->getAttribute('class'));

            $classes = array_merge($oldClasses, explode(' ', $classes));
            // Beschränkung auf die uniquen Array Values - performanter als array_unique
            // Siehe (http://php.net/manual/en/function.array-unique.php#98453)
            $classes = array_keys(array_flip($classes));

            $svgNodeInDocument->setAttribute('class', trim(implode(' ', $classes)));
        }

        if ($this->options['title']) {
            $attId = uniqid('icon__title--');
            $titleTag = $svgDomDoc->createElement('title', $this->options['title']);
            $titleTag->setAttribute('id', $attId);
            $svgNodeInDocument->appendChild($titleTag);
            $svgNodeInDocument->setAttribute('role', 'image');
            $svgNodeInDocument->setAttribute('aria-labelledby', $attId);
        }
        else {
            $svgNodeInDocument->setAttribute('role', 'presentation');
            $svgNodeInDocument->setAttribute('aria-hidden', 'true');
        }

        $svgNodeInDocument->setAttribute('preserveAspectRatio', $this->options['preserveAspectRatio']);

        // Lediglich erste Node ausgeben, um XML Deklaration (<!--xml...-->) zu unterdrücken
        return $svgDomDoc->saveXML($svgDomDoc->documentElement);
    }
}
