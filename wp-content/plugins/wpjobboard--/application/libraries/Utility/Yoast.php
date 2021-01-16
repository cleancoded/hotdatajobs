<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Yoast
 *
 * @author Grzegorz
 */
class Wpjb_Utility_Yoast {

    public static function connect() 
    {
        $self = new self();
        
        add_filter("wpseo_sitemap_index", array($self, "sitemapIndex"));
        add_action("init", array($self, "sitemaps"));
        add_action("wp", array($self, "wp"));
        
        add_action("wpseo_do_sitemap_wpjb-jobs", array($self, "sitemap_jobs"));
        add_action("wpseo_do_sitemap_wpjb-resumes", array($self, "sitemap_resumes"));
    }
    
    public function sitemaps()
    {
        global $wpseo_sitemaps;
        
        if( !$wpseo_sitemaps ) {
            return;
        }

	$wpseo_sitemaps->register_sitemap("wpjb-jobs", array($this, "sitemap_jobs"));
	$wpseo_sitemaps->register_sitemap("wpjb-resumes", array($this, "sitemap_resumes"));
    }
    
    public function wp() 
    {
        if(!is_wpjb() && !is_wpjr()) {
            return $this;
        }

        add_filter("wpseo_title", array($this, "title"));  
        add_filter("get_the_date", array($this, "date"), 10, 2);
        add_filter("get_the_modified_date", array($this, "modifiedDate"), 10, 2);
        
        add_action("wpseo_opengraph", array($this, "image"));
    }
    
    public function sitemapIndex($xml)
    {
        $base = $GLOBALS['wp_rewrite']->using_index_permalinks() ? 'index.php/' : '';
        $count = "";
        
        $xml .= '<sitemap>' . "\n";
        $xml .= '<loc>' . home_url( $base . 'wpjb-jobs-sitemap' . $count . '.xml' ) . '</loc>' . "\n";
        $xml .= '<lastmod>' . htmlspecialchars( date("c") ) . '</lastmod>' . "\n";
        $xml .= '</sitemap>' . "\n";
        
        $xml .= '<sitemap>' . "\n";
        $xml .= '<loc>' . home_url( $base . 'wpjb-resumes-sitemap' . $count . '.xml' ) . '</loc>' . "\n";
        $xml .= '<lastmod>' . htmlspecialchars( date("c") ) . '</lastmod>' . "\n";
        $xml .= '</sitemap>' . "\n";
 
	return $xml;
    }
    
    public function title()
    {
        return Wpjb_Project::getInstance()->title;
    }
    
    public function image($image) 
    {
        $ph = Wpjb_Project::getInstance()->placeHolder;
        
        if(wpjb_is_routed_to("index.single") && $ph->job && $ph->job->getLogoUrl()) {
            echo '<meta property="og:image" content="' . esc_attr( $ph->job->getLogoUrl() ) . '" />' . "\n";
        } 
    }
    
    public function date($the_date, $d) 
    {
        if($d == "c" && wpjb_is_routed_to("index.single")) {
            $the_date = Wpjb_Project::getInstance()->placeHolder->job->job_created_at;
            $the_date = mysql2date($d, $the_date);
        } elseif($d == "c" && wpjb_is_routed_to("index.view", "resumes")) {
            $the_date = Wpjb_Project::getInstance()->placeHolder->resume->created_at;
            $the_date = mysql2date($d, $the_date);
        }
        
        return $the_date;
    }
    
    public function modifiedDate($the_date, $d)
    {
        if($d == "c" && wpjb_is_routed_to("index.single")) {
            $the_date = Wpjb_Project::getInstance()->placeHolder->job->job_modified_at;
            $the_date = mysql2date($d, $the_date);
        } elseif($d == "c" && wpjb_is_routed_to("index.view", "resumes")) {
            $the_date = Wpjb_Project::getInstance()->placeHolder->resume->modified_at;
            $the_date = mysql2date($d, $the_date);
        }
        
        return $the_date;
        
    }
    
    public function sitemap_jobs()
    {
	global $wpseo_sitemaps;

        $param = array(
            "filter" => "active",
            "ids_only" => true
        );
        
	$data = '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
	
        foreach(wpjb_find_jobs($param)->job as $id) {
            
            $job = new Wpjb_Model_Job($id);
            
            $data.= "<url>";
            $data.= "<loc>".esc_html($job->url())."</loc>";
            $data.= "<lastmod>".date("c", $job->time->job_modified_at)."</lastmod>";
            $data.= "<changefreq>weekly</changefreq>";
            $data.= "<priority>1</priority>";
            $data.= "</url>";
            
            unset($job);
        }	

	$data.= '</urlset>';
 
	$wpseo_sitemaps->set_sitemap($data);
    }
    
    public function sitemap_resumes()
    {
        global $wpseo_sitemaps;
        
        $param = array(
            "filter" => "active",
            "ids_only" => true
        );
        
        $data = '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach(wpjb_find_resumes($param)->resume as $id) {
            
            $resume = new Wpjb_Model_Resume($id);
            
            $data.= "<url>";
            $data.= "<loc>".esc_html($resume->url())."</loc>";
            $data.= "<lastmod>".date("c", $resume->time->modified_at)."</lastmod>";
            $data.= "<changefreq>weekly</changefreq>";
            $data.= "<priority>1</priority>";
            $data.= "</url>";
            
            unset($resume);
        }	
        
        $data.= '</urlset>';
        
        $wpseo_sitemaps->set_sitemap($data);
    }
}

?>
