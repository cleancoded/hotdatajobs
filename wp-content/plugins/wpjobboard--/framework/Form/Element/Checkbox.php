<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Checkbox
 *
 * @author greg
 */
class Daq_Form_Element_Checkbox extends Daq_Form_Element_Multi 
{
    /**
     * Is input boolean (accepts only checked or unchecked state)
     *
     * @var boolean
     */
    protected $_boolean = false;

    /**
     * Number of columns
     *
     * @var int
     */
    protected $_cols = 1;

    /**
     * Turns on/off boolean mode for this checkbox.
     * 
     * @param boolean $boolean
     */
    public function setBoolean($boolean) 
    {
        $this->_boolean = $boolean;
    } 
    
    /**
     * Check if checkbox is a boolean type
     * 
     * @since 4.4.2
     * @return boolean
     */
    public function isBoolean() 
    {
        return $this->_boolean;
    }

    /**
     * Returns input type
     * 
     * @return string
     */
    public final function getType()
    {
        return "checkbox";
    }
    
    /**
     * Renders input HTML
     * 
     * @return string       Input HTML
     */
    public function render() 
    {
        if(is_admin()) {
            $isCute = false;
        } else {
            $isCute = $this->isCute();
        }
        
        $options = array(
            "id" => $this->getName(),
            "name" => $this->getName(),
            "class" => $this->getClasses()
        );
        
        $options += $this->getAttr();
        
        $html = array();
        $c = count($this->getOptions());
        
        foreach($this->getOptions() as $k => $v) {
            
            $id = $this->getName()."-".$v["key"];
            $checked = null;
            $name = $this->getName()."[]";
            
            if(in_array($v["value"], (array)$this->getValue())) {
                $checked = "checked";
            }
            
            if($this->isBoolean()) {
                $name = $this->getName();
            }
            
            $o = new Daq_Helper_Html("input", array(
                "type" => "checkbox",
                "value" => $v["value"],
                "checked" => $checked,
                "name" => $name,
                "class" => $this->getClasses(),
                "id" => $id
            ));

            if($isCute) {
                $indicator = new Daq_Helper_Html("div", array("class" => "wpjb-cute-input-indicator"), "");
                $indicator->forceLongClosing(true);
                $i = $indicator->render();
            } else {
                $i = "";
            }
            
            $span = new Daq_Helper_Html("span", array(
                "class" => "wpjb-input-description"
            ), $v["desc"]);
            
            $label = new Daq_Helper_Html("label", array(
                "for"=>$id,
                "class" => ($isCute ? "wpjb-cute-input wpjb-cute-checkbox" : "")
            ), $o->render() . $i . $span->render());
            

            $li = new Daq_Helper_Html("li", array(
                "class" => "wpjb-input-cols wpjb-input-cols-" . (int)$this->getCols()
            ), $label->render());
            
            $html[] = $li;

        }
        
        return "<ul class=\"wpjb-options-list\">" . join("", $html) . "</ul>";
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
        
        if(isset($data["select_choices"]) && $data["select_choices"]) {
           $this->setMaxChoices($data["select_choices"]); 
        }
    }
    
    /**
     * Set number of columns
     * 
     * @since 5.0
     * @param int $cols
     */
    public function setCols($cols) {
        $this->_cols = (int)$cols;
    }
    
    /**
     * Returns number of columns
     * 
     * @since 5.0
     * @return int
     */
    public function getCols() {
        return $this->_cols;
    }
    

}

?>
