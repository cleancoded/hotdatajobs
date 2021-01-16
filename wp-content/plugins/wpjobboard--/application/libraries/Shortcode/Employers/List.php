<?php

class Wpjb_Shortcode_Employers_List extends Wpjb_Shortcode_Abstract
{
    /**
     * Class constructor
     * 
     * Registers [wpjb_employers_list] shortcode if not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_employers_list")) {
            add_shortcode("wpjb_employers_list", array($this, "main"));
        }
    }
    
    /**
     * Displays login form
     * 
     * This function is executed when [wpjb_employers_list] shortcode is being called.
     * 
     * @link https://wpjobboard.net/kb/wpjb_employers_list/ documentation
     * 
     * @param array     $atts   Shortcode attributes
     * @return string           Shortcode HTML
     */
    public function main($atts = array()) {
        
        if( !wpjb_candidate_have_access( get_the_ID() ) ) {
            
            if( wpjb_conf( "cv_members_have_access" ) == 1 ) {
                $msg = __("Only registered candidates have access to this page.", "wpjobboard");
            } elseif( wpjb_conf( "cv_members_have_access" ) == 2 ) {
                $msg = sprintf( __('Only premium candidates have access to this page. Get your premium account <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_cand_membership" ) ) );
            }
            
            $this->addError( $msg );
            return wpjb_flash();
        }
        
        $request = Daq_Request::getInstance();

        $page = $request->get("pg", get_query_var("paged", 1));
        if($page < 1) {
            $page = 1;
        }

        $params = shortcode_atts(array(
            "filter" => "all",
            "query" => null,
            "location" => null,
            "meta" => array(),
            "sort" => null,
            "order" => null,
            "search_bar" => wpjb_conf("search_bar", "disabled"),
            "sort_order" => "t1.company_name ASC",
            "pagination" => true,
            'page' => $page,
            'count' => 20,
            'page_id' => get_the_ID()
        ), $atts);

        foreach((array)$atts as $k=>$v) {
            if(stripos($k, "meta__") === 0) {
                $params["meta"][substr($k, 6)] = $v;
            }
        }

        $init = array();
        foreach(array_keys((array)$atts) as $key) {
            if(isset($params[$key]) && !in_array($key, array("search_bar"))) {
                $init[$key] = $params[$key];
            }
        }

        if($request->get("query")) {
            $params["query"] = $request->get("query");
        }
        if($request->get("location")) {
            $params["location"] = $request->get("location");
        }

        if ( get_option('permalink_structure') ) {
            $format = 'page/%#%/';
        } else {
            $format = '&paged=%#%';
        }

        $this->view = new stdClass();
        $this->view->param = $params;
        $this->view->url = get_the_permalink();
        $this->view->query = "";
        $this->view->shortcode = true;
        $this->view->format = $format;
        $this->view->page_id = $params["page_id"];
        $this->view->search_bar = $params["search_bar"];
        $this->view->search_init = $init;
        $this->view->pagination = $params["pagination"];
        $this->view->form = new Wpjb_Form_Frontend_CompanySearch();
        
        wp_enqueue_style("wpjb-css");

        return $this->render("job-board", "employers");
    }
    
    public function __set($name, $value) {
        if($name == "company" && $this->view) {
            $this->view->company = $value;
        }
    }
}
