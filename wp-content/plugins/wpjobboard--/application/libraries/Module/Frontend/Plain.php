<?php
/**
 * Description of Plain
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Frontend_Plain extends Wpjb_Controller_Frontend
{
    public function apiAction()
    {
        if(!$this->hasParam("engine")) {
            return false;
        }
        
        $engine = $this->getRequest()->getParam("engine");

        switch($engine) {
            case "indeed": $url = wpjb_api_url("xml/indeed"); break;
            case "trovit": $url = wpjb_api_url("xml/indeed"); break;
            case "simply-hired": $url = wpjb_api_url("xml/indeed"); break;
            case "juju": $url = wpjb_api_url("xml/indeed"); break;
            case "xing": $url = wpjb_api_url("xml/xing"); break;
        }

        wp_redirect($url);
        exit;

    }
    
    public function rssAction()
    {
        wp_redirect(wpjb_api_url("xml/rss", $_GET));
        exit;
    }
    
    public function feedAction()
    {
        $category = $this->getRequest()->get("slug", "all");
        wp_redirect(wpjb_api_url("xml/rss", array("category"=>$category)));
        exit;
    }


}

?>
