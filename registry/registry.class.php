<?php

class Registry {

  private $objects;

  private $settings;

  public function createAndStoreObject($object, $key) {
    require_once( $object . '.class.php' );
    $this->objects[$key] = new $object($this);
  }

  public function getObject($key) {
    return $this->objects[$key];
  }

  public function storeSetting($setting, $key) {
    $this->settings[$key] = $setting;
  }

  public function getSetting($key) {
    return $this->settings[$key];
  }
}
?>
