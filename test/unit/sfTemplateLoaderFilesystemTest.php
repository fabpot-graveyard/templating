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
require_once dirname(__FILE__).'/lib/ProjectTemplateDebugger.php';

$t = new lime_test(15);

class ProjectTemplateLoader extends sfTemplateLoaderFilesystem
{
  public function getTemplatePathPatterns()
  {
    return $this->templatePathPatterns;
  }

  static public function isAbsolutePath($path)
  {
    return parent::isAbsolutePath($path);
  }
}

// ->isAbsolutePath()
$t->diag('->isAbsolutePath()');
$t->ok(ProjectTemplateLoader::isAbsolutePath('/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
$t->ok(ProjectTemplateLoader::isAbsolutePath('c:\\\\foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
$t->ok(ProjectTemplateLoader::isAbsolutePath('c:/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
$t->ok(ProjectTemplateLoader::isAbsolutePath('\\server\\foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');

// __construct()
$t->diag('__construct()');
$pathPattern = dirname(__FILE__).'/fixtures/templates/%name%.%renderer%';
$path = dirname(__FILE__).'/fixtures/templates';
$loader = new ProjectTemplateLoader($pathPattern);
$t->is($loader->getTemplatePathPatterns(), array($pathPattern), '__construct() takes a path as its second argument');
$loader = new ProjectTemplateLoader(array($pathPattern));
$t->is($loader->getTemplatePathPatterns(), array($pathPattern), '__construct() takes an array of paths as its second argument');

// ->load()
$t->diag('->load()');
$loader = new ProjectTemplateLoader($pathPattern);
$storage = $loader->load($path.'/foo.php');
$t->ok($storage instanceof sfTemplateStorageFile, '->load() returns a sfTemplateStorageFile if you pass an absolute path');
$t->is((string) $storage, $path.'/foo.php', '->load() returns a sfTemplateStorageFile pointing to the passed absolute path');

$t->ok($loader->load('bar') === false, '->load() returns false if the template is not found');

$storage = $loader->load('foo');
$t->ok($storage instanceof sfTemplateStorageFile, '->load() returns a sfTemplateStorageFile if you pass a relative template that exists');
$t->is((string) $storage, $path.'/foo.php', '->load() returns a sfTemplateStorageFile pointing to the absolute path of the template');

$loader = new ProjectTemplateLoader($pathPattern);
$loader->setDebugger($debugger = new ProjectTemplateDebugger());
$t->ok($loader->load('foo', 'xml') === false, '->load() returns false if the template does not exists for the given renderer');
$t->ok($debugger->hasMessage('Failed loading template'), '->load() logs a "Failed loading template" message if the template is not found');

$loader = new ProjectTemplateLoader(array(dirname(__FILE__).'/null/%name%', $pathPattern));
$loader->setDebugger($debugger = new ProjectTemplateDebugger());
$loader->load('foo');
$t->ok($debugger->hasMessage('Failed loading template'), '->load() logs a "Failed loading template" message if the template is not found');
$t->ok($debugger->hasMessage('Loaded template file'), '->load() logs a "Loaded template file" message if the template is found');
