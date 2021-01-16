<?php
/**
 * Description of Element
 *
 * @author greg
 * @package 
 */

abstract class Daq_Form_Element
{
    const TYPE_TEXT = "text";
    const TYPE_RADIO = "radio";
    const TYPE_CHECKBOX = "checkbox";
    const TYPE_SELECT = "select";
    const TYPE_FILE = "file";
    const TYPE_TEXTAREA = "textarea";
    const TYPE_HIDDEN = "hidden";
    const TYPE_PASSWORD = "password";

    protected $_attr = array();
    
    protected $_validator = array();

    protected $_filter = array();

    protected $_name = "";

    protected $_value = null;

    protected $_errors = array();

    protected $_hasErrors = null;

    protected $_required = false;
    
    protected $_trashed = false;
    
    protected $_builtin = true;

    protected $_label = null;

    protected $_hint = null;

    protected $_visible = true;
    
    protected $_css = array();
    
    protected $_renderer = null;
    
    protected $_order = 0;
    
    protected $_overload = null;
    
    protected $_meta = array();

    /*
    abstract public function render();
    abstract public function validate();
    abstract public function getType();
     * 
     */
    
    public function __construct($name)
    {
        $this->_name = $name;
    }
    
    public function getTypeTag()
    {
        return "input-".$this->getType();
    }

    /**
     * Check if element is visible
     *
     * @return bool
     */
    public function isVisible()
    {
        return (bool)$this->_visible;
    }

    /**
     * Sets element visibility
     *
     * @param bool $visible
     */
    public function setVisible($visible)
    {
        $this->_visible = (bool)$visible;
    }

    public function addValidator(Daq_Validate_Interface $validator)
    {
        $this->_validator[get_class($validator)] = $validator;
        return $this;
    }
    
    public function removeValidator($validator)
    {
        if($validator instanceof Daq_Validate_Interface) {
            $remove = get_class($validator);
        } else {
            $remove = $validator;
        }
        
        if(isset($this->_validator[$remove])) {
            unset($this->_validator[$remove]);
        }
    }
    
    public function getValidators() 
    {
        return $this->_validator;
    }

    public function addFilter(Daq_Filter_Interface $filter)
    {
        $this->_filter[get_class($filter)] = $filter;
        return $this;
    }
    
    public function removeFilter($filter)
    {
        throw new Exception("Not implemented yet!");
    }
    
    public function getFilters()
    {
        return $this->_filter;
    }

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function pushError($error)
    {
        $this->_errors[] = $error;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function hasErrors()
    {
        return (bool)count($this->_errors);
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setRequired($bool = true)
    {
        $this->_required = $bool;
    }

    public function isRequired()
    {
        return $this->_required;
    }

    public function getLabel()
    {
        return $this->_label;
    }

    public function setLabel($label)
    {
        $this->_label = $label;
    }

    public function getHint()
    {
        return $this->_hint;
    }

    public function hasHint()
    {
        if($this->_hint === null) {
            return false;
        }
        return true;
    }

    public function setHint($hint)
    {
        $this->_hint = $hint;
    }
    
    public function addClass($class)
    {
        $this->_css[] = $class;
    }
    
    public function hasClass($class)
    {
        return in_array($class, $this->_css);
    }
    
    public function getClasses($toString = true)
    {
        if($toString) {
            return join(" ", $this->_css);
        } else {
            return $this->_css;
        }
    }
    
    public function hasRenderer()
    {
        return !is_null($this->_renderer);
    }
    
    public function setRenderer($renderer)
    {
        if(!is_callable($renderer)) {
            throw new Exception("Given argument is not a valid callback");
        }
        
        $this->_renderer = $renderer;
    }
    
    public function unsetRenderer()
    {
        $this->_renderer = null;
    }
    
    public function getRenderer($options = array())
    {
        return $this->_renderer;
    }
    
    public function setAttr($key, $value = null)
    {
        if(!is_array($key)) {
            $key = array($key=>$value);
        }
        
        foreach($key as $k => $v) {
            $this->_attr[$k] = $v;
        }
    }
    
    public function getAttr($key = null)
    {
        if($key === null) {
            return $this->_attr;
        } elseif(isset($this->_attr[$key])) {
            return $this->_attr[$key];
        }
    }
    
    public function setOrder($order) 
    {
        $this->_order = $order;
    }
    
    public function getOrder()
    {
        return $this->_order;
    }
    
    public function overload(array $data)
    {
        $this->_overload = $data;
        
        if(isset($data["title"])) {
            $this->setLabel($data["title"]);
        }
        
        if(isset($data["hint"])) {
            $this->setHint($data["hint"]);
        }
        
        if(isset($data["renderer"])) {
            $this->setRenderer($data["renderer"]);
        }
        
        if(isset($data["is_required"]) && $data["is_required"]) {
            $this->setRequired(true);
        } else {
            $this->setRequired(false);
        }
        
        if(isset($data["is_trashed"]) && $data["is_trashed"]) {
            $this->setTrashed(true);
        } else {
            $this->setTrashed(false);
        }
        
        $this->setOrder($data["order"]);
    }
    
    public function setBuiltin($builtin = true)
    {
        $this->_builtin = $builtin;
    }
    
    public function isBuiltin() 
    {
        return $this->_builtin;
    }
    
    public function setTrashed($trashed = true)
    {
        $this->_trashed = $trashed;
    }
    
    public function isTrashed() 
    {
        return $this->_trashed;
    }
    
    public function dump()
    {
        $f = new stdClass();
        $f->title = $this->getLabel();
        $f->name = $this->getName();
        $f->hint = $this->getHint();
        $f->default = $this->getValue();
        $f->css = $this->getClasses();
        $f->is_required = $this->isRequired();
        $f->is_trashed = $this->isTrashed();
        $f->is_builtin = $this->isBuiltin();
        $f->type = "ui-".$this->getTypeTag();
        
        if(isset($this->_overload["visibility"])) {
            $f->visibility = $this->_overload["visibility"];
        }
        
        return $f;
    }
    
    public function __toString()
    {
        return $this->render();
    }
    
    public function addMeta($name, $value) {
        $this->_meta[$name] = $value;
    }
    
    public function getMeta($name) {
        if(isset($this->_meta[$name])) {
            return $this->_meta[$name];
        } else {
            return null;
        }
    }
    
}


?>