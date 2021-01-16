<?php
/**
 * Generates top and left WPJB menus in wp-admin
 *
 * @package WPJobBoard
 * @author Greg Winiarski
 * @since 4.4.1
 */
class Wpjb_Utility_AdminMenu {

    /**
     * Returns data for left wp-admin menu
     * 
     * @since 4.4.1
     * @return array Data for left admin menu
     */
    public function getLeftItems()
    {
        return apply_filters("wpjb_admin_menu", array (
            'job_board' => array (
                'page_title' => __('Job Board', 'wpjobboard'),
                'handle' => '/job',
                'access' => 'edit_pages',
                'order' => '9.19840520',
                'logo' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxOC4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCIgWw0KCTwhRU5USVRZIG5zX2V4dGVuZCAiaHR0cDovL25zLmFkb2JlLmNvbS9FeHRlbnNpYmlsaXR5LzEuMC8iPg0KCTwhRU5USVRZIG5zX2FpICJodHRwOi8vbnMuYWRvYmUuY29tL0Fkb2JlSWxsdXN0cmF0b3IvMTAuMC8iPg0KCTwhRU5USVRZIG5zX2dyYXBocyAiaHR0cDovL25zLmFkb2JlLmNvbS9HcmFwaHMvMS4wLyI+DQoJPCFFTlRJVFkgbnNfdmFycyAiaHR0cDovL25zLmFkb2JlLmNvbS9WYXJpYWJsZXMvMS4wLyI+DQoJPCFFTlRJVFkgbnNfaW1yZXAgImh0dHA6Ly9ucy5hZG9iZS5jb20vSW1hZ2VSZXBsYWNlbWVudC8xLjAvIj4NCgk8IUVOVElUWSBuc19zZncgImh0dHA6Ly9ucy5hZG9iZS5jb20vU2F2ZUZvcldlYi8xLjAvIj4NCgk8IUVOVElUWSBuc19jdXN0b20gImh0dHA6Ly9ucy5hZG9iZS5jb20vR2VuZXJpY0N1c3RvbU5hbWVzcGFjZS8xLjAvIj4NCgk8IUVOVElUWSBuc19hZG9iZV94cGF0aCAiaHR0cDovL25zLmFkb2JlLmNvbS9YUGF0aC8xLjAvIj4NCl0+DQo8c3ZnIHZlcnNpb249IjEuMSIgeG1sbnM6eD0iJm5zX2V4dGVuZDsiIHhtbG5zOmk9IiZuc19haTsiIHhtbG5zOmdyYXBoPSImbnNfZ3JhcGhzOyINCgkgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjU5MS44IDk3OC4zIDE2IDE4Ig0KCSBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDU5MS44IDk3OC4zIDE2IDE4IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxtZXRhZGF0YT4NCgk8c2Z3ICB4bWxucz0iJm5zX3NmdzsiPg0KCQk8c2xpY2VzPjwvc2xpY2VzPg0KCQk8c2xpY2VTb3VyY2VCb3VuZHMgIGhlaWdodD0iNDguMSIgd2lkdGg9IjU5LjgiIHk9Ijg5NS42IiB4PSI1NDkiIGJvdHRvbUxlZnRPcmlnaW49InRydWUiPjwvc2xpY2VTb3VyY2VCb3VuZHM+DQoJPC9zZnc+DQo8L21ldGFkYXRhPg0KPGcgaWQ9IkxpdmVsbG9fMyIgZGlzcGxheT0ibm9uZSI+DQo8L2c+DQo8ZyBpZD0iTGl2ZWxsb18yIj4NCgk8Zz4NCgkJPHBhdGggZmlsbD0iIzlBOTk5OSIgZD0iTTYwNi44LDk4Mi4zdi0xaC0xdjF2MmgtMTJ2LTJ2LTFoLTF2MWgtMXYxM2gxdjFoMTR2LTFoMXYtMTNINjA2Ljh6IE02MDUuOCw5ODYuM3YxaC0xMnYtMUg2MDUuOHoNCgkJCSBNNTkzLjgsOTkwLjN2LTFoMTJ2MUg1OTMuOHogTTYwNS44LDk5Mi4zdjFoLTEydi0xSDYwNS44eiIvPg0KCQk8cGF0aCBmaWxsPSIjOUE5OTk5IiBkPSJNNjAyLjgsOTgwLjN2LTFoLTJ2LTFoLTJ2MWgtMnYxaC0ydjNoMTB2LTNINjAyLjh6IE02MDAuOCw5ODIuM2gtMnYtMmgyVjk4Mi4zeiIvPg0KCTwvZz4NCjwvZz4NCjwvc3ZnPg0K',
                'screen' => 'job',
            ),
            'jobs' => array (
                'parent' => 'job_board',
                'page_title' => __('Jobs', 'wpjobboard'),
                'handle' => '/job',
                'access' => 'edit_pages',
                'callback' => array($this, "afterJobsTitle"),
                'screen' => 'job'
            ),
            'applications' => array (
                'parent' => 'job_board',
                'page_title' => __('Applications', 'wpjobboard'),
                'handle' => '/application',
                'access' => 'edit_pages',
                'callback' => array($this, "afterApplicationsTitle"),
                'screen' => 'application'
            ),
            'companies' => array (
                'parent' => 'job_board',
                'page_title' => __('Employers', 'wpjobboard'),
                'handle' => '/employers',
                'access' => 'edit_pages',
                'callback' => array($this, "afterCompaniesTitle"),
                'screen' => 'employer'
            ),
            'resumes_manage' => array (
                'parent' => 'job_board',
                'page_title' => __('Candidates', 'wpjobboard'),
                'handle' => '/resumes',
                'access' => 'edit_pages',
                'screen' => 'resume'
            ),
            'payments' => array (
                'parent' => 'job_board',
                'page_title' => __('Payments', 'wpjobboard'),
                'handle' => '/payment',
                'access' => 'edit_pages',
            ),
            'memberships' => array (
                'parent' => 'job_board',
                'page_title' => __('Memberships', 'wpjobboard'),
                'handle' => '/memberships',
                'access' => 'edit_pages',
            ),
            'alerts' => array (
                'parent' => 'job_board',
                'page_title' => __('E-mail Alerts', 'wpjobboard'),
                'handle' => '/alerts',
                'access' => 'edit_pages',
            ),
            'settings' => array (
                'page_title' => __('Settings (WPJB)', 'wpjobboard'),
                'handle' => '/config',
                'access' => 'administrator',
                'order' => '80.19840520',
                'logo' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxOC4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCIgWw0KCTwhRU5USVRZIG5zX2V4dGVuZCAiaHR0cDovL25zLmFkb2JlLmNvbS9FeHRlbnNpYmlsaXR5LzEuMC8iPg0KCTwhRU5USVRZIG5zX2FpICJodHRwOi8vbnMuYWRvYmUuY29tL0Fkb2JlSWxsdXN0cmF0b3IvMTAuMC8iPg0KCTwhRU5USVRZIG5zX2dyYXBocyAiaHR0cDovL25zLmFkb2JlLmNvbS9HcmFwaHMvMS4wLyI+DQoJPCFFTlRJVFkgbnNfdmFycyAiaHR0cDovL25zLmFkb2JlLmNvbS9WYXJpYWJsZXMvMS4wLyI+DQoJPCFFTlRJVFkgbnNfaW1yZXAgImh0dHA6Ly9ucy5hZG9iZS5jb20vSW1hZ2VSZXBsYWNlbWVudC8xLjAvIj4NCgk8IUVOVElUWSBuc19zZncgImh0dHA6Ly9ucy5hZG9iZS5jb20vU2F2ZUZvcldlYi8xLjAvIj4NCgk8IUVOVElUWSBuc19jdXN0b20gImh0dHA6Ly9ucy5hZG9iZS5jb20vR2VuZXJpY0N1c3RvbU5hbWVzcGFjZS8xLjAvIj4NCgk8IUVOVElUWSBuc19hZG9iZV94cGF0aCAiaHR0cDovL25zLmFkb2JlLmNvbS9YUGF0aC8xLjAvIj4NCl0+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxpdmVsbG9fMSIgeG1sbnM6eD0iJm5zX2V4dGVuZDsiIHhtbG5zOmk9IiZuc19haTsiIHhtbG5zOmdyYXBoPSImbnNfZ3JhcGhzOyINCgkgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCA0MCA0MCINCgkgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgNDAgNDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPG1ldGFkYXRhPg0KCTxzZncgIHhtbG5zPSImbnNfc2Z3OyI+DQoJCTxzbGljZXM+PC9zbGljZXM+DQoJCTxzbGljZVNvdXJjZUJvdW5kcyAgaGVpZ2h0PSIyMDAiIHdpZHRoPSI0NzAuOCIgeT0iLTExNDkuMiIgeD0iNTI5LjkiIGJvdHRvbUxlZnRPcmlnaW49InRydWUiPjwvc2xpY2VTb3VyY2VCb3VuZHM+DQoJPC9zZnc+DQo8L21ldGFkYXRhPg0KPGc+DQoJPHBhdGggZmlsbD0iIzlBOTk5OSIgZD0iTTM4LjYsMzguNkwzOC42LDM4LjZjLTEuOSwxLjktNS4yLDEuNy02LjQsMC41Yy0xLjktMS45LTE4LTIwLjMtMTgtMjAuM2wwLDBjLTEuNiwwLjgtMy41LDEuMi01LjUsMQ0KCQljLTQuNC0wLjUtOC4xLTQtOC43LTguM0MtMC4xLDkuOCwwLDguMiwwLjUsNi44YzAuMS0wLjMsMC41LTAuNCwwLjgtMC4ybDYsNmMwLjMsMC4zLDAuOCwwLjMsMSwwbDQuMS00LjFjMC4zLTAuMywwLjMtMC44LDAtMQ0KCQlsLTYtNkM2LjIsMS4zLDYuMywwLjksNi42LDAuOGMxLjctMC42LDMuNi0wLjgsNS41LTAuM2MzLjgsMC44LDYuOCwzLjksNy41LDcuN2MwLjQsMi4yLDAsNC40LTAuOSw2LjJsMCwwYzAsMCwxOC40LDE2LDIwLjQsMTcuOQ0KCQlDNDAuMywzMy41LDQwLjQsMzYuOCwzOC42LDM4LjZ6IE0zNC44LDMzLjJjLTAuOSwwLTEuNiwwLjctMS42LDEuNnMwLjcsMS42LDEuNiwxLjZjMC45LDAsMS42LTAuNywxLjYtMS42UzM1LjYsMzMuMiwzNC44LDMzLjJ6Ig0KCQkvPg0KCTxnPg0KCQk8cGF0aCBmaWxsPSIjOUE5OTk5IiBkPSJNMjAuMywxNGMxLjMsMS4xLDQuMiwzLjYsNy4zLDYuNGwxMC4yLTkuOWMyLjYtMi42LDIuOS02LjYsMC41LTguOWMtMi4zLTIuMy02LjMtMi4xLTksMC41TDIxLDEwLjgNCgkJCUMyMC45LDExLjksMjAuNywxMywyMC4zLDE0eiIvPg0KCTwvZz4NCgk8Zz4NCgkJPHBhdGggZmlsbD0iIzlBOTk5OSIgZD0iTTE0LjIsMjAuN2MtMS40LDEuNi0yLjgsMy4zLTMuMywzLjhjLTEuNSwwLjctMy44LDAuMi01LjIsMS42QzQuMiwyNy42LTAuOSwzNiwwLjIsMzcuMXMxLjMsMS4yLDEuMywxLjINCgkJCWMwLDAsMC4xLDAuMSwxLjMsMS4yYzEuMSwxLjEsOS41LTMuOSwxMS01LjRjMS41LTEuNCwwLjktMy43LDEuNi01LjJjMC41LTAuNSwyLTEuNywzLjUtM0MxNi45LDIzLjgsMTUuMiwyMS45LDE0LjIsMjAuN3oiLz4NCgk8L2c+DQo8L2c+DQo8L3N2Zz4NCg=='
            ),
            'config' => array (
                'parent' => 'settings',
                'page_title' => __('Configuration', 'wpjobboard'),
                'handle' => '/config',
                'access' => 'administrator',
            ),
            'listings' => array (
                'parent' => 'settings',
                'page_title' => __('Pricing', 'wpjobboard'),
                'handle' => '/pricing',
                'access' => 'administrator',
            ),
            'custom' => array (
                'parent' => 'settings',
                'page_title' => __('Custom Fields', 'wpjobboard'),
                'handle' => '/custom',
                'access' => 'administrator',
            ),
            'promo_codes' => array (
                'parent' => 'settings',
                'page_title' => __('Promotions', 'wpjobboard'),
                'handle' => '/discount',
                'access' => 'administrator',
            ),
            'categories' => array (
                'parent' => 'settings',
                'page_title' => __('Categories', 'wpjobboard'),
                'handle' => '/category',
                'access' => 'administrator',
            ),
            'job_types' => array (
                'parent' => 'settings',
                'page_title' => __('Job Types', 'wpjobboard'),
                'handle' => '/jobType',
                'access' => 'administrator',
            ),
            'application_status' => array (
                'parent' => 'settings',
                'page_title' => __('Application Statuses', 'wpjobboard'),
                'handle' => '/applicationStatus',
                'access' => 'administrator',
            ),
            'email_templates' => array (
                'parent' => 'settings',
                'page_title' => __('Emails', 'wpjobboard'),
                'handle' => '/email',
                'access' => 'administrator',
            ),
            'import' => array (
                'parent' => 'settings',
                'page_title' => __('Import and Export', 'wpjobboard'),
                'handle' => '/import',
                'access' => 'administrator',
            ),
        ));
    }
    
    /**
     * Generates WPJB wp-admin left menu.
     * 
     * This function is executed by 'admin_menu' action.
     * 
     * @see admin_menu action
     * @uses add_menu_page
     * @uses add_submenu_page
     * 
     * @since 4.4.1
     * @return void
     */
    public function left() {

        $menu = $this->getLeftItems();
        
        foreach($menu as $key => $conf) {
            
            if(isset($conf['parent'])) {
                
                $menu[$key]["id"] = add_submenu_page(
                    "wpjb-".ltrim($menu[$conf['parent']]['handle'], "/"),
                    $conf['page_title'],
                    $this->menuTitle($conf),
                    $conf['access'],
                    "wpjb-".ltrim($conf['handle'], "/"),
                    array(Wpjb_Project::getInstance(), "dispatch")
                );
                
            } else {
                
                $menu[$key]["id"] = add_menu_page(
                    $conf['page_title'],
                    $conf['page_title'],
                    $conf['access'],
                    "wpjb-".ltrim($conf['handle'], "/"),
                    array(Wpjb_Project::getInstance(), "dispatch"),
                    $conf["logo"],
                    $conf['order']
                );
                
            }
        }
        
        Wpjb_Utility_Registry::set("admin_menu", $menu);
    }

    /**
     * Modifies menu title before inserting it into menu.
     * 
     * This functions can be used to display dynamic data, for example
     * number of pending jobs next to job title.
     * 
     * @param array $conf Menu item data
     * @return string Updated menu item title
     */
    public function menuTitle($conf)
    {
        $title = $conf["page_title"];
        
        try {
            if(isset($conf["callback"])) {
                $title = call_user_func($conf["callback"], $title);
            }
        } catch(Exception $e) {
            // do nothing;
        }
        
        return $title;
    }
    
    /**
     * Modifies 'Jobs' title in wp-admin left menu.
     * 
     * Apends to the title bulb with number of jobs pending approval.
     * 
     * @since 4.4.1
     * @param string $title Default title
     * @return string Modified title
     */
    public function afterJobsTitle($title)
    {
        $pending = wpjb_find_jobs(array("filter"=>"awaiting", "count_only"=>true));
        $warning = __("jobs awaiting approval", "wpjobboard");
        return $title . " <span class='update-plugins wpjb-bubble-jobs count-$pending' title='$warning'><span class='update-count'>".$pending."</span></span>";
    }
    
    /**
     * Modifies 'Applications' title in wp-admin left menu.
     * 
     * Apends to the title bulb with number of new applications.
     * 
     * @since 4.4.1
     * @param string $title Default title
     * @return string Modified title
     */
    public function afterApplicationsTitle($title)
    {
        $list = new Daq_Db_Query();
        $list->select("COUNT(*) as `cnt`");
        $list->from("Wpjb_Model_Application t");
        $list->where("status = 1");
        $applications = $list->fetchColumn();

        $warning = __("new applications", "wpjobboard");
        return $title . " <span class='update-plugins wpjb-bubble-applications count-$applications' title='$warning'><span class='update-count'>".$applications."</span></span>";
        
    }
    
    /**
     * Modifies 'Employers' title in wp-admin left menu.
     * 
     * Apends to the title bulb with number of employers pending approval.
     * 
     * @since 4.4.1
     * @param string $title Default title
     * @return string Modified title
     */
    public function afterCompaniesTitle($title)
    {
        $query = new Daq_Db_Query();
        $query->select();
        $query->from("Wpjb_Model_Company t")->join("t.user u")->select("COUNT(*) AS cnt")->limit(1);
        $pending = $query->where("t.is_verified=?", Wpjb_Model_Company::ACCESS_PENDING)->fetchColumn();
            
        $warning = __("employers requesting approval", "wpjobboard");
        return $title . " <span class='update-plugins wpjb-bubble-companies count-$pending' title='$warning'><span class='update-count'>".$pending."</span></span>";
    }
    
    /**
     * Renders WPJB items for wp-admin top bar menu
     * 
     * This function is applied using admin_bar_menu action
     * 
     * @see admin_bar_menu action
     * 
     * @since 4.4.1
     * @return void
     */
    public function bar() 
    {
        global $wp_admin_bar;
        
        if ((!is_super_admin() && !current_user_can("edit_pages")) || !is_admin_bar_showing()) {
            return;
        }
        
        $item = $this->getItemFromPostType();
        
        if(!$item) {
            return;
        }
        
        $wp_admin_bar->remove_menu("edit");
        $wp_admin_bar->add_menu($item);
    }
    
    public function getItemFromPostType()
    {
        global $post_type;
        
        switch($post_type) {
            case "job": $model = "Wpjb_Model_Job"; break;
            case "resume": $model = "Wpjb_Model_Resume"; break;
            case "company": $model = "Wpjb_Model_Company"; break;
            default: $model = null; 
        }
        
        if(!$model) {
            return;
        }
        
        $query = new Daq_Db_Query();
        $query->select("id");
        $query->from("$model t");
        $query->where("post_id = ?", get_the_ID());
        $query->limit(1);
        
        $id = $query->fetchColumn();
        
        if(!$id) {
            return;
        }
        
        switch($post_type) {
            case "job": return array(
                'id' => 'edit-job',
                'title' => '<span class="ab-icon wpjb-glyphs wpjb-icon-briefcase"></span><span class="ab-label">'.__("Edit Job", "wpjobboard").'</span>',
                'href' => wpjb_admin_url("job", "edit", $id)
            );
            // break;
            case "resume": return array(
                'id' => 'edit-resume',
                'title' => '<span class="ab-icon wpjb-glyphs wpjb-icon-user"></span><span class="ab-label">'.__("Edit Resume", "wpjobboard").'</span>',
                'href' => wpjb_admin_url("resumes", "edit", $id)
            );
            // break;
            case "company": return array(
                'id' => 'edit-employer',
                'title' => '<span class="ab-icon wpjb-glyphs wpjb-icon-building"></span><span class="ab-label">'.__("Edit Employer", "wpjobboard").'</span>',
                'href' => wpjb_admin_url("employers", "edit", $id)
            );
            break;
        }
        
    }

}
