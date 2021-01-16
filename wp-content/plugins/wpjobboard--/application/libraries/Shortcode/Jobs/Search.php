<?php

class Wpjb_Shortcode_Jobs_Search extends Wpjb_Shortcode_Abstract 
{
    /**
     * Class constructor
     * 
     * Registers [wpjb_jobs_search] shortcode if not already registered
     * 
     * @since 5.0
     * @return void
     */
    public function __construct() {
        if(!shortcode_exists("wpjb_jobs_search")) {
            add_shortcode("wpjb_jobs_search", array($this, "main"));
        }
    }
    
    /**
     * Displays advanced jobs search form and results
     * 
     * This function is executed when [wpjb_jobs_search] shortcode is being called.
     * 
     * @link http://wpjobboard.net/kb/wpjb_jobs_search/ documentation
     * 
     * @param array $atts   Shortcode attributes
     * @return void
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

        $params = shortcode_atts(array(
            "form_only" => "0",
            "redirect_to" => wpjb_link_to("search"),
            "page_id" => get_the_ID()
        ), $atts);

        if( is_numeric( $params["redirect_to"] ) ) {
            $redirect_to = get_permalink( $params["redirect_to"] );
        } else {
            $redirect_to = $params["redirect_to"];
        }

        $request = Daq_Request::getInstance();
        $job = new Wpjb_Model_Job();
        $meta = array();

        foreach($job->meta as $k => $m) {
            if($request->get($k)) {
                $meta[$k] = $request->get($k);
            }
        }

        $date_from = $request->get("date_from");
        $date_to = $request->get("date_to");

        if($request->get("posted")>0) {
            $posted = intval($request->get("posted"))-1;
            $date_to = date("Y-m-d");
            $date_from = date("Y-m-d", wpjb_time("$date_to -$posted DAY"));
        }

        $paged = get_query_var("paged", 1);

        if($paged < 1) {
            $paged = 1;
        }

        $param = array(
            "query" => $request->get("query"),
            "category" => $request->get("category"),
            "type" => $request->get("type"),
            "page" => $request->get("page", $request->get("pg", $paged)),
            "count" => $request->get("count", wpjb_conf("front_jobs_per_page", 20)),
            "country" => $request->get("country"),
            "state" => $request->get("state"),
            "city" => $request->get("city"),
            "posted" => $request->get("posted"),
            "location" => $request->get("location"),
            "radius" => $request->get("radius"),
            "is_featured" => $request->get("is_featured"),
            "employer_id" => $request->get("employer_id"),
            "meta" => $meta,
            "sort" => $request->get("sort"),
            "order" => $request->get("order"),
            "date_from" => $date_from,
            "date_to" => $date_to
        );
        
        if ( get_option('permalink_structure') ) {
            $format = 'page/%#%/';
        } else {
            $format = '&paged=%#%';
        }

        $this->view = new stdClass();
        $this->view->atts = $atts;
        $this->view->pagination = true;
        $this->view->format = $format;
        $this->view->param = $param;
        $this->view->redirect_to = $redirect_to;

        $query = array();
        foreach($request->get() as $k => $v) {
            if(!empty($v) && !in_array($k, array("page", "job_board", "page_id"))) {
                $query[$k] = $v;
            }
        }

        $init = array();
        foreach($param as $k => $v) {
            if(!empty($v) && !in_array($k, array("page", "job_board", "page_id"))) {
                $init[$k] = $v;
            }
        }

        $this->view->query = $query;

        $this->view->search_bar = wpjb_conf("search_bar", "disabled");
        $this->view->search_init = $init;

        $form = new Wpjb_Form_AdvancedSearch();
        $form->isValid($request->get());
        $this->view->form = $form;

        if(empty($query) || $params["form_only"] == "1") {
            $this->view->show_results = false;
        } else {
            $this->view->show_results = true;

            $rQuery = wpjb_readable_query($request->get(), $form, new Wpjb_Form_AddJob());
            $readable = array();
            foreach($rQuery as $rk => $data) {

                $values = array();

                foreach($data["value"] as $vk => $vv) {
                    $aparam = array(
                        "href"=>"#", 
                        "class"=>"wpjb-glyphs wpjb-icon-cancel wpjb-refine-cancel",
                        "data-wpjb-field-remove" => $rk,
                        "data-wpjb-field-value" => $vv
                    );

                    $htmlA = new Daq_Helper_Html("a", $aparam, "");
                    $htmlA->forceLongClosing();

                    $values[] = esc_html($vv)."".$htmlA->render();
                }

                $htmlB = new Daq_Helper_Html("b", array(), esc_html($data["param"]));
                $htmlS = new Daq_Helper_Html("span", array(
                    "class" => "wpjb-tag",
                ), $htmlB->render() ." ". join(" ", $values));

                $readable[] = $htmlS->render();
            }
            if(empty($readable)) {
                $txt = __("No search params provided, showing all active jobs.", "wpjobboard");
                $readable[] = '<span class="wpjb-tag"><em>'.$txt.'</em></span>';
            }
            $this->view->readable = join(" ", $readable);
        }

        wp_enqueue_style("wpjb-css");
        wp_enqueue_script('wpjb-js');

        return $this->render("job-board", "search");
    }
    
    public function __set($name, $value) {
        if($name == "job" && $this->view) {
            $this->view->job = $value;
        }
    }
}
