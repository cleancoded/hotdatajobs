<?php

class Wpjb_Shortcode_Resumes_List extends Wpjb_Shortcode_Abstract
{
    /**
     * Class constructor
     * 
     * Registers [wpjb_resumes_list] shortcode if not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_resumes_list")) {
            add_shortcode("wpjb_resumes_list", array($this, "main"));
        }
    }
    
    /**
     * Displays login form
     * 
     * This function is executed when [wpjb_resumes_list] shortcode is being called.
     * 
     * @link https://wpjobboard.net/kb/shortcode_wpjb_resumes_list/ documentation
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
        
        $request = Daq_Request::getInstance();

        $page = $request->get("pg", get_query_var("paged", 1));
        if($page < 1) {
            $page = 1;
        }

        $params = shortcode_atts(array(
            "filter"            => "active",
            "query"             => null,
            "fullname"          => null,
            "category"          => $category,
            "type"              => $type,
            "country"           => null,
            "posted"            => null,
            "location"          => null,
            "is_featured"       => null,
            "meta"              => array(),
            "sort"              => null,
            "order"             => null,
            "sort_order"        => "t1.modified_at DESC, t1.id DESC",
            'page'              => $page,
            'count'             => 20,
            'search_bar'        => wpjb_conf("cv_search_bar", "disabled"),
            'page_id'           => get_the_ID(),
            'featured_level'    => null,
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
        
        $form = new Wpjb_Form_Resumes_ListSearch();
        $form->isValid($this->getRequest()->get());
        
        $can_browse = wpjr_can_browse();
        
        if(!empty($category)) {
            $permalink = wpjb_link_to("category", $model);
        } elseif(!empty($type)) {
            $permalink = wpjb_link_to("type", $model);
        } else {
            $permalink = get_the_permalink();
        }

        $this->view = new stdClass();
        $this->view->param = array_merge($params, $form->getValues());
        $this->view->url = $permalink;
        $this->view->query = "";
        $this->view->shortcode = true;
        $this->view->format = '?pg=%#%';
        $this->view->search_bar = $params["search_bar"];
        $this->view->search_init = $init;
        $this->view->page_id = $params["page_id"];
        $this->view->can_browse = $can_browse;
        $this->view->form = $form;


        if(!$can_browse) {

            $error = wpjr_can_browse_err();

            if($error) {
                $this->addError($error);
            }

            if(wpjb_conf("cv_privacy") == 1) {
                return $this->flash();
            }
        }

        wp_enqueue_style("wpjb-css");

        return $this->render("resumes", "index");
    }
    
    public function __set($name, $value) {
        if($name == "resume" && $this->view) {
            $this->view->resume = $value;
        }
    }
}
