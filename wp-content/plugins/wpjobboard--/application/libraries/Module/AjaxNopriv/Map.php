<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Main
 *
 * @author greg
 */
class Wpjb_Module_AjaxNopriv_Map
{
    
    public static function dataAction()
    {
        $request = Daq_Request::getInstance();
        $olist = $request->post("objects");
        
        if(empty($olist)) {
            exit -3;
        }

        $objects = array_map("trim", explode(",", $olist));
        $data = array();
        
        if(in_array("jobs", $objects)) {
            $data += self::_jsonJobs();
        }
        
        if(in_array("resumes", $objects)) {
            $data += self::_jsonResumes();
        }
        
        if(in_array("companies", $objects)) {
            $data += self::_jsonCompanies();
        }
        
        echo json_encode($data);
        exit;
    }
    
    public static function detailsAction()
    {
        $request = Daq_Request::getInstance();
        
        switch($request->post("object")) {
            case "job": return self::_htmlJob(); break;
            case "resume": return self::_htmlResume(); break;
            case "company": return self::_htmlCompany(); break;
            default: echo "-1";  exit; break;
        }
        
        
    }
    
    protected static function _jsonJobs() 
    {
        $request = Daq_Request::getInstance();
        $post = $request->post();
        $post["ids_only"] = true;
        $post["page"] = 1;
        $post["count"] = apply_filters("wpjb_map_max_items", 1000);
        
        $list = apply_filters("wpjb_filter_jobs", wpjb_find_jobs($post), "map");
        $json = array();
        
        foreach($list->job as $id) {
            $job = new Wpjb_Model_Job($id);
            $json[] = array(
                "type" => "Feature",
                "geometry" => array(
                    "type" => "Point",
                    "coordinates" => array(
                        $job->meta->geo_longitude->value(), 
                        $job->meta->geo_latitude->value()
                    ) // end coordinates
                ),
                "properties" => array(
                    "id" => $job->id,
                    "object" => "job",
                    "title" => $job->job_title
                ) // end properties
            ); // end $json[]
            unset($job);
        }
        
        return $json;
    }
    
    protected static function _jsonResumes() 
    {
        $request = Daq_Request::getInstance();
        $post = $request->post();
        $post["ids_only"] = true;
        $post["page"] = 1;
        $post["count"] = apply_filters("wpjb_map_max_items", 1000);
        
        if(wpjb_conf("cv_privacy") == 1 && !wpjr_can_browse()) {
            $list = new stdClass();
            $list->resume = array();
        } else {
            $list = wpjb_find_resumes($post);
        }
        $json = array();
        
        foreach($list->resume as $id) {
            $resume = new Wpjb_Model_Resume($id);
            $json[] = array(
                "type" => "Feature",
                "geometry" => array(
                    "type" => "Point",
                    "coordinates" => array(
                        $resume->meta->geo_longitude->value(), 
                        $resume->meta->geo_latitude->value()
                    ) // end coordinates
                ),
                "properties" => array(
                    "id" => $resume->id,
                    "object" => "resume",
                    "title" => $resume->headline
                ) // end properties
            ); // end $json[]
        }
        
        return $json;
    }
    
    protected static function _jsonCompanies() 
    {
        $request = Daq_Request::getInstance();
        $post = $request->post();
        $post["ids_only"] = true;
        $post["page"] = 1;
        $post["count"] = apply_filters("wpjb_map_max_items", 1000);
        
        $list = Wpjb_Model_Company::search($post);
        $json = array();
        
        foreach($list->company as $id) {
            $company = new Wpjb_Model_Company($id);
            $json[] = array(
                "type" => "Feature",
                "geometry" => array(
                    "type" => "Point",
                    "coordinates" => array(
                        $company->meta->geo_longitude->value(), 
                        $company->meta->geo_latitude->value()
                    ) // end coordinates
                ),
                "properties" => array(
                    "id" => $company->id,
                    "object" => "company",
                    "title" => $company->company_name
                ) // end properties
            ); // end $json[]
        }
        
        return $json;
    }
    
    protected static function _htmlJob()
    {
        $request = Daq_Request::getInstance();
        $job = new Wpjb_Model_Job($request->post("id"));
        
        $index = absint($request->post("index", 0))+1;
        $total = absint($request->post("total"));
        
        $prev = "visible";
        $next = "visible";
        
        if($index == 1) {
            $prev = "hidden";
        }
        if($index >= $total) {
            $next = "hidden";
        }
        
        if($job->exists() == false) {
            exit -2;
        }
        
        ?>
        <span class='wpjb-infobox-title'><?php esc_html_e($job->job_title) ?></span>
        <p><?php esc_html_e($job->company_name) ?></p>
        <p><a href="<?php esc_attr_e($job->url()) ?>"><?php _e("View Job Details", "wpjobboard")?> <span class="wpjb-glyphs wpjb-icon-right-open"></span></a></p>
        <div class="wpjb-infobox-footer" style="background-color:<?php echo "#".$job->getTag()->type[0]->meta->color ?>">
            <span class="footer-icon wpjb-glyphs wpjb-icon-tags"></span>
            <small><?php esc_html_e($job->tag->type[0]->title) ?></small>
            
            <?php if($total > 1): ?>
            <span class="" style="float:right">
                <a href="#" class="wpjb-infobox-prev"><span class="footer-icon wpjb-glyphs wpjb-icon-left-open" style="padding:0px; visibility: <?php echo $prev ?>"></span></a>
                <small style="margin:0px"><?php echo $index ?> / <?php echo $total ?></small>
                <a href="#" class="wpjb-infobox-next"><span class="footer-icon wpjb-glyphs wpjb-icon-right-open" style="padding:0px; visibility: <?php echo $next ?>"></span></a>
            </span>
            <?php endif; ?>
        </div>
        
        <?php
        exit;
    }
    
    protected static function _htmlResume()
    {
        $request = Daq_Request::getInstance();
        $resume = new Wpjb_Model_Resume($request->post("id"));
        
        $index = absint($request->post("index", 0))+1;
        $total = absint($request->post("total"));
        
        $prev = "visible";
        $next = "visible";
        
        if($index == 1) {
            $prev = "hidden";
        }
        if($index >= $total) {
            $next = "hidden";
        }
        
        if($resume->exists() == false) {
            exit -2;
        }
        
        ?>
        <span class='wpjb-infobox-title'><?php esc_html_e(apply_filters("wpjb_candidate_name", $resume->getSearch(true)->fullname, $resume->id)) ?></span>
        <p><?php esc_html_e($resume->headline) ?></p>
        <p><a href="<?php esc_attr_e($resume->url()) ?>"><?php _e("View Resume Details", "wpjobboard") ?> <span class="wpjb-glyphs wpjb-icon-right-open"></span></a></p>
        <div class="wpjb-infobox-footer">
            <span class="footer-icon wpjb-glyphs wpjb-icon-tags"></span>
            <small><?php esc_html_e($resume->tag->category[0]->title) ?></small>
            
            <?php if($total > 1): ?>
            <span class="" style="float:right">
                <a href="#" class="wpjb-infobox-prev"><span class="footer-icon wpjb-glyphs wpjb-icon-left-open" style="padding:0px; visibility: <?php echo $prev ?>"></span></a>
                <small style="margin:0px"><?php echo $index ?> / <?php echo $total ?></small>
                <a href="#" class="wpjb-infobox-next"><span class="footer-icon wpjb-glyphs wpjb-icon-right-open" style="padding:0px; visibility: <?php echo $next ?>"></span></a>
            </span>
            <?php endif; ?>
        </div>
        
        <?php
        exit;
    }
    
    protected static function _htmlCompany()
    {
        $request = Daq_Request::getInstance();
        $company = new Wpjb_Model_Company($request->post("id"));
        
        $index = absint($request->post("index", 0))+1;
        $total = absint($request->post("total"));
        
        $prev = "visible";
        $next = "visible";
        
        if($index == 1) {
            $prev = "hidden";
        }
        if($index >= $total) {
            $next = "hidden";
        }
        
        if($company->exists() == false) {
            exit -2;
        }
        
        ?>
        <span class='wpjb-infobox-title'><?php esc_html_e($company->company_name) ?></span>
        <p><?php esc_html_e($company->locationToString()) ?></p>
        <p><a href="<?php esc_attr_e($company->url()) ?>"><?php _e("View Company Details", "wpjobboard") ?> <span class="wpjb-glyphs wpjb-icon-right-open"></span></a></p>
        <div class="wpjb-infobox-footer">
            <span class="footer-icon wpjb-glyphs wpjb-icon-globe"></span>
            <small><?php esc_html_e(sprintf(__("Posted Jobs %d", "wpjobboard"), $company->jobs_posted)) ?></small>
            <?php if($total > 1): ?>
            <span class="" style="float:right">
                <a href="#" class="wpjb-infobox-prev"><span class="footer-icon wpjb-glyphs wpjb-icon-left-open" style="padding:0px; visibility: <?php echo $prev ?>"></span></a>
                <small style="margin:0px"><?php echo $index ?> / <?php echo $total ?></small>
                <a href="#" class="wpjb-infobox-next"><span class="footer-icon wpjb-glyphs wpjb-icon-right-open" style="padding:0px; visibility: <?php echo $next ?>"></span></a>
            </span>
            <?php endif; ?>
        </div>
        
        <?php
        exit;
    }
}