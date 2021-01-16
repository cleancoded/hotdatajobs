<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Hidden
 *
 * @author greg
 */
class Daq_Form_Element_Hidden extends Daq_Form_Element implements Daq_Form_Element_Interface
{
    public final function getType()
    {
        return "hidden";
    }
    
    public function dump()
    {
        return parent::dump();
    }
    
    public function overload(array $data) 
    {
        parent::overload($data);
    }
    
    public function render() 
    {
        $options = array(
            "id" => $this->getName(),
            "name" => $this->getName(),
            "class" => $this->getClasses(),
            "value" => $this->getValue(),
            "type" => "hidden"
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
        } else {
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
