<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Linkedin extends Daq_Form_Abstract
{
    public $name = null;
    
    public $linkedin = "";
    
    private $_share_as = null;

    public function init()
    {
        $this->name = __("LinkedIn", "wpjobboard");
        $instance = Wpjb_Project::getInstance();

        $this->addGroup( "default", __( "LinkedIn API", "wpjobboard" ) );
        
        $e = $this->create("linkedin_api_key");
        $e->setValue($instance->getConfig("linkedin_api_key"));
        $e->setLabel(__("API Key", "wpjobboard"));
        $this->addElement($e, "default");  
        
        $e = $this->create("linkedin_secret_key");
        $e->setValue($instance->getConfig("linkedin_secret_key"));
        $e->setLabel(__("Secret Key", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("linkedin_api_version", "radio");
        $e->setValue($instance->getConfig("linkedin_api_version"));
        $e->setLabel(__("API Version", "wpjobboard"));
        $e->setHint(__("If you are using old App on LinkedIn select V1, if you are using new App (created after July 23, 2018) please choose V2", "wpjobboard"));
        $e->addOption( "v1", "v1", "V1");
        $e->addOption( "v2", "v2", "V2");
        $this->addElement($e, "default");
        
        $this->executePostSave(null);

        apply_filters("wpja_form_init_config_facebook", $this);

    }
    
    public function editAction($controller)
    {
        $request = Daq_Request::getInstance();
        $project = Wpjb_Project::getInstance();
        $linkedin = Wpjb_Service_Linkedin::linkedin();
        // init
        
        if($controller->isPost() && apply_filters("_wpjb_can_save_config", $controller)) {

            $isValid = $this->isValid($request->getAll());

            if($isValid) {
                foreach($this->getValues() as $k => $v) {
                    $project->setConfigParam($k, $v);
                }
                
                $project->saveConfig();
                $controller->view->_flash->addInfo(__("Configuration saved.", "wpjobboard"));
                // post-save
                
            } else {
                $controller->view->_flash->addError(__("There are errors in the form.", "wpjobboard"));
                // post-error
            }
        }
    }
    
    public function executeInit($controller = null)
    {
        $controller->view->submit_action = wpjb_admin_url("config", "edit", null, array("form"=>"linkedin"));
        
        $request = Daq_Request::getInstance();
        $project = Wpjb_Project::getInstance();
        $linkedin = Wpjb_Service_Linkedin::linkedin();
        $url = wpjb_admin_url("config", "edit", null, array("form"=>"linkedin", "do"=>"get-access-token"));
        
        $linkedin_api_key = wpjb_conf("linkedin_api_key", $request->post("linkedin_api_key"));
        $linkedin_secret_key = wpjb_conf("linkedin_secret_key", $request->post("linkedin_secret_key"));
        
        if($request->get("revoke")) {
            $project->setConfigParam("linkedin_oauth_token", null);
            $project->setConfigParam("linkedin_oauth_token_secret", null);
            $project->saveConfig();
            
            $linkedin->setOauthToken(null);
            $linkedin->setOauthTokenSecret(null);
            
            delete_user_meta(get_current_user_id(), "_linkedin_request_oauth_token_secret");
        }
        
        if(!$linkedin->getOauthToken() && $request->get("do") == "get-access-token") {
            
            if( Daq_Request::getInstance()->get( "code", null) != null ) {
                // OAuth v2
                $code = Daq_Request::getInstance()->get( "code", null);
                $atParams = array(
                    "code" => $code,
                );
                
                $token = $linkedin->accessToken($atParams);
                $project->setConfigParam( "linkedin_oauth_token", $token["linkedin_oauth_token"] );
                $project->saveConfig();
                
                $linkedin->setOauthToken( $token["linkedin_oauth_token"] );
                
            } else {
            
                $atParams = array(
                    "oauth_token" => $_REQUEST['oauth_token'], 
                    "oauth_verifier" => $_REQUEST['oauth_verifier'], 
                    "request_oauth_token_secret" => get_user_meta(get_current_user_id(), "_linkedin_request_oauth_token_secret", true)
                );
                
                $token = $linkedin->accessToken($atParams);
                $project->setConfigParam("linkedin_oauth_token", $token["oauth_token"]);
                $project->setConfigParam("linkedin_oauth_token_secret", $token["oauth_token_secret"]);
                $project->saveConfig();

                $linkedin->setOauthToken($token["oauth_token"]);
                $linkedin->setOauthTokenSecret($token["oauth_token_secret"]);
            }
            
        } elseif(!$linkedin->getOauthToken() && $linkedin_api_key && $linkedin_api_key) {
            $linkedin = Wpjb_Service_Linkedin::linkedin(array(
                "api_key" => $linkedin_api_key,
                "secret_key" => $linkedin_secret_key
            ));
            
            $requestToken = $linkedin->requestToken(array("redirect_url"=>$url));
            $tokenUrl = $requestToken["url"];
            
            update_user_meta(get_current_user_id(), "_linkedin_request_oauth_token_secret", $requestToken["oauth_token_secret"]);
            
            $helper = new Daq_Helper_Html("a", array(
                "href" => $tokenUrl
            ), __("Connect with LinkedIn", "wpjobboard"));

            $this->linkedin = $helper->render();
        }
        
        if($linkedin->getOauthToken()) {
            $url = wpjb_admin_url("config", "edit", null, array("form"=>"linkedin", "revoke"=>1));
            
            try {
                $profile = $linkedin->profile();

                $name  = '<span style="line-height:28px">'.(string)$profile->{'first-name'}." ".(string)$profile->{'last-name'}.'</span>';
                $name .= ' <a href="'.esc_attr($url).'" class="button">Revoke Access</a>';

                if(!$this->getElement("linkedin_share_as")->getOptions()) {
                    $this->getElement("linkedin_share_as")->addOptions($this->_shareAs());
                }

            } catch(Exception $e) {
                $name  = '<span style="line-height:28px; color:red">Could not connect, please try again later or "Revoke Access" and connect to LinkedIn again.</span>';
                $name .= '<span style="line-height:28px; color:red">Error: '.$e->getMessage().'</span>';
                $name .= ' <a href="'.esc_attr($url).'" class="button">Revoke Access</a>';
            }
            
            $this->linkedin = $name;
        }

    }
    
    protected function _shareAs() 
    {
        $linkedin = Wpjb_Service_Linkedin::linkedin();
        $shareAs = array();
        
        try {
        
            if($linkedin->getOauthToken()) {
               $profile = $linkedin->profile();

                $shareAs = array(
                    array(
                        "key"=>"private", 
                        "value"=>"private", 
                        "description"=>(string)$profile->{'first-name'}." ".(string)$profile->{'last-name'}
                    )
                );

                $companies = $linkedin->admin();

                if($companies->attributes()->total > 0) {
                    foreach($companies->company as $c) {
                        $shareAs[] = array(
                            "key"=>(string)$c->id, 
                            "value"=>(string)$c->id, 
                            "description"=>(string)$c->name
                        );
                    }
                }
            }
            
        } catch(Exception $e) {
            // do some logging here
        }
        
        return $shareAs;
    }
    
    public function executePostSave($controller = null)
    {
        $instance = Wpjb_Project::getInstance();
        $request = Daq_Request::getInstance();
        
        $linkedin_api_key = wpjb_conf("linkedin_api_key", $request->post("linkedin_api_key"));
        $linkedin_secret_key = wpjb_conf("linkedin_secret_key", $request->post("linkedin_secret_key"));
        $linkedin_oauth_token = wpjb_conf("linkedin_oauth_token", $request->get("oauth_token"));
        
        if($linkedin_api_key && $linkedin_secret_key) {
            $e = $this->create("_linkedin_user");
            $e->setRenderer(array($this, "renderUser"));
            $e->setLabel(__("Account", "wpjobboard"));
            $this->addElement($e);
        }
        
        if($request->get("revoke") == "1") {
            return;
        }
        
        if($linkedin_api_key && $linkedin_oauth_token) {
            
            $this->addGroup("share", __("Automatic Sharing", "wpjobboard"));
            
            $share_as = $instance->getConfig("linkedin_share_as", "");
            if(is_array($share_as) && isset($share_as[0])) {
                $share_as = $share_as[0];
            }
            
            $e = $this->create("linkedin_share_as", "radio");
            $e->setLabel(__("Post As", "wpjobboard"));
            $e->setValue($share_as);
            $e->addOptions($this->_shareAs());
            $this->addElement($e);
            
            $share = $instance->getConfig("linkedin_share", "");
            if(is_array($share) && isset($share[0])) {
                $share = $share[0];
            }
            
            $e = $this->create("linkedin_share", "checkbox");
            $e->setValue($share);
            $e->addOption(1, 1, __("Share new jobs on LinkedIn", "wpjobboard"));
            $this->addElement($e, "share");
            
            $e = $this->create("linkedin_share_comment");
            $e->setLabel(__("Comment", "wpjobboard"));
            $e->setRequired(true);
            $e->setValue($instance->getConfig("linkedin_share_comment"));
            $e->setAttr("placeholder", __("E.g. New job posting!", "wpjobboard"));
            $this->addElement($e, "share");
            
            $e = $this->create("linkedin_share_title");
            $e->setLabel(__("Title", "wpjobboard"));
            $e->setValue($instance->getConfig("linkedin_share_title", '{$job.job_title}'));
            $this->addElement($e, "share");
            
            $e = $this->create("_facebook_placeholder");
            $e->setRenderer("wpjb_admin_variable_renderer");
            $e->setHint(__("You can use above variables in Comment and Title", "wpjobboard"));
            $e->setValue(array("job"));
            $this->addElement($e, "share");
            
            add_action("wpjb_config_edit_buttons", array($this, "executeButtons"));
            
            $this->addGroup("apply", __("Apply with LinkedIn", "wpjobboard"));
            
            $e = $this->create("linkedin_apply", "checkbox");
            $e->setValue($instance->getConfig("linkedin_apply", 0));
            $e->addOption(1, 1, __("Allow applications from LinkedIn", "wpjobboard"));
            $this->addElement($e, "apply");
            
            $e = $this->create("linkedin_api_scope", "radio");
            $e->addOption("basic", "basic", __("Get basic LinkedIn profile information only.", "wpjobboard"));
            $e->addOption("extended", "extended", __("Get full LinkedIn profile information. (You need to <a href='https://developer.linkedin.com/docs/apply-with-linkedin'>request review at LinkedIn</a> to use this option.)"));
            $e->setValue($instance->getConfig("linkedin_api_scope", "basic"));
            $this->addElement($e, "apply");
            
       }
       
        if($request->post("linkedin_share_test") && $controller) {
            $list = new Daq_Db_Query();
            $list->select("*");
            $list->from("Wpjb_Model_Job t");
            $list->limit(1);
            $result = $list->execute();
            
            if(empty($result)) {
                $controller->view->_flash->addError(__("LinkedIn: You need to have at least one posted job to send test tweet.", "wpjobboard"));
            } else {
                $job = $result[0];
                try {
                    Wpjb_Service_Linkedin::shareTest($job);
                    $controller->view->_flash->addInfo(__("Share has been posted, please check your LinkedIn account.", "wpjobboard"));
                } catch(Exception $e) {
                    $controller->view->_flash->addError($e->getMessage());
                }
            }
        }
    }
    
    public function renderUser($field)
    {
        return $this->linkedin;
    }
    
    public function executeButtons()
    {
        $button = new Daq_Helper_Html("input", array(
            "type" => "submit",
            "name" => "linkedin_share_test",
            "value" => __("Send Test Share", "wpjobboard")
        ));
        
        echo $button->render();
    }

}

?>