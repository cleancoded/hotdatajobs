<?php

class Daq_Form_Element_Select extends Daq_Form_Element_Multi implements Daq_Form_Element_Interface
{
    protected $_maxChoices = 1;
    
    protected $_emptyOption = false; 
    
    protected $_emptyOptionText = null; 
    
    protected $_optgroup = array( "default" => "" );
    
    public final function getType()
    {
        return "select";
    }
    
    public function setEmptyOption($option)
    {
        $this->_emptyOption = (bool)$option;
    }
    
    public function setEmptyOptionText($text) {
        $this->_emptyOptionText = $text;
    }
    
    public function getEmptyOptionText() {
        return $this->_emptyOptionText;
    }
    
    public function hasEmptyOption()
    {
        return $this->_emptyOption;
    }
    
    public function getOptgroup() {
        return $this->_optgroup;
    }
    
    public function addOptgroup( $id, $label ) {
        $this->_optgroup[$id] = $label;
    }
    
    public function render()
    {
        $html = "";
        $name = $this->getName();
        $multiple = false;
        $classes = $this->getClasses();
        
        if($this->isCute()) {
            $classes .= " daq-multiselect-cute "; 
        }
        
        if($this->getMaxChoices()>1) {
            wp_enqueue_script("wpjb-vendor-selectlist");
            $max = $this->getMaxChoices();
            $name .= "[]";
            $multiple = "multiple";
            $classes = "$classes daq-multiselect daq-max-choices[$max]";
        }
        
        $options = array(
            "id" => $this->getName(),
            "name" => $name,
            "class" => $classes,
            "multiple" => $multiple
        );
        
        $options += $this->getAttr();
        
        if($this->hasEmptyOption() && $this->getMaxChoices()<=1) {
            if($this->getEmptyOptionText()) {
                $emptyText = $this->getEmptyOptionText();
            } else {
                $emptyText = "&nbsp;";
            }
            $html .= '<option value="" class="daq-multiselect-empty-option">'.esc_html($emptyText).'</option>'; 
        }
        
        foreach( $this->getOptgroup() as $group_id => $group_label) {
            
            $group_options = "";
            
            foreach($this->getOptions() as $k => $v) {
                
                if( ( !isset($v['param']) && $group_id == "default" ) || $v['param'] == $group_id ) {
                
                    $selected = null;
                    if(in_array($v["value"], (array)$this->getValue())) {
                        $selected = "selected";
                    }
                    $o = new Daq_Helper_Html("option", array(
                        "value" => $v["value"],
                        "selected" => $selected,
                    ), $v["desc"]);

                    $group_options .= $o->render();
                }
            }
            
            $go = new Daq_Helper_Html("optgroup", array(
                    "label" => $group_label,
                ), $group_options);
            
            if( $group_id == "default" ) {
                $html .= $group_options;
            } else {
                $html .= $go->render();
            } 
        }
        
        $input = new Daq_Helper_Html("select", $options, $html);
        
        return $input->render();
    }

    public function overload(array $data)
    {
        parent::overload($data);
        
        if(isset($data["select_choices"]) && $data["select_choices"]) {
           $this->setMaxChoices($data["select_choices"]); 
        }
        if(isset($data["empty_option"]) && $data["empty_option"]) {
            $this->setEmptyOption($data["empty_option"]);
        }
        if(isset($data["empty_option_text"]) && $data["empty_option_text"]) {
            $this->setEmptyOptionText($data["empty_option_text"]);
        }
    }
    
    public function dump()
    {
        $dump = parent::dump();
        $dump->empty_option = $this->_overload["empty_option"];
        
        return $dump;
    }
    
    public function validate()
    {
        return parent::validate();        
    }
}

