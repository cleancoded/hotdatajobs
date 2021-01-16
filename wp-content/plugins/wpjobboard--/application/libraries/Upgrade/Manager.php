<?php

class Wpjb_Upgrade_Manager
{
    const MULTI = true;
    
    /**
     * Updates server URL
     *
     * @var string
     */
    public static $url = "https://wpjobboard.net/api";
    
    /**
     * Plugin slug
     * 
     * @deprecated 5.2.0
     * @var string
     */
    const SLUG = "wpjobboard";
    
    /**
     * Plugin path
     * 
     * @deprecated 5.2.0
     * @var string
     */
    const PATH = "wpjobboard/index.php";

    /**
     * Installed addon version
     *
     * @var string
     */
    public $version = null;
    
    /**
     * Plugin name (for example my-plugin/my-plugin.php)
     *
     * @var string
     */
    public $plugin = null;
    
    /**
     * Plugin slug (for example my-plugin)
     * 
     * This most of the time will be the plugin directory.
     *
     * @var string
     */
    public $slug = null;
    
    /**
     * Update Type
     *
     * Either "plugin" or "theme"
     * 
     * @var string
     */
    public $type = "plugin";
    
    protected $_message = null;
    
    /**
     * Class Constructor
     * 
     * @param string $plugin    Plugin Path (e.g. wpjobboard/index.php)
     * @param string $slug      Plugin Slug (e.g. wpjobboard)
     * @param string $version   Current plugin version
     * @return void
     */
    public function __construct($plugin, $slug, $version, $type = "plugin") {
        $this->plugin = $plugin;
        $this->slug = $slug;
        $this->version = $version;
        $this->type = $type;
    }
    
    public function connect()
    {
	add_filter('pre_set_site_transient_update_plugins', array($this, 'check'));
	add_filter('plugins_api', array($this, 'info'), 10, 3);
        
        add_filter("after_plugin_row_".$this->plugin, array($this, "upgradeNotice"));
        add_action("admin_enqueue_scripts", array($this, "adminEnqueueScripts"));

        /*
        $transient = get_site_transient("update_plugins");
        if(!isset($transient->response[$this->plugin])) {
            return;
        }
        
        $transient = $transient->response[$this->plugin];
        
        if($transient->downloads < 0 || apply_filters("_wpjb_upgrade_manager_can_upgrade", false)) {
            add_filter("after_plugin_row_".$this->plugin, array($this, "upgradeNotice"));
            add_action("admin_enqueue_scripts", array($this, "adminEnqueueScripts"));
        }   
        
        */
    }
    
    protected static function _getUrl() 
    {
        $url = self::$url;
        
        if(wpjb_conf("license_use_non_ssl") == "1") {
            $url = str_replace("https://", "http://", $url);
        }

        return apply_filters( "wpjb_upgrade_manager_url", $url);
    }
    
    public function remote($action, $args = array())
    {
        $url = trim(self::_getUrl(), "/")."/".$this->slug."/".$action;
        
        $args["site_url"] = get_bloginfo("url");
        $args["site_version"] = $this->version;
        
        if(!isset($args["license"])) {
            $args["license"] = wpjb_conf("license_key");
        }
        
        $request = wp_remote_post($url, array("body"=>$args));

        if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
            return json_decode($request["body"]);
	} else {
            return $request->get_error_message();
        }
    }
    
    public function check($transient)
    {
        if (empty($transient->checked) && isset($transient->response[$this->plugin])) {
            return $transient;
        }

        $remote = $this->remote("version");
        
        if($remote === false || $remote->result == 0) {
            return $transient;
        }
        
        $obj = new stdClass();
        $obj->slug = $this->slug;
        $obj->new_version = $remote->data->version;
        $obj->url = self::_getUrl()."/".$this->slug."/download/?license=".wpjb_conf("license_key");
        $obj->package = self::_getUrl()."/".$this->slug."/download/?license=".wpjb_conf("license_key");
        $obj->downloads = $remote->data->downloads;
        
        if($remote->data->downloads < 0) {
            $obj->upgrade_notice = $this->_m();
        }
        
        if (version_compare($this->version, $remote->data->version, '<')) {
            $transient->response[$this->plugin] = $obj;
        }
        
        return $transient;
    }

    /**
     * Add our self-hosted description to the filter
     *
     * @param boolean $false
     * @param array $action
     * @param object $arg
     * @return bool|object
     */
    public function info($false, $action, $arg)
    {
        if (!isset($arg->slug) || $arg->slug != $this->slug) {
            return $false;
        }
 
        $request = $this->remote("info", array("license"=>wpjb_conf("license_key")));
        
        if(is_object($request) && isset($request->data)) {
            
            $data = $request->data;
            $data->slug = $this->slug;
            $data->plugin_name = $this->plugin;
            $data->sections = (array)$data->sections;

            return $data;
        } else {
            return $false;
        }
    }
    
    protected function _m()
    {

    }
    
    public function upgradeNotice($param)
    {
        $transient = get_site_transient("update_plugins");
        
        if(isset($transient->response[$this->plugin])) {
            $transient = $transient->response[$this->plugin];
        }

        ?>
        <?php if(!wpjb_conf("license_key")): ?>
            <tr class="plugin-update-tr active wpjb-update-error">
                <td colspan="3" class="plugin-update">

                    <div class="update-message notice inline notice-warning notice-alt wpjb-upgrade-error">

                        <span class="wpjb-upgrade-lock-icon dashicons dashicons-lock"></span>
                        <span class="wpjb-upgrade-lock-text">
                        <?php _e('Please enter your license key in the configuration to enable automatic updates.', 'wpjobboard'); ?>

                        </span>

                        <a href="<?php echo esc_attr(wpjb_admin_url("config", "edit", null, array("form"=>"license"))) ?>" class="button button-primary">
                            <span class="dashicons dashicons-admin-network wpjb-upgrade-button-icon"></span>
                            <?php _e("Enter License Key", "wpjobboard") ?>
                        </a>

                    </div>
                </td>
            </tr>
        <?php elseif(isset($transient->downloads) && $transient->downloads < 0): ?>
            <tr class="plugin-update-tr active wpjb-update-error">
                <td colspan="3" class="plugin-update">

                    <div class="update-message notice inline notice-warning notice-alt wpjb-upgrade-error">

                        <span class="wpjb-upgrade-lock-icon dashicons dashicons-lock"></span>
                        <span class="wpjb-upgrade-lock-text">
                        <?php _e('Your license has expired. Please renew it to keep your job board up to date and compatible with WordPress.', 'wpjobboard'); ?>

                        </span>

                        <?php $renew = "https://wpjobboard.net/avangate/renew?license=" . wpjb_conf("license_key"); ?>
                        <a href="<?php echo esc_attr($renew) ?>" class="button button-primary">
                            <span class="dashicons dashicons-update wpjb-upgrade-button-icon"></span>
                            <?php _e("Renew Now!", "wpjobboard") ?>
                        </a>

                    </div>

                </td>
            </tr>
        <?php endif; ?>
        <?php
    }
    
    public function adminEnqueueScripts($hook)
    {
        wp_register_style("wpjb-admin-upgrade-css", plugins_url()."/wpjobboard/public/css/admin-upgrade.css");
        wp_register_script("wpjb-admin-upgrade-js", plugins_url()."/wpjobboard/public/js/admin-upgrade.js", array("jquery"));
        wp_localize_script("wpjb-admin-upgrade-js", "wpjb_admin_upgrade_lang", array(
            "message" => $this->_m()
        ));
        
        if($hook == "plugins.php" || $hook == "update-core.php") {
            wp_enqueue_script("wpjb-admin-upgrade-js");
            wp_enqueue_style("wpjb-admin-upgrade-css");
        }
        
        return $hook;
    }
    
    protected static function sort($a, $b) 
    {
        return version_compare($a->getVersion(), $b->getVersion());
    }
    
    public static function update()
    {
        $mask = dirname(__FILE__)."/*.php";
        $version = wpjb_conf("version", "4.0.0");

        if($version == Wpjb_Project::VERSION) {
            return;
        }

        $flist = wpjb_glob($mask);
        $uplist = array();
        
        foreach($flist as $file) {
            $name = pathinfo($file);
            $name = str_replace(".php", "", $name["basename"]);
            if(is_numeric($name)) {
                $name = "Wpjb_Upgrade_".$name;
                $update = new $name;
                if(!$update instanceof Wpjb_Upgrade_Abstract) {
                    continue;
                }

                if(version_compare($version, $update->getVersion()) === -1) {
                    $uplist[] = $update;
                }
            }
        }
        
        uasort($uplist, array(__CLASS__, "sort"));
        
        foreach($uplist as $update) {
            $update->execute();
        }

        $instance = Wpjb_Project::getInstance();
        $instance->setConfigParam("version", Wpjb_Project::VERSION);
        $instance->saveConfig();
    }
    
}
