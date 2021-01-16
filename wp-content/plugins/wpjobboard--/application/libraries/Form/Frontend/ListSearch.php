<?php

class Wpjb_Form_Frontend_ListSearch extends Daq_Form_Abstract 
{
    public function init() 
    {
        $this->addGroup("visible", "");
        $this->addGroup("_internal", "");
        
        $e = $this->create("show_results", "hidden");
        $e->setValue("1");
        $this->addElement($e, "_internal");
        
        $e = $this->create("query");
        $e->setLabel("");
        $e->addMeta("classes", "wpjb-input wpjb-input-type-half wpjb-input-type-half-left");
        $e->addClass("wpjb-top-search-query wpjb-ls-query");
        $e->setAttr("autocomplete", "off");
        $e->setAttr("placeholder", __("Keyword ...", "wpjobboard"));
        $this->addElement($e, "visible");
        
        $e = $this->create("location");
        $e->setLabel("");
        $e->addMeta("classes", "wpjb-input wpjb-input-type-half wpjb-input-type-half-right");
        $e->addClass("wpjb-top-search-location wpjb-ls-location");
        $e->setAttr("autocomplete", "off");
        $e->setAttr("placeholder", __("Location ...", "wpjobboard"));
        $this->addElement($e, "visible");
        
        $e = $this->create("type", "checkbox");
        $e->setLabel("");
        $e->addMeta("classes", "wpjb-input wpjb-input-type-full");
        $e->addOptions(wpjb_form_get_jobtypes());
        $e->addClass("wpjb-ls-type");
        $e->setCute(true);
        $this->addElement($e, "visible");
        
        
        
        apply_filters("wpjb_form_init_list_search", $this);
    }

}
