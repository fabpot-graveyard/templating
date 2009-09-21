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

$t = new lime_test(1);

class ProjectTemplateHelper extends sfTemplateHelper
{
  public function getName()
  {
    return 'foo';
  }
}

// ->getHelperSet() ->setHelperSet()
$t->diag('->getHelperSet() ->setHelperSet()');
$helper = new ProjectTemplateHelper();
$helper->setHelperSet($helperSet = new sfTemplateHelperSet(array($helper)));
$t->ok($helperSet === $helper->getHelperSet(), '->setHelperSet() sets the helper set related to this helper');
