<?php

 /**
  * @property Wpjb_Shortcode_Jobs_Add $wpjb_jobs_add
  * @property Wpjb_Shortcode_Jobs_List $wpjb_jobs_list
  * @property Wpjb_Shortcode_Jobs_Search $wpjb_jobs_search
  * @property Wpjb_Shortcode_Resumes_List $wpjb_resumes_list
  * @property Wpjb_Shortcode_Resumes_Search $wpjb_resumes_search
  * @property Wpjb_Shortcode_Employers_List $wpjb_employers_list
  * @property Wpjb_Shortcode_Employers_Search $wpjb_employers_search
  * @property Wpjb_Shortcode_Employer_Panel $wpjb_employer_panel
  * @property Wpjb_Shortcode_Employer_Register $wpjb_employer_register
  * @property Wpjb_Shortcode_Candidate_Panel $wpjb_candidate_panel
  * @property Wpjb_Shortcode_Candidate_Register $wpjb_candidate_register
  * @property Wpjb_Shortcode_Apply_Form $wpjb_apply_form
  * @property Wpjb_Shortcode_Map $wpjb_map
  * @property Wpjb_Shortcode_Login $wpjb_login
  */

class Wpjb_Shortcode_Manager {
    
    /**
     * List of registered WPJB shortcodes
     *
     * @var type array
     */
    protected $_shortcode = array();
    
    /**
     * Class constructor
     * 
     * Registers all WPJB shortcodes
     * 
     */
    public function __construct() {

        if(wpjb_conf("urls_link_job_add") != "0" || wpjb_conf("urls_link_emp_panel") != "0") {
            $this->_shortcode["wpjb_jobs_add"] = new Wpjb_Shortcode_Jobs_Add();
        }

        $this->_shortcode["wpjb_jobs_list"] = new Wpjb_Shortcode_Jobs_List();
        $this->_shortcode["wpjb_jobs_search"] = new Wpjb_Shortcode_Jobs_Search();
        
        $this->_shortcode["wpjb_resumes_list"] = new Wpjb_Shortcode_Resumes_List();
        $this->_shortcode["wpjb_resumes_search"] = new Wpjb_Shortcode_Resumes_Search();
        
        if(wpjb_conf("urls_link_emp_panel") != "0") {
            $this->_shortcode["wpjb_employer_panel"] = new Wpjb_Shortcode_Employer_Panel();
        }
        if(wpjb_conf("urls_link_emp_reg") != "0") {
            $this->_shortcode["wpjb_employer_register"] = new Wpjb_Shortcode_Employer_Register();
        }
        if(wpjb_conf("urls_link_cand_panel") != "0") {
            $this->_shortcode["wpjb_candidate_panel"] = new Wpjb_Shortcode_Candidate_Panel();
        }
        if(wpjb_conf("urls_link_cand_reg") != "0") {
            $this->_shortcode["wpjb_candidate_register"] = new Wpjb_Shortcode_Candidate_Register();
        }

        $this->_shortcode["wpjb_apply_form"] = new Wpjb_Shortcode_Apply_Form();
        
        $this->_shortcode["wpjb_employers_list"] = new Wpjb_Shortcode_Employers_List();
        $this->_shortcode["wpjb_employers_search"] = new Wpjb_Shortcode_Employers_List();
        
        $this->_shortcode["wpjb_membership_pricing"] = new Wpjb_Shortcode_Membership_Pricing();
        
        $this->_shortcode["wpjb_map"] = new Wpjb_Shortcode_Map();
        $this->_shortcode["wpjb_login"] = new Wpjb_Shortcode_Login();
        
    }
    
    /**
     * Returns shortcode object
     * 
     * @param string $name  Shortcode name
     * @return object       Shortcode object
     */
    public function __get($name) {
        if(isset($this->_shortcode[$name])) {
            return $this->_shortcode[$name];
        } else {
            return null;
        }
    }
    
    /**
     * Registers all listeners
     * 
     * This function registers listeners for all shortcodes
     * 
     * @return void
     */
    public function setupListeners() {
        foreach(array_keys($this->_shortcode) as $key) {
            $this->_shortcode[$key]->listen();
        }
    }
    
}
