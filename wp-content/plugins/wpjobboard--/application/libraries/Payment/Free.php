<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Free
 *
 * @author Grzegorz
 */
class Wpjb_Payment_Free extends Wpjb_Payment_Abstract
{
    public function getEngine() 
    {
        return "";
    }

    public function getForm() 
    {
        return null;
    }

    public function getTitle() 
    {
        return null;
    }

    public function processTransaction() 
    {
        
    }

    public function render() 
    {
        $html = '<div class="wpjb-flash-info">';
        $html.= '<div class="wpjb-flash-icon"><span class="wpjb-glyphs wpjb-icon-ok"></span></div>';
        $html.= '<div class="wpjb-flash-body">';
        $html.= join("<br/>", $this->getObject()->successMessages());
        $html.= '</div>';
        $html.= '</div>';
        
        return $html;
    }
}

?>
