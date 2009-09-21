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

$t = new lime_test(5);

class ProjectTemplateLoader extends sfTemplateLoaderChain
{
  public function getLoaders()
  {
    return $this->loaders;
  }
}

$loader1 = new sfTemplateLoaderFilesystem(dirname(__FILE__).'/null/%name%');
$loader2 = new sfTemplateLoaderFilesystem(dirname(__FILE__).'/fixtures/templates/%name%.%renderer%');

// __construct()
$t->diag('__construct()');
$loader = new ProjectTemplateLoader(array($loader1, $loader2));
$t->is($loader->getLoaders(), array($loader1, $loader2), '__construct() takes an array of template loaders as its second argument');

// ->addLoader()
$t->diag('->addLoader()');
$loader = new ProjectTemplateLoader(array($loader1));
$loader->addLoader($loader2);
$t->is($loader->getLoaders(), array($loader1, $loader2), '->addLoader() adds a template loader at the end of the loaders');

// ->load()
$t->diag('->load()');
$loader = new ProjectTemplateLoader(array($loader1, $loader2));
$t->ok($loader->load('bar') === false, '->load() returns false if the template is not found');
$t->ok($loader->load('foo', 'xml') === false, '->load() returns false if the template does not exists for the given renderer');
$t->ok($loader->load('foo') instanceof sfTemplateStorageFile, '->load() returns a sfTemplateStorageFile if the template exists');
