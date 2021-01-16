<?php

class Wpjb_Utility_Rewrite {

    /**
     * Rewrite rules array
     *
     * @var array  
     */
    protected $_rewrites = null;
    
    /**
     * Class constructor
     * 
     * @return self
     */
    public function __construct() 
    {
        $this->_rewrites = $this->shortcodeRewrites();
        
        add_filter('query_vars', array($this, "rewriteQueryVars"));
        add_filter('rewrite_rules_array', array($this, "generateRewriteRules"));
        add_filter("template_redirect", array($this, "templateRedirect"));
        add_action('generate_rewrite_rules', array($this, "secureUploadedFiles"));
        add_action('init', array($this, "addRewriteEndpoint"));
        
        $this->generateRewriteRules(array());
    }
    
    /**
     * Checks if current request is WPJB API request
     * 
     * If the current request has non-empty "wpjobboard" query var then the 
     * WPJB API should be executed
     * 
     * This function is executed by template_redirect filter registered in
     * self::__construct()
     * 
     * @see template_redirect filter
     * @uses get_query_var()
     * 
     * @param string $template  Template file path
     * @return string           Template file path
     */
    public function templateRedirect($template)
    {
        $wpjb = get_query_var("wpjobboard");

        if($wpjb) {
            Wpjb_Project::getInstance()->registerAppApi();
            Wpjb_Project::getInstance()->getApplication("api")->dispatch($wpjb);
            exit;
        }
 
        return $template;
        
    }
    
    public function addRewriteEndpoint()
    {
        add_rewrite_endpoint("applied", EP_PERMALINK);
    }
    
    /**
     * Returns new query_vars registered by WPJB
     * 
     * @return array    Unique query vars
     */
    public function getVars() {
        $vars = array("wpjobboard");
        foreach($this->_rewrites as $rewrite) {
            foreach($rewrite["links"] as $links) {
                if(is_array($links)) {
                    $vars =  array_merge($vars, array_keys($links));
                }
            } 
        }

        return array_unique($vars);
    }
    
    /**
     * Adds WPJB query_vars to WP list of vars
     * 
     * This function is executed by query_vars filter
     * 
     * @see query_vars filter
     * 
     * @param array $vars   List of WP query_vars
     * @return array        Updated list of query vars
     */
    public function rewriteQueryVars($vars) 
    {
        return array_merge($vars, $this->getVars());
    }
    
    /**
     * Adds rules to htaccess
     * 
     * The rules added to .htaccess will prevent unauthorized users from accessing
     * job application and resumes files.
     * 
     * This function is applied using generate_rewrite_rules action
     * 
     * @see generate_rewrite_rules
     * 
     * @global wp_rewrite $wp_rewrite
     * @return void
     */
    public function secureUploadedFiles() {
        global $wp_rewrite;
        
        $non_wp_rules = array(
            '([_0-9a-zA-Z-]+/)?uploads/wpjobboard/application/(.+)' => 'wp-content/plugins/wpjobboard/restrict.php?url=application/$2',
            '([_0-9a-zA-Z-]+/)?uploads/wpjobboard/resume/(.+)' => 'wp-content/plugins/wpjobboard/restrict.php?url=resume/$2'
        );
        
        $wp_rewrite->non_wp_rules = $non_wp_rules + $wp_rewrite->non_wp_rules;
    }
    
    /**
     * Adds WPJB related routes to WP rules array
     * 
     * This functions is executed by rewrite_rules_array filter which is applied
     * in self::__construct()
     * 
     * @see rewrite_rules_array
     * 
     * @param array $rules  WordPress rewrite rules array
     * @return array        Updated list of rewrite rules
     */
    public function generateRewriteRules($rules)
    {
        $newrules = array();
        $rewrites = $this->getRewrites();
        
        foreach($rewrites as $k => $r) {
            if(isset($r["ignore"]) && $r["ignore"]==true) {
                continue;
            }
            
            if(!isset($r["rewrites"]) && empty($r["rewrites"])) {
                continue;
            }
            
            $limit = array();
            foreach($r["limit_to"] as $limit_id) {
                $limit[] = get_page_uri($limit_id);
            }
            
            if(!empty($limit)) {
                $path = implode("|", $limit);
            } else {
                $path = ".?.+?";
            }
            
            if($path == ".?.+?" && in_array($r["config_key"], array("urls_link_emp_panel", "urls_link_cand_panel"))) {
                continue;
            }
            
            $all = array();
            foreach($r["links"] as $link) {
                if(!is_array($link)) {
                    continue;
                }
                foreach($link as $lk => $lv) {
                    if(!isset($all[$lk])) {
                        $all[$lk] = array();
                    }
                    if(!in_array($lv, $all[$lk])) {
                        $all[$lk][] = $lv;
                    }
                }
            }
            
            $total = count($all);
            
            $keys = array_keys($all);
            $vars = array_values($all);
            
            if($total >= 2) {
                $k2 = sprintf('(%s)/(%s)/%s/?$', $path, implode("|", $vars[0]), implode("|", $vars[1]));
                $v2 = sprintf('index.php?pagename=$matches[1]&%s=$matches[2]&%s=$matches[3]', $keys[0], $keys[1]);
                $newrules[$k2] = $v2;
            }
            
            if($total >= 1) {
                $k1 = sprintf('(%s)/(%s)/?$', $path, implode("|", $vars[0]));
                $v2 = sprintf('index.php?pagename=$matches[1]&%s=$matches[2]', $keys[0]);
                $newrules[$k1] = $v2;
            }

        }
        
        $page = get_post(wpjb_conf("urls_link_job"));
        $page_resume = get_post(wpjb_conf("urls_link_resume"));

        if($page !== null) {
            $newrules['('.get_page_uri($page).')/(category|type)/([^/]+)/?$'] = 'index.php?pagename=$matches[1]&wpjb-tag=$matches[2]&wpjb-slug=$matches[3]&paged=1';
            $newrules['('.get_page_uri($page).')/(category|type)/([^/]+)/page/?([0-9]{1,})/?$'] = 'index.php?pagename=$matches[1]&wpjb-tag=$matches[2]&wpjb-slug=$matches[3]&paged=$matches[4]';
        } else {
            $newrules['(.?.+?)/(category|type)/([^/]+)/?$'] = 'index.php?pagename=$matches[1]&wpjb-tag=$matches[2]&wpjb-slug=$matches[3]&paged=1';
            $newrules['(.?.+?)/(category|type)/([^/]+)/page/?([0-9]{1,})/?$'] = 'index.php?pagename=$matches[1]&wpjb-tag=$matches[2]&wpjb-slug=$matches[3]&paged=$matches[4]';
        }
        
        if($page_resume !== null) {
            $newrules['('.get_page_uri($page_resume).')/(category|type)/([^/]+)/?$'] = 'index.php?pagename=$matches[1]&wpjb-tag=$matches[2]&wpjb-slug=$matches[3]&paged=1';
            $newrules['('.get_page_uri($page_resume).')/(category|type)/([^/]+)/page/?([0-9]{1,})/?$'] = 'index.php?pagename=$matches[1]&wpjb-tag=$matches[2]&wpjb-slug=$matches[3]&paged=$matches[4]';
        } else {
            $newrules['(.?.+?)/(category|type)/([^/]+)/?$'] = 'index.php?pagename=$matches[1]&wpjb-tag=$matches[2]&wpjb-slug=$matches[3]&paged=1';
            $newrules['(.?.+?)/(category|type)/([^/]+)/page/?([0-9]{1,})/?$'] = 'index.php?pagename=$matches[1]&wpjb-tag=$matches[2]&wpjb-slug=$matches[3]&paged=$matches[4]';
        }
        
        $newrules['wpjobboard/(.*)$'] = 'index.php?wpjobboard=$matches[1]';
        
        return $newrules+$rules;

    }
    
    public function shortcodeRewrites() {
        
        $rewrites = array(
            "wpjb_jobs_add" => array(
                "config_key" => "urls_link_job_add",
                "module" => "frontend",
                "limit_to" => array(),
                "links" => array(
                    "step_add" => array( "wpjb-step" => "add" ),
                    "step_preview" => array( "wpjb-step" => "preview" ),
                    "step_save" => array( "wpjb-step" => "save" ),
                    "step_reset" => array( "wpjb-step" => "reset" ),
                    "step_complete" => array( "wpjb-step" => "complete" ),
                ),
                "rewrites" => array(
                    '(%s)/(.?.+?)/?$' => 'index.php?pagename=$matches[1]&wpjb-step=$matches[2]'
                )
            ),
            "wpjb_employer_register" => array(
                "config_key" => "urls_link_emp_reg",
                "module" => "frontend",
                "limit_to" => array(),
                "links" => array( "employer_new" => null ),
            ),
            "wpjb_employer_panel" => array(
                "config_key" => "urls_link_emp_panel",
                "module" => "frontend",
                "limit_to" => array(),
                "links" => array(
                    "employer_home"  => true,
                ),
                "rewrites" => array(
                    '(%s)/(.?.+?)/(([0-9]{1,}))/?$' => 'index.php?pagename=$matches[1]&wpjb-employer=$matches[2]&wpjb-id=$matches[3]',
                    '(%s)/(.?.+?)/?$' => 'index.php?pagename=$matches[1]&wpjb-employer=$matches[2]',
                )
            ),
            "wpjb_membership_pricing" => array(
                "config_key" => "urls_link_membership_pricing",
                "module" => "frontend",
                "limit_to" => array(),
                "links" => array(
                    
                )
            ),
            "wpjb_candidate_register" => array(
                "config_key" => "urls_link_cand_reg",
                "module" => "resumes",
                "limit_to" => array(),
                "links" => array( "register" => true ),
            ),
            "wpjb_candidate_panel" => array(
                "config_key" => "urls_link_cand_panel",
                "module" => "resumes",
                "limit_to" => array(),
                "links" => array(
                    "myresume_home" => true,
                    "login" => true
                ),
                "rewrites" => array(
                    '(%s)/(.?.+?)/(([0-9]{1,}))/?$' => 'index.php?pagename=$matches[1]&wpjb-candidate=$matches[2]&wpjb-id=$matches[3]',
                    '(%s)/(.?.+?)/?$' => 'index.php?pagename=$matches[1]&wpjb-candidate=$matches[2]',
                )
            ),
            "wpjb_jobs_list" => array(
                "config_key" => "urls_link_job",
                "module" => "frontend",
                "limit_to" => array(),
                "links" => array(
                    "category" => array("wpjb-tag"=>"category", "wpjb-slug"=>"([^/]+)"),
                    "type" => array("wpjb-tag"=>"type", "wpjb-slug"=>"([^/]+)"),
                    "home" => true
                ),
            ),
            "wpjb_jobs_search" => array(
                "config_key" => "urls_link_job_search",
                "module" => "frontend",
                "limit_to" => array(),
                "links" => array("advsearch"=>true, "search"=>true)
            ),
            "wpjb_resumes_list" => array(
                "config_key" => "urls_link_resume",
                "module" => "resumes",
                "limit_to" => array(),
                "links" => array( 
                    "category" => array( "wpjb-tag" => "category", "wpjb-slug" => "([^/]+)"),
                    "home" => true
                )
            ),
            "wpjb_resumes_search" => array(
                "config_key" => "urls_link_resume_search",
                "module" => "resumes",
                "limit_to" => array(),
                "links" => array("advsearch"=>true)
            ),
        );
        
        foreach($rewrites as $rk => $r) {
            if(wpjb_conf($rewrites[$rk]["config_key"]) > 0) {
                $rewrites[$rk]["limit_to"][] = absint(wpjb_conf($rewrites[$rk]["config_key"]));
            }
        }
        
        $users = Wpjb_Project::getInstance()->env("user_manager");
        /* @var $users Wpjb_User_Manager */
        
        foreach($users->getUsers() as $user) {
            /* @var $user Wpjb_User_User */
            if(!isset($rewrites[$user->panel])) {
                continue;
            }

            foreach($user->dashboard->getPages() as $key => $page) {
                $rewrites[$user->panel]["links"][$key] = $page["rewrite"];
            }
            
            

        }
        
        return apply_filters("wpjb_shortcode_rewrites", $rewrites);
    }
   
    
    public function getDefaultPageFor($key, $module = null) {
        $id = null;

        foreach($this->_rewrites as $sh => $data) {
            if($module != $data["module"]) {
                continue;
            }
            
            if(!array_key_exists($key, $data["links"])) {
                continue;
            }
            
            if(!empty($data["limit_to"])) {
                $id = $data["limit_to"][0];
                break;
            }
        }

        if($id === null) {
            $id = get_the_ID();
        }

        return apply_filters("wpjb_get_default_page", $id, $key);
    }
    
    
    
    public function getRewrites()
    {
        return $this->_rewrites;
    }
    
    public function getGlue($url = null) {
        
        if($url === null) {
            $url = get_the_permalink();
        }
        
        if(get_option('permalink_structure')) {
            $glue = "/";
        } elseif(stripos($url, "?")===false) {
            $glue = "?";
        } else {
            $glue = "&";
        }
        
        return $glue;
    }
    
    public function findLink($link)
    {
        foreach($this->getRewrites() as $k => $v) {
            foreach($v["links"] as $lk => $lv) {
                if($lk == $link) {
                    return $lv;
                }
            }
        }
        
        return null;
    }
    
    public function findRoute($path)
    {
        foreach($this->getRewrites() as $k => $v) {
            foreach($v["links"] as $lk => $lv) {
                if($lv == $path) {
                    return $lk;
                }
            }
        }
        
        return null;
    }
    
    public function linkTo($key, $object = null, $param = null, $page_id = false) {
        $glue = $this->getGlue();
        $link = $this->findLink($key);
        $qstring = $param;
        $path = "";
        
        if(is_array($link) && !empty($link)) {
            $path = implode("/", $link);
        } 

        if($object && stripos($path, "/id")) {
            $path = str_replace("/id", "/".$object->id, $path);
        }
        
        if($object && stripos($path, "/slug") && $object->get("slug")!=null) {
            $path = str_replace("/slug", "/".$object->slug, $path);
        }
        
        if($object && stripos($path, "([0-9]{1,})")) {
            $path = str_replace("([0-9]{1,})", $object->id, $path);
        }
        
        if($object && stripos($path, "([^/]+)")) {
            $path = str_replace("([^/]+)", $object->slug, $path);
        }
        
        if(!empty($path)) {
            $path .= "/";
        }
        
        $front_page_id = get_option( 'page_on_front' );
        
        if(get_option('permalink_structure')) {
            if($front_page_id != $page_id) {
                $url = rtrim(get_the_permalink($page_id), "/").$glue.$path;
            } else {
                $url = rtrim(_get_page_link($page_id), "/").$glue.$path;
            }
            if(!empty($qstring)) {
                $url .= "?".http_build_query($qstring);
            }
        } else {

            if(!isset($link["param"])) {
                $params = array();
                $append = "";
            } else {
                $a1 = (array)$link["param"];
                $a2 = explode("/", trim($path, "/"));
                $a3 = explode("/", trim($link["link"], "/"));
                
                $c1 = count($a1);
                $c2 = count($a2);
                $c3 = count($a3);
                
                if($c1 < $c2 && $c2 == $c3) {
                    switch($a3[$c3-1]) {
                        case "id": $a1[] = "wpjbid"; break;
                        case "slug": $a1[] = "wpjbslug"; break;
                    }
                }
                
                $params = array_combine($a1, $a2);
                $append = $glue. http_build_query($params);
            }
            
            $url = rtrim(get_the_permalink($page_id), "/").$append;
        }
        
        return $url;
    }
    
    /**
     * Resolves an URL to route.
     * 
     * @deprecated 4.6.0
     * 
     * @param string $default
     * @param string $narrow
     * @return array
     */
    public function resolve($default = null, $narrow = null) 
    {
        $m = "This function is depracated since WPJB 4.6.0 and will be removed in 4.7.0";
        _doing_it_wrong( __CLASS__."::".__FUNCTION__, $m, "4.6.0");
        
        return array(
            "route" => $default,
            "param" => array()
        );
    }
    
    private function _getOldRoute($path, $module = "frontend") {
        if($module == null) {
            $app = array("frontend", "resumes");
        } else {
            $app = array($module);
        }
        
        $instance = Wpjb_Project::getInstance();
        
        foreach($app as $a) {
            $routed = $instance->router($a)->getRoutes();
            
            foreach($routed as $key => $data) {
                $ma = "{$data['module']}.{$data['action']}";
                if($ma == $path) {
                    return $key;
                }
            }
        } 
    }
    
    public function isRoutedTo($path, $module = "frontend") {

        if(stripos($path, ".") !== false) {
            $path = $this->_getOldRoute($path, $module);
        }
        
        $matched = false;
        $matches = array();

        $singular = null;
        $post_types = array(
            "job" => array("module" => "frontend", "path" => "single"),
            "resume" => array("module" => "resumes", "path" => "single"),
            "company" => array("module" => "frontend", "path" => "company"),
        );

        foreach($post_types as $post_type => $route) {
            if($route["module"] == $module && $route["path"] == $path && is_singular($post_type)) {
                return true;
            }
        }
  
        if(!is_page()) {
            return false;
        }
        
        foreach($this->getVars() as $p) {
            if(get_query_var($p)) {
                $matched = true;
                $matches[$p] = get_query_var($p);
            }
        }
                
        $page_id = get_the_ID();
        
        foreach($this->getRewrites() as $rewrite) {
            if($module && $module != $rewrite["module"]) {
                continue;
            }
            
            if(!in_array($page_id, $rewrite["limit_to"])) {
                continue;
            }

            if(!isset($rewrite["links"][$path])) {
                continue;
            }

            if($rewrite["links"][$path] === true && empty($matches)) {
                return true;
            }

            if(!is_array($rewrite["links"][$path])) {
                continue;
            }

            $test1 = array_keys($rewrite["links"][$path]);
            $test2 = array_keys($matches);
            
            sort($test1);
            sort($test2);
            
            if($test1 != $test2) {
                return false;
            }
            
            $vars1 = array_values($rewrite["links"][$path]);
            $vars2 = array_values($matches);
                
            if($vars1[0] == $vars2[0]) {
                return true;
            }
            
        }
        
        return false;
    }
    
    public function convertRoute($route, $resolved) 
    {
        $result = array(
            "param" => $resolved["param"],
            "route" => $resolved["route"],
            "module" => $route["module"],
            "action" => $route["action"],
            "path" => null,
            "object" => null
        );

        if(isset($route["model"])) {
            $object = new stdClass();
            $object->objClass = $route["model"];
            $result["object"] = $object;
        } 
        
        return $result;
    }
    
    public function titles($title, $id = null) 
    {
        return $title;
    }
    
    public function getOldRoute($route, $app = null)
    {
        if($app == null) {
            $app = array("frontend", "resumes");
        } else {
            $app = array($app);
        }
        
        $instance = Wpjb_Project::getInstance();
        
        foreach($app as $a) {
            $routed = $instance->router($a)->getRoute($route);
            
            if(!is_null($routed)) {
                return $routed;
            }
        }
    }
}

