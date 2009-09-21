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

$t = new lime_test(7);

$engine = new sfTemplateEngine(new sfTemplateLoaderFilesystem('/'));

// __construct()
$t->diag('__construct()');
$helperSet = new sfTemplateHelperSet(array('foo' => $helper = new SimpleHelper('foo')));
$t->ok($helperSet->has('foo'), '__construct() takes an array of helpers as its first argument');

// ->setEngine()
$t->diag('->getEngine()');
$helperSet = new sfTemplateHelperSet(array('foo' => $helper = new SimpleHelper('foo')));
$t->ok($helper->getHelperSet() === $helperSet, '->__construct() changes the embedded helper set of the given helpers');

// ->get() ->set() ->has()
$t->diag('->getHelper() ->setHelper() ->has()');
$helperSet = new sfTemplateHelperSet();
$helperSet->set($helper = new SimpleHelper('bar'));
$t->ok($helper->getHelperSet() === $helperSet, '->set() changes the embedded helper set of the helper');
$t->is((string) $helperSet->get('foo'), 'bar', '->set() sets a helper value');

$t->ok($helperSet->has('foo'), '->has() returns true if the helper is defined');
$t->ok(!$helperSet->has('bar'), '->has() returns false if the helper is not defined');

try
{
  $helperSet->get('bar');
  $t->fail('->get() throws an InvalidArgumentException if the helper is not defined');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->get() throws an InvalidArgumentException if the helper is not defined');
}
