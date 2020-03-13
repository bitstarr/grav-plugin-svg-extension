# SVG Extension Plugin

The **SVG Extension** Plugin is an extension for [Grav CMS](http://github.com/getgrav/grav). It provides a way to inline SVG files in your Twig templates so you can style them with CSS.

> Minifying: To keep the amount of markup low, consider an automated workflow (involving gulp/grunt and svgo for example) to minimize the filesize of the SVG files by optimizing them. Here we are assume that you have an ``assets/svg`` folder with the source files and ``dist/svg`` with the optimized copies. Only the latter will be transfered to the production enviroment in this scenario.

## Usage

You can access your SVG files in two ways.

### Access by filename
First you can set a base path in the plugin config and access them by filename:

````twig
{{ svg( 'search', 'icon' ) }}
````

This will lookup ``search.svg`` in the icons folder. The default icon folder is ``theme://dist/icons/`` and can be set in the [Configuration](#configuration).

### Access with absolute path
The second way is to use a somehow absolute path:

````twig
{{ svg('theme://optimized/icons/search.svg', 'icon') }}
````

### Parameters

First parameter is the path or filname (as mentioned above). The second is the place for CSS classes. The third is an object/associative array. This array accepts up to three items at the moment:
* An ``id`` as the id attribute for the svg element embedded in the markup.
* A ``title`` for better accessability
* ``preserveAspectRatio`` for orientation, defaults to ``xMinYMin``

````twig
{{ svg('logo', 'icon logo__img', { 'id': 'logo-icon', 'title': 'Brand name' }) }}
````

> About Accessability: Without a title, the SVG will be placed as pesentational image. Take a look in the expamples tfor more details.

### Example

````css
.icon {
    fill: currentColor;
    height: 1em;
    width: 1em;
    overflow: hidden;
    vertical-align: -.125em;
}
.meta__item .icon {
    color: rebeccapurple;
}
````

````twig
<ul class="meta">
    <li class="meta__item">
        {{ svg('thumbtack', 'icon' }}
    </li>
    <li class="meta__item">
        {{ svg('clock', 'icon', { 'title': 'Cooking Time' }) }}
        10 Minutes
    </li>
    …
</ul>
````

Will render to this:
````html
<ul class="meta">
    <li class="meta__item">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="icon" role="presentation" aria-hidden="true" preserveAspectRatio="xMinYMin">
            <path d="M306.5 186.6l-5.7-42.6H328c13.2 0 24-10.8 24-24V24c0-13.2-10.8-24-24-24H56C42.8 0 32 10.8 32 24v96c0 13.2 10.8 24 24 24h27.2l-5.7 42.6C29.6 219.4 0 270.7 0 328c0 13.2 10.8 24 24 24h144v104c0 .9.1 1.7.4 2.5l16 48c2.4 7.3 12.8 7.3 15.2 0l16-48c.3-.8.4-1.7.4-2.5V352h144c13.2 0 24-10.8 24-24 0-57.3-29.6-108.6-77.5-141.4zM50.5 304c8.3-38.5 35.6-70 71.5-87.8L138 96H80V48h224v48h-58l16 120.2c35.8 17.8 63.2 49.4 71.5 87.8z"></path>
        </svg>
    </li>
    <li class="meta__item">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="icon" role="image" aria-labelledby="icon__title--5e6b577f45c8b" preserveAspectRatio="xMinYMin">
            <path fill="currentColor" d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 448c-110.5 0-200-89.5-200-200S145.5 56 256 56s200 89.5 200 200-89.5 200-200 200zm61.8-104.4l-84.9-61.7c-3.1-2.3-4.9-5.9-4.9-9.7V116c0-6.6 5.4-12 12-12h32c6.6 0 12 5.4 12 12v141.7l66.8 48.6c5.4 3.9 6.5 11.4 2.6 16.8L334.6 349c-3.9 5.3-11.4 6.5-16.8 2.6z"></path>
            <title id="icon__title--5e6b577f45c8b">Cooking Time</title>
        </svg>
        10 Minutes
    </li>
    …
</ul>
````
![Rednered Preview](example.png)

## Installation

Installing the SVG Extension plugin can be done in one of three ways: The GPM (Grav Package Manager) installation method lets you quickly install the plugin with a simple terminal command, the manual method lets you do so via a zip file, and the admin method lets you do so via the Admin Plugin.

### GPM Installation (Preferred)

To install the plugin via the [GPM](http://learn.getgrav.org/advanced/grav-gpm), through your system's terminal (also called the command line), navigate to the root of your Grav-installation, and enter:

    bin/gpm install svg-extension

This will install the SVG Extension plugin into your `/user/plugins`-directory within Grav. Its files can be found under `/your/site/grav/user/plugins/svg-extension`.

### Manual Installation

To install the plugin manually, download the zip-version of this repository and unzip it under `/your/site/grav/user/plugins`. Then rename the folder to `svg-extension`. You can find these files on [GitHub](https://github.com//grav-plugin-svg-extension) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/svg-extension


### Admin Plugin

If you use the Admin Plugin, you can install the plugin directly by browsing the `Plugins`-menu and clicking on the `Add` button.

## Configuration

Before configuring this plugin, you should copy the `user/plugins/svg-extension/svg-extension.yaml` to `user/config/plugins/svg-extension.yaml` and only edit that copy.

Here is the default configuration and an explanation of available options:

```yaml
enabled: true
path: 'theme://dist/icons/'
```

Note that if you use the Admin Plugin, a file with your configuration named svg-extension.yaml will be saved in the `user/config/plugins/`-folder once the configuration is saved in the Admin.

In order to use keywords/filenames only to target your SVGs, you need to point the path variable to the place where your files are stored.