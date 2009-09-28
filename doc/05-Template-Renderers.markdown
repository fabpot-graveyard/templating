Template Renderers
==================

A **template ~renderer~** is needed to render a loaded template. In this
chapter, you will first learn about the built-in renderers. Then, you will
learn how to create your own template renderer.

Built-in Template Renderers
---------------------------

The component only has one template renderer. If you have read the previous
chapter about compilation, you already know that the PHP renderer is the only
one needed in most cases.

### ~`sfTemplateRendererPhp`~

The component comes with a simple PHP renderer:

    [php]
    $renderer = new sfTemplateRendererPhp();

To take advantage of the template engine features, you should register a
template engine with the ~`setEngine()`~ method:

    [php]
    $renderer->setEngine($engine);

This registration is automatically done when you pass renderers as an argument
to the engine constructor:

    [php]
    $engine = new sfTemplateEngine($loader, array(
      'php' => new sfTemplateRendererPhp(),
    ));

Custom Template Renderers
-------------------------

The only requirement for a template engine class is to implement the
~`sfTemplateEngineInterface`~ interface, defined as follows:

    [php]
    interface sfTemplateEngineInterface
    {
      function evaluate(sfTemplateStorage $template, array $parameters = array());

      function setEngine(sfTemplateEngine $engine);
    }

>**TIP**
>All built-in template engines inherit from the ~`sfTemplateEngine`~
>abstract base class, which provides a proxy for helpers and a way to
>automatically call the engine methods.

The `$template` must be a `sfTemplateStorage` instance.

>**NOTE**
>The previous chapter has more information about
>[template storage classes](#chapter_04_template_torages).

The ~`evaluate()`~ method must return the rendered template as a string.

As an example, the `sfTemplateEnginePhp::evaluate()` method read as follows:

    [php]
    public function evaluate(sfTemplateStorage $template, array $parameters = array())
    {
      if ($template instanceof sfTemplateStorageFile)
      {
        extract($parameters);
        ob_start();
        require $template;

        return ob_get_clean();
      }
      elseif ($template instanceof sfTemplateStorageString)
      {
        extract($parameters);
        ob_start();
        eval('; ?>'.$template.'<?php ;');

        return ob_get_clean();
      }

      return false;
    }

The `evaluate()` method must return `false` if it is not able to render the
template.

### A Simple Template Renderer

In the previous chapter, we introduced a simple template renderer, able to
render templates that have simple `{{ name }}` placeholders:

    [php]
    Hello {{ name }}

The following code implements the renderer class:

    [php]
    class ProjectTemplateRenderer extends sfTemplateRenderer
    {
      public function evaluate(sfTemplateStorage $template, array $parameters = array())
      {
        if ($template instanceof sfTemplateStorageFile)
        {
          $template = file_get_contents($template);
        }

        $this->parameters = $parameters;

        return preg_replace_callback('/{{\s*([a-zA-Z0-9_]+)\s*}}/', array($this, 'replaceParameters'), $template);
      }

      public function replaceParameters($matches)
      {
        return isset($this->parameters[$matches[1]]) ? $this->parameters[$matches[1]] : null;
      }
    }

In the `evaluate()` method, we get the content of the template on the
filesystem if the template is an instance of `sfTemplateStorageFile`. If not,
the template is an object with a `__toString()` method that returns the
content of the template.

>**NOTE**
>As seen in the previous chapter, if you implement a compilable loader, you
>don't even need to write a custom renderer.

### Using an existing Renderer Library

In this section, you will see that it's very easy to wrap an existing renderer
library. We will use [PHPTAL](http://phptal.org/) as an example. PHPTAL is an
implementation of the Zope Page Template (ZPT) system for PHP.

Create the renderer class as shown below:

    [php]
    class sfTemplateRendererPHPTAL extends sfTemplateRenderer
    {
      protected
        $tal = null;

      public function __construct(PHPTAL $tal)
      {
        $this->tal = $tal;
      }

      public function evaluate(sfTemplateStorage $template, array $parameters = array())
      {
        if ($template instanceof sfTemplateStorageFile)
        {
          $this->tal->setTemplate((string) $template);
        }
        elseif ($template instanceof sfTemplateStorageString)
        {
          $this->tal->setSource((string) $template);
        }
        else
        {
          return false;
        }

        foreach ($parameters as $key => $value)
        {
          $this->tal->$key = $value;
        }
        $this->tal->set('this', $this->engine);

        $this->tal->setEncoding($this->engine->getCharset());

        return $this->tal->execute();
      }
    }

    function phptal_tales_this($src, $nothrow)
    {
      return PHPTAL_Php_Transformer::transform('this->'.$src, '$ctx->');
    }

The code is quite straightforward:

  * The constructor takes a `PHPTAL` instance as its argument (using
    dependency injection to be more flexible);

  * Based on the template type, different `PHPTAL` methods are used to set the
    template;

  * The current template engine is always defined as `this` in templates to
    allow the use of its features;

  * The encoding is changing based on the one defined in the templating
    engine.

Using this renderer is also quite easy:

    [php]
    require_once '/path/to/sfTemplateAutoloader.php';
    sfTemplateAutoloader::register();

    require_once '/path/to/sfTemplateRendererPHPTAL.php';

    $loader = new sfTemplateLoaderFilesystem(dirname(__FILE__).'/templates/%name%.xml');

    require_once '/path/to/PHPTAL-1.2.0/PHPTAL.php';

    $tal = new PHPTAL();
    $tal->setPhpCodeDestination('/path/to/cache');

    // in debug mode
    //$tal->setForceReparse(true);

    $t = new sfTemplateEngine($loader, array(
      'tal' => new sfTemplateRendererPHPTAL($tal),
    ));

    echo $t->render('tal:index', array('name' => 'Fabien'));

Here is some basic templates using both the templating features and the PHPTAL
notation:

    [xml]
    <!-- the index template -->
    <span tal:replace="this:extend('tal:layout')" />

    <span tal:replace="this:start('title')" />
    Page title
    <span tal:replace="this:stop()" />

    Hello <span tal:replace="name" />

-

    [xml]
    <?xml version="1.0"?>
    <!-- the layout template -->
    <html>
      <head>
        <title tal:content="structure this:get('title')" />
      </head>
      <body tal:content="structure this:get('content')" />
    </html>
