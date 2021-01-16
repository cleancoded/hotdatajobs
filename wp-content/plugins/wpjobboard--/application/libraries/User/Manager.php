<?php

class Wpjb_User_Manager {
    
    /**
     * Array of registered user types
     *
     * @var array
     */
    protected $_user = array();
    
    /**
     * Class Constructor
     * 
     * Registers different user types
     * 
     * @since 4.6.0
     * @return void
     */
    public function __construct() {
        $users = array();
        $users["employer"] = $this->_registerEmployer();
        $users["candidate"] = $this->_registerCandidate();
        
        $this->_user = apply_filters( "wpjb_user_types", $users );
    }
    
    /**
     * Registers Employer User Type
     * 
     * @since 4.6.0
     * @return void
     */
    protected function _registerEmployer() {
        
        $shortcodes = Wpjb_Project::getInstance()->shortcode;
        $shortcode = $shortcodes->wpjb_employer_panel;
        
        $user = new Wpjb_User_Type;
        $user->role = "Employer";
        $user->panel = "wpjb_employer_panel";

        $user->dashboard->setLinkCallback("wpjb_link_to");
        $user->dashboard->setQueryVar("wpjb-employer");
        $user->dashboard->setCapability("manage_jobs");
        
        $user->dashboard->addMenuSection("manage", __("Manage", "wpjobboard"));
        $user->dashboard->addMenuSection("account", __("Account", "wpjobboard"));

        $user->dashboard->addButton("manage", "job_add", __("Post a Job", "wpjobboard"), "wpjb-icon-plus");
        $user->dashboard->addButton("manage", "employer_panel", __("Listings", "wpjobboard"), "wpjb-icon-briefcase");
        $user->dashboard->addButton("manage", "job_applications", __("Applications", "wpjobboard"), "wpjb-icon-inbox");
        $user->dashboard->addButton("manage", "employer_edit", __("Edit Profile", "wpjobboard"), "wpjb-icon-pencil-squared");
        $user->dashboard->addButton("manage", "membership", __("Membership", "wpjobboard"), "wpjb-icon-users", "membership");
        $user->dashboard->addButton("manage", "payment_history", __("Payment History", "wpjobboard"), "wpjb-icon-credit-card");

        $user->dashboard->addButton("account", "employer_logout", __("Logout", "wpjobboard"), "wpjb-icon-off");
        $user->dashboard->addButton("account", "employer_password", __("Change Password", "wpjobboard"), "wpjb-icon-asterisk");
        $user->dashboard->addButton("account", "employer_delete", __("Delete Account", "wpjobboard"), "wpjb-icon-trash");
        
        $user->dashboard->addPage("job_add", array("wpjb-employer"=>"job-add"), array($shortcode, "jobAdd"));
        $user->dashboard->addPage("job_preview", array("wpjb-employer"=>"job-preview"), array($shortcode, "jobPreview"));
        $user->dashboard->addPage("job_save", array("wpjb-employer"=>"job-save"), array($shortcode, "jobSave"));
        $user->dashboard->addPage("job_reset", array("wpjb-employer"=>"job-reset"), array($shortcode, "jobReset"));
        $user->dashboard->addPage("job_edit", array("wpjb-employer"=>"job-edit", "wpjb-id"=>"([0-9]{1,})"), array($shortcode, "jobEdit"));
        $user->dashboard->addPage("job_delete", array("wpjb-employer"=>"job-delete", "wpjb-id"=>"([0-9]{1,})"), array($shortcode, "jobDelete"));
        $user->dashboard->addPage("employer_login", "login", array($shortcode, "login"));
        $user->dashboard->addPage("employer_logout", "logout", array($shortcode, "logout"));
        $user->dashboard->addPage("employer_edit", "edit", array($shortcode, "employerEdit"));
        $user->dashboard->addPage("employer_panel", "listings", array($shortcode, "listings"));
        $user->dashboard->addPage("employer_password", "password", array($shortcode, "password"));
        $user->dashboard->addPage("employer_delete", "delete", array($shortcode, "employerDelete"));
        $user->dashboard->addPage("job_application", array("wpjb-employer"=>"application", "wpjb-id"=>"([0-9]{1,})"), array($shortcode, "application"));
        $user->dashboard->addPage("job_applications", "applications", array($shortcode, "applications"));
        $user->dashboard->addPage("membership_details", array("wpjb-employer"=>"membership-details", "wpjb-id"=>"([0-9]{1,})"), array($shortcode, "membershipDetails"));
        $user->dashboard->addPage("membership_purchase", array("wpjb-employer"=>"membership-purchase", "wpjb-id"=>"([0-9]{1,})"), array($shortcode, "membershipPurchase"));
        $user->dashboard->addPage("membership", "membership", array($shortcode, "membership"));
        $user->dashboard->addPage("payment_history", "payment-history", array($shortcode, "paymentHistory"));
        $user->dashboard->addPage("payment_details", array("wpjb-employer"=>"payment-details", "wpjb-id"=>"([0-9]{1,})"), array($shortcode, "paymentDetails"));
        $user->dashboard->addPage("employer_home", true, array($shortcode, "home"));

        return $user;
    }
    
    /**
     * Registers Candidate User Type
     * 
     * @since 4.6.0
     * @return void
     */
    protected function _registerCandidate() {
        
        $shortcode = Wpjb_Project::getInstance()->shortcode->wpjb_candidate_panel;
        
        $user = new Wpjb_User_Type;
        $user->role = "Subscriber";
        $user->panel = "wpjb_candidate_panel";
        
        $user->dashboard->setLinkCallback("wpjr_link_to");
        $user->dashboard->setQueryVar("wpjb-candidate");
        $user->dashboard->setCapability("manage_resumes");
        
        $user->dashboard->addMenuSection("manage", __("Manage", "wpjobboard"));
        $user->dashboard->addMenuSection("account", __("Account", "wpjobboard"));
        
        $user->dashboard->addButton("manage", "myresume", __("My Resume", "wpjobboard"), "wpjb-icon-doc-text");
        $user->dashboard->addButton("manage", "myapplications", __("My Applications", "wpjobboard"), "wpjb-icon-inbox");
        $user->dashboard->addButton("manage", "mybookmarks", __("My Bookmarks", "wpjobboard"), "wpjb-icon-bookmark");
        $user->dashboard->addButton("manage", "myalerts", __("My Alerts", "wpjobboard"), "wpjb-icon-bell");
        $user->dashboard->addButton("manage", "mymembership", __("Membership", "wpjobboard"), "wpjb-icon-users");
        $user->dashboard->addButton("manage", "mypaymenthistory", __("Payment History", "wpjobboard"), "wpjb-icon-credit-card");

        $user->dashboard->addButton("account", "logout", __("Logout", "wpjobboard"), "wpjb-icon-off");
        $user->dashboard->addButton("account", "myresume_password", __("Change Password", "wpjobboard"), "wpjb-icon-asterisk");
        $user->dashboard->addButton("account", "myresume_delete", __("Delete Account", "wpjobboard"), "wpjb-icon-trash");
        
        $user->dashboard->addPage("myresume", "my-resume", array($shortcode, "resume"));
        $user->dashboard->addPage("myapplications", "my-applications", array($shortcode, "applications"));
        $user->dashboard->addPage("mybookmarks", "my-bookmarks", array($shortcode, "bookmarks"));
        $user->dashboard->addPage("myalerts", "my-alerts", array($shortcode, "alerts"));
        $user->dashboard->addPage("mymembership", "my-membership", array($shortcode, "membership"));
        $user->dashboard->addPage("mypaymenthistory", "my-payment-history", array($shortcode, "paymentHistory"));
        $user->dashboard->addPage("logout", "logout", array($shortcode, "logout"));
        $user->dashboard->addPage("myresume_password", "password", array($shortcode, "password"));
        $user->dashboard->addPage("myresume_delete", "delete", array($shortcode, "delete"));
        $user->dashboard->addPage("myresume_home", true, array($shortcode, "home"));

        return $user;
    }
    
    /**
     * Adds user type to the list
     * 
     * This function registers a new user type.
     * 
     * @param string $type          User type identifier
     * @param Wpjb_User_Type $user  User type object
     */
    public function addUser($type, Wpjb_User_Type $user) {
        $this->_user[$type] = $user;
    }
    
    /**
     * Returns defined user type.
     * 
     * If $type does not exist (was not registered) the function will throw 
     * an exception.
     * 
     * @param string $type       User type identifier (by default either "employer" or "candidate")
     * @return Wpjb_User_Type
     * @throws Exception        If provided user $type does not exist.
     */
    public function getUser($type) {
        if(isset($this->_user[$type])) {
            return $this->_user[$type];
        } else {
            throw new Exception("User type [$type] does not exist.");
        }
    }
    
    /**
     * Returns registered user types
     * 
     * @since 4.6.0
     * @return array    Array of registered user types
     */
    public function getUsers() {
        return $this->_user;
    }
    
    /**
     * Generates user dashboard array
     * 
     * This function will generate an array which can be used with user dashboard 
     * template it will then generate all the links in the user dashboard.
     * 
     * @param string $type      User type identifier
     * @param int $page_id      Page ID
     * @return array            User dashboard array
     */
    public function buildDashboard($type, $page_id = null)
    {
        $board = array();
        $dash = $this->_user[$type]->dashboard->getMenu();
        
        foreach($dash as $key => $section) {
            $board[$key] = array(
                "title" => $section["title"],
                "links" => array()
            );
            
            foreach($section["links"] as $key2 => $link) {
                $board[$key]["links"][$key2] = $link;
                $board[$key]["links"][$key2]["url"] = wpjb_link_to($key2, null, array(), $page_id);
            }
        }

        return $board;
    }
}
