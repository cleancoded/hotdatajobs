<?php
/**
 * Description of ProjectAbstract
 *
 * @author greg
 * @package 
 */

abstract class Daq_ProjectAbstract
{
    const REWRITE_PATTERN = "/(.*)";
    
    protected $_application = array();

    /**
     * Path to directory containing user widgets
     * 
     * @var array
     */
    protected $_widgetPath = null;

    /**
     * Path to plugin main directory
     *
     * @var string
     */
    protected $_baseDir = null;
    
    /**
     * List of application paths
     *
     * @var array
     */
    protected $_path = array();

    /**
     * Array of environment variables
     *
     * @var array
     */
    protected $_env = array();
    
    /**
     * URL
     *
     * @var string
     */
    public $url = null;
    
    public $text = null;
    
    public $placeHolder = null;
    
    private $_config = null;
   
    
    /**
     * Returns instance of project
     */
    public static function getInstance()
    {
        throw new Exception("You need to overwrite this method!");
    }

    /**
     * Register hooks and filters
     */
    abstract public function run();

    /**
     * Adds application to the list of registered applications
     *
     * @param string $key Application name
     * @param Daq_Application $application Object
     */
    public function addApplication($key, Daq_Application_Abstract $application)
    {
        $this->_application[$key] = $application;
    }

    /**
     * Returns registered application by key
     *
     * @param string $key
     * @return Daq_Application
     */
    public function getApplication($key)
    {
        return $this->_application[$key];
    }

    /**
     * Registers widgets from given directory.
     *
     * It's good practice to load user widgets before default widgets because
     * it gives users opportunity to overwrite default widgets.
     *
     * @param string $dir Path to widgets directory
     */
    public function addUserWidgets($widget)
    {
        $this->_widgetPath = $widget;
    }

    /**
     * Sets path to plugin main directory
     *
     * @param string $dir
     */
    public function setBaseDir($dir)
    {
        $this->_baseDir = $dir;
    }

    /**
     * Returns base path, ie path to the plugin main directory
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->_baseDir;
    }
    
    /**
     * Sets environment variable
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function env($key, $default = null)
    {
        if(isset($this->_env[$key])) {
            return $this->_env[$key];
        }

        return $default;
    }

    /**
     * Returns environment variable
     *
     * @param string $key
     * @param mixed $value 
     */
    public function setEnv($key, $value)
    {
        $this->_env[$key] = $value;
    }
    
    /**
     * Returns project base path
     *
     * @return string
     */
    public function getProjectBaseDir()
    {
        return $this->_baseDir;
    }
    
    public function getUrl($app = "frontend")
    {
        return $this->getApplication($app)->getUrl();
    }

    public function conf($param = null, $default = null)
    {
        return $this->getConfig($param, $default);
    }
    
    /**
     * Returns router for selected application or for frontend
     * application if no param is specified.
     *
     * @param string $app Router for application
     * @return Daq_Router
     */
    public function router($app = "frontend")
    {
        return $this->getApplication($app)->getRouter();
    }

    public function media()
    {
        $dir = $this->env("directory");
        $dir = plugins_url("/wpjobboard/public/images/");

        return $dir;
    }

    public function getConfig($param = null, $default = null)
    {
        $config = $this->_config;

        if($config === null) {
            $config = get_option($this->env("config_key"));
            $this->_config = $config;
        }

        if($config === false) {
            $config = array();
            add_option($this->env("config_key"), $config);
        }

        if($param === null) {
            return $config;
        }

        if(isset($config[$param]) && (!empty($config[$param]) || is_numeric($config[$param]))) {
            return $config[$param];
        } else {
            return $default;
        }
    }

    public function setConfigParam($param, $value)
    {
        $config = $this->getConfig();
        $config[$param] = $value;
        $this->_config = $config;
    }

    public function saveConfig()
    {
        update_option($this->env("config_key"), $this->getConfig());
    }
    
    /**
     * Initiates project: widgets, AJAX actions, activation and deactivation hooks
     * 
     * @return void
     */
    public function _init()
    {
        $d = $this->getBaseDir();
        $cp = $this->env("prefix_class");
        $pr = $this->env("prefix");
        $directory = $this->env("directory");
        foreach((array)glob($d."/application/libraries/Module/Ajax/*.php") as $ajax) {
            $ctrl = basename($ajax, ".php");
            $ajaxClass = "{$cp}_Module_Ajax_".$ctrl;
            $ctrl = strtolower($ctrl);
            foreach(get_class_methods($ajaxClass) as $method) {
                if(substr($method, -6) == "Action") {
                    $m = substr($method, 0, -6);
                    add_action("wp_ajax_{$pr}_".$ctrl."_".$m, array($ajaxClass, $method));
                }
            }
        }
        foreach((array)glob($d."/application/libraries/Module/AjaxNopriv/*.php") as $ajax) {
            $ctrl = basename($ajax, ".php");
            $ajaxClass = "{$cp}_Module_AjaxNopriv_".$ctrl;
            $ctrl = strtolower($ctrl);
            foreach(get_class_methods($ajaxClass) as $method) {
                if(substr($method, -6) == "Action") {
                    $m = substr($method, 0, -6);
                    add_action("wp_ajax_{$pr}_".$ctrl."_".$m, array($ajaxClass, $method));
                    add_action("wp_ajax_nopriv_{$pr}_".$ctrl."_".$m, array($ajaxClass, $method));
                }
            }
        }
        
        add_action("widgets_init", array($this, "widgetsInit"));
        add_filter("sidebars_widgets", array($this, "widgets"));
        
        register_activation_hook("$directory/index.php", array($this,"install"));
        register_deactivation_hook("$directory/index.php", array($this,"deactivate"));
    }
    
    public function widgetsInit()
    {
        $d = $this->getBaseDir();
        $cp = $this->env("prefix_class");
        foreach((array)glob($d."/application/libraries/Widget/*.php") as $widget) {
            $widgetClass = "{$cp}_Widget_".basename($widget, ".php");
            if(class_exists($widgetClass)) {
                $widget = new $widgetClass();
                if($widget instanceof WP_Widget) {
                    register_widget($widgetClass);
                } 
            }
        }
    }
    
    /**
     * Returns array of non-admin applications
     *
     * @return array
     */
    protected function _apps()
    {
        $apps = array();
        foreach($this->_application as $a) {
            if(!$a->isAdmin()) {
                $apps[] = $a;
            }
        }
        
        return $apps;
    }
    
    public function widgets($widgets)
    {

        if(is_admin()) {
            return $widgets;
        }

        if(defined("WP_ADMIN") && WP_ADMIN === true) {
            return $widgets;
        }
        
        if(!is_array($widgets)) {
            return $widgets;
        }

        $prefix = $this->env("prefix");

        foreach($this->_apps() as $app) {
            if($app->isDispatched()) {
                return $widgets;
            }
        }

        // running on blog
        $pLen = strlen($prefix)+1;
        foreach($widgets as &$sb) {
            if(!is_array($sb)) {
                continue;
            }
            foreach($sb as &$name) {
                if(is_string($name) && strlen($name)>$pLen && substr($name, 0, $pLen) == $prefix."-") {
                    $opt = explode("-", $name);
                    $i = array_pop($opt);
                    $option = get_option("widget_".join("-", $opt), array());
                    $option = $option[$i];
                    if(isset($option["hide"]) && $option["hide"] == 1) {
                        $name = null;
                    }
                }
            }
        }

        return $widgets;
    }
    
    public function loadPaths(array $paths)
    {
        $this->_path = $paths;
    }
    
    public function path($key)
    {
        return $this->getProjectBaseDir().$this->_path[$key];
    }

    public function pathRaw($key)
    {
        return $this->_path[$key];
    }
    
    /**
     *
     * @return Daq_Application
     */
    public function getAdmin()
    {
        if(!isset($this->_application["admin"])) {
            throw new Exception("Admin application not set.");
        }

        return $this->_application['admin'];
    }

    public function dispatch()
    {
        $action = "";
        if(Daq_Request::getInstance()->get("action")) {
            $action = Daq_Request::getInstance()->get("action");
        }

        $path = trim($_GET['page'], "/")."/".trim($action, "/");
        $path = substr($path, strlen($this->env("prefix"))+1);
        
        $admin = $this->getAdmin();
        $admin->dispatch($path);
    }
    
    public function execute($content)
    {
        global $wp;
        
        foreach($this->_apps() as $app) {
            /* @var $app Daq_Application */
            
            if(!$app->getOption("link_name")) {
                continue;;
            }
            
            $id = $this->conf($app->getOption("link_name"));
            
            if(!is_page($id)) {
                continue;
            }
            
            add_action('wp_head', array($this, "canonicalUrl"), 1);
            ob_start();

            try {
                $opt = $app->getOption("query_var");
                $qv = "";
                if(isset($wp->query_vars[$opt])) {
                    $qv = $wp->query_vars[$opt];
                }
                
                remove_all_filters("comments_template");
                remove_all_filters("the_content");
                
                add_filter('the_content', array($this, "theContent"), 900000);
                add_filter('the_content', "capital_P_dangit");
                add_filter('the_content', "wptexturize");
                add_filter('the_content', "convert_smilies");
                add_filter('the_content', "convert_chars");
                add_filter('the_content', "shortcode_unautop");
                add_filter('the_content', "do_shortcode", 9500);
                
                add_filter("comments_template", array($this, "commentsTemplate"));

                $app->dispatch(ltrim($qv, "/"));

            } catch(Exception $e) {
                if(defined("DAQ_DEBUG") &&  DAQ_DEBUG) {
                    var_dump($e);
                } elseif(is_file(get_404_template())) {
                    include_once get_404_template();
                    exit;
                }
            }
            
            $this->text = ob_get_clean();
        }
        
    }
    
    public function theContent($content)
    {
        foreach($this->_apps() as $app) {
            $linkName = $app->getOption("link_name");
            $id = $this->conf($linkName);
            if($linkName && is_page($id)) {
               $shortcode = $app->getOption("shortcode");
               if (post_password_required($id)) {
                   return get_the_password_form();
               }
               
               if(stripos($content, "<p>$shortcode</p>") !== false) {
                   return str_replace("<p>$shortcode</p>", $this->text, $content);
               } else {
                   return str_replace($shortcode, $this->text, $content);
               }
            }
        }

        return $content;
    }
    
    public function commentsTemplate()
    {
        return $this->path("templates")."/blank.php";
    }
    
    public function canonicalUrl()
    {
        $url = $this->env("canonical", null);
        remove_action('wp_head','rel_canonical');
        if ($url) {
            echo '<link rel="canonical" href="'.$url.'"/>'."\n";
        }
    }
    
    public function nonoTags($t) 
    {
        $t[] = "textarea";
        return $t;
    }
}
?>