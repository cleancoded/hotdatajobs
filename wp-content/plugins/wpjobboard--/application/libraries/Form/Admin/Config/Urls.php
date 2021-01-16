<?php
/**
 * Description of Seo
 *
 * @author greg
 * @package 
 */

class Wpjb_Form_Admin_Config_Urls extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("Default Pages and URLs", "wpjobboard");
        $instance = Wpjb_Project::getInstance();
        
        $this->addGroup("shortcoded", __("Shortcode Pages", "wpjobboard"));
        
        $e = $this->create("urls_link_job");
        $e->setValue($instance->getConfig("urls_link_job"));
        $e->setRenderer("wpjb_dropdown_pages");
        $e->setLabel("[wpjb_jobs_list]");
        $this->addElement($e, "shortcoded");
        
        $e = $this->create("urls_link_job_search");
        $e->setValue($instance->getConfig("urls_link_job_search"));
        $e->setRenderer("wpjb_dropdown_pages");
        $e->setLabel("[wpjb_jobs_search]");
        $this->addElement($e, "shortcoded");
        
        $e = $this->create("urls_link_job_add");
        $e->setValue($instance->getConfig("urls_link_job_add"));
        $e->setRenderer("wpjb_dropdown_pages");
        $e->setLabel("[wpjb_jobs_add]");
        $this->addElement($e, "shortcoded");
        
        $e = $this->create("urls_link_emp_reg");
        $e->setValue($instance->getConfig("urls_link_emp_reg"));
        $e->setRenderer("wpjb_dropdown_pages");
        $e->setLabel("[wpjb_employer_register]");
        $this->addElement($e, "shortcoded");
        
        $e = $this->create("urls_link_emp_panel");
        $e->setValue($instance->getConfig("urls_link_emp_panel"));
        $e->setRenderer("wpjb_dropdown_pages");
        $e->setLabel("[wpjb_employer_panel]");
        $this->addElement($e, "shortcoded");
        
        $e = $this->create("urls_link_resume");
        $e->setValue($instance->getConfig("urls_link_resume"));
        $e->setRenderer("wpjb_dropdown_pages");
        $e->setLabel("[wpjb_resumes_list]");
        $this->addElement($e, "shortcoded");
        
        $e = $this->create("urls_link_resume_search");
        $e->setValue($instance->getConfig("urls_link_resume_search"));
        $e->setRenderer("wpjb_dropdown_pages");
        $e->setLabel("[wpjb_resumes_search]");
        $this->addElement($e, "shortcoded");
        
        $e = $this->create("urls_link_cand_reg");
        $e->setValue($instance->getConfig("urls_link_cand_reg"));
        $e->setRenderer("wpjb_dropdown_pages");
        $e->setLabel("[wpjb_candidate_register]");
        $this->addElement($e, "shortcoded");
        
        $e = $this->create("urls_link_cand_panel");
        $e->setValue($instance->getConfig("urls_link_cand_panel"));
        $e->setRenderer("wpjb_dropdown_pages");
        $e->setLabel("[wpjb_candidate_panel]");
        $this->addElement($e, "shortcoded");
        
        $e = $this->create("urls_link_membership_pricing");
        $e->setValue($instance->getConfig("urls_link_membership_pricing"));
        $e->setRenderer("wpjb_dropdown_pages");
        $e->setLabel("[wpjb_membership_pricing]");
        $this->addElement($e, "shortcoded");
        
        $e = $this->create("urls_link_cand_membership");
        $e->setValue($instance->getConfig("urls_link_cand_membership"));
        $e->setRenderer("wpjb_dropdown_pages");
        $e->setLabel("[wpjb_candidate_membership]");
        $this->addElement($e, "shortcoded");

        $this->addGroup("slugs", __("Details Pages Slugs", "wpjobboard"));
        
        $t = __('After making changes in this section remember to go to <a href="%s">Settings / Permalinks</a> panel and to click "Save Changes" button to reset WordPress router.', 'wpjobboard');
        $e = $this->create("label", "label");
        $e->setDescription(sprintf($t, admin_url("options-permalink.php")));
        $this->addElement($e, "slugs");
        
        $e = $this->create("urls_rewrite_job");
        $e->setValue($instance->getConfig("urls_rewrite_job"));
        $e->setLabel(__("Job Details", "wpjobboard"));
        $e->setAttr("placeholder", "job");
        $e->addValidator(new Daq_Validate_Slug());
        $e->addClass("wpjb-rewrite-slug");
        $e->setHint(get_home_url() . "/<strong></strong>/test-job/");
        $this->addElement($e, "slugs");
        
        $e = $this->create("urls_rewrite_resume");
        $e->setValue($instance->getConfig("urls_rewrite_resume"));
        $e->setLabel(__("Resume Details", "wpjobboard"));
        $e->setAttr("placeholder", "resume");
        $e->addValidator(new Daq_Validate_Slug());
        $e->addClass("wpjb-rewrite-slug");
        $e->setHint(get_home_url() . "/<strong></strong>/test-resume/");
        $this->addElement($e, "slugs");
        
        $e = $this->create("urls_rewrite_company");
        $e->setValue($instance->getConfig("urls_rewrite_company"));
        $e->setLabel(__("Company Details", "wpjobboard"));
        $e->setAttr("placeholder", "company");
        $e->addValidator(new Daq_Validate_Slug());
        $e->addClass("wpjb-rewrite-slug");
        $e->setHint(get_home_url() . "/<strong></strong>/test-company/");
        $this->addElement($e, "slugs");
        
        $this->addGroup("redirect", __("Redirect After", "wpjobboard"));
        
        $e = $this->create("urls_after_apply");
        $e->setValue($instance->getConfig("urls_after_apply"));
        $e->setLabel(__("Job Application", "wpjobboard"));
        $e->setAttr("placeholder", "http://");
        $e->addValidator(new Daq_Validate_Url());
        $e->addFilter(new Daq_Filter_WP_Url());
        $this->addElement($e, "redirect");
        
        $e = $this->create("urls_after_reg_employer");
        $e->setValue($instance->getConfig("urls_after_reg_employer"));
        $e->setLabel(__("Employer Registration", "wpjobboard"));
        $e->setAttr("placeholder", "http://");
        $e->addValidator(new Daq_Validate_Url());
        $e->addFilter(new Daq_Filter_WP_Url());
        $this->addElement($e, "redirect");
        
        $e = $this->create("urls_after_employer_login");
        $e->setValue($instance->getConfig("urls_after_employer_login"));
        $e->setLabel(__("Employer Login", "wpjobboard"));
        $e->setAttr("placeholder", "http://");
        $e->addValidator(new Daq_Validate_Url());
        $e->addFilter(new Daq_Filter_WP_Url());
        $this->addElement($e, "redirect");
        
        $e = $this->create("urls_after_employer_logout");
        $e->setValue($instance->getConfig("urls_after_employer_logout"));
        $e->setLabel(__("Employer Logout", "wpjobboard"));
        $e->setAttr("placeholder", "http://");
        $e->addValidator(new Daq_Validate_Url());
        $e->addFilter(new Daq_Filter_WP_Url());
        $this->addElement($e, "redirect");
        
        $e = $this->create("urls_after_reg_candidate");
        $e->setValue($instance->getConfig("urls_after_reg_candidate"));
        $e->setLabel(__("Candidate Registration", "wpjobboard"));
        $e->setAttr("placeholder", "http://");
        $e->addValidator(new Daq_Validate_Url());
        $e->addFilter(new Daq_Filter_WP_Url());
        $this->addElement($e, "redirect");
        
        $e = $this->create("urls_after_candidate_login");
        $e->setValue($instance->getConfig("urls_after_candidate_login"));
        $e->setLabel(__("Candidate Login", "wpjobboard"));
        $e->setAttr("placeholder", "http://");
        $e->addValidator(new Daq_Validate_Url());
        $e->addFilter(new Daq_Filter_WP_Url());
        $this->addElement($e, "redirect");
        
        $e = $this->create("urls_after_candidate_logout");
        $e->setValue($instance->getConfig("urls_after_candidate_logout"));
        $e->setLabel(__("Candidate Logout", "wpjobboard"));
        $e->setAttr("placeholder", "http://");
        $e->addValidator(new Daq_Validate_Url());
        $e->addFilter(new Daq_Filter_WP_Url());
        $this->addElement($e, "redirect");
        
        apply_filters("wpja_form_init_config_urls", $this);
        
        
    }
    
    public function executePostSave()
    {
        
    }
    
}



?>