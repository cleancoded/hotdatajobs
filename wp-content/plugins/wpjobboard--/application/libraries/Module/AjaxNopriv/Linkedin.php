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
class Wpjb_Module_AjaxNopriv_Linkedin 
{
    
    public function rtokenAction()
    {
        $request = Daq_Request::getInstance();
        $job = new Wpjb_Model_Job($request->post("id"));
        
        $linkedin_api_key = wpjb_conf("linkedin_api_key");
        $linkedin_secret_key = wpjb_conf("linkedin_secret_key");
        $wpjb_oauth_token = "";
        $wpjb_oauth_token_secret = "";
        $tryauth = 0;
        $url = "";
        
        if(isset($_COOKIE["wpjb_linkedin_oauth_token"]) && !empty($_COOKIE["wpjb_linkedin_oauth_token"])) {
            $wpjb_oauth_token = $_COOKIE["wpjb_linkedin_oauth_token"];
            $tryauth++;
        }
        
        if(isset($_COOKIE["wpjb_linkedin_oauth_token_secret"]) && !empty($_COOKIE["wpjb_linkedin_oauth_token_secret"])) {
            $wpjb_oauth_token_secret = $_COOKIE["wpjb_linkedin_oauth_token_secret"];
            $tryauth++;
        }
        
        if($tryauth == 2) {
            $linkedin = Wpjb_Service_Linkedin::linkedin(array(
                "api_key" => $linkedin_api_key,
                "secret_key" => $linkedin_secret_key,
                "oauth_token" => $wpjb_oauth_token,
                "oauth_token_secret" => $wpjb_oauth_token_secret
            ));
            
            $profile = $linkedin->profile();
            
            if($profile->status && in_array((string)$profile->status, array("400", "401", "403", "500"))) {
                $tryauth = 0;
            } else {
                $url = wpjb_link_to("job", $job, array("linkedin_apply"=>1));
            }
        } 
        
        
         if($tryauth != 2) {   
             
            $linkedin = Wpjb_Service_Linkedin::linkedin(array(
                "api_key" => $linkedin_api_key,
                "secret_key" => $linkedin_secret_key,
                "oauth_token" => "",
                "oauth_token_secret" => ""
            ));

            if(wpjb_conf("linkedin_api_scope", "basic") == "basic") {
                $scope = "r_basicprofile,r_emailaddress,rw_company_admin,w_share";
            } else {
                $scope = "r_fullprofile,r_emailaddress,rw_company_admin,w_share,r_contactinfo";
            }
            
            $requestToken = $linkedin->requestToken(array("redirect_url"=>$job->url(), "scope"=>$scope));
            $url = $requestToken["url"];
            
            setcookie('wpjb_linkedin_ots', $requestToken["oauth_token_secret"], time()+3600*24*7, COOKIEPATH, COOKIE_DOMAIN, false);
        }

        
        echo json_encode(array("url"=>$url));
        exit;
    }
    

}

