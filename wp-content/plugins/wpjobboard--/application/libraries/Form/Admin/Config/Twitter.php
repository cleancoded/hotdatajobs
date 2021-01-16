<?php
/**
 * Description of Frontend
 *
 * @author greg
 * @package
 */

class Wpjb_Form_Admin_Config_Twitter extends Daq_Form_Abstract
{
    public $name = null;

    public function init()
    {
        $this->name = __("Twitter", "wpjobboard");
        $instance = Wpjb_Project::getInstance();
        
        $this->addGroup( "default", __( "Twitter API", "wpjobboard" ) );
        
        $e = $this->create("api_twitter_consumer_key");
        $e->setValue($instance->getConfig("api_twitter_consumer_key"));
        $e->setLabel(__("Consumer Key", "wpjobboard"));
        $this->addElement($e, "default");

        $e = $this->create("api_twitter_consumer_secret");
        $e->setValue($instance->getConfig("api_twitter_consumer_secret"));
        $e->setLabel(__("Consumer Secret", "wpjobboard"));
        $this->addElement($e, "default");

        $e = $this->create("api_twitter_oauth_token");
        $e->setValue($instance->getConfig("api_twitter_oauth_token"));
        $e->setLabel(__("Access Token", "wpjobboard"));
        $this->addElement($e, "default");

        $e = $this->create("api_twitter_oauth_secret");
        $e->setValue($instance->getConfig("api_twitter_oauth_secret"));
        $e->setLabel(__("Access Token Secret", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("posting_tweet", "checkbox");
        $e->setValue($instance->getConfig("posting_tweet"));
        $e->addOption(1, 1, __("Post new jobs to Twitter", "wpjobboard"));
        $this->addElement($e, "default");

        $e = $this->create("posting_tweet_template");
        $e->setValue($instance->getConfig("posting_tweet_template"));
        $e->setLabel(__("Tweet Template", "wpjobboard"));
        $this->addElement($e, "default");
        
        $e = $this->create("_twitter_placeholder");
        $e->setRenderer("wpjb_admin_variable_renderer");
        $e->setHint(__("You can use above variables in Tweet Template", "wpjobboard"));
        $e->setValue(array("job"));
        $this->addElement($e, "default");

        apply_filters("wpja_form_init_config_twitter", $this);

    }
}

?>