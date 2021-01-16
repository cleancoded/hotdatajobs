<?php

// Bail if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wpjobboard_Am_Frontend {
    /**
     * Frontend Constructor
     * 
     * Creates object which manages WPJB AM integration options.
     * 
     * @since 1.0
     * @return Wpjobboard_Am_Frontend
     */
    public function __construct() {
        
        add_action( "init", array( $this, "init" ) );        
    }
    
    /**
     * Frontend Init
     * 
     * Initiates frontend actions and filters
     * 
     * @since 1.0
     * @return void
     */
    public function init() {
        
        wp_register_script(
            "wpjobboard-am-frontend",
            Wpjobboard_Am::get_instance()->get_baseurl() . "/assets/js/wpjobboard-am-frontend.js",
            array( "jquery" ),
            "1.0",
            true  
        );
        
        add_filter("wpjb_tpl_single_actions", array($this, "wpjb_am_tpl_single_actions") );
        add_filter("wpjb_form_init_apply", array($this, "wpjb_am_form_init_apply") );
    }

    /**
     * Single Job Page
     * 
     * Hides default application buttons and show based on Application Manager
     * 
     * @param Wpjb_Model_Job $job
     */
    public function wpjb_am_tpl_single_actions($job) {
        
        $application_methods = unserialize($job->meta->wpjobboard_am_data->value());
        $methods = 0;
        if( is_array($application_methods) && count($application_methods) ) {
            $methods = 1;
        }

        wp_localize_script( "wpjobboard-am-frontend", "wpjobboard_am_lang", array(
            "ajaxurl"           => admin_url( "admin-ajax.php" ),
            "app_methods"       => $application_methods,
            "has_methods"       => $methods,
            "label_url"         => __("Visit Application Site", "wpjobboard-am"),
            "label_email"       => __("Apply Online", "wpjobboard-am"),
            "label_linkedin"    => __("Apply From LinkedIn", "wpjobboard-am"),
            "job_id"            => $job->id,
        ));  
        
        wp_enqueue_script("wpjobboard-am-frontend");
        
    }
    
    /**
     * Apply Form Init
     * 
     * Adds additional e-mails to application form
     * 
     * @param Wpjb_Form_Apply $form
     * @return Wpjb_Form_Apply
     */
    public function wpjb_am_form_init_apply($form) {
        
        $e = new Daq_Form_Element_Hidden("wpjb_am_emails");
        $form->addElement($e, "apply");
        
        return $form;
    }
    
}