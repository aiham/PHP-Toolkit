<?php

class Template {

  protected $values = array(), $path;

  public function __construct ($path) {
    $this->setPath($path);
  }

  public function setPath ($path) {
    if (!is_readable($path)) {
      throw new Exception('Invalid path');
    }
    $this->path = $path;
  }

  public function assign ($key, $val = null) {
    $this->assignRef($key, $val);
  }

  public function assignRef (&$key, &$val = null) {
    if ($key === 'this') {
      throw new InvalidArgumentException;
    } else if (is_array($key)) {
      $this->values = array_merge($this->values, $key);
    } else {
      $this->values[$key] = $val;
    }
  }

  public function clearAll() {
    $this->values = array();
  }

  public function clear($key) {
    unset($this->values[$key]);
  }

  public function output($ref = false) {
    try {
      ob_start();
      extract($this->values, $ref ? EXTR_REFS : EXTR_OVERWRITE);
      @include($this->path);
    } catch (Exception $e) {
      ob_end_clean();
      throw $e; // TODO - add exception linking
    }
    return ob_get_clean();
  }

  public function display ($ref = false) {
    echo $this->output($ref);
  }

}