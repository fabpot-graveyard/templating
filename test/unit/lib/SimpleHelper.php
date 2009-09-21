<?php

class SimpleHelper extends sfTemplateHelper
{
  protected $value = '';

  public function __construct($value)
  {
    $this->value = $value;
  }

  public function __toString()
  {
    return $this->value;
  }

  public function getName()
  {
    return 'foo';
  }
}
