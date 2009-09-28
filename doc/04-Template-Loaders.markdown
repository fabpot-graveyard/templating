Template Loaders
================

A **template ~loader~** knows how to find templates based on their logical
names. In this chapter, you will first learn about the built-in loaders. Then,
you will learn how to create your own template loader.

Built-in Template Loaders
-------------------------

### ~`sfTemplateLoaderFilesystem`~

As most of the time, templates are stored on the filesystem, the built-in
`sfTemplateLoaderFilesystem` loader class provides a simple filesystem
template loader:

    [php]
    $loader = new sfTemplateLoaderFilesystem(dirname(__FILE__).'/templates/%name%.php');

The `sfTemplateLoaderFilesystem` class takes an array of template path
patterns as its constructor argument (or a single path as shown in the
previous example).

In a path pattern, the ~`%name%`~ placeholder represents the ~logical template
name~.

When searching for a template, the template loader looks for the template in
all paths in turn and use the first one it finds. It allows for a simple but
effective overriding and fallback mechanism, where a default template can be
overridden by another one:

    [php]
    $loader = new sfTemplateLoaderFilesystem(array(
      dirname(__FILE__).'/templates/%name%.php',
      dirname(__FILE__).'/templates/default/%name%.php',
    ));

In the above example, the loader will first look in the `templates/`
sub-directory of the current directory, and if no file is found, it will try
the `templates/default/` directory before giving up.

The path patterns can also contain the ~`%renderer%`~ placeholder, which
represent the renderer name:

    [php]
    $loader = new sfTemplateLoaderFilesystem(dirname(__FILE__).'/templates/%name%.%renderer%');

It will be replaced with the renderer, or `php` if none is given in the
`render()` call.

### ~`sfTemplateLoaderChain`~

The `sfTemplateLoaderChain` class is a loader that wraps several other
template loader instances. It calls all template loaders in turn until one is
able to find and load the template.

Imagine a CMS where templates can be stored in three different places:

  * under the `templates/default/` directory for default templates bundled
    with the CMS;

  * under the `templates/` directory for templates defined by the developers;

  * in a database for templates customized by the webmaster via the web
    administration interface.

When loading a template, the template loader must first see if it exists in
the database, and if not, fallback to the filesystem:

    [php]
    $loader = new sfTemplateLoaderChain($dispatcher, array(
      new DatabaseTemplateLoaderDatabase($dispatcher),
      new sfTemplateLoaderFilesystem($dispatcher, array(
        'templates/%s.php',
        'templates/default/%s.php',
      )),
    ));

>**NOTE**
>The creation of a custom template loader like the
>`DatabaseTemplateLoaderDatabase` one is described
>[later on](#chapter_04_a_custom_database_template_loader).

More template loaders can also be added dynamically by using the
~`addLoader()`~ method:

    [php]
    $loader->addLoader($loader);

>**NOTE**
>Note that the loader instances registration order matters.

### ~`sfTemplateLoaderCache`~

When you have a long chain of template loaders, or if the cost of retrieving
the template is high, the `sfTemplateLoaderCache` class can wrap the loader to
cache the result for sub-sequent calls.

The `sfTemplateLoaderCache` constructor takes a template loader and a cache
directory as its arguments:

    [php]
    $loader = new sfTemplateLoaderCache($loader, $cacheDir);

The first time a template is loaded, the template loader cache will call the
wrapped loader, and will store the resulting template in the cache. For
sub-sequent requests, a call to load the same template will hit the cache,
and bypass the wrapped loader all-together.

>**TIP**
>If you use a PHP renderer with a non-filesystem loader, the speed-up can be
>significant as the template will be required (`require()`) and not evaluated
>(`eval()`) by the renderer, which means that a PHP accelerator will be able
>to cache the op-codes.

This loader is also able to **compile** templates if the embedded loader
supports it. Combined with a PHP accelerator, it means that the overhead of
using a non-PHP template language is nil. Read the
[section](#chapter_04_sub_template_compilation) to learn more on implementing
your own compilable loader.

Custom Template Loaders
-----------------------

By default, the templating framework comes bundled with the most basic and
common template loaders, but everything has been done to ease the extension
and customization of them.

The only requirement for a template loader class is to implement the
~`sfTemplateLoaderInterface`~ interface, defined as follows:

    [php]
    interface sfTemplateLoaderInterface
    {
      function load($template, $renderer = 'php');
    }

>**TIP**
>All built-in template loaders inherit from the ~`sfTemplateLoader`~
>abstract base class, which provides a debugger interface.

The ~`load()`~ method must return a `sfTemplateStorage` instance.

### Template ~Storages|storage~

By default, two storage classes are bundled: ~`sfTemplateStorageFile`~ and
~`sfTemplateStorageString`~. Both extend the ~`sfTemplateStorage`~ base class
and implement the `__toString()` method. The `sfTemplateStorageFile` returns
the template file path and the `sfTemplateStorageString` returns the template
as a string.

It is up to the template engine to do something with the template returned by
the loader.

### A Custom Database Template Loader

As an example, the following code shows how to implement a template loader for
templates stored in a database table:

    [php]
    class DatabaseTemplateLoader extends sfTemplateLoader
    {
      protected $pdo = null;

      public function __construct(PDO $pdo)
      {
        $this->pdo = $pdo;
      }

      public function load($template)
      {
        $stmt = $this->pdo->prepare('SELECT template FROM template WHERE name = :name');
        try
        {
          $stmt->execute(array('name' => $template));
          if (count($rows = $stmt->fetchAll(PDO::FETCH_NUM)))
          {
            if ($this->options['logging'])
            {
              $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Loading template "%s" from database', $template))));
            }

            return new sfTemplateStorageString($rows[0][0]);
          }
        }
        catch (PDOException $e)
        {
          // not found
        }

        if ($this->debugger)
        {
          $this->debugger->log(sprintf('Failed loading template "%s" from database', $template));
        }

        return false;
      }
    }

In this example, the database table has been hardcoded to `template`, and the
columns are `name` for the template logical name and `template` for the
template content itself. Here is a small usage example:

    [php]
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE template (name, template)');
    $pdo->exec('INSERT INTO template (name, template) VALUES ("index", "Hello <?php echo $name ?>")');

    $loader = new DatabaseTemplateLoader($pdo);

    $engine = new sfTemplateEngine($loader);

    echo $engine->render('index', array('name' => 'Fabien'));

If a template loader is not able to locate the template, it must return
`false` to allow template loader chaining.

### A Custom Var Template Loader

To demonstrate the loaders power, let's implement a template loader that looks
for templates in methods defined in the loader itself. First, here is the
base loader class:

    [php]
    abstract class ProjectTemplateLoaderVar extends sfTemplateLoader
    {
      public function load($template, $renderer = 'php')
      {
        if (method_exists($this, $method = 'getTemplate'.$template))
        {
          return new sfTemplateStorageString($this->$method());
        }

        return false;
      }
    }

The `load()` method checks that a methods named `getTemplateXXX()`, where
`XXX` is the template name, exists. If it exists, it calls the method and
returns the returned value. If not, it returns false, so that other loaders
can have a chance to load the template.

Using the loader is as simple as defining some methods that returns templates:

    [php]
    class ProjectTemplateLoader extends ProjectTemplateLoaderVar
    {
      public function getTemplateIndex()
      {
        return <<<EOF
    <?php \$this->extend('layout') ?>
    Hello <?php $name ?>
    EOF;
      }

      public function getTemplateLayout()
      {
        return <<<EOF
    <?php echo \$this->get('content') ?>
    EOF;
      }
    }

You can now use it like any other loader:

    [php]
    $engine = new sfTemplateEngine(new ProjectTemplateLoader());
    $engine->render('index', array('name' => 'Fabien'));

### Template ~Compilation|compilation~

Imagine templates written with a simple grammar: all `{{ var }}` placeholders
are replaced with the value of the corresponding variable:

    [php]
    Hello {{ name }}

>**NOTE**
>Of course, to render such a template, you will need to create a custom
>template renderer, like the one detailed in next chapter. But as you will see
>later on, this is not needed, as the template will be converted to PHP by the
>loader before it gets rendered.

If the templates are stored on the filesystem, loading them is easy. But as
the template is not a PHP file, the overhead needed to render it can be quite
high, especially if you add more features to the grammar. Moreover, PHP
accelerators won't be able to cache the op-codes as the evaluation will be
done on the fly by the renderer.

In such a situation, you can create a custom template loader that is able to
"compile" the templates to PHP. Compilable-aware loaders should implements the
~`sfTemplateLoaderCompilableInterface`~ interface:

    [php]
    class ProjectTemplateLoader extends sfTemplateLoaderFilesystem implements sfTemplateLoaderCompilableInterface
    {
      public function compile($template)
      {
        return preg_replace('/{{\s*([a-zA-Z0-9_]+)\s*}}/', '<?php echo $$1 ?>', $template);
      }
    }

>**TIP**
>In the previous example, the `ProjectTemplateLoader` class extends
>`sfTemplateLoaderFilesystem`. Of course, you can extends the
>`sfTemplateLoaderChain` to allow your templates to be stored anywhere.

This loader can be used like any other one as shown below:

    [php]
    $engine = new sfTemplateEngine(new ProjectTemplateLoader(), array('project' => new ProjectTemplateRenderer()));

    print $engine->render('project:index', array('name' => 'Fabien'));

But, as the loader implements the ~`sfTemplateLoaderCompilableInterface`~
interface, the `sfTemplateLoaderCache` class will convert the raw format of
the template to a "compiled" one before caching it:

    [php]
    $engine = new sfTemplateEngine(new sfTemplateLoaderCache(new ProjectTemplateLoader(), sys_get_temp_dir()));

    print $engine->render('project:index', array('name' => 'Fabien'))."\n";

The ~`compile()`~ method must returns a valid PHP string.

The first time a template is rendered, the cache loader converts the content
of the template to PHP by calling the `compile()` method of the loader, and
the resulting PHP is cached on the filesystem. As the template stored in the
cache is now in PHP, the cache loader also automatically changes the renderer
to `php`. That way, there won't be any overhead for subsequent calls to the
same template. It also means that you don't even need to write a custom
renderer.

Debugging
---------

If you abuse multiple-inheritance and template loader chaining, it might
become difficult to troubleshoot errors. Thankfully, the template loaders
accept a ~debugger~ (if they extends the `sfTemplateLoader` class like all
built-in loaders):

    [php]
    $loader->setDebugger($debugger);

A debugger must implements the `sfTemplateDebuggerInterface`:

    [php]
    interface sfTemplateDebuggerInterface
    {
      function log($message);
    }
