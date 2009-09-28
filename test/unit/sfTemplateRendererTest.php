<?php

/*
 * This file is part of the symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__).'/../lib/lime/lime.php';
require_once dirname(__FILE__).'/../../lib/sfTemplateAutoloader.php';
sfTemplateAutoloader::register();
require_once dirname(__FILE__).'/lib/SimpleHelper.php';

$t = new lime_test(3);

class ProjectTemplateRenderer extends sfTemplateRenderer
{
  public function getEngine()
  {
    return $this->engine;
  }

  public function evaluate(sfTemplateStorage $template, array $parameters = array())
  {
  }
}

$loader = new sfTemplateLoaderFilesystem(array(dirname(__FILE__).'/fixtures/templates/%name%.%renderer%'));
$engine = new sfTemplateEngine($loader);
$engine->set('foo', 'bar');
$engine->getHelperSet()->set(new SimpleHelper('foo'), 'bar');

// ->setEngine()
$t->diag('->setEngine()');
$renderer = new ProjectTemplateRenderer();
$renderer->setEngine($engine);
$t->ok($renderer->getEngine() === $engine, '->setEngine() sets the engine instance tied to this renderer');

// __call()
$t->diag('__call()');
$renderer = new ProjectTemplateRenderer();
$renderer->setEngine($engine);
$t->is($renderer->get('foo'), 'bar', '__call() proxies to the embedded engine instance');

// __get()
$t->diag('__get()');
$renderer = new ProjectTemplateRenderer();
$renderer->setEngine($engine);
$t->is((string) $renderer->bar, 'foo', '__get() proxies to the embedded engine instance');
