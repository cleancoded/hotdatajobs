<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Fieldset
 *
 * @author greg
 */
class Daq_Form_Fieldset 
{
    /**
     * Fieldset title
     * 
     * @var string 
     */
    public $title = "";
    
    /**
     * Fieldset name/id
     *
     * @var string 
     */
    protected $_name = "";
    
    /**
     * Fieldset order
     * 
     * The lower the order number the higher in the form field will be placed.
     *
     * @var int
     */
    protected $_order = 0;
    
    /**
     * List of fieldset elements
     *
     * @var array List of Daq_Form_Element 
     */
    protected $_field = array();
    
    /**
     * Was element put in trash using VE
     *
     * @var boolean
     */
    protected $_trashed = false;
    
    /**
     * Other options
     *
     * @var stdClass 
     */
    protected $_meta = null;
    
    /**
     * Is fieldset always visible (even if empty)
     *
     * @var bool 
     */
    protected $_alwaysVisible = false;
    
    /**
     * Object constructor
     * 
     * Allowed initial parameters:
     * - name
     * - title
     * - order
     *
     * @param array $options 
     */
    public function __construct($options = array())
    {
        if(isset($options["name"])) {
            $this->setName($options["name"]);
            unset($options["name"]);
        }
        if(isset($options["order"])) {
            $this->setOrder($options["order"]);
            unset($options["order"]);
        }
        if(isset($options["title"])) {
            $this->title = $options["title"];
            unset($options["title"]);
        }
        if(isset($options["is_trashed"]) && $options["is_trashed"]) {
            $this->setTrashed();
            unset($options["is_trashed"]);
        }
        
        if(!empty($options)) {
            $this->meta = (object)$options;
        } else {
            $this->meta = new stdClass();
        }
    }
    
    public function setOrder($order)
    {
        $this->_order = (int)$order;
    }
    
    public function getOrder()
    {
        return $this->_order;
    }
    
    public function setName($name)
    {
        $this->_name = $name;
    }
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function add(Daq_Form_Element $e)
    {
        $this->_field[$e->getName()] = $e;
    }
    
    public function update(Daq_Form_Element $e)
    {
        $this->_field[$e->getName()] = $e;
    }
    
    public function has($field)
    {
        if($field instanceof Daq_Form_Element) {
            $name = $field->getName();
        } else {
            $name = $field;
        }
        
        if(isset($this->_field[$name])) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Returns form element
     *
     * @param mixed $field
     * @return Daq_Form_Element
     */
    public function get($field) 
    {
        if($this->has($field)) {
            if($field instanceof Daq_Form_Element) {
                $name = $field->getName();
            } else {
                $name = $field;
            }
            return $this->_field[$name];
        }
    }
    
    public function remove($field)
    {
        if($field instanceof Daq_Form_Element) {
            $name = $field->getName();
        } else {
            $name = $field;
        }
        
        unset($this->_field[$name]);
    }
    
    public function isEmpty()
    {
        return empty($this->_field);
    }
    
    public function hasVisibleElements($exclude = array())
    {
        if($this->isEmpty()) {
            return false;
        }
        
        foreach($this->_field as $field) {
            if($field->getType() !== Daq_Form_Element::TYPE_HIDDEN && !in_array($field->getName(), $exclude)) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getAll()
    {
        return $this->_field;
    }
    
    protected function _sort($a, $b)
    {
        $r1 = $a->getOrder();
        $r2 = $b->getOrder();
        
        if($r1>$r2) {
            return 1;
        } else {
            return -1;
        }
    }
    
    public function getVisible()
    {
        $arr = array();
        foreach($this->_field as $field) {
            if($field->getType() !== Daq_Form_Element::TYPE_HIDDEN) {
                $arr[] = $field;
            }
        }
        return $arr;
    }
    
    public function getReordered()
    {
        usort($this->_field, array($this, "_sort"));
        return $this->getVisible();
    }
    
    public function setTrashed($trashed = true)
    {
        $this->_trashed = $trashed;
    }
    
    public function isTrashed() 
    {
        return $this->_trashed;
    }
    
    public function setAlwaysVisible($visible)
    {
        $this->_alwaysVisible = $visible;
    }
    
    public function isAlwaysVisible()
    {
        return $this->_alwaysVisible;
    }

}

?>
