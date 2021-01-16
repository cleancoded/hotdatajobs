<?php

use Facebook\PersistentData\PersistentDataInterface;

class Wpjb_Service_FacebookData implements PersistentDataInterface
{
    protected $_option = null;
    
  /**
   * @inheritdoc
   */
  public function get($key)
  {
      if(is_null($this->_option)) {
          $this->_option = get_option( "wpjb_facebook_pdi");
      }

      if(isset($this->_option[$key])) {
          return $this->_option[$key];
      } else {
          return null;
      }
  }

  /**
   * @inheritdoc
   */
  public function set($key, $value)
  {
      if(is_null($this->_option)) {
          $this->_option = get_option( "wpjb_facebook_pdi");
      }
      
      if(!is_array($this->_option)) {
          $this->_option = array();
      }
      
      $this->_option[$key] = $value;
      
      update_option("wpjb_facebook_pdi", $this->_option);
      $this->_option = null;
  }
}