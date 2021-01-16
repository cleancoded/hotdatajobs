<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package 
 */

class Wpjb_Form_Admin_Config_Jobs extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("Job Board Configuration", "wpjobboard");
        
        $instance = Wpjb_Project::getInstance();

        $this->addGroup("jobs-list", __("Jobs List", "wpjobboard"));
        
        $e = $this->create("search_bar", Daq_Form_Element::TYPE_SELECT);
        $e->setValue(Wpjb_Project::getInstance()->conf("search_bar", "disabled"));
        $e->addOption("disabled", "disabled", __("Disabled", "wpjobboard"));
        $e->addOption("enabled", "enabled", __("Enabled", "wpjobboard"));
        $e->addOption("enabled-live", "enabled-live", __("Enabled (with live search)", "wpjobboard"));
        $e->setLabel(__("Search form on jobs list", "wpjobboard"));
        $this->addElement($e, "jobs-list");
        
        $e = $this->create("front_jobs_per_page");
        $e->setRequired(true);
        $e->setValue($instance->getConfig("front_jobs_per_page", 20));
        $e->setLabel(__("Job offers per page", "wpjobboard"));
        $e->setHint(__("Number of listings per page.", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Int(1));
        $this->addElement($e, "jobs-list");
        
        $e = $this->create("front_hide_filled", "checkbox");
        $e->setValue($instance->getConfig("front_hide_filled"));
        $e->setLabel(__("Filled Jobs", "wpjobboard"));
        $e->addOption(1, 1, __("When job is marked as filled, hide it on the jobs list.", "wpjobboard"));
        $this->addElement($e, "jobs-list");
        
        $e = $this->create("front_show_expired", "checkbox");
        $e->setValue($instance->getConfig("front_show_expired"));
        $e->setLabel(__("Expired Jobs", "wpjobboard"));
        $e->addOption(1, 1, __("Allow visitors to view expired jobs details pages.", "wpjobboard"));
        $this->addElement($e, "jobs-list");

        $this->addGroup("job-details", __("Job Details", "wpjobboard"));
        
        $e = $this->create("front_marked_as_new");
        //$e->setRequired(true);
        $e->setValue($instance->getConfig("front_marked_as_new", 7));
        $e->setLabel(__("Days marked as new", "wpjobboard"));
        $e->setHint(__("Number of days since posting job will be displayed as new.", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Int(0));
        $this->addElement($e, "job-details");
        
        $e = $this->create("front_show_related_jobs", "checkbox");
        $e->setValue($instance->getConfig("front_show_related_jobs"));
        $e->setLabel(__("Related Jobs", "wpjobboard"));
        $e->addOption(1, 1, __("Show related jobs on job details page.", "wpjobboard"));
        $this->addElement($e, "job-details");
        
        $e = $this->create("front_hide_apply_link", "checkbox");
        $e->setValue($instance->getConfig("front_hide_apply_link"));
        $e->setLabel(__("Apply online", "wpjobboard"));
        $e->addOption(1, 1, __("Hide 'Apply Online' button on job details page.", "wpjobboard"));
        $this->addElement($e, "job-details");
        
        $e = $this->create("front_hide_bookmarks", "checkbox");
        $e->setValue($instance->getConfig("front_hide_bookmarks"));
        $e->setLabel(__("Bookmarks", "wpjobboard"));
        $e->addOption(1, 1, __("Hide 'bookmark' button on job details page.", "wpjobboard"));
        $this->addElement($e, "job-details");
        
        $e = $this->create("front_apply_members_only", "checkbox");
        $e->setValue($instance->getConfig("front_apply_members_only"));
        $e->setLabel(__("Applications", "wpjobboard"));
        $e->addOption(1, 1, __("Only registered members can apply for jobs.", "wpjobboard"));
        $this->addElement($e, "job-details");
        
        
        $this->addGroup("jobs-add", __("Jobs Publishing", "wpjobboard"));

        $e = $this->create("default_job_duration");
        $e->setRequired(false);
        $e->setValue($instance->getConfig("default_job_duration", 30));
        $e->setLabel(__("Default Duration", "wpjobboard"));
        $e->setHint(__("How many days the job will will be displayed (when no listing type selected).", "wpjobboard"));
        $e->addValidator(new Daq_Validate_Int(0));
        $this->addElement($e, "job-details");
        
        $e = $this->create("posting_allow", Daq_Form_Element::TYPE_SELECT);
        $e->setValue(Wpjb_Project::getInstance()->conf("posting_allow"));
        $e->addOption(1, 1, __("Anyone", "wpjobboard"));
        $e->addOption(2, 2, __("Employers", "wpjobboard"));
        $e->addOption(3, 3, __("Administrators", "wpjobboard"));
        $e->setLabel(__("Who Can Post Jobs", "wpjobboard"));
        $this->addElement($e, "jobs-add");

        $e = $this->create("posting_moderation", Daq_Form_Element::TYPE_CHECKBOX);
        $e->setValue($instance->getConfig("posting_moderation"));
        $e->addOption(1, 1, __("Free Jobs.", "wpjobboard"));
        $e->addOption(2, 2, __("Paid Jobs.", "wpjobboard"));
        $e->addOption(3, 3, __("Package Jobs.", "wpjobboard"));
        $e->setLabel(__("Hold For Moderation", "wpjobboard"));
        $this->addElement($e, "jobs-add");
        
        $e = $this->create("front_allow_edition", "checkbox");
        $e->setValue($instance->getConfig("front_allow_edition"));
        $e->setLabel(__("Job Edition", "wpjobboard"));
        $e->addOption(1, 1, __("Allow Employers to edit their job listings.", "wpjobboard"));
        $this->addElement($e, "jobs-add");


        
        $this->addGroup("moderation", __("Employers Moderation", "wpjobboard"));
        
        $e = $this->create("employer_login_only_approved", "checkbox");
        $e->setValue($instance->getConfig("employer_login_only_approved"));
        $e->setLabel(__("Moderation", "wpjobboard"));
        $e->addOption("1", "1", __("Only approved members can login.", "wpjobboard"));
        $this->addElement($e, "moderation");
        
        $e = $this->create("employer_approval", "select");
        $e->setValue($instance->getConfig("employer_approval"));
        $e->setLabel(__("Employer Approval", "wpjobboard"));
        $e->setHint("");
        $e->addValidator(new Daq_Validate_InArray(array(0,1)));
        $e->addOption("0", "0", __("Instant", "wpjobboard"));
        $e->addOption(1, 1, __("By Administrator", "wpjobboard"));
        $this->addElement($e, "moderation");
        
        $e = $this->create("employer_is_public", "select");
        $e->setValue($instance->getConfig("employer_is_public", 1));
        $e->setLabel(__("Employer Default Visibility", "wpjobboard"));
        $e->setHint("");
        $e->addValidator(new Daq_Validate_InArray(array(0,1)));
        $e->addOption("0", "0", __("Private (not displayed on employers list)", "wpjobboard"));
        $e->addOption("1", "1", __("Public (visible on employers list)", "wpjobboard"));
        $this->addElement($e, "moderation");

    }
}

?>