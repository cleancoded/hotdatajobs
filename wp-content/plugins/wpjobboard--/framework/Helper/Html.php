<?php

/**
 * Description of ${name}
 *
 * @author ${user}
 * @package 
 */
class Daq_Helper_Html
{
    protected $_tag = "";
    
    protected $_attr = array();
    
    protected $_html = null;
    
    protected $_force = false;
    
    public function __construct($tag, $attr = array(), $html = null)
    {
        $this->_tag = $tag;
        $this->_attr = $attr;
        $this->_html = $html;
    }
    
    public function forceLongClosing($force = true)
    {
        $this->_force = $force;
    }
    
    public function render()
    {
        $output = "<".$this->_tag." ";
        
        $esc = "htmlspecialchars";
        if(function_exists("esc_attr")) {
            $esc = "esc_attr";
        }
        
        foreach($this->_attr as $k => $v) {
            if(!empty($v) || is_string($v) && strlen($v)>0) {
                $output .= $esc($k).'="'.$esc($v).'" ';
            }
        }
        
        if(empty($this->_html) && !$this->_force) {
            $output .= " />";
        } else {
            $output .= ">";
            $output .= $this->_html;
            $output .= "</".$this->_tag.">";
        }
        
        return $output;
    }
    
    public function __toString()
    {
        return $this->render();
    }
    
    public static function build($tag, $attr = array(), $html = null) {
        $helper = new self($tag, $attr, $html);
        echo $helper->render();
    }
}
?>
