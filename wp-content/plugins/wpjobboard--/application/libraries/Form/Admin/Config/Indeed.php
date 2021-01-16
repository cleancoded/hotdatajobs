<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Indeed extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("Indeed API", "wpjobboard");
        $instance = Wpjb_Project::getInstance();

        $this->addGroup("api", __("Config", "wpjobboard"));
        
        $e = $this->create("indeed_publisher");
        $e->setValue($instance->getConfig("indeed_publisher"));
        $e->setLabel(__("Indeed Publisher API Key", "wpjobboard"));
        $e->setHint(__("Claim your key at https://indeed.com/publisher/, It's <strong>required</strong> to use Indeed Import", "wpjobboard"));
        $this->addElement($e, "api");
        
        $this->addGroup("conversion", __("Conversion Tracking", "wpjobboard"));
        
        $e = $this->create("indeed_conversion_tracking_enable", "checkbox");
        $e->setLabel(__("Enable", "wpjobboard"));
        $e->setValue($instance->getConfig("indeed_conversion_tracking_enable"));
        $e->addOption("1", "1", __("Enable conversion tracking.", "wpjobboard"));
        $this->addElement($e, "conversion");
        
        $e = $this->create("indeed_conversion_tracking_id");
        $e->setLabel(__("Indeed Conversion ID", "wpjobboard"));
        $e->setValue($instance->getConfig("indeed_conversion_tracking_id"));
        $this->addElement($e, "conversion");
        
        $e = $this->create("indeed_conversion_tracking_label");
        $e->setLabel(__("Indeed Conversion Label", "wpjobboard"));
        $e->setValue($instance->getConfig("indeed_conversion_tracking_label"));
        $this->addElement($e, "conversion");
        
        $this->addGroup("backfill", __("Backfilling <small>(automatically display jobs from Indeed)</small>"));
        
        $e = $this->create("indeed_backfill", "checkbox");
        $e->setLabel(__("Backfill Options", "wpjobboard"));
        $e->setValue($instance->getConfig("indeed_backfill"));
        $e->addOption("enabled-list", "enabled-list", __("Enable Backfilling on jobs list.", "wpjobboard"));
        $e->addOption("enabled-search", "enabled-search", __("Enable Backfilling in jobs search.", "wpjobboard"));
        $e->addOption("attribution", "attribution", __("Automatically insert Indeed attribution link.", "wpjobboard"));
        $e->addOption("click-tracking", "click-tracking", __("Enable Indeed click tracking.", "wpjobboard"));
        $this->addElement($e, "backfill");
        
        $e = $this->create("indeed_backfill_when");
        $e->setLabel(__("Backfill When", "wpjobboard"));
        $e->setHint(__("Load Indeed results when number of current results is less than.", "wpjobboard"));
        $e->setValue($instance->getConfig("indeed_backfill_when"));
        $this->addElement($e, "backfill");
        
        $this->addGroup("defaults", __("Defaults <small>(default job search params)</small>", "wpjobboard"));
        
        $e = $this->create("indeed_default_q");
        $e->setLabel(__("Query", "wpjobboard"));
        $e->setValue($instance->getConfig("indeed_default_q"));
        $this->addElement($e, "defaults");
        
        $e = $this->create("indeed_default_l");
        $e->setLabel(__("Location", "wpjobboard"));
        $e->setValue($instance->getConfig("indeed_default_l"));
        $this->addElement($e, "defaults");
        
        $e = $this->create("indeed_default_jt", "select");
        $e->setEmptyOption(true);
        $e->setLabel(__("Job Type", "wpjobboard"));
        $e->setValue($instance->getConfig("indeed_default_jt"));
        foreach(Wpjb_Service_Indeed::jobTypes() as $k => $v) {
            $e->addOption($k, $k, $v);
        }
        $this->addElement($e, "defaults");
        
        $e = $this->create("indeed_default_co", "select");
        $e->setEmptyOption(true);
        $e->setLabel(__("Country", "wpjobboard"));
        $e->setValue($instance->getConfig("indeed_default_co"));
        foreach(Wpjb_Service_Indeed::countries() as $k => $v) {
            $e->addOption($k, $k, $v);
        }
        $this->addElement($e, "defaults");
        
        $e = $this->create("indeed_default_st", "select");
        $e->setEmptyOption(true);
        $e->setLabel(__("Show Jobs From", "wpjobboard"));
        $e->setValue($instance->getConfig("indeed_default_st"));
        $e->addOption("jobsite", "jobsite", __("Job Boards", "wpjobboard"));
        $e->addOption("employer", "employer", __("Employers", "wpjobboard"));
        $this->addElement($e, "defaults");
        
        $e = $this->create("indeed_default_sort", "select");
        $e->setLabel(__("Sort By", "wpjobboard"));
        $e->setValue($instance->getConfig("indeed_default_sort"));
        $e->addOption("relevance", "relevance", __("Relevance", "wpjobboard"));
        $e->addOption("date", "date", __("Date", "wpjobboard"));
        $this->addElement($e, "defaults");

        apply_filters("wpja_form_init_config_indeed", $this);

    }
}

?>