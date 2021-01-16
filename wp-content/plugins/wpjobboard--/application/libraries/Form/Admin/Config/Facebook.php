<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Facebook extends Daq_Form_Abstract
{
    public $name = null;
    
    public $facebook = "";

    public function init()
    {
        $this->name = __("Facebook", "wpjobboard");
        
        if(wpjb_conf("facebook_access_token")) {
            $this->_initConfig();
        } else {
            $this->_initApiKeys();
        }
        
        apply_filters("wpja_form_init_config_facebook", $this);
    }
    
    protected function _initConfig() 
    {
        $instance = Wpjb_Project::getInstance();
        
        $this->addGroup( "default", __( "Facebook Configuration", "wpjobboard" ) );
        
        $e = $this->create("_facebook_user");
        $e->setRenderer(array($this, "renderUser"));
        $e->setLabel(__("Account", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("facebook_share", "checkbox");
        $e->setLabel(__("Activity", "wpjobboard"));
        $e->setValue($instance->getConfig("facebook_share", 1));
        $e->addOption(1, 1, __("Share new jobs on Facebook", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Int());
        $this->addElement($e, "default");

        $e = $this->create("facebook_share_message");
        $e->setLabel(__("Message", "wpjobboard"));
        $e->setRequired(true);
        $e->setValue($instance->getConfig("facebook_share_message"));
        $e->setAttr("placeholder", __("E.g. New job posting!", "wpjobboard"));
        $this->addElement($e, "default");

        $e = $this->create("facebook_share_name");
        $e->setLabel(__("Title", "wpjobboard"));
        $e->setValue($instance->getConfig("facebook_share_name", '{$job.job_title}'));
        $this->addElement($e, "default");

        $e = $this->create("facebook_share_caption");
        $e->setLabel(__("Caption", "wpjobboard"));
        $e->setValue($instance->getConfig("facebook_share_caption"), get_option("blogname"));
        $e->setAttr("placeholder", __("Site headline here", "wpjobboard"));
        $this->addElement($e, "default");

        $e = $this->create("_facebook_placeholder");
        $e->setRenderer("wpjb_admin_variable_renderer");
        $e->setHint(__("You can use above variables in Message, Title and Caption", "wpjobboard"));
        $e->setValue(array("job"));
        $this->addElement($e, "default");
    }
    
    protected function _initApiKeys()
    {
        $instance = Wpjb_Project::getInstance();
        
        $this->addGroup( "api", __( "Facebook API", "wpjobboard" ) );

        $e = $this->create("facebook_app_id");
        $e->setValue($instance->getConfig("facebook_app_id"));
        $e->setLabel(__("App ID", "wpjobboard"));
        $this->addElement($e, "api");
        
        $e = $this->create("facebook_app_secret");
        $e->setValue($instance->getConfig("facebook_app_secret"));
        $e->setLabel(__("App Secret", "wpjobboard"));
        $this->addElement($e, "api");
        
        

    }
    
    public function renderUser($field)
    {
        require_once Wpjb_List_Path::getPath("vendor") . '/Facebook/autoload.php';
        
        try {
            $fb = new Facebook\Facebook([
                'app_id' => wpjb_conf('facebook_app_id'), // Replace {app-id} with your app id
                'app_secret' => wpjb_conf('facebook_app_secret'),
                'default_graph_version' => 'v2.2',
                'persistent_data_handler' => new Wpjb_Service_FacebookData(),
            ]);
            
            $me = $fb->get("/me", wpjb_conf("facebook_access_token"));
            $data = $me->getDecodedBody();
            
            $m = __("You are posting as <strong>%s</strong>", "wpjobboard");
            return sprintf($m, $data["name"]);
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }
    
}


?>