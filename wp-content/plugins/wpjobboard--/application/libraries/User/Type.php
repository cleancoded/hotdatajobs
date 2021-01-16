<?php

class Wpjb_User_Type {
    
    /**
     * User dashboard object
     *
     * @var Wpjb_User_Dashboard
     */
    public $dashboard = null;
    
    /**
     * Class constructor
     */
    public function __construct() {
        $this->dashboard = new Wpjb_User_Dashboard;
    }
    
    /**
     * Returns user dashboard
     * 
     * @return Wpjb_User_Dashboard
     */
    public function getDashboard() {
        return $this->dashboard;
    }
}
