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
class Daq_Form_Element_Label extends Daq_Form_Element implements Daq_Form_Element_Interface
{
    protected $_labelDescription = "";

    public final function getType()
    {
        return "label";
    }
    
    public function getDescription() {
        return $this->_labelDescription;
    }
    
    public function setDescription($description) {
        $this->_labelDescription = $description;
    }
    
    public function dump()
    {
        $data = parent::dump();
        $data->description = $this->getDescription();
        
        return $data;
    }
    
    public function overload(array $data) 
    {
        if(isset($data["description"])) {
            $this->setDescription($data["description"]);
        }
        parent::overload($data);
    }
    
    public function render() 
    {
        $options = array(
            "id" => $this->getName(),
            "class" => $this->getClasses(),
        );
        
        $options += $this->getAttr();
        
        $input = new Daq_Helper_Html("span", $options, $this->getDescription());
        
        return $input->render();
    }
    
    public function validate()
    {
        return true;
    }
    
}

?>
