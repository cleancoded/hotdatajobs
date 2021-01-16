<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Ziprecruiter extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("ZipRecruiter API", "wpjobboard");
        $instance = Wpjb_Project::getInstance();

        $this->addGroup("api", __("Config", "wpjobboard"));
        
        $e = $this->create("ziprecruiter_api_key");
        $e->setValue($instance->getConfig("ziprecruiter_api_key"));
        $e->setLabel(__("ZipRecruiter API Key", "wpjobboard"));
        $e->setHint( sprintf( __('Fill the <a href="%s" target="_blank">form</a> and ZipRecruiter team will reach you to claim you API key, It\'s <strong>required</strong> to use ZipRecruiter', "wpjobboard"), "https://docs.google.com/forms/d/e/1FAIpQLSd0Aov7fvR6jCW-885H9IsP_1ojnjWwLQn4kEgkq57iiD4tVA/viewform" ) );
        $this->addElement($e, "api");
        
        $this->addGroup("conversion", __("Conversion Tracking", "wpjobboard"));

        
        $this->addGroup("backfill", __("Backfilling (automatically display jobs from ZipRecruiter)"));
        
        $e = $this->create("ziprecruiter_backfill", "checkbox");
        $e->setLabel(__("Backfill Options", "wpjobboard"));
        $e->setValue($instance->getConfig("ziprecruiter_backfill"));
        $e->addOption("enabled-list", "enabled-list", __("Enable Backfilling on jobs list.", "wpjobboard"));
        $e->addOption("enabled-search", "enabled-search", __("Enable Backfilling in jobs search.", "wpjobboard"));
        $e->addOption("attribution", "attribution", __("Automatically insert ZipRecruiter attribution link.", "wpjobboard"));
        //$e->addOption("click-tracking", "click-tracking", __("Enable ZipRecruiter click tracking.", "wpjobboard"));
        $this->addElement($e, "backfill");
        
        $e = $this->create("ziprecruiter_backfill_when");
        $e->setLabel(__("Backfill When", "wpjobboard"));
        $e->setHint(__("Load ZipRecruiter results when number of current results is less than.", "wpjobboard"));
        $e->setValue($instance->getConfig("ziprecruiter_backfill_when"));
        $this->addElement($e, "backfill");
        
        $this->addGroup("defaults", __("Defaults (default job search params)", "wpjobboard"));
        
        $e = $this->create("ziprecruiter_default_q");
        $e->setLabel(__("Query", "wpjobboard"));
        $e->setHint(__("search terms, e.g. “Inside Sales”", "wpjobboard"));
        $e->setValue($instance->getConfig("ziprecruiter_default_q"));
        $this->addElement($e, "defaults");
        
        $e = $this->create("ziprecruiter_default_l");
        $e->setLabel(__("Location", "wpjobboard"));
        $e->setHint(__("location, e.g., “Santa Monica, CA” or “London, UK”", "wpjobboard"));
        $e->setValue($instance->getConfig("ziprecruiter_default_l"));
        $this->addElement($e, "defaults");

        $e = $this->create("ziprecruiter_default_r");
        $e->setLabel(__("Radius (in miles)", "wpjobboard"));
        $e->setHint(__("distance of the job relative to the location", "wpjobboard"));
        $e->setValue($instance->getConfig("ziprecruiter_default_r"));
        $this->addElement($e, "defaults");
        
        $e = $this->create("ziprecruiter_default_s");
        $e->setLabel(__("Minimal Salary", "wpjobboard"));
        $e->setHint(__("only show jobs with salary greater than this number", "wpjobboard"));
        $e->setValue($instance->getConfig("ziprecruiter_default_s"));
        $this->addElement($e, "defaults");
        
        $e = $this->create("ziprecruiter_default_da");
        $e->setLabel(__("Days Ago", "wpjobboard"));
        $e->setHint(__("only show jobs posted within this number of days", "wpjobboard"));
        $e->setValue($instance->getConfig("ziprecruiter_default_da"));
        $this->addElement($e, "defaults");

        apply_filters("wpja_form_init_config_ziprecruiter", $this);

    }
}

?>