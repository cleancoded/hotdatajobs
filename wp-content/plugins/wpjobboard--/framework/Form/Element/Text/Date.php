<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Date
 *
 * @author Grzegorz
 */
class Daq_Form_Element_Text_Date extends Daq_Form_Element_Text
{
    private $_date_format = "Y-m-d";
    
    public function setDateFormat($format)
    {
        $this->_date_format = $format;
    }
    
    public function getDateFormat()
    {
        return $this->_date_format;
    }
    
    public function render() 
    {
        try {
            $date = new DateTime($this->getValue());
            $value = wpjb_date($date->format($this->getDateFormat()));
        } catch(Exception $e) {
            $value = $this->getValue();
        }
        
        $options = array(
            "id" => $this->getName(),
            "name" => $this->getName(),
            "class" => $this->getClasses(),
            "value" => $value,
            "type" => "text"
        );

        $options += $this->getAttr();
        
        $input = new Daq_Helper_Html("input", $options);
        
        return $input->render();
    }
    
    public function validate()
    {
        $this->addValidator(new Daq_Validate_Date);
        $this->addFilter(new Daq_Filter_Date($this->getDateFormat()));
        
        return parent::validate();
    }
}

?>
