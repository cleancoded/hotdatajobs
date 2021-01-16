<?php
/**
 * Description of Apply
 * 
 * IMPORTANT:
 * In order to properly save fields in DB (using AJAX) you need to apply following
 * classes to the fields
 * - default fields: wpjb-widget-alert-param
 * - meta fields: wpjb-widget-alert-meta
 * - frequency field: wpjb-widget-alert-frequency
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Frontend_Alert extends Daq_Form_Abstract
{
    public function init()
    {
        $e = $this->create("keyword", "text");
        $e->setAttr("placeholder", __("Keyword", "wpjobboard"));
        $e->addClass("wpjb-widget-alert-keyword");
        //$e->setRequired(true);
        $this->addElement($e, "alert");

        $e = $this->create("email", "text");
        $e->setAttr("placeholder", __("E-mail", "wpjobboard"));
        $e->addClass("wpjb-widget-alert-email");
        $e->setRequired(true);
        $e->addValidator(new Daq_Validate_Email());
        $this->addElement($e, "apply");

        apply_filters("wpjb_form_init_alert", $this);
    }

}

?>