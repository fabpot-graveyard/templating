The Component in Five Minutes
=============================

The Symfony Templating component provides all the tools needed to build any
kind of template system. It is a thin and flexible layer on top of PHP that
provides simple but powerful features, covering templating common needs.

It is centered around a main class, `sfTemplateEngine`, which provides the
features that are common to all templating systems (inclusions, template
inheritance, slots, helpers, ...), and three main sub-components: template
loaders, template renderers, and template helpers.

>**TIP**
>Like other symfony components, the templating component can use
>autoloading:
>
>     [php]
>     require_once '/path/to/sfTemplateAutoloader.php';
>     sfTemplateAutoloader::register();

If you don't have time to read the full documentation, which you should read
anyway later on, the following code can get you started right away:

    [php]
    require_once '/path/to/sfTemplateAutoloader.php';
    sfTemplateAutoloader::register();

    $loader = new sfTemplateLoaderFilesystem(dirname(__FILE__).'/templates/%name%.php');

    $engine = new sfTemplateEngine($loader);

    echo $engine->render('index', array('name' => 'Fabien'));

The previous code will render a template named `index`, which should exist in
the `dirname(__FILE__).'/templates/'` directory.

The templating component makes no assumption on where to find templates and
how to render them.

The "where" is taken care of by the **template loader**. The built-in
`sfTemplateLoaderFilesystem` class, used in the above code snippet, knows how
to load templates from the filesystem.

The "how" is taken care of by the **template renderer**. The built-in
`sfTemplateRendererPhp` class knows how to render plain PHP templates. It is
always implicitly registered.

The `index` template is a plain PHP file which has access to several
convenient methods:

    [php]
    <?php $this->extend('layout') ?>

    <?php $this->set('title', 'My Page Title') ?>

    Hello <?php echo $name ?>

Within a template, you can access template parameters like local variables,
and the `$this` object refers to the template engine instance, which provides
useful methods for templates. For instance, the `extend()` method tells the
engine that the template should be decorated by the `layout` template. The
layout template reads as follows:

    [php]
    <html>
      <head>
        <title><?php echo $this->get('title') ?></title>
      </head>
      <body>
        <?php echo $this->get('content') ?>
      </body>
    </html>

The `get()` and `set()` methods allows the definition of named snippets of
HTML (called **slots**) that can be used in another template or layout later
on (like the `title` one). The same mechanism is also used by the templating
layout system, where `content` represents the content of the child template.

Keep reading to learn more about the above code, and much more.
