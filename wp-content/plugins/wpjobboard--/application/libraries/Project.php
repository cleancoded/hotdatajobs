<?php

 /**
  * @property Wpjb_Utility_Assets $assets
  * @property Wpjb_Utility_AdminMenu $admin_menu
  * @property Wpjb_Utility_Rewrite $rewrite
  * @property Wpjb_User_Manager $user_manager
  * @property Wpjb_Shortcode_Manager $shortcode 
  * @property Wpjb_Singular_Manager $singular
  * @property Wpjb_Customize_Manager $customize
  */

class Wpjb_Project extends Daq_ProjectAbstract
{
    /**
     * WPJB Project singleton
     *
     * @var Wpjb_Project
     */
    protected static $_instance = null;
    
    /**
     * Help Screen
     *
     * @var Wpjb_Utility_HelpScreen
     */
    public $helpScreen = null;
    
    /**
     * Payment Manager
     *
     * @var Wpjb_Payment_Factory
     */
    public $payment = null;
    
    /**
     * Version is modified by build script.
     */
    const VERSION = "5.4.0";

    /**
     * Returns instance of self
     *
     * @return Wpjb_Project
     */
    public static function getInstance()
    {
        if(self::$_instance === null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * WPJB Getter
     * 
     * Returns a key from WPJB environment.
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        return $this->env($name);
    }
    
    /**
     * Register Admin Application
     * 
     * Registers and application which allows to manage WPJB pages in 
     * the administration panel.
     * 
     * @since 5.0
     * @return void
     */
    public function registerAppAdmin() {

        $routes = Daq_Config::parseIni(
            $this->path("app_config")."/admin-routes.ini",
            $this->path("user_config")."/admin-routes.ini",
            true
        );

        $view = new Daq_View($this->getBaseDir().$this->pathRaw("admin_views"));
        $view->addHelper("url", new Daq_Helper_AdminUrl());
        $view->addHelper("flash", new Daq_Helper_Flash_User("wpjb_admin_flash"));
        
        $application = new Wpjb_Application_Admin;
        $application->isAdmin(true);
        $application->setRouter(new Daq_Router($routes));
        $application->setController("Wpjb_Module_Admin_*");
        $application->setView($view);
        
        $this->addApplication("admin", $application);
    }
    
    /**
     * Register API Application
     * 
     * Registers an API application which is used when user is requesting
     * and URL with "wpjobboard" query var.
     * 
     * @since 5.0
     * @return void
     */
    public function registerAppApi() {
        
        $view = new Daq_View();
        $view->addHelper("flash", new Wpjb_Utility_Session);
        
        $routes = Daq_Config::parseIni($this->path("app_config")."/api-routes.ini", null, true);
        
        $api = new Wpjb_Application_Api;
        $api->setRouter(new Daq_Router($routes));
        $api->setController("Wpjb_Module_Api_*");
        $api->addOption("query_var", "wpjobboard");
        $api->setView($view);

        $this->addApplication("api", $api);
    }
    
    /**
     * Registers Filters and Actions used in wp-admin panel.
     * 
     * This function is run only if is_admin() === true.
     * 
     * Note this also includes actions and filters for AJAX requests.
     * 
     * @since 5.0
     * @return void
     */
    public function runAdmin() {
        
        $this->registerAppAdmin();
        $this->registerAppApi();
        
        $so = new Wpjb_Utility_ScreenOptions();
        
        add_filter('screen_settings', array($so, "screenSettings"), 10, 2 );
        add_filter('set-screen-option', array($so, "setScreenOptions"), 11, 3);
        add_filter('screen_options_show_screen', array($so, "showScreen"), 10, 2);

        //Wpjb_Upgrade_Manager::connect(self::VERSION);
        Wpjb_Utility_AdminNotices::connect();

        $manager = new stdClass();
        $manager->wpjobboard = new Wpjb_Upgrade_Manager(
            "wpjobboard/index.php", 
            "wpjobboard", 
            self::VERSION
        );
        $manager->wpjobboard->connect();
        
        $this->setEnv("upgrade", $manager);
        
        if(wpjb_conf("version")) {
            add_action("init", array("Wpjb_Upgrade_Manager", "update"));
        }
        
        add_action("admin_menu", array($this->admin_menu, "left"));
        
    }
    
    /**
     * Registers Actions and Filters used in the frontend
     * 
     * This function is run only if is_admin() === false.
     * 
     * @since 5.0
     * @return void
     */
    public function runFrontend() {
        
        add_filter('init', array($this, "login"), 15);
        add_action('wp_footer', array($this, "ttFix"));
        add_filter("no_texturize_tags", array($this, "nonoTags"));
        
        
        foreach((array)$this->conf("front_recaptcha_enabled") as $hook) {
            add_filter($hook, array($this, "recaptcha"));
        }

        if($this->conf("honeypot_enabled") == "1") {
            Wpjb_Utility_Registry::set("honeypot", new Wpjb_Utility_Honeypot());
        }

        if($this->conf("timetrap_enabled") == "1") {
            Wpjb_Utility_Registry::set("timetrap", new Wpjb_Utility_Timetrap());
        }
        

    }
    
    public function pluginsLoaded()
    {
        $this->setEnv("user_manager", new Wpjb_User_Manager());
        $this->setEnv("rewrite", new Wpjb_Utility_Rewrite());
    }
    
    /**
     * Registers Actions and Filters required for both wp-admin and frontend
     * 
     * This is main WPJB function it:
     * - registers objects in the environment
     * - registers shortcodes
     * - exexcutes admin or frontend filters (depending on is_admin())
     * - registers required WPJB actions and filters
     * 
     * @since 3.0
     * @return void
     */
    public function run()
    {
        load_plugin_textdomain("wpjobboard", false, "wpjobboard/languages");
        
        $this->setEnv("assets", new Wpjb_Utility_Assets());
        $this->setEnv("admin_menu", new Wpjb_Utility_AdminMenu());
        $this->setEnv("redirect", new Wpjb_Utility_Redirect());
        $this->setEnv("session", new Wpjb_Session_Manager());
        $this->setEnv("shortcode", new Wpjb_Shortcode_Manager());
        $this->setEnv("singular", new Wpjb_Singular_Manager());
        
        
        $this->setEnv("cpt", new Wpjb_Utility_Cpt());
        //$this->setEnv("customize", new Wpjb_Customize_Manager());
        
        add_action("plugins_loaded", array($this, "pluginsLoaded"));
        
        $this->shortcode->setupListeners();
        $this->singular->setupListeners();

        add_action("admin_bar_menu", array($this->admin_menu, "bar"), 1000 );
        add_action('deleted_user', array($this, "deletedUser"));
        add_filter("init", array($this, "init"));
        add_filter("wpjb_application_status", array( $this, "loadApplicationStatuses"), 15 );
        
        // shortcodes 
        add_shortcode('wpjb_flash', 'wpjb_flash');
        
        // events
        add_action("wpjb_event_import", "wpjb_event_import");
        add_action("wpjb_event_expiring_jobs", "wpjb_event_expiring_jobs");
        add_action("wpjb_event_subscriptions_daily", "wpjb_event_subscriptions_daily");
        add_action("wpjb_event_subscriptions_weekly", "wpjb_event_subscriptions_weekly");
        add_action("wpjb_event_clean_memberships", "wpjb_event_clean_memberships");
   
        $this->setEnv("uses_cpt", true);

        // payment methods
        $payments = array(
            new Wpjb_Payment_Credits,
            new Wpjb_Payment_PayPal,
        );
        
        if(version_compare(PHP_VERSION, "5.3.0", ">=")) {
            $payments[] = new Wpjb_Payment_Stripe;
        }
        
        $this->payment = new Wpjb_Payment_Factory($payments);
        $this->payment->sort();
        
        
        $linkedin_share = $this->conf("linkedin_share");
        $linkedin_apply = $this->conf("linkedin_apply");
        
        if($this->conf("posting_tweet")) {
            add_filter("wpjb_job_published", array("Wpjb_Service_Twitter", "tweet"));
        }
        if($this->conf("facebook_share")) {
            add_filter("wpjb_job_published", array("Wpjb_Service_Facebook", "share"));
        }
        if(isset($linkedin_share[0]) && $linkedin_share[0]==1) {
            add_filter("wpjb_job_published", array("Wpjb_Service_Linkedin", "share"));
        }
        if(isset($linkedin_apply[0]) && $linkedin_apply[0]==1) {
            add_action("wpjb_tpl_single_actions", array("Wpjb_Service_Linkedin", "apply"), 5, 2);
            add_filter("wp", array("Wpjb_Service_Linkedin", "dispatch"), 20);
            add_action("wpja_minor_section_apply", array("Wpjb_Service_Linkedin", "sectionApply"));
        }

        $indeed_backfill = $this->conf("indeed_backfill", array());
        if(in_array("enabled-list", $indeed_backfill) || in_array("enabled-search", $indeed_backfill)) {
            Wpjb_Service_Indeed::connect();
        }
        
        $ziprecruiter_backfill = $this->conf("ziprecruiter_backfill", array());
        if(in_array("enabled-list", $ziprecruiter_backfill) || in_array("enabled-search", $ziprecruiter_backfill)) {
            Wpjb_Service_ZipRecruiter::connect();
        }
        
        $indeed_conv = wpjb_conf("indeed_conversion_tracking_enable");
        if(is_array($indeed_conv) && $indeed_conv[0] == "1") {
            add_filter("wp_footer", array("Wpjb_Service_Indeed", "conversion"));
        }
        
        if(wpjb_conf("cv_login_only_approved") || wpjb_conf("employer_login_only_approved")) {
            Wpjb_Utility_Moderation::connect();
        }
        
        $request = Daq_Request::getInstance();
        
        if($request->post("txn_id") && is_numeric($request->post("custom"))) {
            
            $payment = new Wpjb_Model_Payment($request->post("custom"));
            
            if($payment->exists() && $payment->status != 2) {
                $paypal = new Wpjb_Payment_PayPal;

                add_action("wp_footer", array($paypal, "progressAction"));
            }
        }
        
        if(is_admin()) {
            $this->runAdmin();
        } else {
            $this->runFrontend();
        }
        
        $this->_init();
    }

    /**
     * WPJB Main init action
     * 
     * Executes common WPJB "init" actions.
     * 
     * @global wp $wp
     * @global wp_rewrite $wp_rewrite
     * 
     * @since 3.0
     * @return void
     */
    public function init()
    {   
        global $wp, $wp_rewrite;

        register_post_status("wpjb-disabled", array(
            "public" => current_user_can( "edit_pages" )
        ));
        
        if(!$this->conf("front_hide_bookmarks") && current_user_can("manage_resumes")) {
            add_action("wpjb_tpl_single_actions", array("Wpjb_Model_Shortlist", "displaySingleJob"), 5);
        }
        
        if($this->conf("count_date") != date_i18n("Y-m-d")) {
            $this->scheduleEvent();
        }
        
        if(!is_user_logged_in()) {
            wpjb_transient_id();
        }

    }

    /**
     * Counts amount of jobs and resumes in categories and types
     * 
     * This function is run to calculte number of jobs and resumes in each
     * category and job type, once calculated the data is cached.
     * 
     * The cache is restored once daily or when job is added, edited or removed.
     * 
     * This function is called in WPJB init function
     * 
     * @see self::init()
     * 
     * @return void
     */
    public static function scheduleEvent()
    {
        $select = new Daq_Db_Query();
        $select = $select->select("t2.tag_id AS `id`, COUNT(*) AS `cnt`");
        $select->from("Wpjb_Model_Job t1");
        $select->join("t1.tagged t2", "object = 'job'");
        $select->where("t1.is_active = 1");
        if( wpjb_conf( "front_hide_filled", false ) ) {
            $select->where("t1.is_filled = 0");
        }
        $select->where("t1.job_expires_at >= ?", date("Y-m-d"));
        $select->group("t2.tag_id");

        $all = array();
        
        foreach($select->fetchAll() as $r) {
            $all[$r->id] = $r->cnt;
        }

        $conf = self::getInstance();
        $conf->setConfigParam("count", $all);
        $conf->setConfigParam("count_date", date_i18n("Y-m-d"));
        $conf->saveConfig();
    }

    /**
     * Deletes a company or resume from the database
     * 
     * This function is called when a wp-user is deleted it checks if the user 
     * had a Resume or Company and if he did then the data is deleted from the
     * database 
     * 
     * This function is called by deleted_user action
     * 
     * @see deleted_user action
     * 
     * @param int $id    Company or Resume ID
     * @return void
     */
    public function deletedUser($id)
    {
        foreach(array("Wpjb_Model_Company", "Wpjb_Model_Resume") as $class) {
            $query = new Daq_Db_Query();
            $result = $query->select()
                ->from("Wpjb_Model_Company t")
                ->where("user_id = ?", $id)
                ->limit(1)
                ->execute();

            if(isset($result[0])) {
                $object = $result[0];
                $object->delete();
            }
        }

    }

    /**
     * WPJB Installation Function
     * 
     * This function is run when WPJB is activated. 
     * 
     * It is registered in Daq_ProjectAbstract::_init()
     * 
     * @see Daq_ProjectAbstract::_init()
     * 
     * @global wpdb $wpdb
     * @global wp_rewrite $wp_rewrite
     * @global wp_roles $wp_roles
     * @return boolean
     */
    public function install()
    {
        global $wpdb, $wp_rewrite, $wp_roles;
        
        /* @var $wpdb wpdb */
        
        if(stripos(PHP_OS, "win")!==false || true) {
            $mods = explode(",", $wpdb->get_var("SELECT @@session.sql_mode"));
            $mods = array_map("trim", $mods);
            $invalid = array(
                "STRICT_TRANS_TABLES", "STRICT_ALL_TABLES", "TRADITIONAL"
            );
            foreach($invalid as $m) {
                if(in_array($m, $mods)) {
                    $wpdb->query("SET @@session.sql_mode='' ");
                    break;
                }
            }
        }
        
        $db = Daq_Db::getInstance();
        if($db->getDb() === null) {
            $db->setDb($wpdb);
        }

        global $wp_roles;
        remove_role("employer");
        
        add_role("employer", "Employer", array("read"=>true, "manage_jobs"=>true));
        $wp_roles->add_cap("administrator", "manage_jobs");
        $wp_roles->add_cap("administrator", "manage_resumes");
        $wp_roles->add_cap("subscriber", "manage_resumes");
        
        wp_clear_scheduled_hook("wpjb_event_expiring_jobs");
        wp_schedule_event(current_time('timestamp'), "daily", "wpjb_event_expiring_jobs");
        
        wp_clear_scheduled_hook("wpjb_event_subscriptions_daily");
        wp_schedule_event(current_time('timestamp'), "hourly", "wpjb_event_subscriptions_daily");
        
        wp_clear_scheduled_hook("wpjb_event_subscriptions_weekly");
        wp_schedule_event(current_time('timestamp'), "hourly", "wpjb_event_subscriptions_weekly");
        
        wp_clear_scheduled_hook("wpjb_event_clean_memberships");
        wp_schedule_event(current_time('timestamp'), "daily", "wpjb_event_clean_memberships");

        $instance = self::getInstance();
        //$appj = $instance->getApplication("frontend");
        //$appr = $instance->getApplication("resumes");

        $config = $instance;
        
        $cpt = new Wpjb_Utility_Cpt;
        $cpt->init();
        
        /* @var $wp_rewrite wp_rewrite */
        $wp_rewrite->flush_rules();

        if($this->conf("first_run")!==null) {
            return true;
        }
        
        $config->setConfigParam("urls_mode", "2");
        $config->setConfigParam("urls_cpt", "1");

        $config->setConfigParam("first_run", 0);
        $config->setConfigParam("front_show_related_jobs", 1);
        $config->setConfigParam("show_maps", 1);
        $config->setConfigParam("cv_enabled", 1);
        $config->saveConfig();

        $file = $this->path("install") . "/install.sql";
        $queries = explode("; --", file_get_contents($file));

        foreach($queries as $query) {
            $query = trim($query);
            if(!empty($query)) {
                $query = str_replace('{$wpdb->prefix}', $wpdb->prefix, $query);
                $query = str_replace('{$wpjb->prefix}', $wpdb->prefix, $query);
                $wpdb->query($query);
            }
        }

        $email = get_option("admin_email");
        $query =  new Daq_Db_Query();
        $result = $query->select("*")->from("Wpjb_Model_Email t")->execute();
        foreach($result as $r) {
            if($r->mail_from == "") {
                $r->mail_from = $email;
                $r->save();
            }
        }

        $config = Wpjb_Project::getInstance();
        $config->saveConfig();

        Wpjb_Upgrade_Manager::update();

        $ptmp = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => '',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_content' => ''
        );
        $pages = array(
            "urls_link_job" => array(
                "id" => null,
                "post_parent" => null,
                "post_title" => "Jobs",
                "post_content" => "[wpjb_jobs_list]",
            ),
            "urls_link_job_add" => array(
                "id" => null,
                "post_parent" => "urls_link_job",
                "post_title" => "Post a Job",
                "post_content" => "[wpjb_jobs_add]",
            ),
            "urls_link_job_search" => array(
                "id" => null,
                "post_parent" => "urls_link_job",
                "post_title" => "Advanced Search",
                "post_content" => "[wpjb_jobs_search]",
            ),
            "urls_link_resume" => array(
                "id" => null,
                "post_parent" => null,
                "post_title" => "Resumes",
                "post_content" => "[wpjb_resumes_list]",
            ),
            "urls_link_resume_search" => array(
                "id" => null,
                "post_parent" => "urls_link_resume",
                "post_title" => "Advanced Search",
                "post_content" => "[wpjb_resumes_search]",
            ),
            "urls_link_emp_panel" => array(
                "id" => null,
                "post_parent" => null,
                "post_title" => "Employer Panel",
                "post_content" => "[wpjb_employer_panel]",
            ),
            "urls_link_emp_reg" => array(
                "id" => null,
                "post_parent" => "urls_link_emp_panel",
                "post_title" => "Employer Registration",
                "post_content" => "[wpjb_employer_register]",
            ),
            "urls_link_cand_panel" => array(
                "id" => null,
                "post_parent" => null,
                "post_title" => "Candidate Panel",
                "post_content" => "[wpjb_candidate_panel]",
            ),
            "urls_link_cand_reg" => array(
                "id" => null,
                "post_parent" => "urls_link_cand_panel",
                "post_title" => "Candidate Registration",
                "post_content" => "[wpjb_candidate_register]",
            ),
            "urls_link_membership_pricing" => array(
                "id" => null,
                "post_parent" => null,
                "post_title" => "Membership Pricing",
                "post_content" => "[wpjb_membership_pricing]",
            ),
            "urls_link_cand_membership" => array(
                "id" => null,
                "post_parent" => null,
                "post_title" => "Candidate Membership Pricing",
                "post_content" => "[wpjb_candidate_membership]",
            ),
            
        );
        
        foreach($pages as $key => $pdata) {
            
            $parr = $ptmp;
            if($pdata['post_parent']) {
                $parr['post_parent'] = $pages[$pdata['post_parent']]['id'];
            }
            $parr['post_title'] = $pdata['post_title'];
            $parr['post_content'] = $pdata['post_content'];

            $id = wp_insert_post($parr);

            $pages[$key]['id'] = $id;
            
            $config->setConfigParam($key, (int)$id);
            $config->saveConfig();
            
        }
        
        return true;
    }

    /**
     * Uninstall Action
     * 
     * Currently, not being used, WPJB is using the uninstall.php file instead.
     * 
     * @return boolean
     */
    public static function uninstall()
    {
        return true;
    }

    /**
     * Deactivation Hook
     * 
     * Currently, WPJB does not need to do anything when the plugin is deactivated.
     * 
     * @return boolean
     */
    public function deactivate()
    {
        return true;
    }
    
    /**
     * Adds a captcha to form
     * 
     * This function is applied using Form filters. 
     * 
     * You can check to which forms the reCAPTCHA was applied to in 
     * wp-admin / Settings (WPJB) / reCAPTCHA panel
     * 
     * The filter is applied in self::runFrontend() function
     * 
     * @see self::runFrontend()
     * 
     * @param Daq_Form_Abstract $form   Form to which captcha should be added.
     * @return Daq_Form_Abstract
     */
    public function recaptcha($form)
    {
        $form->addGroup("recaptcha", __("Captcha", "wpjobboard"), 1000);
        
        $e = $form->create("recaptcha_response_field");
        $e->setRequired(true);
        $e->addValidator(new Daq_Validate_Callback("wpjb_recaptcha_check"));
        $e->setRenderer("wpjb_recaptcha_form");
        $e->setLabel(__("Captcha", "wpjobboard"));
        
        $form->addElement($e, "recaptcha");
        
        if($form instanceof Wpjb_Form_Apply) {
            $form->removeElement("protection");
        }
        
        return $form;
    }
    
    /**
     * Logins a user if they submitted login and password using WPJB login form
     * 
     * This function will login user if there is a $_POST request with variables:
     * _wpjb_action = login, username and password.
     * 
     * This function is executed in "init" filter
     * 
     * @see init action
     * @see self::runFrontend()
     * 
     * @return void
     */
    public function login()
    {
        if(Daq_Request::getInstance()->post("_wpjb_action") != "login") {
            return;
        }

        $form = new Wpjb_Form_Login();
        $user = $form->isValid(Daq_Request::getInstance()->post());
        /* @var $user WP_User */
        $flash = new Wpjb_Utility_Session();
        $errors = $form->getErrors();

        if(isset($errors["recaptcha_response_field"])) {
            $flash->addError(__("Incorrect Captcha.", "wpjobboard"));
        } elseif($user instanceof WP_Error) {
            foreach($user->get_error_messages() as $error) {
                $flash->addError($error);
            }
        } elseif($user === false) {
            $flash->addError(__("Incorrect username or password.", "wpjobboard"));
        } else {
            $r = trim($form->value("redirect_to"));
            if(!empty($r)) {
                $redirect = $r;
            } else if($user->has_cap("manage_jobs")) {
                $redirect = wpjb_link_to("employer_home");
            } else if($user->has_cap("manage_resumes")) {
                $redirect = wpjr_link_to("myresume_home");
            } else {
                $redirect = home_url();
            }

            if($user->has_cap("manage_jobs")) {
                $type = "employer";
            } else {
                $type = "candidate";
            }

            $login = array(
                "redirect_to" => $redirect,
                "message" => __("You have been logged in.", "wpjobboard")
            );

            $login = apply_filters("wpjb_login", $login, $type);

            if(isset($login["message"]) && !empty($login["message"])) {
                $flash->addInfo($login["message"]);
                $flash->save();
            }

            wp_redirect($login["redirect_to"]);
            exit;
        }
    }
    
    /**
     * Renders WPJB singular page content
     * 
     * @deprecated since version 5.0
     * 
     * @param string $content   content
     * @return string
     */
    public function theContentCpt($content)
    {
        if (is_singular('job') && in_the_loop()) {
            return $this->singular->job->main(get_the_ID());
        } elseif(is_singular('company') && in_the_loop()) {
            return $this->singular->company->main(get_the_ID());
        } elseif(is_singular('resume') && in_the_loop()) {
            return $this->singular->resume->main(get_the_ID());
        } else {
            return $content;
        }
    }
    
    /**
     * Renders some additional CSS for TwentyTwelve theme
     * 
     * This function is being used to fix some random font resizing in 
     * the TwentyTwelve theme.
     * 
     * Function is executed by wp_footer action
     * 
     * @see wp_footer action
     * @return void
     */
    public function ttFix()
    {   
        $theme = wp_get_theme();
        if($theme->get_template() == "twentytwelve") {
            echo '<style type="text/css">';
            echo '.wpjb-form select { padding: 0.428571rem }'.PHP_EOL;
            echo '#wpjb-main img { border-radius: 0px; box-shadow: 0 0px 0px rgba(0, 0, 0, 0) }'.PHP_EOL;
            echo 'table.wpjb-table { font-size: 13px }'.PHP_EOL;
            echo '.entry-content .wpjb a:visited { color: #21759b }'.PHP_EOL;
            echo 'footer.entry-meta { display: none }'.PHP_EOL;
            echo '.nav-single { display: none }'.PHP_EOL;
            echo '.wpjb-col-title a { text-decoration: none; color: #21759b !important; }'.PHP_EOL;
            echo '.wpjb-widget .wpjb-custom-menu-link a { text-decoration: none; }'.PHP_EOL;
            echo '</style>';
        }
    }
    
    /**
     * Loads application statuses saved in the database.
     * 
     * This function is executed by wpjb_application_status filter
     * 
     * @see wpjb_application_status filter
     * 
     * @since 5.4.0
     * @param array $statuses   List of available statuses
     * @return array            Updated list
     */
    public function loadApplicationStatuses( $statuses ) {

        $saved = wpjb_conf( "wpjb_application_statuses" );

        if( is_array( $saved ) && ! empty( $saved ) ) {
            foreach( $saved as $k => $s ) {
                $statuses[$k] = $s;
            } 
        }
        
        return $statuses;
    }
}

