<?php

class Daq_Form_Element_Radio extends Daq_Form_Element_Multi implements Daq_Form_Element_Interface
{
    /**
     * Number of columns
     *
     * @var int
     */
    protected $_cols = 1;

    
    /**
     * Maximum number of choices
     *
     * @var int
     */
    protected $_maxChoices = 1;
    
    /**
     * Returns input type
     * 
     * @return string
     */
    public final function getType()
    {
        return "radio";
    }
    
    /**
     * Sets maximum number of choices
     * 
     * Due to how "radio" input works the only allowed value is "1".
     * 
     * @param int $choices
     * @throws Exception        If the maximum number is greater then 1
     */
    public function setMaxChoices($choices) 
    {
        if($choices > 1) {
            throw new Exception("Radio input cannot have more than one selected option.");
        }
        
        parent::setMaxChoices($choices);
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
            $name = $this->getName();
            
            if(in_array($v["value"], (array)$this->getValue())) {
                $checked = "checked";
            }
            
            $o = new Daq_Helper_Html("input", array(
                "type" => "radio",
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
                "class" => ($isCute ? "wpjb-cute-input wpjb-cute-radio" : "")
            ), $o->render() . $i . $span->render());
            

            $li = new Daq_Helper_Html("li", array(
                "class" => "wpjb-input-cols wpjb-input-cols-" . (int)$this->getCols()
            ), $label->render());
            
            $html[] = $li;

        }
        
        return "<ul class=\"wpjb-options-list\">" . join("", $html) . "</ul>";
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

