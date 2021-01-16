<?php

class Wpjb_Utility_Assets {
    
    /**
     * Class constructor
     * 
     * Registers "init" action which will register scripts, styles and localizations.
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        add_action("init", array($this, "registerAll"));
        
        if(is_admin()) {
            add_action("admin_enqueue_scripts", array($this, "adminEnqueueScripts"));
            add_action("admin_print_styles", array($this, "adminPrintStyles"));
        } else {
            add_action('wp_enqueue_scripts', array($this, "addScriptsFront"), 20);
        }
    }
    
    /**
     * Register All (scripts, styles and localizations)
     * 
     * This function is called by "init" action
     * 
     * @see init action
     * @since 5.0
     * @return void
     */
    public function registerAll() {
        $this->registerScripts();
        $this->localizeScripts();
        $this->registerStyles();
    }
    
    /**
     * Register Common and Frontend or wp-admin scripts
     * 
     * Depending on is_admin() registers wp-admin or frontend scripts
     * 
     * @see is_admin()
     * @since 5.0
     * @return void
     */
    public function registerScripts() {
        $this->registerScriptsCommon();
        
        if(is_admin()) {
            $this->registerScriptsAdmin();
        } else {
            $this->registerScriptsFrontend();
        }
    }
    
    /**
     * Register localizations for WPJB scripts
     * 
     * Depending on is_admin() registers localizations for wp-admin or frontend scripts
     * 
     * @see is_admin()
     * @since 5.0
     * @return void
     */
    public function localizeScripts() {
        $this->localizeScriptsCommon();
        
        if(is_admin()) {
            $this->localizeScriptsAdmin();
        } else {
            $this->localizeScriptsFrontend();
        }
    }
    
    /**
     * Register Common and Frontend or wp-admin styles
     * 
     * Depending on is_admin() registers wp-admin or frontend styles
     * 
     * @see is_admin()
     * @since 5.0
     * @return void
     */
    public function registerStyles() {
        $this->registerStylesCommon();
        
        if(is_admin()) {
            $this->registerStylesAdmin();
        } else {
            $this->registerStylesFrontend();
        }
    }
    
    /**
     * Registers Common Scripts
     * 
     * Common scripts are being used both in wp-admin and the frontend so they
     * need to be always registered.
     * 
     * @since 5.0
     * @return void
     */
    public function registerScriptsCommon() {
        $v = Wpjb_Project::VERSION;
        $p = plugins_url().'/wpjobboard/public/js/';
        $x = plugins_url().'/wpjobboard/application/vendor/';
        
        wp_register_script("wpjb-suggest", $p."wpjb-suggest.js", array("jquery"), $v, true );
        
        wp_register_script("wpjb-vendor-selectlist", $x."select-list/jquery.selectlist.pack.js", array("jquery"), null, true);
        wp_register_script("wpjb-plupload", $p."wpjb-plupload.js", array("plupload-all"), $v, true);
        wp_register_script("wpjb-vendor-datepicker", $x."date-picker/js/datepicker.js", array("jquery"));
        
        wp_register_script("wpjb-gmaps-infobox", $p."gcode-infobox.js", array());
        wp_register_script("wpjb-gmaps-markerclusterer", $p."gcode-markerclusterer.js", array());
        
        wp_register_script("wpjb-ace", $x."ace/ace.js", array(), "1.2.6");
        
        wp_register_script("wpjb-vendor-stripe", "https://js.stripe.com/v2/");
        wp_register_script("wpjb-stripe", $p."wpjb-stripe.js", array("jquery"), $v);
        
        wp_register_script('wpjb-myresume', $p.'frontend-myresume.js', array("jquery", "wp-util"), $v );
        
        wp_register_script('wpjb-admin-customize', $p.'admin-customize.js', array("jquery"), $v, true );
    }
    
    /**
     * Registers Admin Scripts
     * 
     * Admin scripts are registered only if user is viewing wp-admin panel.
     * 
     * @since 5.0
     * @return void
     */
    public function registerScriptsAdmin() {
        $v = Wpjb_Project::VERSION;
        $p = plugins_url().'/wpjobboard/public/js/';
        $x = plugins_url().'/wpjobboard/application/vendor/';
        
        //wp_register_script("wpjb-color-picker", $p."jquery.colorPicker.js" );
        wp_register_script("wpjb-color-picker", $p."colorpicker.js" );
        wp_register_script("wpjb-admin", $p."admin.js", array("jquery"), $v);
        wp_register_script("wpjb-admin-job", $p."admin-job.js", array("jquery"), $v);
        wp_register_script("wpjb-admin-resume", $p."admin-resume.js", array("jquery"), $v);
        wp_register_script("wpjb-admin-config-email", $p."admin-config-email.js", array("jquery", "jquery-ui-dialog"), $v);
        wp_register_script("wpjb-admin-config-email-composer", $p."admin-config-email-composer.js", array("jquery", "iris"), $v);
        wp_register_script("wpjb-admin-google-for-jobs", $p."admin-google-for-jobs.js", array("jquery", "wpjb-ace", "jquery-ui-dialog", "jquery-ui-sortable", "wp-util"), $v);
        wp_register_script("wpjb-admin-config-email-editor", $p."admin-config-email-editor.js", array("jquery", "wpjb-ace", "jquery-ui-dialog"), $v);
        wp_register_script("wpjb-admin-config-urls", $p."admin-config-urls.js", array("jquery"), $v);
        wp_register_script("wpjb-admin-export", $p."admin-export.js", array("jquery"), $v);
        wp_register_script("wpjb-admin-import", $p."admin-import.js", array("jquery", "wpjb-plupload"));
        wp_register_script("wpjb-admin-apps", $p."admin-apps.js", array("jquery"), $v, true);
        wp_register_script("wpjb-vendor-ve", $x."visual-editor/visual-editor.js", array("jquery"));
        wp_register_script("wpjb-multi-level-accordion-menu", $p."multi-level-accordion-menu.js", array(), $v);
        wp_register_script("wpjb-admin-user-register", $p."admin-user-register.js", array("jquery-ui-autocomplete", "jquery"), $v);
        wp_register_script("wpjb-admin-alert", $p."admin-alert.js", array("jquery", "wp-util"), $v);
    }
    
    /**
     * Registers Frontend Scripts
     * 
     * Frontend scripts are registered only if user is in the frontend.
     * 
     * @since 5.0
     * @return void
     */
    public function registerScriptsFrontend() {
        $v = Wpjb_Project::VERSION;
        $p = plugins_url().'/wpjobboard/public/js/';
        
        wp_register_script('wpjb-js', $p.'frontend.js', array("jquery"), $v );
        
        wp_register_script('wpjb-payment', $p.'frontend-payment.js', array("jquery"), $v );
        wp_register_script('wpjb-serialize', $p.'serialize.js', array(), $v, true);
        wp_register_script('wpjb-alert', $p.'frontend-alert.js', array("jquery", "wp-util", 'wpjb-serialize'), $v, true);
        wp_register_script('wpjb-manage', $p.'frontend-manage.js', array("jquery"), $v, true);
        wp_register_script('wpjb-manage-apps', $p.'frontend-manage-apps.js', array("jquery"), $v, true);
        
        wp_register_script("wpjb-paypal-reply", $p."wpjb-paypal-reply.js", array("jquery"), $v);

    }
    
    /**
     * Registers Common Localizations
     * 
     * Common localizations are being used both in wp-admin and the frontend so they
     * need to be always registered.
     * 
     * Each localization is registered for one of the scripts registered in
     * self::registerScriptsCommon() function.
     * 
     * @see elf::registerScriptsCommon()
     * 
     * @since 5.0
     * @return void
     */
    public function localizeScriptsCommon() {
        
        $js_date_max_o = new DateTime(WPJB_MAX_DATE);
        $js_date_max = $js_date_max_o->format(wpjb_date_format());
        $js_date_format = str_replace(array("J"), array("B"), wpjb_date_format());
        $js_months = array();
        
        for($i=1; $i<=12; $i++) {
            $js_months[$i] = wpjb_date("2000-$i", "M");
        }
        
        
        wp_localize_script("wpjb-plupload", "wpjb_plupload_lang", array(
            "error" => __("Error", "wpjobboard"),
            "dispose_message" => __("Click here to dispose this message", "wpjobboard"),
            "x_more_left" => __("%d more left", "wpjobboard"),
            "preview" => __("Preview", "wpjobboard"),
            "download_file" => __("Download", "wpjobboard"),
            "delete_file" => __("Delete", "wpjobboard"),
            "play_file" => __("Play", "wpjobboard"),
        ));
        
        wp_localize_script("wpjb-vendor-selectlist", "daq_selectlist_lang", array(
            "hint" => __("Select options ...", "wpjobboard")
        ));
        
        wp_localize_script("wpjb-myresume", "wpjb_myresume_lang", array(
            "ajaxurl" => admin_url("admin-ajax.php"),
            "date_format" => $js_date_format,
            "month_abbr" => $js_months,
            "form_error" => __("There are errors in your form.", "wpjobboard"),
            "close_or_save_all" => __("'Save' or 'Cancel' all Education and Experience boxes before continuing.", "wpjobboard")
        ));
        
        wp_localize_script("wpjb-suggest", "wpjb_suggest_lang", array(
            "ajaxurl" => admin_url('admin-ajax.php')
        ));
        
        wp_localize_script("wpjb-alert", "wpjb_alert_lang", array(
            "ajaxurl" => admin_url("admin-ajax.php"),
            "date_format" => $js_date_format,
            "month_abbr" => $js_months,
            "form_error" => __("There are errors in your form.", "wpjobboard"),
            "close_or_save_all" => __("'Save' or 'Cancel' all Education and Experience boxes before continuing.", "wpjobboard")
        ));
    }
    
    /**
     * Registers Admin Localizations
     * 
     * Admin localizations are registered only if user is viewing wp-admin panel.
     * 
     * Each localization is registered for one of the scripts registered in
     * self::registerScriptsAdmin() function.
     * 
     * @see self::registerScriptsAdmin()
     * 
     * @since 5.0
     * @return void
     */
    public function localizeScriptsAdmin() {
        
        $js_date_max = wpjb_date(WPJB_MAX_DATE);
        $js_date_format = str_replace(array("J"), array("B"), wpjb_date_format());
        $js_months = array();
        
        for($i=1; $i<=12; $i++) {
            $js_months[$i] = wpjb_date("2000-$i", "M");
        }
        
        wp_localize_script("wpjb-admin", "WpjbAdminLang", array(
            "slug_save" => __("save", "wpjobboard"),
            "slug_cancel" => __("cancel", "wpjobboard"),
            "slug_change" => __("change", "wpjobboard"),
            "remove" => __("Do you really want to delete", "wpjobboard"),
            "selectAction" => __("Select action first", "wpjobboard"),
            
        ));
        
        wp_localize_script("wpjb-admin", "wpjb_admin_lang", array(
            "date_format" => $js_date_format,
            "max_date" => $js_date_max,
            "confirm_item_delete" => __("Are you sure you want to delete this item?", "wpjobboard")
        ));
        
        wp_localize_script("wpjb-admin-import", "wpjb_admin_import", array(
            "deleted" => __("Deleted", "wpjobboard"),
            "failed" => __("Failed", "wpjobboard")
        ));
        
        wp_localize_script("wpjb-admin-config-email-editor", "wpjb_admin_config_email_editor", array(
            "yes" => __("Yes"),
            "cancel" => __("Cancel")
        ));
        
        wp_localize_script("wpjb-admin-job", "wpjb_admin_job_lang", array(
            "date_format" => $js_date_format,
            "max_date" => $js_date_max,
            "free_listing" => __("None (free listing)", "wpjobboard"),
            "yesterday" => __("yesterday", "wpjobboard"),
            "immediately" => __("immediately", "wpjobboard"),
            "tomorrow" => __("tomorrow", "wpjobboard"),
            "day" => __("%d day", "wpjobboard"),
            "days" => __("%d days", "wpjobboard")
        ));
        
        wp_localize_script("wpjb-admin-resume", "wpjb_admin_resume_lang", array(
            "date_format" => $js_date_format,
            "max_date" => $js_date_max,
        ));
    }
    
    /**
     * Registers Frontend Localizations
     * 
     * Frontend localizations are registered only if user is in the frontend.
     * 
     * Each localization is registered for one of the scripts registered in
     * self::registerScriptsFrontend() function.
     * 
     * @see self::registerScriptsFrontend()
     * 
     * @since 5.0
     * @return void
     */
    public function localizeScriptsFrontend() {
        
        $request = Daq_Request::getInstance();
        
        $js_date_max_o = new DateTime(WPJB_MAX_DATE);
        $js_date_max = $js_date_max_o->format(wpjb_date_format());
        $js_date_format = str_replace(array("J"), array("B"), wpjb_date_format());
        $js_months = array();
        
        for($i=1; $i<=12; $i++) {
            $js_months[$i] = wpjb_date("2000-$i", "M");
        }
        
        wp_localize_script("wpjb-js", "WpjbData", array(
            "no_jobs_found" => __('No job listings found', 'wpjobboard'),
            "no_resumes_found" => __('No resumes found', 'wpjobboard'),
            "load_x_more" => __('Load %d more', 'wpjobboard'),
            "date_format" => $js_date_format,
            "max_date" => $js_date_max
        ));
        
        wp_localize_script("wpjb-stripe", "wpjb_stripe", array(
            "payment_accepted" => __("Payment completed successfully.", "wpjobboard")
        ));
        
        wp_localize_script("wpjb-payment", "wpjb_payment_lang", array(
            "ajaxurl" => admin_url("admin-ajax.php"),
            "form_error" => __("There are errors in your form.", "wpjobboard")
        ));
        
        wp_localize_script('wpjb-paypal-reply', 'wpjb_paypal_reply', array(
            "ajaxurl" => admin_url('admin-ajax.php'), 
            "payment_id" => $request->post("custom"),
            "external_id" => $request->post("txn_id"),
            "interval" => 2000, 
            "interval_x" => 5, 
            "interval_i" => 0
        ));
        
        wp_localize_script("wpjb-manage", "wpjb_manage_lang", array(
            "ajaxurl" => admin_url("admin-ajax.php"),
            "ok" => __("OK", "wpjobboard")
        ));
        
        wp_localize_script('wpjb-manage-apps', 'wpjb_manage_apps_lang', array(
            "ajaxurl" => admin_url('admin-ajax.php')
        ));
    }
    
    /**
     * Registers Common Styles
     * 
     * Common styles are being used both in wp-admin and the frontend so they
     * need to be always registered.
     * 
     * @since 5.0
     * @return void
     */
    public function registerStylesCommon() {
        $v = Wpjb_Project::VERSION;
        $p = plugins_url().'/wpjobboard/public/css/';
        $x = plugins_url().'/wpjobboard/application/vendor/';
        
        wp_register_style("wpjb-vendor-datepicker", $x."date-picker/css/datepicker.css");
        wp_register_style('wpjb-glyphs', $p."wpjb-glyphs.css", array(), $v );
    }
    
    /**
     * Registers Admin Styles
     * 
     * Admin styles are registered only if user is viewing wp-admin panel.
     * 
     * @since 5.0
     * @return void
     */
    public function registerStylesAdmin() {
        $v = Wpjb_Project::VERSION;
        $p = plugins_url().'/wpjobboard/public/css/';
        $x = plugins_url().'/wpjobboard/application/vendor/';
        
        wp_register_style("wpjb-admin-css", $p."admin.css", array(), $v);
        wp_register_style("wpjb-colorpicker-css", $p."colorpicker.css", array(), $v);
        wp_register_style("wpjb-vendor-ve-css", $x."visual-editor/visual-editor.css", array(), $v);
        wp_register_style("wpjb-multi-level-accordion-menu", $p."multi-level-accordion-menu.css", array(), $v);
    }
    
    /**
     * Registers Frontend Styles
     * 
     * Frontend styles are registered only if user is in the frontend.
     * 
     * @since 5.0
     * @return void
     */
    public function registerStylesFrontend() {
        $v = Wpjb_Project::VERSION;
        $p = plugins_url().'/wpjobboard/public/css/';
        $x = plugins_url().'/wpjobboard/application/vendor/';
        
        wp_register_style('wpjb-css', $p."frontend.css", array('wpjb-glyphs'), $v );
    }

    public function adminEnqueueScripts($hook) 
    {
        if(stripos(Daq_Request::getInstance()->get("page"), "wpjb-") !== 0) {
            return;
        }
        
        wp_enqueue_script("wpjb-admin");
        wp_enqueue_style("wpjb-admin-css");
        
        list($x, $page) = explode("_wpjb-", $hook);
        
        $request = Daq_Request::getInstance();
        $action = $request->get("action");

        if($page == "job" && in_array($action, array("add", "edit"))) {

            wp_enqueue_script("wpjb-admin-job");
            wp_enqueue_script("wpjb-suggest");
            wp_enqueue_script("wpjb-vendor-datepicker");
            
            wp_enqueue_style("wpjb-vendor-datepicker");
        } elseif($page == "custom" && $action == "edit") {
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-draggable');
            wp_enqueue_script('jquery-ui-droppable');
            wp_enqueue_script('thickbox');
            
            wp_enqueue_script("wpjb-vendor-ve");
            wp_enqueue_style("wpjb-vendor-ve-css");
        } elseif($page == "jobType") {
            wp_enqueue_script("wpjb-color-picker", null, null, null, true);
            wp_enqueue_style("wpjb-colorpicker-css", null, null, null, true);
        } elseif($page == "resumes") {
            wp_enqueue_script("wpjb-admin-resume");
            wp_enqueue_script("wpjb-vendor-datepicker");
            wp_enqueue_style("wpjb-vendor-datepicker");

        } elseif($page == "import" && in_array($action, array("xml", "csv"))) {
            wp_enqueue_script("wpjb-vendor-plupload");
        } elseif($page == "memberships" || $page == "discount") {
            wp_enqueue_script("wpjb-vendor-datepicker");
            wp_enqueue_script("suggest");
            wp_enqueue_style("wpjb-vendor-datepicker");
        } elseif($page == "config" && $action == "edit" && $request->get("form")=="urls") {
            wp_enqueue_script("wpjb-admin-config-urls");
        } elseif($page == "application" && in_array($action, array("add", "edit"))) {
            wp_enqueue_script("suggest");
        }
    } 
    
    public function adminPrintStyles() 
    {
        echo '<style type="text/css">#adminmenu .toplevel_page_wpjb-config .wp-menu-image.svg, #adminmenu .toplevel_page_wpjb-job .wp-menu-image.svg { background-size: 17px auto; }</style>'.PHP_EOL;
    }
    
    public function addScriptsFront()
    {
        wp_enqueue_style('wpjb-css');
        wp_enqueue_script('wpjb-js');
    }
}
