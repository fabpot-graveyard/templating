Template Helpers
================

The templating engine supports **template ~helpers|helper~**. A helper is an
object that provides unique features in your templates. Helpers are accessible
in every template as properties. This chapter describes the built-in template
helpers and shows you how to create custom ones.

Playing with Helpers
--------------------

Template helpers are managed through a helper set instance
(~`sfTemplateHelperSet`~):

    [php]
    $helperSet = new sfTemplateHelperSet(array(
      new sfTemplateHelperJavascripts(),
      new sfTemplateHelperStylesheets(),
    ));

Helpers can be also added by using the ~`set()`~ method directly:

    [php]
    $helperSet->set(new sfTemplateHelperJavascripts());

A template engine instance can takes a helper set instance as a constructor
argument (the third one), or can be changed anytime with the
~`setHelperSet()`~ method:

    [php]
    $engine = new sfTemplateEngine($loader, array(), $helperSet);

    $engine->setHelperSet($helperSet);

Helpers are then available in any template rendered through the engine as
object properties:

    [php]
    // in a template
    <?php $this->javascripts->add('/js/foo.js') ?>

By default, helpers are available with their "~canonical~" name. You can also
set an ~alias~ name if you want:

    [php]
    $helperSet = new sfTemplateHelperSet(array(
      'scripts' => new sfTemplateHelperJavascripts(),
    ));

    $helperSet->set(new sfTemplateHelperJavascripts(), 'scripts);

Even if an alias is defined, the helpers are still available through their
canonical names.

The following section describe the built-in helpers.

Built-in Template Helpers
-------------------------

### Assets (~`sfTemplateHelperAssets`~)

*Dependencies*: none

*Canonical name*: ~`assets`~

The `assets` helper manages asset URLs. Assets are static files like images,
stylesheets, or JavaScript files. The main feature of this helper is its
ability to convert relative asset URLs to absolute ones based on three
optional parameters:

 * The *base path* is added at the beginning of non-absolute URLs (URLs that
   do not start with either a host or a `/`)

 * The *base URL* is added at the beginning of all non-absolute URLs (URLs
   that do not start with a host)

 * The assets *version* is added at the end of each URL as a query string

These parameters can be passed as arguments of the helper constructor, or by
using the dedicated setters:

    [php]
    $helper = new sfTemplateHelperAssets($basePath, $baseURL, $version);

    $helper->setBasePath($basePath);
    $helper->setBaseURL($baseURL);
    $helper->setVersion($version);

Converting an URL an be done by using the ~`getUrl()`~ method, like shown
below:

    [php]
    <img src="<?php $this->assets->getUrl('fabien.jpg') ?>" />

#### The Base Path

The ~base path~ setting is useful when your website is hosted in a
sub-directory of the web server root directory. To make your URLs portable and
avoid hardcoding the web server relative installation directory for each asset
URL, you can instead set the base path with the ~`setBasePath()`~ method:

    [php]
    // your website is accessible at: http://example.com/somedir/index.php/
    // somedir is the base path

    $helper->setBasePath('/somedir');

    echo $this->assets->getUrl('fabien.jpg');

    // displays /somedir/fabien.jpg

    echo $this->assets->getUrl('/fabien.jpg');

    // displays /fabien.jpg

#### The Base URL

If you set the ~base URL~, all asset URLs will be prepended with this base
URL. This makes for instance possible to move your assets to a dedicated
server:

    [php]
    // your assets are hosted at: http://assets.example.com/

    $helper->setBaseURLs('http://assets.example.com/');

    echo $this->assets->getUrl('fabien.jpg');

    // displays http://assets.example.com/fabien.jpg

    echo $this->assets->getUrl('/fabien.jpg');

    // displays http://assets.example.com/fabien.jpg

Of course, you can combine the base URL with the base path. Also, the base URL
can have a path on its own:

    [php]
    $helper->setBaseURLs('http://assets.example.com/foo/');

You have probably noticed that the method to set the base url is named
~`setBaseURLs()`~ with a `s` at the end. That's because you can also pass an
array of URLs. If you do so, the `getUrl()` method will get a random URL from
the array. It can be used to load-balance the asset serving amongst several
servers, but it is mainly useful to take advantage of the way browsers work.
Define up to four host aliases for your assets.

>**NOTE**
>For a given asset path, the `getUrl()` method will always return the same
>base URL to play nice with proxy and browser caches.

#### The Version

If you set a very long expires time for your assets in your web server
configuration, you need a way for force the browser to refetch cached content
when it changes on the server.

One way to do it is to configure the assets ~version~. It must be a unique
identifier that changes every time you deploy a new version of your website.

A good version can be the latest version of the project repository. You can
find some code snippet for
[Git](http://pemberthy.blogspot.com/2009/05/overriding-railsassetid-based-on-last.html)
and
[SVN](http://geekblog.vodpod.com/2008/01/16/rails-why-your-pages-load-so-slowly/)
online.

### JavaScripts (~`sfTemplateHelperJavascripts`~)

*Dependencies*: `assets` (`sfTemplateHelperAssets`)

*Canonical name*: ~`javascripts`~

The JavaScript helper manages JavaScript files across templates. It needs the
`assets` helper to manages its URLs:

    [php]
    $helperSet = new sfTemplateHelperSet(array(
      new sfTemplateHelperAssets(),
      new sfTemplateHelperJavascripts(),
    ));

Call the ~`add()`~ method to add a required JavaScript for the current
template:

    [php]
    <?php $this->javascripts->add('/js/foo.js') ?>

To output the HTML needed for the JavaScripts to be required in the layout,
simply echo helper object itself as shown below:

    [php]
    <?php echo $this->javascripts ?>

### Stylesheets (~`sfTemplateHelperStylesheets`~)

*Dependencies*: `assets` (`sfTemplateHelperAssets`)

*Canonical name*: ~`stylesheets`~

The stylesheet helper manages stylesheet files across templates. It needs the
`assets` helper to manages its URLs:

    [php]
    $helperSet = new sfTemplateHelperSet(array(
      new sfTemplateHelperAssets(),
      new sfTemplateHelperStylesheets(),
    ));

Call the ~`add()`~ method to add a required stylesheet for the current
template:

    [php]
    <?php $this->stylesheets->add('/css/foo.css') ?>

To output the HTML needed for the stylesheets to be required in the layout,
simply echo helper object itself as shown below:

    [php]
    <?php echo $this->stylesheets ?>

Custom Template Helpers
-----------------------

Beside the built-in helpers, you can create your own one very easily. Template
helpers must implement the ~`sfTemplateHelperInterface`~ which reads as
follow:

    [php]
    interface sfTemplateHelperInterface
    {
      function getName();

      function setHelperSet(sfTemplateHelperSet $helperSet = null);

      function getHelperSet();
    }

If you don't want to implement the ~`getHelperSet()`~ and ~`setHelperSet()`~
method each time you create a helper class, just extend the abstract
~`sfTemplateHelper`~ class.

A template helper must have a default canonical name as explained before. It's
defined with the ~`getName()`~ method. Here is the minimum helper definition:

    [php]
    class CustomTemplateHelper extends sfTemplateHelper
    {
      public function getName()
      {
        return 'custom';
      }
    }

If you add this helper to a helper set, it will be available as `custom` in
the templates:

    [php]
    $helperSet = new sfTemplateHelperSet(array(
      new CustomTemplateHelper(),
    ));

    // in a template
    $this->custom->...();

### Escaping

If you need to escape a string from a helper, you can use the engine
`escape()` method like this:

    [php]
    $escaped = $this->getHelperSet()->getEngine()->escape($string);

### Helper Dependencies

In a helper, you can also use other helpers by getting them with their
canonical name, like getting an asset URL from the `assets` helper:

    [php]
    $url = $this->getHelperSet()->get('assets')->getUrl($someUrl);

>**TIP**
>Read the built-in helpers source code to learn more tricks.
