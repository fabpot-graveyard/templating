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

$t = new lime_test(1);

class ProjectTemplateLoader extends sfTemplateLoader
{
  public function load($template, $renderer = 'php')
  {
  }

  public function getDebugger()
  {
    return $this->debugger;
  }
}

// ->setDebugger()
$t->diag('->setDebugger()');
$loader = new ProjectTemplateLoader();
$loader->setDebugger($debugger = new ProjectTemplateDebugger());
$t->ok($loader->getDebugger() === $debugger, '->setDebugger() sets the debugger instance');
