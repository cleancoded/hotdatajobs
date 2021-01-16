<?php

abstract class Daq_Form_Element_Multi extends Daq_Form_Element 
{
    /**
     * Enable cute inputs
     *
     * @var boolean
     */
    protected $_is_cute = false;
    
    /**
     * Maximum number of choices
     *
     * @var int
     */
    protected $_maxChoices = 0;
    
    /**
     * Allowed: default, callback, choices
     *
     * @var string
     */
    protected $_fillMethod = "default";
    
    /**
     * Options array
     *
     * @var array
     */
    protected $_option = array();
    
    /**
     * Set maximum number of choices
     * 
     * @param int $choices
     */
    public function setMaxChoices($choices) 
    {
        $this->_maxChoices = intval($choices);
    }
    
    /**
     * Returns maximum number of choices for this input
     * 
     * @return int
     */
    public function getMaxChoices()
    {
        return $this->_maxChoices;
    }
    
    /**
     * Sets input fill method
     * 
     * @param string $method    One of "default", "callback" or "choices"
     * @throws Exception        If unallowed fill method set
     */
    public function setFillMethod($method) 
    {
        if(!in_array($method, array("default", "callback", "choices"))) {
            throw new Exception("Unknown fill method [$method].");
        }
        
        $this->_fillMethod = $method;
    }
    
    /**
     * Returns fill method
     * 
     * @return string   Fill method
     */
    public function getFillMethod() 
    {
        return $this->_fillMethod;
    }
    
    /**
     * Checks if input allows selecting multiple options
     * 
     * @return boolean
     */
    public function isMultiOption() 
    {
        return true;
    }
    
    /**
     * Adds an options to the options list
     * 
     * @param string $key       Option Key
     * @param string $value     Option value (used in "value" attribute)
     * @param string $desc      Option description used as a label in the forms
     */
    public function addOption($key, $value, $desc, $param = null)
    {
        $this->_option[] = array("key"=>$key, "value"=>$value, "desc"=>$desc, "param"=>$param);
    }
    
    /**
     * Adds multiple options to the options list
     * 
     * This function basically executes self::addOption() in a loop.
     * 
     * @see self::addOption()
     * @param array $options
     */
    public function addOptions($options)
    {
        foreach($options as $opt) {
            $this->addOption($opt["key"], $opt["value"], $opt["description"]);
        }
    }

    /**
     * Returns list of options
     * 
     * @return array    List of options
     */
    public function getOptions()
    {
        return $this->_option;
    }
    
    /**
     * Removes an option from the list
     * 
     * @param string $key   Option key
     */
    public function removeOption($key)
    {
        $c = count($this->_option);
        
        for($i=0; $i<$c; $i++) {
            if($this->_option[$i]["key"] == $key) {
                unset($this->_option[$i]);
                break;
            }
        }
    }
    
    /**
     * Returns input value as a string
     * 
     * @param string $glue      String used to join values
     * @return string           Inout value as a string
     */
    public function getValueText($glue = ", ")
    {
        $arr = array();
        $value = (array)$this->getValue();
        foreach($this->getOptions() as $option) {
            if(in_array($option["value"], $value)) {
                $arr[] = $option["desc"];
            }
        }
        
        if(empty($arr)) {
            return null;
        } else {
            return implode($glue, $arr);
        }
    }
    
    /**
     * Overloads default field values
     * 
     * This function is being used mainly with the data coming from Custom Fields 
     * editor.
     * 
     * @param array $data
     */
    public function overload(array $data) 
    {
        parent::overload($data);
        
        if(isset($data["fill_method"]) && $data["fill_method"] == "choices") {
            $this->_option = array();
            $options = explode("\n", $data["fill_choices"]);
            $options = array_map("trim", $options);
            foreach($options as $k => $option) {
                $this->addOption($k, $option, $option);
            }
        } elseif(isset($data["fill_method"]) && $data["fill_method"] == "callback") {
            if(isset($data["fill_callback"]) && is_callable($data["fill_callback"])) {
                $this->_option = array();
                $this->addOptions(call_user_func($data["fill_callback"]));
            }
        }
        
        if(isset($data["max_choices"]) && $data["max_choices"] > 0) {
            $this->setMaxChoices($data["max_choices"]);
        }
    }
    
    /**
     * Returns object as an stdClass
     * 
     * Dump function is being used to prepare the input for custom fields editor
     * 
     * @return stdClass
     */
    public function dump() 
    {
        $dump = parent::dump();
        $dump->fill_method = $this->_overload["fill_method"];
        $dump->fill_choices = $this->_overload["fill_choices"];
        $dump->fill_callback = $this->_overload["fill_callback"];
        $dump->select_choices = $this->getMaxChoices();
        
        if($dump->select_choices < 1) {
            $dump->select_choices = 1;
        }
        
        return $dump;
        
    }
    
    /**
     * Validates an input field
     * 
     * @return boolean      True if field value is OK
     */
    public function validate()
    {
        $this->_hasErrors = false;
        $count = 0;
        $arr = array();
        
        $value = (array)$this->getValue();
        foreach($value as $v) {
            
            if(is_array($v)) {
                $v = null;
            } elseif(is_string($v)) {
                $v = trim($v);
            }
            
            if(!empty($v)) {
                $count++;
                $arr[] = $v;
            }
        }

        if(empty($arr) && !$this->isRequired()) {
            return true;
        } else {
            $this->addValidator(new Daq_Validate_Required());
        }
        
        $choices = $this->getMaxChoices();
        if($choices > 0) {
            $this->addValidator(new Daq_Validate_Choices(null, $choices));
        }
        
        $allowed = array();
        foreach($this->getOptions() as $opt) {
            $allowed[] = trim($opt["value"]);
        }
        $this->addValidator(new Daq_Validate_InArray($allowed));

        foreach($this->getValidators() as $validate) {
            if(!$validate->isValid($arr)) {
                $this->_hasErrors = true;
                $this->_errors = $validate->getErrors();
                break;
            }
        }
        

        return !$this->_hasErrors;
    }
    
    /**
     * Enable cute inputs
     * 
     * @since 5.0
     * @param boolean $cute
     */
    public function setCute($cute) {
        $this->_is_cute = $cute;
    }  
    
    /**
     * Checks if cute inputs are enabled
     * 
     * @since 5.0
     * @return boolean
     */
    public function isCute() {
        return $this->_is_cute;
    }
}

