<?php

class Wpjb_Customize_Manager {
    
    /**
     * Job customization object
     *
     * @var Wpjb_Customize_Job
     */
    public $job = null;
    
    /**
     * Class Constructor
     * 
     * @since 5.1.0
     */
    public function __construct() {
        $this->job = new Wpjb_Customize_Job();
        
        add_action( 'customize_register', array($this, "register"), 1000);
        add_action( 'customize_register', array($this->job, "register"), 1000);
    }
    
    /**
     * Register WPJobBoard Panel in the Customizer
     * 
     * This function is executed by 'customize_register' action.
     * 
     * @see customize_register action
     * 
     * @param WP_Customize_Manager $wp_customize
     */
    public function register( $wp_customize ) {
        $wp_customize->add_panel( 'wpjobboard', array(
            'priority'       => 10,
            'capability'     => 'edit_theme_options',
            'theme_supports' => '',
            'title'          => __('WPJobBoard', 'wpjobboard'),
            'description'    => __('Several settings pertaining my theme', 'wpjobboard'),
        ) );
    }

}
