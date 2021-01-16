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
    
    const URL = 'https://api.linkedin.com';
    
    const VERSION = 'v1';
    
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
     * @param type $param
     * @return type
     */
    public function requestToken($param)
    {
       
        if( wpjb_conf("linkedin_api_version") == "v1" ) {
             // OAuth V1
            $url = $param["redirect_url"];
            $postfields = array();  

            $request_token_url = self::URL.'/uas/oauth/requestToken';

            $result = null;
            $consumer = new OAuthConsumer($this->param->api_key, $this->param->secret_key, NULL);
            $signatureMethod = new OAuthSignatureMethod_HMAC_SHA1();
            $reqObj = OAuthRequest::from_consumer_and_token($consumer, NULL, "POST", $request_token_url);
            $reqObj->set_parameter("oauth_callback", $url);

            if(isset($param["scope"])) {
                $reqObj->set_parameter("scope", $param["scope"]);
                $postfields[] = "scope=".$param["scope"];
            }

            $reqObj->sign_request($signatureMethod, $consumer, NULL);
            $toHeader = $reqObj->to_header();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($toHeader));
            curl_setopt($ch, CURLOPT_URL, $request_token_url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, join("&", $postfields));
            curl_setopt($ch, CURLOPT_POST, 1);

            $output = curl_exec($ch);
            curl_close($ch);

            parse_str($output, $result);

            //setcookie("linkedin_request_oauth_token", $result["oauth_token"]);
            //setcookie("linkedin_request_oauth_token_secret", $result["oauth_token_secret"]);
            //update_user_meta(get_current_user_id(), "_linkedin_request_oauth_token_secret", $result["oauth_token_secret"]);

            $return_url = self::URL."/uas/oauth/authorize?oauth_token=".$result["oauth_token"];

            return array(
                "url" => $return_url,
                "oauth_token" => $result["oauth_token"],
                "oauth_token_secret" => $result["oauth_token_secret"]
            );
        } else {
            // OAuth v2
            
            $params = array(
                'response_type'     => 'code', // Only Code is OK
                'client_id'         => $this->param->api_key,
                'scope'             => 'r_basicprofile r_emailaddress w_share',
                //'state'             => uniqid('', true), // optional for CSRF protection - long string
                'redirect_uri'      => $param["redirect_url"],
            );
 
            // Authentication request
            $url = 'https://www.linkedin.com/uas/oauth2/authorization?' . http_build_query($params);
            
            return array(
                "url" => $url
            );
        }

    }
    
    public function accessToken($param = array())
    {
        
        if( wpjb_conf("linkedin_api_version") == "v1" ) {
            // OAuth v1
            $linkedin_request_oauth_token_secret = $param["request_oauth_token_secret"];
            $result = null;
            $consumer = new OAuthConsumer($this->param->api_key, $this->param->secret_key, NULL);
            $token = new OAuthConsumer($param['oauth_token'], $linkedin_request_oauth_token_secret, 1);
            $signatureMethod = new OAuthSignatureMethod_HMAC_SHA1();

            $access_token_url = self::URL.'/uas/oauth/accessToken';

            $accObj = OAuthRequest::from_consumer_and_token($consumer, $token, "POST", $access_token_url);
            $accObj->set_parameter("oauth_verifier", $param['oauth_verifier']); # need the verifier too!
            $accObj->sign_request($signatureMethod, $consumer, $token);
            $toHeader = $accObj->to_header();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($toHeader));
            curl_setopt($ch, CURLOPT_URL, $access_token_url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            curl_setopt($ch, CURLOPT_POST, 1);

            $output = curl_exec($ch);
            curl_close($ch);

            parse_str($output, $result);

            return $result;
        
        } else {
            // OAuth v2
            
            $params = array(
                'grant_type'        => 'authorization_code',
                'client_id'         => $this->param->api_key,
                'client_secret'     => $this->param->secret_key,
                'code'              => $param['code'],
                'redirect_uri'      => wpjb_admin_url("config", "edit", null, array("form"=>"linkedin", "do"=>"get-access-token")),
            );
            
            $url = 'https://www.linkedin.com/oauth/v2/accessToken'; // . http_build_query($params);
            
            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, count($params));
            curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($params));

            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 

            //execute post
            $response = curl_exec($ch);
            $token = json_decode($response);
            
            return array( 
                "linkedin_oauth_token" => $token->access_token,
            );
        }
    }
    
    public function profile($fields = null)
    {
        if(is_array($fields)) {
            $fQuery = ":(".join(",", $fields).")";
        } elseif(is_string($fields)) {
            $fQuery = ":(".$fields.")";
        } else {
            $fQuery = "";
        }
        
        $endpoint = self::URL.'/'.self::VERSION.'/people/~'.$fQuery;
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
    }
    
    public function admin()
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
    }
    
    public static function share($object)
    {
        try {
            $post = self::_share($object);
            
            //$meta = $object->meta->facebook_share_id->getFirst();
            //$meta->value = $post["id"];
            //$meta->save();
            
        } catch(Exception $e) {
            // @todo: log error
        }
    }
    
    public static function shareTest($object) 
    {
        self::_share($object);
    }
    
    protected static function _share($object)
    {
        /*
        if(!is_admin()) {
            $pricing = new Wpjb_Model_Pricing($object->pricing_id);
            $features = $pricing->meta->features->value();
            if(!isset($features["linkedin_share"]) || $features["linkedin_share"] != "1") {
                return false;
            }
        }
        */
        
        if($object->meta->job_source->value()) {
            return false;
        }
        
        $linkedin = self::linkedin();
        $parameters = apply_filters("wpjb_linkedin_share_params", array(
            'comment' => wpjb_conf("linkedin_share_comment"),
            'title' => wpjb_conf("linkedin_share_title"),
            'description' => "",
            'url' => $object->url(),
            'image_url' => $object->getLogoURL(),
            'visibility' => "anyone"
        ));
        
        $parser = new Daq_Tpl_Parser();
        $parser->assign("job", $object);
        
        $parameters["comment"] = $parser->draw($parameters["comment"]);
        $parameters["title"] = $parser->draw($parameters["title"]);
        $parameters["description"] = $parser->draw($parameters["description"]);
        
        $share = new SimpleXMLElement("<share></share>");
        $share->addChild("comment");
        $share->comment = $parameters["comment"];
        
        $content = $share->addChild("content");
        if(!empty($parameters["title"])) {
            $content->addChild("title");
            $content->title = $parameters["title"];
        }
        
        if(!empty($parameters["description"])) {
            $content->addChild("description");
            $content->description = $parameters["description"];
        }
        
        $content->addChild("submitted-url");
        $content->{"submitted-url"} = $parameters["url"];
        
        if(!empty($parameters["image_url"])) {
            $content->addChild("submitted-image-url");
            $content->{"submitted-image-url"} = $parameters["image_url"];
        }
        
        $visibility = $share->addChild("visibility");
        $visibility->addChild("code");
        $visibility->code = $parameters["visibility"];
        
        if(is_numeric(wpjb_conf("linkedin_share_as"))) {
            $endpoint = self::URL.'/'.self::VERSION.'/companies/'.wpjb_conf("linkedin_share_as").'/shares';
        } else {
            $endpoint = self::URL.'/'.self::VERSION.'/people/~/shares';
        }
        
        $consumer = new OAuthConsumer($linkedin->param->api_key, $linkedin->param->secret_key, NULL);
        $signatureMethod = new OAuthSignatureMethod_HMAC_SHA1();
        $token = new OAuthConsumer($linkedin->param->oauth_token, $linkedin->param->oauth_token_secret, 1);

        $profileObj = OAuthRequest::from_consumer_and_token($consumer, $token, "POST", $endpoint, array());
        $profileObj->sign_request($signatureMethod, $consumer, $token);
        $toHeader = $profileObj->to_header();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($toHeader, "Content-type: application/xml"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $share->asXML());
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        $output = curl_exec($ch);
        curl_close($ch);
        
        $newpost = new SimpleXMLElement($output);
        
        if($newpost->status && (int)$newpost->status >= 400) {
            throw new Exception((string)$newpost->message);
        }
        
        return $newpost;
    }
    
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
        
        ?>

        <a href="#" data-job-id="<?php esc_attr_e($job->id) ?>" class="wpjb-button wpjb-linkedin-request-token" rel="nofollow"><?php _e("Apply From LinkedIn", "wpjobboard") ?></a>

        <?php
        
    }
    
    public static function footer()
    {
        ?>
        
        <script type="text/javascript">
        if (typeof ajaxurl === 'undefined') {
            ajaxurl = "<?php echo admin_url('admin-ajax.php') ?>";
        }     
        jQuery(function($) {
            $(".wpjb-linkedin-request-token").click(function(e) {
                e.preventDefault();
                
                $("#wpjb-linkedin-connect").toggleClass("show");
                $("#wpjb-linkedin-connect").show();
                
                var data = {id: $(this).data("job-id"), action: 'wpjb_linkedin_rtoken'};
                
                $.ajax({
                    type: "POST",
                    data: data,
                    url: ajaxurl,
                    dataType: "json",
                    success: function(response) {
                        location.href = response.url;
                        
                    }
                });
            });
        });  
        </script>

        <div id="wpjb-linkedin-connect" class="wpjb wpjb-overlay" style="display: none">
            <div>
                <h2 style="text-align: center; padding:45px 0px 15px 0px; font-size:25px">
                    <img style="vertical-align:bottom" src="<?php esc_attr_e(plugins_url()."/wpjobboard/public/images/linkedin-logo-30px.png") ?>" alt="" />
                    <?php _e("Connecting to LinkedIn ...", "wpjobboard") ?>
                </h2>
            </div>
        </div>
        
        <?php
    }

}

?>
