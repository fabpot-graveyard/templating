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

$t = new lime_test(2);

$renderer = new sfTemplateRendererPhp();

// ->evaluate()
$t->diag('->evaluate()');

$template = new sfTemplateStorageString('<?php echo $foo ?>');
$t->is($renderer->evaluate($template, array('foo' => 'bar')), 'bar', '->evaluate() renders templates that are instances of sfTemplateStorageString');

$template = new sfTemplateStorageFile(dirname(__FILE__).'/fixtures/templates/foo.php');
$t->is($renderer->evaluate($template, array('foo' => 'bar')), 'bar', '->evaluate() renders templates that are instances of sfTemplateStorageFile');
