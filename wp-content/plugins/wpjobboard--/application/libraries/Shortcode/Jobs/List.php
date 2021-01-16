<?php

class Wpjb_Shortcode_Jobs_List extends Wpjb_Shortcode_Abstract {
    
    /**
     * Class constructor
     * 
     * Registers [wpjb_jobs_list] shortcode is not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_jobs_list")) {
            add_shortcode("wpjb_jobs_list", array($this, "main"));
        }
    }
    
    /**
     * Displays jobs list
     * 
     * This function is executed when [wpjb_jobs_list] shortcode is being called.
     * 
     * @link http://wpjobboard.net/kb/shortcode_wpjb_jobs_list/ documentation
     * 
     * @param array $atts   Shortcode attributes
     * @return void
     */
    public function main($atts) {
        
        $instance = Wpjb_Project::getInstance();
        $request = Daq_Request::getInstance();
        
        if( !wpjb_candidate_have_access( get_the_ID() ) ) {
            
            if( wpjb_conf( "cv_members_have_access" ) == 1 ) {
                $msg = __("Only registred candidates have access to this page.", "wpjobboard");
            } elseif( wpjb_conf( "cv_members_have_access" ) == 2 ) {
                $msg = sprintf( __('Only premium candidates have access to this page. Get your premium account <a href="%s">here</a>', "wpjobboard"), get_the_permalink( wpjb_conf( "urls_link_cand_membership" ) ) );
            }
            
            $this->addError( $msg );
            return wpjb_flash();
        }

        $slug = get_query_var("wpjb-slug");
        $tag = get_query_var("wpjb-tag");
        
        $category = null;
        $type = null;

        if(!empty($slug)) {

            switch(get_query_var("wpjb-tag")) {
                case "category": $tag = Wpjb_Model_Tag::TYPE_CATEGORY; break;
                case "type": $tag = Wpjb_Model_Tag::TYPE_TYPE; break;
                default: $tag = null;
            }

            $query = new Daq_Db_Query;
            $query->from("Wpjb_Model_Tag t");
            $query->where("slug = ?", $slug);
            $query->where("type = ?", $tag);
            $query->limit(1);

            $result = $query->execute();
            $model = $result[0];

            switch($result[0]->type) {
                case Wpjb_Model_Tag::TYPE_CATEGORY: $category = $result[0]->id; break;
                case Wpjb_Model_Tag::TYPE_TYPE: $type = $result[0]->id; break;
            }
        }

        $page = $request->get("pg", get_query_var("paged", 1));
        if($page < 1) {
            $page = 1;
        }

        if(is_home() || is_front_page()) {
            $page = get_query_var("page", $page);
        }

        $params = shortcode_atts(array(
            "filter"        => "active",
            "query"         => null,
            "category"      => $category,
            "type"          => $type,
            "country"       => null,
            "state"         => null,
            "city"          => null,
            "posted"        => null,
            "location"      => null,
            "is_featured"   => null,
            "employer_id"   => null,
            "meta"          => array(),
            "hide_filled"   => wpjb_conf("front_hide_filled", false),
            "sort"          => null,
            "order"         => null,
            "sort_order"    => "t1.is_featured DESC, t1.job_created_at DESC, t1.id DESC",
            "search_bar"    => wpjb_conf("search_bar", "disabled"),
            "pagination"    => true,
            "standalone"    => false,
            "id__not_in"    => null,
            'page'          => $page,
            'count'         => wpjb_conf("front_jobs_per_page", 20),
            'page_id'       => get_the_ID(),
            'show_results'  => 1,
            'redirect_to'   => '',
            'backfill'      => null,
        ), $atts);

        if( is_string( $params["type"] ) ) {
            $params["type"] = array_map("trim", explode(",", $params["type"]));
        }

        foreach((array)$atts as $k=>$v) {
            if(stripos($k, "meta__") === 0) {
                $params["meta"][substr($k, 6)] = $v;
            }
        }

        $plist = array("query", "location", "country", "state", "city", "type", "category");
        foreach($plist as $p) {
            if($request->get($p)) {
                $params[$p] = $request->get($p);
            }
        }

        $init = array();
        foreach(array_keys((array)$atts) as $key) {
            if(isset($params[$key]) && !in_array($key, array("search_bar"))) {
                $init[$key] = $params[$key];
            }
        }

        if(!empty($category)) {
            $permalink = wpjb_link_to("category", $model);
        } elseif(!empty($type)) {
            $permalink = wpjb_link_to("type", $model);
        } else {
            $permalink = get_the_permalink();
        }
        
        if( is_numeric( $params['redirect_to'] ) ) {
            $action = get_permalink( $params['redirect_to'] );
        } else {
            $action = $params['redirect_to'];
        }

        $form = new Wpjb_Form_Frontend_ListSearch();
        $form->isValid($this->getRequest()->get());
        $form->getElement("show_results")->setValue(1);
        
        if($request->get('show_results', 0) == 1) {
            $params['show_results'] = 1;
        }
        
        $this->view = new stdClass();
        $this->view->form = $form;
        $this->view->atts = $atts;
        $this->view->param = $params;
        $this->view->pagination = $params["pagination"];
        $this->view->url = $permalink;
        $this->view->query = "";
        $this->view->shortcode = true;
        $this->view->search_bar = $params["search_bar"];
        $this->view->show_results = $params["show_results"];
        $this->view->search_init = $init;
        $this->view->page_id = $params["page_id"];
        $this->view->action = $action;
        if ( get_option('permalink_structure') ) {
            $this->view->format = 'page/%#%/';
        } else {
            $this->view->format = '&paged=%#%';
        }

        wp_enqueue_style("wpjb-css");
        wp_enqueue_script('wpjb-js');

        return $this->render("job-board", "index");
    }
    
    public function __set($name, $value) {
        if($name == "job" && $this->view) {
            $this->view->job = $value;
        }
    }
}
