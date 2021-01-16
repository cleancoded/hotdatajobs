<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Text
 *
 * @author greg
 */
class Daq_Form_Element_Text extends Daq_Form_Element implements Daq_Form_Element_Interface
{

    public final function getType()
    {
        return "text";
    }
    
    public function dump()
    {
        $data = parent::dump();
        
        $allowed = array(
            "Daq_Validate_Email",
            "Daq_Validate_Url",
            "Daq_Validate_Float",
            "Daq_Validate_Int",
            "Daq_Validate_Date",
        );
        
        foreach($this->getValidators() as $v) {
            /* @var $v Daq_Validate_Abstract */

            $class = get_class($v);
            if(in_array($class, $allowed)) {
                $data->validation_rules = $class;
            }
        }
        
        $data->placeholder = $this->getAttr("placeholder");
        $data->validation_min_size = $this->getAttr("validation_min_size");
        $data->validation_max_size = $this->getAttr("validation_max_size");

        return $data;
    }
    
    public function overload(array $data) 
    {
        if(isset($data["validation_rules"]) && class_exists($data["validation_rules"])) {
            $class = $data["validation_rules"];

            if($class == "Daq_Validate_Date") {
                $object = new $class(wpjb_date_format());
            } else {
                $object = new $class;
            }
            
            $this->addValidator($object);
        }
        
        if(isset($data["validation_rules"]) && $data["validation_rules"]=="Daq_Validate_Date") {
            $this->addClass("daq-date-picker");
            wp_enqueue_script("wpjb-vendor-datepicker", false, array(), false, true);
            wp_enqueue_style("wpjb-vendor-datepicker", false, array(), false, true);
        }
        
        if(!empty($data["placeholder"])) {
            $this->setAttr("placeholder", $data["placeholder"]);
        }
        
        if(!empty($data["validation_min_size"])) {
            $this->setAttr("validation_min_size", $data["validation_min_size"]);
        }
        
        if(!empty($data["validation_max_size"])) {
            $this->setAttr("validation_max_size", $data["validation_max_size"]);
        }
        
        if( !empty( $data['validation_min_size'] ) || !empty( $data['validation_max_size'] ) ) {
            $this->addValidator( new Daq_Validate_StringLength( $data['validation_min_size'], $data['validation_max_size'] ) );
        }
        
        parent::overload($data);
    }
    
    public function render() 
    {
        $options = array(
            "id" => $this->getName(),
            "name" => $this->getName(),
            "class" => $this->getClasses(),
            "value" => $this->getValue(),
            "type" => "text"
        );
        
        $options += $this->getAttr();
        
        $input = new Daq_Helper_Html("input", $options);
        
        return $input->render();
    }
    
    public function validate()
    {
        $this->_hasErrors = false;
        
        $value = $this->getValue();
        $value = trim($value);
        $this->setValue($value);
        
        if(empty($value) && !$this->isRequired()) {
            return true;
        } elseif($this->isRequired()) {
            $this->addValidator(new Daq_Validate_Required());
        }
        
        foreach($this->getFilters() as $filter) {
            $value = $filter->filter($value);
        }
        
        $this->setValue($value);
        
        foreach($this->getValidators() as $validator) {
            if(!$validator->isValid($value)) {
                $this->_hasErrors = true;
                $this->_errors = $validator->getErrors();
            }
        }

        return !$this->_hasErrors;
    }
    
}

?>
