<?php

class Wpjb_Utility_Cpt 
{
    /**
     * Class constructor
     * 
     * - registers custom post types
     * - in wp-admin registers admin_print_scripts-post.php, admin_print_styles-post.php
     * - in frontend registers template_redirect action
     * 
     * @return void
     */
    public function __construct() {
        
        add_action("init", array($this, "init"));
        
        if(is_admin()) {
            add_action("admin_print_scripts-post.php", array($this, "cptPrintScripts"), 11 );
            add_action("admin_print_styles-post.php", array($this, "cptPrintStyles"), 11 );
        } else {
            add_action("template_redirect", array($this, "disableArchive"));
        }
    }
    
    /**
     * Initiates Custom Post Types
     * 
     * Register job, company and resume post types using register_post_type function
     * 
     * The custom post types can be customized on the fly using wpjb_cpt_init filter.
     * 
     * @see register_post_type()
     * @see wpjb_cpt_init filter
     * 
     * @return void
     */
    public function init()
    {

        $args = array(
            'labels'        => array(
                "name" => __("Job", "wpjobboard"),
                "edit_item" => __("Edit Job", "wpjobboard"),
                "view_item" => __("View Job", "wpjobboard")
            ),
            'description'   => '',
            'public'        => true,
            'show_ui'       => true,
            'show_in_menu'  => false,
            'supports'      => array('title', 'comments'),
            'taxonomies'    => array( ),
            'has_archive'   => true,
            'rewrite'       => array(
                "slug"  => "job"
            )
        );
        register_post_type( 'job', apply_filters("wpjb_cpt_init", $args, "job") ); 
        
        $args = array(
            'labels'        => array(
                "name" => __("Candidate", "wpjobboard"),
                "edit_item" => __("Edit Candidate", "wpjobboard"),
                "view_item" => __("View Candidate", "wpjobboard")
            ),
            'description'   => '',
            'public'        => true,
            'show_ui'       => true,
            'show_in_menu'  => false,
            'supports'      => array('title', 'comments'),
            'taxonomies'    => array( ),
            'has_archive'   => true,
            'rewrite'       => array(
                'slug' => 'resume'
            )
        );
        register_post_type( 'resume', apply_filters("wpjb_cpt_init", $args, "resume") ); 
        
        $args = array(
            'labels'        => array(
                "name" => __("Employer", "wpjobboard"),
                "edit_item" => __("Edit Employer", "wpjobboard"),
                "view_item" => __("View Employer", "wpjobboard")
            ),
            'description'   => '',
            'public'        => true,
            'show_ui'       => true,
            'show_in_menu'  => false,
            'supports'      => array('title', 'comments'),
            'taxonomies'    => array( ),
            'has_archive'   => true,
            'rewrite'       => array(
                'slug' => 'company'
            )
        );
        register_post_type( 'company', apply_filters("wpjb_cpt_init", $args, "company") ); 
        
        add_action("wpjb_job_saved", array($this, "link"));
        add_action("wpjb_company_saved", array($this, "link"));
        add_action("wpjb_resume_saved", array($this, "link"));
    }
    
    /**
     * Saves Custom Post Type data when object is saved
     * 
     * This function is called by wpjb_job_saved, wpjb_company_saved 
     * or wpjb_job_saved action.
     * 
     * @param Daq_Db_OrmAbstract $object
     * @return void
     */
    public function link($object) 
    {
        $object->cpt();
    }
    
    /**
     * Disables WPJB custom post types archives
     * 
     * The archives does not display jobs list properly it is best to disable them.
     * 
     * @return void
     */
    public function disableArchive()
    {
        $arr = array(
            "job" => wpjb_link_to("home"),
            "resume" => get_permalink( wpjb_conf("urls_link_resume") ),
            "company" => home_url()
        );
        
        foreach($arr as $key => $redirect) {
            if(is_post_type_archive( $key )) {
                wp_redirect( $redirect );
                exit;
            }
        }
    }
    
    /**
     * Print CSS when editing Custom Post Type in wp-admin
     * 
     * Prints CSS when editing Job, Resume or Company custom post type
     * from wp-admin panel.
     * 
     * @see admin_print_styles-post.php
     * 
     * @global string $post_type
     * @return void
     */
    public function cptPrintStyles()
    {
        global $post_type;
        
        switch($post_type) {
            case 'job': $from = "Wpjb_Model_Job"; break;
            case 'company': $from = "Wpjb_Model_Company"; break;
            case 'resume': $from = "Wpjb_Model_Resume"; break;
            default: return; break;
        }
        
        wp_enqueue_style( 'wpjb-admin-cpt-css', plugins_url()."/wpjobboard/public/css/admin-cpt.css");
    }
    
    /**
     * Print JavaScript when editing Custom Post Type in wp-admin
     * 
     * Prints JavaScript when editing Job, Resume or Company custom post type
     * from wp-admin panel.
     * 
     * @see admin_print_scripts-post.php
     * 
     * @global WP_Post $post
     * @global string $post_type
     * @return void
     */
    public function cptPrintScripts() {
        global $post_type, $post;
        
        $cpt = array(
            "href" => "",
            "url" => "",
            "go_back" => __("Go back to basic options &raquo;", "wpjobboard")
        );
        
        switch($post_type) {
            case 'job': 
                $from = "Wpjb_Model_Job"; 
                $page = "job";
                break;
            case 'company': 
                $from = "Wpjb_Model_Company"; 
                $page = "employers";
                break;
            case 'resume': 
                $from = "Wpjb_Model_Resume"; 
                $page = "resumes";
                break;
            default: 
                return;
                break;
        }
        
        $query = new Daq_Db_Query;
        $query->from("$from t");
        $query->where("post_id = ?", $post->ID);
        $query->limit(1);
        
        $result = $query->execute();
        
        if(!isset($result[0])) {
            return;
        }
        
        $object = $result[0];
        
        wp_enqueue_script( 'wpjb-admin-cpt', plugins_url()."/wpjobboard/public/js/admin-cpt.js", array("jquery"));
        
        $cpt["href"] = "wpjb-".$page;
        $cpt["url"] = wpjb_admin_url($page, "edit", $object->id);
        
        echo '<script type="text/javascript">'.PHP_EOL;
        echo 'var WPJB_CPT = '.json_encode($cpt).';'.PHP_EOL;
        echo '</script>'.PHP_EOL;
        
    }
}


