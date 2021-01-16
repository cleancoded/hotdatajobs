<?php
/**
 * Description of PayPal
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Resumes extends Daq_Form_Abstract
{

    public $name = null;
    
    protected function _currArr()
    {
        $list = array();
        foreach(Wpjb_List_Currency::getList() as $k => $arr) {
            $v = $arr['name'];
            if($arr['symbol'] != null) {
                $v = $arr['symbol'].' '.$v;
            }
            $list[] = array($k, $k, $v);
        }
        return $list;
    }
    
    public function init()
    {
        $this->name = __("Resumes Settings", "wpjobboard");
        $instance = Wpjb_Project::getInstance();

        $this->addGroup("privacy", __("Privacy", "wpjobboard"));
        $this->addGroup("membership", __("Membership Defaults", "wpjobboard"));
        
        $e = $this->create("cv_privacy", Daq_Form_Element::TYPE_SELECT);
        $e->setValue($instance->getConfig("cv_privacy"));
        $e->setLabel(__("Resumes Privacy", "wpjobboard"));
        $e->addOption("0", "0", __("Hide contact details only.", "wpjobboard"));
        $e->addOption(1, 1, __("Hide resume list and details", "wpjobboard"));
        $this->addElement($e, "privacy");
        
        $e = $this->create("cv_access", "select");
        $e->setValue($instance->getConfig("cv_access"));
        $e->setLabel(__("Grant Resumes Access", "wpjobboard"));
        $e->setHint(__("Note that automatically activating employer accounts might cause, potential security issue for employers since anyone will be able to browse their personal data", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Int());
        $e->addOption(1, 1, __("To all", "wpjobboard"));
        $e->addOption(2, 2, __("To registered members", "wpjobboard"));
        $e->addOption(3, 3, __("To employers", "wpjobboard"));
        $e->addOption(5, 5, __("To premium members", "wpjobboard"));
        $e->addOption(6, 6, __("To admin only (If candidate will apply for job, owner of the job will see resume details)", "wpjobboard"));
        $this->addElement($e, "privacy");

        $this->addGroup("moderation", __("Candidates Moderation", "wpjobboard"));
        
        $e = $this->create("cv_login_only_approved", "checkbox");
        $e->setValue($instance->getConfig("cv_login_only_approved"));
        $e->setLabel(__("Moderation", "wpjobboard"));
        $e->addOption("1", "1", __("Only approved members can login.", "wpjobboard"));
        $this->addElement($e, "moderation");
        
        $e = $this->create("cv_approval", "select");
        $e->setValue($instance->getConfig("cv_approval"));
        $e->setLabel(__("Resumes Approval", "wpjobboard"));
        $e->setHint("");
        $e->addValidator(new Daq_Validate_InArray(array(0,1)));
        $e->addOption("0", "0", __("Instant", "wpjobboard"));
        $e->addOption(1, 1, __("By Administrator", "wpjobboard"));
        $this->addElement($e, "moderation");
        
        $e = $this->create("cv_is_public", "select");
        $e->setValue($instance->getConfig("cv_is_public", 1));
        $e->setLabel(__("Resumes Default Visibility", "wpjobboard"));
        $e->setHint("");
        $e->addValidator(new Daq_Validate_InArray(array(0,1)));
        $e->addOption("0", "0", __("Private (not displayed on resumes list)", "wpjobboard"));
        $e->addOption("1", "1", __("Public (visible on resumes list)", "wpjobboard"));
        $this->addElement($e, "moderation");
        
        $this->addGroup("other", __("Other", "wpjobboard"));
        
        $e = $this->create("cv_show_applicant_resume", "checkbox");
        $e->setValue($instance->getConfig("cv_show_applicant_resume"));
        $e->setLabel(__("On Application", "wpjobboard"));
        $e->setHint("");
        $e->addOption(1, 1, __("Allow Employer to view whole user Resume", "wpjobboard"));
        $this->addElement($e, "other");
        
        $e = $this->create("cv_search_bar", Daq_Form_Element::TYPE_SELECT);
        $e->setValue(Wpjb_Project::getInstance()->conf("cv_search_bar", "disabled"));
        $e->addOption("disabled", "disabled", __("Disabled", "wpjobboard"));
        $e->addOption("enabled", "enabled", __("Enabled", "wpjobboard"));
        //$e->addOption("enabled-live", "enabled-live", __("Enabled (with live search)", "wpjobboard"));
        $e->setLabel(__("Search form on jobs list", "wpjobboard"));
        $this->addElement($e, "other");
        
        $pages = pages_with_shortcode( 'wpjb_employers_list' );
        $company_list_page_id = $pages[0]->ID;
        
        $e = $this->create("cv_members_restricted_pages", "select");
        $e->setValue($instance->getConfig("cv_members_restricted_pages"));
        $e->setLabel(__("Restricted Pages", "wpjobboard"));
        $e->setHint(__("What pages should have restricted access for candidates.", "wpjobboard"));
        $e->addOption( $instance->getConfig("urls_link_job"), $instance->getConfig("urls_link_job"), __("Jobs List", "wpjobboard" ) );
        $e->addOption( $instance->getConfig("urls_link_job_search"), $instance->getConfig("urls_link_job_search"), __("Jobs Search", "wpjobboard" ) );
        $e->addOption( $instance->getConfig("urls_link_job_add"), $instance->getConfig("urls_link_job_add"), __("Jobs Add", "wpjobboard" ) );
        $e->addOption( 'job', 'job', __("Job Details", "wpjobboard" ) );
        $e->addOption( $instance->getConfig("urls_link_resume"), $instance->getConfig("urls_link_resume"), __("Resumes List", "wpjobboard" ) );
        $e->addOption( $instance->getConfig("urls_link_resume_search"), $instance->getConfig("urls_link_resume_search"), __("Resumes Search", "wpjobboard" ) );
        $e->addOption( 'resume', 'resume', __("Resume Details", "wpjobboard" ) );
        if( $company_list_page_id > 0 ) {
            $e->addOption( $company_list_page_id, $company_list_page_id, __("Company List", "wpjobboard" ) );
        }
        $e->addOption( 'company', 'company', __("Company Details", "wpjobboard" ) );
        $e->setMaxChoices(15);
        $this->addElement($e, "membership");
        
        $e = $this->create("cv_members_have_access", "select");
        $e->setValue($instance->getConfig("cv_members_have_access"));
        $e->setLabel(__("Access", "wpjobboard"));
        $e->setHint(__("What user types have access to restricted pages", "wpjobboard"));
        $e->addOption("0", "0", __("Anyone", "wpjobboard"));
        $e->addOption("1", "1", __("Only registered candidates", "wpjobboard"));
        $e->addOption("2", "2", __("Only premium candidates.", "wpjobboard"));
        $this->addElement($e, "membership");
        
        $e = $this->create("cv_members_are_searchable", "checkbox");
        $e->setValue($instance->getConfig("cv_members_are_searchable"));
        $e->setLabel(__("Search Restrictions", "wpjobboard"));
        $e->addOption("1", "1", __("Only candidates with valid premium membership are visible in search results and on candidates list.", "wpjobboard"));
        $this->addElement($e, "membership");
        
        $e = $this->create("cv_members_can_apply", "checkbox");
        $e->setValue($instance->getConfig("cv_members_can_apply"));
        $e->setLabel(__("Apply Restrictions", "wpjobboard"));
        $e->addOption("1", "1", __("Only candidates with valid premium membership are able to apply for job.", "wpjobboard"));
        $this->addElement($e, "membership");
        
        $e = $this->create("cv_alerts_limit", "text");
        $e->setValue($instance->getConfig("cv_alerts_limit"));
        $e->addValidator(new Daq_Validate_Int());
        $e->setLabel(__("Alerts Limit", "wpjobboard"));
        $e->setHint(__("Set Job Alerts limits Candidates. Enter '-1' to allow unlimited alerts. When limit is set, anonymous users can't create alerts.", "wpjobboard"));
        $this->addElement($e, "membership");
        
        apply_filters("wpja_form_init_config_resume", $this);

    }
}

?>