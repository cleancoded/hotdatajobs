<?php

class Wpjb_Shortcode_Login extends Wpjb_Shortcode_Abstract
{
    /**
     * Class constructor
     * 
     * Registers [wpjb_login] shortcode if not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_login")) {
            add_shortcode("wpjb_login", array($this, "main"));
        }
    }
    
    /**
     * Displays login form
     * 
     * This function is executed when [wpjb_login] shortcode is being called.
     * 
     * @link https://wpjobboard.net/kb/wpjb_login/ documentation
     * 
     * @param array     $atts   Shortcode attributes
     * @return string           Shortcode HTML
     */
    public function main($atts = array()) {
        $this->view = new stdClass();

        if(get_current_user_id()) {
            $m = __('You are already logged in. Go to <a href="%1$s">Client Panel</a> or <a href="%2$s">Logout</a>.', "wpjobboard");

            if(current_user_can("manage_jobs")) {
                $url1 = wpjb_link_to("employer_home");
                $url2 = wpjb_link_to("employer_logout");
            } else {
                $url1 = wpjr_link_to("myresume_home");
                $url2 = wpjr_link_to("logout");
            }

            $flash = new Wpjb_Utility_Session();
            $flash->addInfo(sprintf($m, esc_attr($url1), esc_attr($url2)));
            $flash->save();

            ob_start();
            wpjb_flash();
            return ob_get_clean();
        }

        $params = shortcode_atts(array(
            "links" => array()
        ), $atts);

        if(!is_array($params["links"])) {
            $params["links"] = array_map("trim", explode(",", $params["links"]));
        }

        $form = new Wpjb_Form_Login();
        $form->getElement("redirect_to")->setValue("");

        $buttons = array();

        if(in_array("employer_reg", $params["links"])) {
            $buttons[] = array(
                "tag" => "a", 
                "href" => wpjb_link_to("employer_new"), 
                "html" => __("Employer Registration", "wpjobboard")
            );
        }

        if(in_array("candidate_reg", $params["links"])) {
            $buttons[] = array(
                "tag" => "a", 
                "href" => wpjr_link_to("register"), 
                "html" => __("Candidate Registration", "wpjobboard")
            );
        } 

        $this->view->action = "";
        $this->view->form = $form;
        $this->view->submit = __("Login", "wpjobboard");
        $this->view->buttons = $buttons;
        $this->view = apply_filters("wpjb_shortcode_login", $this->view);

        return $this->render("default", "form");
    }
}
