<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Linkedin
 *
 * @author Grzegorz
 */
class Wpjb_Service_Linkedin {
    
    const URL = 'https://www.linkedin.com/';
    
    const API_URL = 'https://api.linkedin.com/';
    
    const VERSION = 'v2';
    
    public $param = null;
    
    public function __construct($param = array())
    {
        if(is_array($param)) {
            $param = (object)$param;
        }
        
        $this->param = $param;
    }
    
    public function setOauthToken($token)
    {
        $this->param->oauth_token = $token;
    }
    
    public function getOauthToken()
    {
        return $this->param->oauth_token;
    }
    
    public function setOauthTokenSecret($token)
    {
        $this->param->oauth_token_secret = $token;
    }
    
    public function getOauthTokenSecret()
    {
        return $this->param->oauth_token_secret;
    }
    
    /**
     * 
     * @param array $param
     * @return Wpjb_Service_Linkedin
     */
    public static function linkedin($param = array())
    {
        
        $path = Wpjb_List_Path::getPath("vendor");
        if(!class_exists("OAuthException")) {
            require_once $path."/TwitterOAuth/OAuth.php";
        }
        if(!class_exists("TwitterOAuth")) {
            require_once $path."/TwitterOAuth/TwitterOAuth.php";
        }
        
        $default = array(
            'api_key'  => wpjb_conf("linkedin_api_key"),
            'secret_key' => wpjb_conf("linkedin_secret_key"),
            'oauth_token' => wpjb_conf("linkedin_oauth_token"),
            'oauth_token_secret' => wpjb_conf("linkedin_oauth_token_secret"),
        );
        
        foreach($default as $key => $value) {
            if(!isset($param[$key])) {
                $param[$key] = $value;
            }
        }

        $self = new self($param);
        
        return $self;
    }
    
    /**
     * Request Token to Authenticate User 
     * 
     * @param array $param
     * @return string
     */
    public function authorizationUrl( $param )
    {
        $params = array(
            'response_type'     => 'code', // Only Code is OK
            'client_id'         => $this->param->api_key,
            'scope'             => 'r_liteprofile r_emailaddress w_member_social',
            //'state'             => uniqid('', true), // optional for CSRF protection - long string
            'redirect_uri'      => $param["redirect_url"],
        );

        // Authentication request
        $url = self::URL . "oauth/" . self::VERSION . "/authorization/?" . http_build_query($params);

        return $url;
    }
    
    /**
     * Get Access Token to make requests
     * 
     * @param array $param
     * @return array
     */
    public function accessToken( $param = array() )
    {
        $params = array(
            'grant_type'        => 'authorization_code',
            'client_id'         => $this->param->api_key,
            'client_secret'     => $this->param->secret_key,
            'code'              => $param['code'],
            'redirect_uri'      => wpjb_admin_url("config", "edit", null, array("form"=>"linkedin", "do"=>"get-access-token")),
        );

        $url = self::URL . "oauth/" . self::VERSION . '/accessToken';

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt( $ch,CURLOPT_URL, $url );
        curl_setopt( $ch,CURLOPT_POST, count( $params ) );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, http_build_query( $params ) );

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

        //execute post
        $response = curl_exec($ch);
        $token = json_decode($response);
          
        return array( 
            "linkedin_access_token" => $token->access_token,
        );
    }
    
    /**
     * Get profile of the user
     * 
     * @return object
     */
    public function profile( )
    {
        $url = self::API_URL . self::VERSION . "/me";
        $header = array(
            "Authorization: Bearer " . $this->getOauthToken(),
            "Connection: Keep-Alive"
        );
            
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true ); 

        $output = curl_exec($ch);
        curl_close($ch);
        
        return json_decode( $output ); 
    }
    
    /*public function organizations( $person_id ) 
    {
        $url = self::API_URL . self::VERSION . "/organizationalEntityAcls?q=roleAssignee";
        $url = self::API_URL . self::VERSION . "/people/id=" . $person_id . "?fields=positions:($*:(company))";
        
        $header = array(
            "Authorization: Bearer " . $this->getOauthToken(),
            "Connection: Keep-Alive"
        );
            
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true ); 

        $output = curl_exec($ch);
        curl_close($ch);
        
        return json_decode( $output ); 
        
    }*/
    
    /*public function admin()
    {
        $endpoint = self::URL.'/'.self::VERSION.'/companies?is-company-admin=true';
        $consumer = new OAuthConsumer($this->param->api_key, $this->param->secret_key, NULL);
        $signatureMethod = new OAuthSignatureMethod_HMAC_SHA1();
        $token = new OAuthConsumer($this->param->oauth_token, $this->param->oauth_token_secret, 1);

        $profileObj = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $endpoint, array());
        $profileObj->sign_request($signatureMethod, $consumer, $token);
        $toHeader = $profileObj->to_header();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($toHeader));
        curl_setopt($ch, CURLOPT_URL, $endpoint);

        $output = curl_exec($ch);
        curl_close($ch);

        return new SimpleXMLElement($output);
    }*/
    
    /**
     * Share post
     * 
     * @param type $object
     * @return type
     */
    public static function share($object)
    {
        try {
            $post = self::_share($object);
            return json_decode( $post );
            
            //$meta = $object->meta->facebook_share_id->getFirst();
            //$meta->value = $post["id"];
            //$meta->save();
            
        } catch(Exception $e) {
            // @todo: log error
        }
    }
    
    /**
     * Post test share
     * 
     * @param type $object
     * @return type
     */
    public static function shareTest($object) 
    {
        $post = self::_share($object);
        return json_decode( $post );
    }
    
    /**
     * Share function
     * 
     * @param type $object
     * @return boolean
     */
    protected static function _share( $object ) {
        
        if($object->meta->job_source->value()) {
            return false;
        }
        
        $url = self::API_URL . self::VERSION . "/ugcPosts";
        
        $linkedin = self::linkedin();
        $parameters = apply_filters("wpjb_linkedin_share_params", array(
            'comment' => wpjb_conf("linkedin_share_comment"),
            'title' => wpjb_conf("linkedin_share_title"),
            'description' => wpjb_conf("linkedin_share_description"),
            'url' => $object->url(),
            'image_url' => $object->getLogoURL(),
            'visibility' => "PUBLIC"
        ));
        
        $parser = new Daq_Tpl_Parser();
        $parser->assign("job", $object);
        
        $parameters["comment"] = $parser->draw($parameters["comment"]);
        $parameters["title"] = $parser->draw($parameters["title"]);
        $parameters["description"] = $parser->draw($parameters["description"]);
        
        $profile = $linkedin->profile();
        
        $media_obj = new stdClass();
        $media_obj->status = "READY";
        $media_obj->description->text = $parameters["description"];
        $media_obj->originalUrl = $parameters["url"];
        $media_obj->title->text = $parameters["title"];
         
        $data = new stdClass();
        $data->author = "urn:li:person:" . $profile->id;
        $data->lifecycleState = "PUBLISHED";
        $data->specificContent->{"com.linkedin.ugc.ShareContent"}->shareCommentary->text = $parameters["comment"];
        $data->specificContent->{"com.linkedin.ugc.ShareContent"}->shareMediaCategory =  "ARTICLE";
        $data->specificContent->{"com.linkedin.ugc.ShareContent"}->media = array( 0 => $media_obj );
        $data->visibility->{"com.linkedin.ugc.MemberNetworkVisibility"} = $parameters["visibility"];
        
        $data = json_encode($data);
    
        
        $header = array(
            "Authorization: Bearer " . $linkedin->param->oauth_token,
            "Content-Type: application/json",
            'Content-Length: ' . strlen($data),
            "X-Restli-Protocol-Version: 2.0.0"
        );
            
        // PRZYDATNY LINK!
        //https://docs.microsoft.com/en-us/linkedin/consumer/integrations/self-serve/share-on-linkedin?context=linkedin/consumer/context
        
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header );
        //curl_setopt( $ch,CURLOPT_POST, count( $data ) );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, $data );
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true ); 

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * Executed on wp action, handle apply via LinkedIn
     * 
     * @param type $route
     * @return type
     */
    public static function dispatch($route = null)
    {
        if(!is_singular('job')) {
            return $route;
        }

        $request = Daq_Request::getInstance();
        $applyFromLinkedin = false;
        
        if(isset($_GET["oauth_token"]) && isset($_COOKIE["wpjb_linkedin_ots"])) {
            
            $linkedin_api_key = wpjb_conf("linkedin_api_key");
            $linkedin_secret_key = wpjb_conf("linkedin_secret_key");

            $linkedin = Wpjb_Service_Linkedin::linkedin(array(
                "api_key" => $linkedin_api_key,
                "secret_key" => $linkedin_secret_key,
                "oauth_token" => "",
                "oauth_token_secret" => ""
            ));

            $atParams = array(
                "oauth_token" => $request->get("oauth_token"), 
                "oauth_verifier" => $request->get("oauth_verifier"), 
                "request_oauth_token_secret" => $_COOKIE["wpjb_linkedin_ots"]
            );

            $token = $linkedin->accessToken($atParams);
            
            setcookie('wpjb_linkedin_ots', null, time()-3600, COOKIEPATH, COOKIE_DOMAIN, false);
            setcookie('wpjb_linkedin_oauth_token', $token['oauth_token'], time()+3600*24*7, COOKIEPATH, COOKIE_DOMAIN, false);
            setcookie('wpjb_linkedin_oauth_token_secret', $token['oauth_token_secret'], time()+3600*24*7, COOKIEPATH, COOKIE_DOMAIN, false);
            
            $applyFromLinkedin = true;
        } elseif($request->get("linkedin_apply") && $_COOKIE["wpjb_linkedin_oauth_token"]) {
            $token = array(
                "oauth_token" => $_COOKIE["wpjb_linkedin_oauth_token"],
                "oauth_token_secret" => $_COOKIE["wpjb_linkedin_oauth_token_secret"],
            );
            
            $applyFromLinkedin = true;
        }
        
        if($applyFromLinkedin) {
            
            $linkedin_api_key = wpjb_conf("linkedin_api_key");
            $linkedin_secret_key = wpjb_conf("linkedin_secret_key");

            $linkedin = Wpjb_Service_Linkedin::linkedin(array(
                "api_key" => $linkedin_api_key,
                "secret_key" => $linkedin_secret_key,
                "oauth_token" => $token['oauth_token'],
                "oauth_token_secret" => $token['oauth_token_secret']
            ));
            
            $fields = array(
                "id", "formatted-name", "email-address", "headline", "location", "industry",
                "summary", "positions", "picture-url", "skills", "languages", "educations",
                "site-standard-profile-request"
            );
            
            $profile = $linkedin->profile(apply_filters("wpjb_linkedin_fields", $fields));
            
            if(!isset($profile->{'formatted-name'})) {
                return $route;
            } 
            
            $applicantName = trim((string)$profile->{'formatted-name'});
            if(empty($applicantName)) {
                return $route;
            }
            
            $user_id = null;

            $query = new Daq_Db_Query();
            $query->from("Wpjb_Model_Job t");
            $query->where("post_id = ?", get_the_ID());
            $query->limit(1);
            $result = $query->execute();

            if(isset($result[0])) {
                $job = $result[0];
            } else {
                return $route;
            }
                

            if(wpjb_get_current_user_id("candidate")) {
                $user_id = wpjb_get_current_user_id("candidate");
            }
            
            $apply = new Wpjb_Model_Application();
            $apply->job_id = $job->id;
            $apply->user_id = $user_id;
            $apply->applied_at = date("Y-m-d");
            $apply->applicant_name = $applicantName;
            $apply->message = (string)$profile->{'summary'};
            $apply->email = (string)$profile->{'email-address'};
            $apply->status = Wpjb_Model_Application::STATUS_NEW;
            $apply->save();
            
            $meta = $apply->meta->linkedin_profile_url->getFirst();
            $meta->value = (string)$profile->{'site-standard-profile-request'}->url;
            $meta->save();

            do_action("wpjb_linkedin_application", $apply, $profile);
            
            if($job->user_id) {
                $user = new WP_User($job->user_id);
            }

            // notify admin
            $mail = Wpjb_Utility_Message::load("notify_admin_new_application");
            $mail->assign("job", $job);
            $mail->assign("application", $apply);
            $mail->assign("resume", Wpjb_Model_Resume::current());
            $mail->setTo(wpjb_conf("admin_email", get_option("admin_email")));
            $mail->send();

            // notify employer
            $notify = null;
            if($job->company_email) {
                $notify = $job->company_email;
            } elseif($user && $user->user_email) {
                $notify = $user->user_email;
            }
            if($notify == wpjb_conf("admin_email", get_option("admin_email"))) {
                $notify = null;
            }
            $mail = Wpjb_Utility_Message::load("notify_employer_new_application");
            $mail->assign("job", $job);
            $mail->assign("application", $apply);
            $mail->assign("resume", Wpjb_Model_Resume::current());
            $mail->setTo($notify);
            if($notify !== null) {
                $mail->send();
            }
            
            $m = __("Your <strong>LinkedIn</strong> application has been submitted.", "wpjobboard");
            
            $flash = new Wpjb_Utility_Session();
            $flash->addInfo($m);
            $flash->save();
            
            wp_redirect($job->url());
            exit;
        }
        
        return $route;
    }
    
    /**
     * Show View Linked In Profile button in wp-admin
     * 
     * @param Wpjb_Form_Admin_Application $form
     * @return void
     */
    public static function sectionApply($form)
    {
        $object = $form->getObject();
        
        if(!$object->meta->linkedin_profile_url || !$object->meta->linkedin_profile_url->value()) {
            return;
        }
        
        ?>
        <div class="postbox ">
            <div class="handlediv"><br /></div>
            <h3 class="hndle"><span><?php _e("LinkedIn Profile", "wpjobboard") ?></span></h3>

            <div class="inside">
                <div class="submitbox">

                <div class="">
                    <a href="<?php esc_attr_e($object->meta->linkedin_profile_url->value()) ?>" target="_blank" class="button" style="height:auto; font-size:1.4em; line-height:2.4em">
                        <img src="<?php esc_attr_e(plugins_url()."/wpjobboard/public/images/linkedin-logo-30px.png") ?>" alt="" style="vertical-align:middle; padding:4px 0px" />
                        <?php _e("View LinkedIn Profile", "wpjobboard") ?>
                    </a>
                </div>


                </div>

            </div>
        </div>  
        <?php 
    }
    
    public static function pricing($form) 
    {
        $features = maybe_unserialize($form->getObject()->meta->features->value());
        $value = 0;
        
        if(isset($features["linkedin_share"])) {
            $value = $features["linkedin_share"];
        } 

        $e = $form->create("features[linkedin_share]", "checkbox");
        $e->setBoolean(true);
        $e->setValue($value);
        $e->setLabel(__("LinkedIn", "wpjobboard"));
        $e->addFilter(new Daq_Filter_Int());
        $e->addOption(1, 1, __("Share jobs on LinkedIn", "wpjobboard"));
        $e->setBuiltin(false);
        $e->setOrder(105);
        $form->addElement($e, "perks");

        return $form;
    }
    
    public static function features($listing) 
    {
        $features = maybe_unserialize($listing->meta->features->value());
        
        if(!isset($features["linkedin_share"]) || $features["linkedin_share"] != "1") {
            return;
        } 
        
        ?>
        <span class="wpjb-listing-type-feature-featured wpjb-listing-border">
            <span class="wpjb-glyphs wpjb-icon-linkedin">
                <abbr title="<?php _e("Share on LinkedIn", "wpjobboard") ?>"><?php _e("LinkedIn", "wpjobboard") ?></abbr>
            </span>
        </span>
        <span class="wpjb-listing-type-feature-featured wpjb-listing-border" style="display: inline-block">
            <span class="wpjb-glyphs wpjb-icon-facebook">
                <abbr title="<?php _e("Share on LinkedIn", "wpjobboard") ?>"><?php _e("Facebook", "wpjobboard") ?></abbr>
            </span>
        </span>
        <?php
    }
    
    public static function apply($job, $can_apply = true) 
    {
        
        if(!wp_get_current_user() && wpjb_conf("front_apply_members_only", false)) {
            return;
        }
        if(!$can_apply || $job->meta->job_source->value()) {
            return;
        }
        
        add_action("wp_footer", array("Wpjb_Service_Linkedin", "footer"));
        
        $linkedin = self::linkedin();
        
        ?>


        <div name="widget-holder">
            <script type="text/javascript" 
                src="https://www.linkedin.com/mjobs/awli/awliWidget">
            </script>
            <script type="IN/AwliWidget" 
              data-company-job-code="<?php echo $job->id; ?>" 
                data-integration-context="urn:li:organization:86g1uhejewcibr"
                data-mode="BUTTON_DATA" 
                data-callback-method="onProfileData" 
                data-api-key="86g1uhejewcibr"
                data-allow-sign-in="true">
            </script>
        </div>


        <?php
        
    }
    
    public static function footer()
    {
        ?>
        

        <script>
            
            function onProfileData(profileData) {
                alert("test");
                //var firstnameField = document.getElementById('firstname');
                //var lastnameField = document.getElementById('lastname');
                //firstnameField.value = profileData.firstName;
                //lastnameField.value = profileData.lastName;
            }
            // prefill form with profile data
        </script>
        
        <?php
    }

}

?>
