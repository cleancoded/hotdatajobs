<?php

class Wpjb_Module_Admin_Config_Facebook extends Wpjb_Controller_Admin {
    
    public static function getLoginUrl() {
        require_once Wpjb_List_Path::getPath("vendor") . '/Facebook/autoload.php';

        $fb = new Facebook\Facebook([
            'app_id' => wpjb_conf('facebook_app_id'), // Replace {app-id} with your app id
            'app_secret' => wpjb_conf('facebook_app_secret'),
            'default_graph_version' => 'v2.2',
            'persistent_data_handler' => new Wpjb_Service_FacebookData(),
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = array('publish_actions', 'publish_pages', 'manage_pages'); // Optional permissions
        
        return $helper->getLoginUrl(wpjb_admin_url('config', 'facebooktrans', null, array('noheader'=>1)), $permissions);
    }
    
    
    public function facebookAction()
    {
        
        $this->view->show_form = true;
        $this->view->section = null;
        $this->view->submit_action = "";
        $this->view->submit_title = __("Update", "wpjobboard");
        
        $form = new Wpjb_Form_Admin_Config_Facebook;

        if($this->isPost() && apply_filters("_wpjb_can_save_config", $this)) {

            $isValid = $form->isValid($this->_request->getAll());

            if($isValid) {
                $instance = Wpjb_Project::getInstance();

                foreach($form->getValues() as $k => $v) {
                    $instance->setConfigParam($k, $v);
                }
                
                $instance->saveConfig();
                $this->_addInfo(__("Configuration saved.", "wpjobboard"));

    
                
            } else {
                $this->_addError(__("There are errors in the form.", "wpjobboard"));
                // if form has postError
            }
        }
        
        $this->view->form = $form;
        
        return Wpjb_List_Path::getPath("admin_views") . "/config/facebook.php";
        
    }
    
    public function facebooktestAction()
    {
        $query = new Daq_Db_Query();
        $query->select();
        $query->from("Wpjb_Model_Job t");
        $query->limit(1);
        
        $result = $query->execute();
        
        if(!isset($result[0])) {
            $this->_addError(__("You need to post at least one job before creating a test share.", "wpjobboard"));
            wp_redirect(wpjb_admin_url("config", "facebook"));
            exit;
        }
        
        try {
            $fb = new Wpjb_Service_Facebook();
            $result = $fb->shareTest($result[0]);
        } catch(Exception $e) {
            $this->_addError($e->getMessage());
            wp_redirect(wpjb_admin_url("config", "facebook"));
            exit;
        }
        
        if($result instanceof \Facebook\FacebookResponse) {
            $body = $result->getDecodedBody();
            list($page_id, $post_id) = explode("_", $body["id"]);
            $m = __('Test share has been posted. You can <a href="https://www.facebook.com/%s/posts/%s">view it here</a>.', "wpjobboard");
            $this->_addInfo(sprintf($m, $page_id, $post_id));
            wp_redirect(wpjb_admin_url("config", "facebook"));
            exit;
        }
        
        wp_die("<pre>".print_r($result, true)."</pre>", "Unknown Facebook Error", 200);
    }
    
    public function facebookresetAction()
    {
        $instance = Wpjb_Project::getInstance();
        $instance->setConfigParam("facebook_app_id", null);
        $instance->setConfigParam("facebook_app_secret", null);
        $instance->setConfigParam("facebook_access_token", null);
        $instance->saveConfig();
        
        $this->_addInfo(__("Configuration Reset.", "wpjobboard"));
        
        wp_redirect(wpjb_admin_url("config", "facebook"));
        exit;
    }
    
    public function facebooktransAction()
    {
        if($this->isPost() && $this->_request->post("facebook_access_token")) {
            $this->_transSave();
        } else {
            $this->_transSelection();
        }
    }
    
    protected function _transSave() {
        $instance = Wpjb_Project::getInstance();
        $instance->setConfigParam("facebook_access_token", $this->_request->post("facebook_access_token"));
        $instance->saveConfig();
        
        wp_redirect(wpjb_admin_url("config", "facebook"));
        exit;
    }
    
    protected function _transSelection() {
        require_once Wpjb_List_Path::getPath("vendor") . '/Facebook/autoload.php';
        
        $fb = new Facebook\Facebook([
          'app_id' => wpjb_conf('facebook_app_id'), // Replace {app-id} with your app id
          'app_secret' => wpjb_conf('facebook_app_secret'),
          'default_graph_version' => 'v2.2',
          'persistent_data_handler' => new Wpjb_Service_FacebookData(),
        ]);

        $helper = $fb->getRedirectLoginHelper();

        try {
          $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        if (! isset($accessToken)) {
          if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $helper->getError() . "\n";
            echo "Error Code: " . $helper->getErrorCode() . "\n";
            echo "Error Reason: " . $helper->getErrorReason() . "\n";
            echo "Error Description: " . $helper->getErrorDescription() . "\n";
          } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
          }
          exit;
        }

        // Logged in
        //echo '<h3>Access Token</h3>';
        //var_dump($accessToken->getValue());

        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        //echo '<h3>Metadata</h3>';
        //var_dump($tokenMetadata);

        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId(wpjb_conf('facebook_app_id')); // Replace {app-id} with your app id
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();

        if (! $accessToken->isLongLived()) {
          // Exchanges a short-lived access token for a long-lived one
          try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
          } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
            exit;
          }

          //echo '<h3>Long-lived</h3>';
          //var_dump($accessToken->getValue());
        }

        $me = $fb->get('/me', $accessToken)->getDecodedBody();
        
        $css = '<style type="text/css">'.PHP_EOL;
        $css.= 'h1, h3 { font-weight: normal }'.PHP_EOL;
        $css.= 'label { margin: 1em 0; display: block }'.PHP_EOL;
        $css.= 'label > span.name { margin: 0 1em }'.PHP_EOL;
        $css.= 'label > span.category { opacity: 0.8 }'.PHP_EOL;
        $css.= '</style>';
        $lines = array(
            $css,
            sprintf('<h1>Hi, <strong>%s</strong></h1>', $me["name"]),
            sprintf('<h3>We are almost done, select page or profile to which you wish to post notifications and save the form.</h3>'),
        );
        
        $lines[] = '<form action="" method="post">';
        
        $name = sprintf(
            '<span class="name">%s</span><span class="category">%s</span>', 
            esc_html($me["name"]),
            '(Your Profile)'
        );

        $input = new Daq_Helper_Html("input", array(
            "type" => "radio",
            "id" => "id-".$me["id"],
            "name" => "facebook_access_token",
            "value" => $accessToken,
            "checked" => "checked"
        ));
        
        $lines[] = '<label for="'."id-".$me["id"].'">'.$input.' '.$name.'</label>'; 
        
        $accounts = $fb->get('/me/accounts', $accessToken)->getDecodedBody();

        if(isset($accounts["data"]) && is_array($accounts["data"])) {
            $pages = $accounts["data"];
        } else {
            $pages = array();
        }
        
        foreach($pages as $page) {

            $name = sprintf(
                '<span class="name">%s</span><span class="category">%s</span>', 
                esc_html($page["name"]), 
                esc_html($page["category"])
            );

            $input = new Daq_Helper_Html("input", array(
                "type" => "radio",
                "id" => "id-".$page["id"],
                "name" => "facebook_access_token",
                "value" => $page["access_token"]
            ));

            $lines[] = '<label for="'."id-".$page["id"].'">'.$input.' '.$name.'</label>'; 
            
        }
        
        $lines[] = '<hr/>';
        $lines[] = '<input type="submit" value="Save!" class="button" />';
        $lines[] = '</form>';
        
        wp_die(implode("", $lines), "Facebook Configuration", 200);
        exit;
    }
}
