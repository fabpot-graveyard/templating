Templating Engine Core Features
===============================

This chapter describes the built-in features of the main `sfTemplateEngine`
class.

Rendering Templates
-------------------

The main goal of the ~`sfTemplateEngine`~ class is to render templates. This
is done by calling its ~`render()`~ method:

    [php]
    echo $engine->render('index', array('name' => 'Fabien'));

To render a template, `sfTemplateEngine` first needs to load it. By default,
the template engine makes no assumption about where templates are stored and
how to render them. Templates can be stored on the filesystem, in a database,
in memory, or wherever you want. And templates can be in PHP, PHPTAL, Smarty,
or any other custom template language.

The template engine relies on **template loader objects** to find templates,
based on a **logical template name**, and on **template renderer objects** to
render them. These objects are passed as arguments to the constructor:

    [php]
    $engine = new sfTemplateEngine($loader, array('php' => $renderer));

The engine constructor takes a loader object as its first argument and an
array of renderers as its second one.

### Loaders and Template ~Logical Name~

When calling the `render()` method, the engine first tries to load the
template. The first argument of the `render()` method is the logical name of
the template. The logical name of the template is independent of the template
loader you use. That's why we call it a "logical" template name (in contrast
with the "physical" template name). With a filesystem loader, the name is
probably part of the full path. But if you use a database template loader, the
name can be the primary key of the template record for instance. The logical
name can be whatever the template loader is able to understand.

The component comes bundled with several template loaders, and the
`sfTemplateLoaderFilesystem` is the one to use if the templates are stored on
the filesystem:

    [php]
    $loader = new sfTemplateLoaderFilesystem(dirname(__FILE__).'/templates/%name%.php');

The loader constructor takes a path pattern as its first argument. The
~`%name%`~ placeholder is replaced at runtime by the template logical name.

More information about template loaders can be found in its
[dedicated chapter](#chapter_04).

### Renderers

After loading the template, it must be rendered. A renderer object is
responsible for the evaluation of the template, based on the passed arguments
(the second argument of the `render()` method). By default, the `php` renderer
(`sfTemplateRendererPhp`) is always available and is the default renderer used
by the template engine. It is always implicitly registered, but here is the
equivalent explicit declaration:

    [php]
    $engine = new sfTemplateEngine($loader, array('php' => new sfTemplateRendererPhp()));

In the previous section, you have learned about the logical name of a
template. As a matter of fact, it is composed of two parts, the template
renderer, a colon, and the template name; like in `php:index`. As the `php`
renderer is the default, it can be omitted, like we did in the previous
examples.

>**TIP**
>In a loader pattern, the renderer is available as ~`%renderer%`~.

More information about template renderers can be found in its
[dedicated chapter](#chapter_05).

General Principles
------------------

Within a template, the `$this` variable always refers to the engine instance,
and local variables refer to the corresponding values from the parameters
passed to the `render()` method.

~Embedding|embedding~
---------------------

**A template can embed another one** by using the `render()` method directly
in a template:

    [php]
    Hello <?php echo $name ?>

    <?php echo $this->render('embedded', array('name' => $name)) ?>

Within a template, the `render()` method is the same as the one used to render
the main template and as such takes the same arguments.

The embedded template can of course embed other templates, and so on.

If you have several renderers, templates in one format can embed one in
another one. It means for instance that you can mix plain PHP templates with
Smarty ones.

>**CAUTION**
>The parameters passed to a template are not available in the embedded
>template except if you explicitly pass them as shown in the above example.

~Inheritance|inheritance~
-------------------------

One of the most common need when dealing with templates is to have a common
~layout~ for all of them. **A layout is a template that decorates another
one**. It replaces the well-known header and footer templates. The template is
decorated after the content is rendered by a "global" template, the layout.

Decorating a template can be done by calling the ~`extend()`~ method in a
template:

    [php]
    <?php $this->extend('layout') ?>

    Hello <?php echo $name ?>

>**TIP**
>By convention, it is better to define the layout at the top of the template
>as it makes the template more readable, but this is not mandatory.

The layout template can access the main template content by getting the
special ~`content` slot~ (see the next section for a discussion on slots):

    [php]
    <html>
      <head>
      </head>
      <body>
        <?php $this->output('content') ?>
      </body>
    </html>

Rendering the `index` template gives you the following generated HTML code:

    [php]
    <html>
      <head>
      </head>
      <body>
        Hello Fabien
      </body>
    </html>

The template engine also support multiple inheritance as explained later on.

>**CAUTION**
>The parameters passed to a template are never available in the layout.
>One way to pass variables to the layout is to define slots in the main
>template (see below).

~Slots|slot~
------------

When a zone of the layout depends on the template to be displayed, you need to
define a **slot**. It allows to define more than one ~dynamic zones~ in a
layout.

Displaying the content of a slot can be done by calling the ~`output()`~
method:

    [php]
    <html>
      <head>
        <title><?php $this->output('title') ?></title>
      </head>
      <body>
        <?php $this->output('content') ?>
      </body>
    </html>

>**TIP**
>The `output()` method is just a shortcut for the more verbose
>`<?php echo $this->get('content') ?>` call.

The `title` slot content can be defined in any template by calling the
~`set()`~ method:

    [php]
    <?php $this->set('title', 'Hello World! Template') ?>

Of course, slots must be defined before they are used. It works as expected
because the main template is executed first, and the layout is then applied.

>**NOTE**
>The `content` slot used by the layout mechanism is a standard slot, for
>which the content is defined in a template without using the `set()`
>method.

When the content is more complex, you can use the ~`start()`~/~`stop()`~ pair
of methods to make the code more readable:

    [php]
    Hello <?php echo $name ?>

    <?php $this->start('title') ?>
      Hello World!
    <?php $this->stop() ?>

A slot can be defined in any template/layout. The `output()` and ~`get()`~
method returns `false` when the slot is not yet defined, and allows for the
definition of a default content:

    [php]
    <?php $this->output('title', 'Default Title') ?>

    <?php echo $this->get('title', 'Default Title') ?>

    <?php if (!$this->output('title')): ?>
      Default Title
    <?php endif; ?>

The latest example is a shortcut for the frequent following idiom:

    [php]
    <?php if ($this->has('title')): ?>
      <?php $this->output('title') ?>
    <?php else: ?>
      Default Title
    <?php endif; ?>

A slot can also be used in the same template as the one where it is defined.
This can be useful for instance to avoid code duplication within a template:

    [php]
    <?php $this->start('pagination_bar') ?>
      The pagination bar content
    <?php $this->stop() ?>

    <?php $this->output('pagination') ?>

    The list that is paginated

    <?php $this->output('pagination') ?>

~Multiple Inheritance|inheritance (multiple)~
---------------------------------------------

Layouts are very powerful as they can be nested as much as needed. In other
terms, **a layout can be decorated by another layout**. Combined with the
usage of slots, it makes for a very powerful inheritance and overloading
system.

It is very useful for instance when you manage a very big website with a lot
of sections. In such a case, several layouts can be used to achieve greater
flexibility:

 * A base layout defines several slots;

 * Section specific layouts define slot contents that are the same for all
   templates of the given section and are decorated by the base layout;

 * The section templates inherit from the section specific layout.

Let's see an example. All templates of the "articles" section inherits from
the articles layout:

    [php]
    <!-- articles/index.php -->
    <?php $this->extend('articles/layout') ?>

    <?php $this->set('title', 'Index') ?>

    Articles index content

The template also define the title of the page by setting a `title` slot.

The articles layout is itself decorated by a base layout.

    [php]
    <!-- articles/layout.php -->
    <?php $this->extend('base') ?>

    <?php $this->set('title', 'Articles | '.$this->get('title')) ?>

    <?php $this->stylesheets->add('/css/articles.css') ?>

    <?php $this->output('content') ?>

This layout also defined a specific stylesheet for all articles templates,
and prefixes the page title with "Articles".

>**CAUTION**
>Don't forget to add the decorated template output in all intermediate
>layouts.

Eventually, the `base.php` layout decorates all other templates:

    [php]
    <!-- base.php -->
    <html>
      <head>
        <title><?php $this->output('title') ?></title>

        <?php $this->stylesheets->output() ?>
      </head>
      <body>
        <?php $this->output('content') ?>
      </body>
    </html>

The generated HTML should read as follows:

    [php]
    <html>
      <head>
        <title>Articles | Index</title>
        <link rel="stylesheet" type="text/css" media="all" href="/css/articles.css" />
      </head>
      <body>
        Articles index content
      </body>
    </html>

In this simple example, you have seen how to define specific stylesheets for
several templates, and how to prefix the page title for a section. This
technique is very powerful and flexible and can also be used to define
JavaScripts, sidebar contents, or anything else that makes sense for your
project.

>**TIP**
>To add a stylesheet in a template, you should use the
>[stylesheet helper](#chapter_04_built_in_template_helpers) as explained in
>the next chapter.

Helpers
-------

The template engine supports **helpers**. Helpers are special objects that are
accessible in every template as properties (even in layouts):

    [php]
    $engine = new sfTemplateEngine($loader);

    $helperSet = new sfTemplateHelperSet(array(
      new sfTemplateHelperJavascripts(),
    ));

    $engine->setHelperSet($helperSet);

    // in a template
    <?php $this->javascripts->add('/js/foo.js') ?>

More information about template helpers can be found in its
[dedicated chapter](#chapter_03).

~Escaping|escaping~ and ~Charset|charset~
-----------------------------------------

By default, the template engine does not escape the variables passed to the
template. Some template renderers automatically escape the variables for you,
but the default PHP one does not. To escape a variable, you can use the
~`escape()`~ method:

    [php]
    <?php echo $this->escape($name) ?>

>**NOTE**
>The `escape()` method is only able to escape strings. For all other
>values, it returns the value unchanged.

By default, the escaping assumes that your templates and variables are encoded
in UTF-8. This can be changed by using the ~`setCharset()`~ method:

    [php]
    $engine->setCharset('ISO-8859-1');
