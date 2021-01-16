<?php

class Wpjb_Service_Facebook 
{
    public static function share($object)
    {
        try {
            
            if($object->meta->job_source->value()) {
                return false;
            }
            
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
        return self::_share($object);
    }
    
    protected static function _share($object)
    {
        $parameters = array(
            'access_token' => wpjb_conf("facebook_access_token"),
            'message' => wpjb_conf("facebook_share_message"),
            'link' => $object->url(),
            'name' => $object->job_title, 
            'caption' => wpjb_conf("facebook_share_caption")
        );
        
        if($object->job_description) {
            $parameters["description"] = strip_tags($object->job_description);
        }
        
        if($object->getLogoUrl()) {
            $parameters["picture"] = $object->getLogoUrl();
        }
        
        $parser = new Daq_Tpl_Parser();
        $parser->assign("job", $object);
        
        $parameters["message"] = $parser->draw($parameters["message"]);
        $parameters["name"] = $parser->draw($parameters["name"]);
        $parameters["caption"] = $parser->draw($parameters["caption"]);
        
        require_once Wpjb_List_Path::getPath("vendor") . '/Facebook/autoload.php';
        
        $fb = new Facebook\Facebook(array(
            'app_id' => wpjb_conf('facebook_app_id'), // Replace {app-id} with your app id
            'app_secret' => wpjb_conf('facebook_app_secret'),
            'default_graph_version' => 'v2.2',
            'persistent_data_handler' => new Wpjb_Service_FacebookData(),
        ));
        
        $newpost = $fb->post(
           '/me/feed',
            apply_filters("wpjb_facebook_share_params", $parameters, $object),
            wpjb_conf("facebook_access_token")
        );

        return $newpost;
    }


}

?>
