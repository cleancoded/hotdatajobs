<?php
/**
 * Description of Twitter
 *
 * @author greg
 * @package 
 */

class Wpjb_Service_Twitter
{
    public static function tweet(Wpjb_Model_Job $job)
    {
        try {
            $post = self::_tweet($job);
            
            //$meta = $job->meta->twitter_tweet_id->getFirst();
            //$meta->value = $post->id;
            //$meta->save();
            
        } catch(Exception $e) {
            // @todo: log error
        }
    }
    
    public static function tweetTest(Wpjb_Model_Job $job) 
    {
        self::_tweet($job);
    }
    
    protected static function _tweet(Wpjb_Model_Job $job)
    {
        if($job->meta->job_source->value()) {
            return false;
        }
        
        if(!function_exists("curl_init")) {
            throw new Exception("cURL extension on your server is disabled. Please contact you hosting support and ask to enable cURL, without it posting to Twitter will not work.");
        }

        $exchange = array(
            "{url}" => $job->url(),
            "{title}" => $job->job_title
        );
        
        if(isset($job->tag->category)) {
            $exchange["{category}"] = $job->tag->category[0]->title;
        }
        if(isset($job->tag->type)) {
            $exchange["{type}"] = $job->tag->type[0]->title;
        }

        $msg = str_replace(
            array_keys($exchange),
            array_values($exchange),
            Wpjb_Project::getInstance()->conf("posting_tweet_template")
        );
        
        $parser = new Daq_Tpl_Parser();
        $parser->assign("job", $job);
        
        $msg = $parser->draw($msg);
        $msg = apply_filters("wpjb_tweet_template", $msg, $job);
        
        
        self::_send($msg);
    }

    protected static function _shortUrlLength()
    {
        return 35;
    }
    
    protected static function _send($msg)
    {
        $path = Wpjb_List_Path::getPath("vendor");
        if(!class_exists("OAuthException")) {
            require_once $path."/TwitterOAuth/OAuth.php";
        }
        if(!class_exists("TwitterOAuth")) {
            require_once $path."/TwitterOAuth/TwitterOAuth.php";
        }

        $ck = Wpjb_Project::getInstance()->conf("api_twitter_consumer_key");
        $cs = Wpjb_Project::getInstance()->conf("api_twitter_consumer_secret");
        $ot = Wpjb_Project::getInstance()->conf("api_twitter_oauth_token");
        $os = Wpjb_Project::getInstance()->conf("api_twitter_oauth_secret");

        $connection = new TwitterOAuth($ck, $cs, $ot, $os);
        $connection->host = "https://api.twitter.com/1.1/";
        $content = $connection->get('account/verify_credentials');
        
        if(isset($content->errors) && !empty($content->errors)) {
            throw new Exception($content->errors[0]->message);
        }

        $content = $connection->post('statuses/update', array('status' => $msg));
        
        if(isset($content->errors) && !empty($content->errors)) {
            throw new Exception($content->errors[0]->message);
        }
        
        return $content;
    }
}

?>