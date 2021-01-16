<?php

// Bail if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wpjobboard_Am_Admin {

    
    /**
     * Admin Constructor
     * 
     * Creates object which manages WPJB AM integration options.
     * 
     * @since 1.0
     * @return Wpjobboard_Am_Admin
     */
    public function __construct() {
        
        add_action( "init", array( $this, "init" ) );
        add_action( "plugins_loaded", array( $this, "updates_manager" ) );
    }
    
    /**
     * Admin Init
     * 
     * Initiates wp-admin actions and filters
     * 
     * @since 1.0
     * @return void
     */
    public function init() {
        
    }

    /**
     * Application Methods Updates Manager
     *
     * This functions is executed by plugins_loaded action
     *
     * @since 1.1
     * @return void
     */
    public function updates_manager() {

        if(!defined("Wpjb_Upgrade_Manager::MULTI")) {
            // old WPJB version without add-ons automatic updates support
            return;
        }

        $manager = new Wpjb_Upgrade_Manager(
            "wpjobboard-application-methods/wpjobboard-am.php", 
            "wpjobboard-application-methods", 
            "1.1.1"
        );
        $manager->connect();

        Wpjb_Project::getInstance()->env("upgrade")->{'wpjobboard-application-methods'} = $manager;
    }
}
