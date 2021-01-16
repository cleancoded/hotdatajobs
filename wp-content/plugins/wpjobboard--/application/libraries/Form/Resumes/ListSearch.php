<?php

class Wpjb_Form_Resumes_ListSearch extends Daq_Form_Abstract 
{
    public function init() 
    {
        global $page, $wp_rewrite;

        if(!$wp_rewrite->using_permalinks()) {
            $e = $this->create("page_id", "hidden");
            $e->setValue($page->ID);
            $this->addElement($e, "_internal");
            
            $e = $this->create("job_resumes", "hidden");
            $e->setValue("find");
            $this->addElement($e, "_internal");
        }
        
        $this->addGroup("visible", "");
        
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
        
        apply_filters("wpjb_form_init_resumes_list_search", $this);
    }

}
