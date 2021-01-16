<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Overload
 *
 * @author greg
 */
class Daq_Form_Overload 
{
    protected $_name = null;
    
    protected $_data = null;
    
    public function __construct($name)
    {
        $this->_name = $name;
        if(function_exists("get_option")) {
            $this->_data = get_option($name);
        } 
        
        if(!is_array($this->_data)) {
            $this->_data = array();
        }
    }
    
    public function hasField($field) 
    {
        if($field instanceof Daq_Form_Element) {
            $name = $field->getName();
        } else {
            $name = $field;
        }
        
        if(isset($this->_data["field"][$name])) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getField($field)
    {
        if($field instanceof Daq_Form_Element) {
            $name = $field->getName();
        } else {
            $name = $field;
        }
        
        if(!$this->hasField($name)) {
            throw new Exception("Field [$name] is not being overloaded.");
        }
        
        return $this->_data["field"][$name];
    }
    
    public function hasGroup($group)
    {
        if(empty($group)) {
            return false;
        }
        
        if(isset($this->_data["group"][$group])) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getGroup($group = null) 
    {
        if(!isset($this->_data["group"])) {
            return null;
        }
        
        if($group === null) {
            return $this->_data["group"];
        } else {
            return $this->_data["group"][$group];
        }
    }

}

?>
