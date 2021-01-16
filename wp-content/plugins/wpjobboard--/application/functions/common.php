<?php

/**
 * Renders Custom Field Value
 * 
 * This function is being used on job, resume and company details pages
 * to display data in the grid.
 * 
 * @since 5.1.0
 * 
 * @param mixed             $value
 * @param string            $k
 * @param Daq_Orm_Abstract  $object
 * @param string            $display
 * @return string
 */
function wpjb_row_value($value, $k, $object, $display) {
    if($display === "text") {
        return esc_html($value);
    } else if($display == "html") {
        return $value;
    }
}

/**
 * Returns table prefix to use.
 * 
 * Depending on installation method this will return either prefix for whole
 * network or for a single network site.
 * 
 * @since 4.4.2
 * @global wpdb $wpdb
 * @return string Tables prefix
 */
function wpjb_db_prefix() {
    global $wpdb;
    
    if(is_multisite()) {        
        $plugins = get_site_option( 'active_sitewide_plugins');
        if ( isset($plugins["wpjobboard/index.php"]) ) {
            return $wpdb->base_prefix;
        }
    } 
        
    return $wpdb->prefix;
}

/**
 * Returns currently logged in user ID
 * 
 * This function is a wrapper for get_current_user_id()
 * 
 * @see get_current_user_id()
 * 
 * @since 4.4.7
 * @param string $context   One of "employer", "candidate" or empty string.
 * @return int              User ID if current user is logged in.
 */
function wpjb_get_current_user_id($context = "") {
    $user_id = get_current_user_id();
    return apply_filters("wpjb_get_current_user_id", $user_id, $context);
}

/**
 * Formats the price
 * 
 * @param float $amount     Amount to pay
 * @param string $currency  Currency
 * @return string           Formatted value
 */
function wpjb_price($amount, $currency) {
    $currency = Wpjb_List_Currency::getByCode($currency);
    
    $amount = number_format($amount, $currency["decimal"], ".", "");
    
    if(isset($currency["symbol"])) {
        return $currency["symbol"].$amount;
    } else {
        return $amount." ".$currency["code"];
    }
    
}

/**
 * Returns WPJB object based on $post_id
 * 
 * @param int       $post_id        Post ID
 * @param string    $type           Type one of "job", "resume", "company"
 * @return Daq_Db_OrmAbstract       Instance of a WPJB object or null
 */
function wpjb_get_object_from_post_id($post_id, $type = null) {
    
    if($type === null) {
        $post = get_post($post_id);
        $type = $post->post_type;
    }
    
    $arr = array(
        "job" => "Wpjb_Model_Job",
        "resume" => "Wpjb_Model_Resume",
        "company" => "Wpjb_Model_Company"
    );
    
    if(!array_key_exists($type, $arr)) {
        return null;
    }
    
    $query = new Daq_Db_Query();
    $query->from($arr[$type] . " t");
    $query->where("post_id = ?", $post_id);
    $query->limit(1);
    
    $result = $query->execute();
    
    if(!isset($result[0])) {
        return null;
    } 
    
    return $result[0];
}

/**
 * Returns HTML for a object details page
 * 
 * This function returns HTML and includes assets for an object identified by 
 * $post_id.
 * 
 * @since 4.6.0
 * @param int $post_id  ID of a post
 * @return string       HTML Content
 */
function wpjb_get_singular($post_id, $type = null) {
    
    if($type === null) {
        $post = get_post($post_id);
        $type = $post->post_type;
    }
    
    if ($type == "job") {
        return Wpjb_Project::getInstance()->singular->job->main($post_id);
    } elseif($type == "company") {
        return Wpjb_Project::getInstance()->singular->company->main($post_id);
    } elseif($type == "resume") {
        return Wpjb_Project::getInstance()->singular->resume->main($post_id);
    } else {
        return false;
    }
}

/**
 * Converts search query into human readable data
 * 
 * This function converts search params (usually $_GET array) into an array 
 * which then can be used to display human readable search params in the
 * Advanced Jobs [wpjb_jobs_search] and Resumes [wpjb_resumes_search] search.
 * 
 * @since 5.0
 * 
 * @param array             $params     Search Params (usually $_GET)
 * @param Daq_Form_Abstract $form1      Search Form
 * @param Daq_Form_Abstract $form2      Object Form
 * @return array
 */
function wpjb_readable_query($params, $form1, $form2)  {
    $param = array();
    $ignore = array("page", "page_id", "pg", "results");

    foreach($params as $k => $value) {

        if(is_string($value)) {
            $value = trim($value);
        }

        if(empty($value)) {
            continue;
        }

        if(in_array($k, $ignore)) {
            continue;
        }


        switch($k) {
            case "job_id":
            case "job": 
                $object = new Wpjb_Model_Job($value);
                $value = $object->job_title;
                break;
            case "employer_id":
                $object = new Wpjb_Model_Company($value);
                $value = $object->company_name;
                break;
            case "country":
                $country = Wpjb_List_Country::getByCode($value);
                $value = $country["name"];
                break;
            case "type":
            case "category":
                $data = (array)$value;
                $value = array();
                foreach((array)$data as $v) {
                    $object = new Wpjb_Model_Tag($v);
                    $value[$v] = $object->title;
                }
                break;
            case "posted":
                $arr = array(
                    1 => __("Today", "wpjobboard"),
                    2 => __("Since Yesterday", "wpjobboard"),
                    7 => __("Less than 7 days ago", "wpjobboard"),
                    30=> __("Less than 30 days ago", "wpjobboard")
                );
                $value = $arr[$value];
                break;

        }

        if(!is_array($value)) {
            $value = array($value);
        }

        if(substr($k, -3) == "_id") {
            $key = str_replace("_id", "", $k);
        } else {
            $key = $k;
        }

        if($form1->hasElement($key)) {
           $label = $form1->getElement($key)->getLabel(); 
        } elseif($form2->hasElement($key)) {
            $label = $form2->getElement($key)->getLabel(); 
        } else {
            $label = ucfirst(str_replace("_", " ", $key));
        }
        
        if( $k == 'country') {
            $label = __( "Country", "wpjobboard" );
        } elseif( $k == 'state' ) {
            $label = __( "State", "wpjobboard" );
        } elseif( $k == 'city' ) {
            $label = __( "City", "wpjobboard" );
        } elseif( $k == 'query' ) {
            $label = __( "Keyword", "wpjobboard" );
        } elseif( $k == 'location' ) {
            $label = __( "Location", "wpjobboard" );
        } elseif( $k == 'type' ) {
            $label = __( "Job Type", "wpjobboard" );
        } elseif( $k == 'category' ) {
            $label = __( "Job Category", "wpjobboard" );
        } elseif( $k == 'posted' ) {
            $label = __( "Posted", "wpjobboard" );
        } 

        $param[$k] = array("param"=>$label, "value"=>$value);
    }

    return apply_filters( "wpjb_readable_query", $param );
}

function wpjb_form_get_listings() {
    
    $query = new Daq_Db_Query();
    $query->select();
    $query->from("Wpjb_Model_Pricing t");
    $query->where("price_for = ?", Wpjb_Model_Pricing::PRICE_SINGLE_JOB);
    $query->where("is_active = 1");
    $result = $query->execute();
    $arr = array();
    
    foreach($result as $p) {
        $arr[] = array(
            "key" => $p->id,
            "value" => $p->id,
            "description" => $p->title
        );
    }
    
    return apply_filters("wpjb_form_get_listings", $arr);
}

/**
 * Returns allowed categories
 *
 * @return array
 */
function wpjb_form_get_categories() {
    $select = Daq_Db_Query::create();
    $select->from("Wpjb_Model_Tag t");
    $select->where("type = ?", Wpjb_Model_Tag::TYPE_CATEGORY);
    $select->order("`order` ASC, `title` ASC"); 
    $list = $select->execute();
    $arr = array();
    
    foreach($list as $item) {
        $arr[] = array(
            "key" => $item->id,
            "value" => $item->id,
            "description" => $item->title
        );
    }
    
    return apply_filters("wpjb_form_get_categories", $arr);
}

/**
 * Returns allowed job types
 *
 * @return array 
 */
function wpjb_form_get_jobtypes() {
    $select = Daq_Db_Query::create();
    $select->from("Wpjb_Model_Tag t");
    $select->where("type = ?", Wpjb_Model_Tag::TYPE_TYPE);
    $select->order("`order` ASC, `title` ASC"); 
    $list = $select->execute();
    $arr = array();
    
    foreach($list as $item) {
        $arr[] = array(
            "key" => $item->id,
            "value" => $item->id,
            "description" => $item->title
        );
    }
    
    return apply_filters("wpjb_form_get_jobtypes", $arr);
}

function wpjb_form_get_countries() {
    $arr = array();
    foreach(Wpjb_List_Country::getAll() as $listing) {
        $arr[] = array(
            "key" => $listing['code'], 
            "value" => $listing['code'], 
            "description" => $listing['name']
        );
    }
    
    return apply_filters("wpjb_form_get_countries", $arr);
}

function wpjb_upload_id($id = null) {
    if(!empty($id)) {
        $unique = $id;
    } elseif(get_current_user_id()>0) {
        $unique = "tmp_u_".get_current_user_id();
    } elseif(wpjb_transient_id()) {
        $unique = "tmp_s_".wpjb_transient_id();
    } else {
        return null;
    }
    
    return $unique;
}

function wpjb_upload_dir($object, $field, $id = null, $index = null) {
    
    global $blog_id;
    
    if($blog_id > 1) {
        $bid = "-".$blog_id;
    } else {
        $bid = "";
    }
    
    $unique = wpjb_upload_id($id);
    
    $dir = wp_upload_dir();
    $d = array();
    $d["baseurl"] = $dir["baseurl"]."/wpjobboard{$bid}/{$object}/{$unique}/{$field}";
    $d["basedir"] = $dir["basedir"]."/wpjobboard{$bid}/{$object}/{$unique}/{$field}";
    $d["dir"] = "{$object}/{$unique}/{$field}";

    $d = apply_filters("wpjb_upload_dir", $d, $object, $field, $id, $index);
    
    if(isset($d[$index])) {
        return $d[$index];
    } else {
        return $d;
    }
}

function wpjb_bubble_delete($path) {
        
    $path = rtrim($path, "/")."/";
    $files = wpjb_glob($path . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            wpjb_bubble_delete($file);
        } else {
            unlink($file);
        }
    }
    
    if(is_dir($path)) {
        rmdir($path);
    }

}

function wpjb_recursive_delete($dirname)
{ 
    if(is_dir($dirname)) {
        $dir_handle = opendir($dirname);
    } else {
        return true;
    }
    
    while($file = readdir($dir_handle)) {
        if($file!="." && $file!="..") {
            if(!is_dir($dirname."/".$file)) {
                unlink ($dirname."/".$file);
            } else {
                wpjb_recursive_delete($dirname."/".$file);
            }
        }
    }
    
    closedir($dir_handle);
    rmdir($dirname);
    
    return true;
}

/**
 * Returns list of available Payment statuses.
 * 
 * @uses wpjb_get_payment_status filter
 * 
 * @param int $status Status ID / Key
 * @return array Either all status or selected status
 */
function wpjb_get_payment_status($status = null) {
    $defaults = array(
        1 => array(
            "id" => 1,
            "key" => "pending",
            "label" => __("Pending", "wpjobboard")
        ),
        2 => array(
            "id" => 2,
            "key" => "completed",
            "label" => __("Completed", "wpjobboard")
        ),
        3 => array(
            "id" => 3,
            "key" => "failed",
            "label" => __("Failed", "wpjobboard")
        ),
        4 => array(
            "id" => 4,
            "key" => "refunded",
            "label" => __("Refunded", "wpjobboard")
        )
    );
    
    $filtered = apply_filters("wpjb_get_payment_status", $defaults);
    
    if(array_key_exists($status, $filtered)) {
        return $filtered[$status];
    } else {
        return $filtered;
    }
}

function wpjb_get_application_status($status = null) {
    
    //$query = Daq_Db_Query::create();
    //$query->from("Wpjb_Model_ApplicationStatus t1");
    //$query->order("t1.order, t1.title");
    //$result = $query->execute();

    $templ = Wpjb_Utility_Message::load("notify_applicant_status_change");

    $defaults = array(
        1 => array(
            "id" => 1,
            "key" => "new",
            "color" => null,
            "bulb" => "wpjb-bulb-new",
            "label" => __("New", "wpjobboard"),
            "public" => 1,
            "email_template" => null,
            "notify_applicant_email" => null,
            "labels" => array(
            )
        ),
        3 => array(
            "id" => 3,
            "key" => "read",
            "color" => null,
            "bulb" => "wpjb-bulb-new",
            "label" => __("Read", "wpjobboard"),
            "public" => 1,
            "email_template" => null,
            "notify_applicant_email" => null,
            "labels" => array(
                "multi_success" => __("Number of applications marked as read: {success}", "wpjobboard"),
            )
        ),
        0 => array(
            "id" => 0,
            "key" => "rejected",
            "color" => null,
            "bulb" => "wpjb-bulb-rejected",
            "label" => __("Rejected", "wpjobboard"),
            "public" => 1,
            "email_template" => $templ->getTemplate()->id,
            "notify_applicant_email" => "notify_applicant_status_change",
            "labels" => array(
                "multi_success" => __("Number of rejected applications: {success}", "wpjobboard"),
            )
        ),
        2 => array(
            "id" => 2,
            "key" => "accepted",
            "color" => null,
            "bulb" => "wpjb-bulb-accepted",
            "label" => __("Accepted", "wpjobboard"),
            "public" => 1,
            "email_template" => $templ->getTemplate()->id,
            "notify_applicant_email" => "notify_applicant_status_change",
            "labels" => array(
                "multi_success" => __("Number of accepted applications: {success}", "wpjobboard"),
            )
        )
    );
    
    ksort( $defaults );
    
    $filtered = apply_filters("wpjb_application_status", $defaults);
    
    if($status === null) {
        return $filtered;
    }
    
    if(!array_key_exists($status, $filtered)) {
        return null;
    } else {
        return $filtered[$status];
    }
}

function wpjb_application_status($s = null, $bulb = false) {
    
    $list = wpjb_get_application_status();
    $status = array();
    $bb = array();
    $cc = array();
    $tc = array();
    
    foreach($list as $k => $data) {
        $status[$k] = $data["label"];
        $bb[$k] = $data["bulb"];
        $cc[$k] = $data["color"];
        $tc[$k] = $data["tcolor"];
    }
    
    if($s === null) {
        return $status;
    } elseif(!$bulb) {
        return $status[$s];
    } elseif($bb[$s]) {
        $st = esc_html($status[$s]);
        $style = "";
        
        if( isset( $cc[$s] ) ) {
            $style .= sprintf( "background-color: %s; ", $cc[$s] );
        }
        if( isset( $tc[$s] ) ) {
            $style .= sprintf( "color: %s; ", $tc[$s] );
        }
        
        return sprintf( '<span class="wpjb-bulb %s" style="%s">%s</span>', $bb[$s], $style, $st );
    } 
}

function wpjb_application_status_default() {
    return apply_filters("wpjb_application_status_default", 1);
}

function wpjb_date_format() {
    return apply_filters("wpjb_date_format", "Y/m/d");
}

function wpjb_default_currency() {
    return "USD";
}

function wpjb_default_payment_method() {
    return "PayPal";
}

function wpjb_option($param, $default = null) {
    return Wpjb_Project::getInstance()->conf($param, $default);
}

function wpjb_date($date, $format = null) {
    
    if(!$format) {
        $format = wpjb_date_format();
    }
    
    $ts = time();
    $format = apply_filters("wpjb_date", $format);
    
    $offset = get_option("gmt_offset");
    
    if(stripos($offset, "-") !== 0) {
        $offset = "+".$offset;
    }

    $date = new DateTime($date);
    $date->setTime(date("H", $ts), date("i", $ts), date("s", $ts));
    $date->modify($offset." hours");
    
    return $date->format($format);
}

function wpjb_time($date) {
    $date = new DateTime($date);
    return $date->format("U");
}

function wpjb_transient_id() {
    
    $sid = "wpjb_transient_id";
    $id = null;
    if(!headers_sent() && (!isset($_COOKIE[$sid]) || empty($_COOKIE[$sid]))) {
        $id = strval(time()."-".str_pad(rand(0, 9999), 4, "0", STR_PAD_LEFT));
        setcookie($sid, $id, time()+86400, COOKIEPATH, COOKIE_DOMAIN, false);
    } elseif(isset($_COOKIE[$sid])) {
        $id = $_COOKIE[$sid];
    }
    
    return $id;
}

function wpjb_form_field_upload(Daq_Form_Element $e, $form = null) {
    
    wp_enqueue_script("wpjb-plupload");
    
    $path = $e->getUploadPath();
    $upload = wpjb_upload_dir($path["object"], $path["field"], $form->getId());
    $basedir = $upload["basedir"];
    $url = $upload["baseurl"];
    $dir = $upload["dir"];
    $size = wp_max_upload_size().'b';

    foreach($e->getValidators() as $k => $v) {
        if($k == "Daq_Validate_File_Size") {
            $size = $v->getSize();
            break;
        }
    }
    
    $init = array(
        'runtimes'            => 'html5,silverlight,flash,html4',
        'browse_button'       => 'wpjb-upload-browse-'.$e->getName(),
        'container'           => 'wpjb-upload-container-'.$e->getName(),
        'drop_element'        => 'wpjb-drop-zone-'.$e->getName(),
        'file_data_name'      => 'file',            
        'multiple_queues'     => true,
        'max_file_size'       => $size,
        'url'                 => admin_url('admin-ajax.php'),
        'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
        'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
        'filters'             => array(array('title' => __('Allowed Files'), 'extensions' => '*')),
        'multipart'           => true,
        'urlstream_upload'    => true,

        // additional post data to send to our ajax hook
        'multipart_params'    => array(
            'action' => "wpjb_main_upload",
            'form' => get_class($form),
            'object' => get_class($form->getObject()),
            'field' => $e->getName() ,
            'id' => ($form && $form->getId()) ? $form->getId() : "null"
        ),
    );
    
    $files = array();
    
    foreach(wpjb_glob("$basedir/[!_]*") as $file) {
        $f = basename($file);
        $files[] = array(
            "type" =>"file", 
            "name" => $f, 
            "url" => $url."/".$f, 
            "path" => $dir."/".$f, 
            "size" => filesize($file), 
            "id" => null
        );
    }

    if(Wpjb_Utility_Registry::has("form-upload-init")) {
        $fui = Wpjb_Utility_Registry::get("form-upload-init");
    } else {
        $fui = array("file"=>array(), "link"=>array());
        if(is_admin()) {
            add_filter("admin_footer", "wpjb_form_field_upload_footer");
            wp_enqueue_style('wpjb-glyphs');
        } else {
            add_filter("wp_footer", "wpjb_form_field_upload_footer");
            
        }
    }
    
    if(is_admin()) {
        $bclass = "button";
    } else {
        $bclass = "wpjb-button";
    }
    
    $fui["file"][] = array(
        "name" => $init["container"], 
        "init" => $init, 
        "files" => $files
    );
    
    if(current_user_can("upload_files")) {
        
        $links = array();
        $link = new Wpjb_Utility_Link(array(
            "object" => get_class($form->getObject()),
            "field" => $e->getName(),
            "id" => $form->getId()
        ));

        foreach($link->getAll() as $l) {
            
            $post = wp_prepare_attachment_for_js($l["id"]);
            
            $item = new stdClass();
            $item->id = $post["id"];
            $item->type = "link";
            $item->name = $post["filename"];
            $item->url = $post["url"];
            $item->path = get_attached_file( $l["id"] );
            $item->size = $post["filesizeInBytes"];
            $links[] = $item;
        }
        
        $fui["link"][] = array(
            "opener" => '#wpjb-upload-media-'.$e->getName(),
            "overlay" => "#wpjb-media-library-overlay",
            "params" => array(
                'action' => 'wpjb_main_link',
                'form' => get_class($form),
                'object' => get_class($form->getObject()),
                'field' => $e->getName() ,
                'id' => ($form && $form->getId()) ? $form->getId() : "null"
            ),
            "links" => $links
        );
    }
    
    Wpjb_Utility_Registry::set("form-upload-init", $fui);
    
    do_action("wpjb_form_field_upload");
      
    /**
     * Uploader Problems:
     * 1. Icon only - No wp_footer() call in footer.php file
     * 2. Icon with text - JavaScript error on site
     */
    
    ?>

    <div id="<?php esc_attr_e($init["container"]) ?>" class="wpjb-upload">
        
        <div class="wpjb-upload-construct">
            <span class="wpjb-glyphs wpjb-animate-spin wpjb-icon-cw"></span>
            <span class="wpjb-upload-info wpjb-upload-init"></span>
        </div>
        
        <div class="wpjb-upload-ui wpjb-none">
            <div id="<?php esc_attr_e($init["drop_element"]) ?>" class="wpjb-drop-zone"></div>
            <div class="wpjb-upload-inner">
                <span class="wpjb-upload-info"> <?php _e( "Drop files here", "wpjobboard" ) ?></span>
                <span class="wpjb-glyphs wpjb-icon-upload-cloud"></span>
                <span>
                    <a href="#" id="<?php esc_attr_e('wpjb-upload-browse-'.$e->getName()) ?>" class="wpjb-upload-file <?php echo $bclass ?>"><?php _e( "browse files ...", "wpjobboard" ) ?></a>
                    <?php if(current_user_can("upload_files")): ?>
                    <a href="#" id="<?php esc_attr_e('wpjb-upload-media-'.$e->getName()) ?>" class="wpjb-upload-media <?php echo $bclass ?>"><?php _e("media library ...", "wpjobboard") ?></a>
                    <?php endif; ?>
                    <?php do_action("wpjb_form_field_upload_buttons", $e, $form) ?>
                </span>
            </div>
        </div>
        <div class="wpjb-uploads wpjb-none">

        </div>
    </div>

    <?php
}

function wpjb_form_field_colorpicker( Daq_Form_Element $e, $form = null ) {
    
    ?>
    <div class="wpjb-colorpicker-selector-wraper wpjb-color-picker">
    <div class="wpjb-colorpicker-selector">
        <input type="hidden" name="<?php echo $e->getName(); ?>" id="<?php echo $e->getName(); ?>" value="<?php echo $e->getValue(); ?>" />
        <div class="wpjb-colorpicker-preview" style="background-color: #<?php echo trim( $e->getValue(), "#" ); ?>"></div>
    </div>
    </div>
    <?php
}

function wpjb_form_field_upload_footer() {
    
    $fuit = Wpjb_Utility_Registry::get("form-upload-init");
    $fset = array();
    $lset = array();
    $fui = array("file"=>array(), "link"=>array());

    foreach($fuit["file"] as $arr) {
        if(!in_array($arr["name"], $fset)) {
            $fui["file"][] = $arr;
            $fset[] = $arr["name"];
        }
    }
    foreach($fuit["link"] as $arr) {
        if(!in_array($arr["opener"], $lset)) {
            $fui["link"][] = $arr;
            $lset[] = $arr["opener"];
        }
    }
            
    $handles = array(
        array(
            "type" => "image",
            "ext" => array("jpg","jpeg", "gif", "png"),
            "icon" => "wpjb-icon-file-image",
            "action" => array("image", "download", "delete"),
        ),
        array(
            "type" => "video",
            "ext" => array("mp4", "ogv", "webm"),
            "icon" => "wpjb-icon-file-video",
            "action" => array("video", "download", "delete"),
        ),
        array(
            "type" => "audio",
            "ext" => array("mp3"),
            "icon" => "wpjb-icon-file-audio",
            "action" => array("audio", "download", "delete"),
        ),
        array(
            "type" => "doc-word",
            "ext" => array("doc", "docx"),
            "icon" => "wpjb-file-doc-text",
            "action" => array("download", "delete"),
        ),
        array(
            "type" => "doc-pdf",
            "ext" => array("pdf"),
            "icon" => "wpjb-icon-file-pdf",
            "action" => array("pdf", "download", "delete"),
        ),
        array(
            "type" => "archive",
            "ext" => array("zip", "rar", "tar", "gz"),
            "icon" => "wpjb-icon-file-archive",
            "action" => array("download", "delete")
        ),
        array(
            "type" => "unknown",
            "ext" => array(),
            "icon" => "wpjb-icon-doc-text",
            "action" => array("download", "delete"),
        )
    );
            
    $handles = apply_filters("wpjb_file_upload_handles", $handles);
    
    if(is_admin()) {
        $bclass = "button";
    } else {
        $bclass = "wpjb-button";
    }
    
    ?>
    <style type="text/css">
        .wpjb-upload-init:after { content: 'Initializing File Uploader ...' }
    </style>
    <script type="text/javascript">
    jQuery(function($) {
        WPJB.upload.ajaxurl = '<?php echo esc_html(admin_url('admin-ajax.php')) ?>';
        WPJB.upload.handles = <?php echo json_encode($handles) ?>;
        <?php foreach($fui["file"] as $upload): ?>
        WPJB.upload.init(<?php echo json_encode($upload["init"]) ?>);
        WPJB.upload.load('<?php echo $upload["name"] ?>', <?php echo json_encode($upload["files"]) ?>);
        <?php endforeach; ?>
            
        <?php foreach($fui["link"] as $link): ?>
        WPJB.upload.media.push(new WPJB.mediabox(<?php echo json_encode($link) ?>));
        <?php endforeach; ?>
    });
    </script>
    
    <!-- START: File delete overlay -->
    <div id="wpjb-file-delete" class="wpjb wpjb-overlay">
        <div class="wpjb-overlay-body">
            <div class="wpjb-overlay-header wpjb-file-delete-header" >
                <div class="wpjb-overlay-title">
                    <span class="wpjb-file-delete-name"></span>
                </div>
            </div>
            <div class="wpjb-overlay-content">
                <span><?php _e("Are you sure you want to delete this file?", "wpjobboard") ?></span>
                
                <div class="wpjb-none wpjb-flash-error wpjb-file-delete-error">
                    <span class="wpjb-file-delete-error-msg"></span>
                </div>
            </div>

            <div class="wpjb-overlay-footer" style="padding: 10px">
                <a href="#" class="<?php echo $bclass ?> wpjb-file-delete-confirm"><?php _e("Yes, delete this file.", "wpjobboard") ?></a>
                <a href="#" class="<?php echo $bclass ?> wpjb-file-delete-cancel"><?php _e("Cancel", "wpjobboard") ?></a>
                <span class="wpjb-none wpjb-glyphs wpjb-animate-spin wpjb-icon-spinner"></span>
            </div>
        </div>
    </div>
    <!-- END: File delete overlay -->
    
    <!-- START: File upload overlay -->
    <div id="wpjb-file-upload-overlay" class="wpjb wpjb-overlay">

        <div class="wpjb-overlay-body">
             
            <div class="wpjb-overlay-header">
                <div class="wpjb-overlay-title">
                    <span class="wpjb-file-pagi-index"></span>
                    <span>/</span>
                    <span class="wpjb-file-pagi-total"></span>
                </div>
                <div class="wpjb-overlay-buttons">
                    <span class="wpjb-overlay-button wpjb-file-pagi-prev wpjb-glyphs wpjb-icon-left-open"></span><!-- no line break
                    --><span class="wpjb-overlay-button wpjb-file-pagi-next wpjb-glyphs wpjb-icon-right-open"></span><!-- no line break
                    --><a href="#" class="wpjb-overlay-button wpjb-overlay-close wpjb-glyphs wpjb-icon-cancel" title="<?php _e("Close", "wpjobboard") ?>"></a>
                </div>
            </div>
             
            <div id="wpjb-file-content" class="wpjb-overlay-content">
                
            </div>
            
            <div class="wpjb-overlay-footer">
                <div class="wpjb-file-name"></div>
            </div>
            
        </div>
    </div>
    <!-- END: File upload overlay -->
    
    <!-- START: Media library overlay -->
    <div id="wpjb-media-library-overlay" class="wpjb wpjb-overlay">

        <div class="wpjb-overlay-body">
             
            <div class="wpjb-overlay-header">
                <div class="wpjb-overlay-title">
                    <input type="text" id="wpjb-media-library-search" placeholder="<?php _e("Search ...", "wpjobboard") ?>" name="s" autocomplete="off" /> 
                    <span class="wpjb-glyphs wpjb-animate-spin wpjb-icon-spinner wpjb-media-library-spinner wpjb-none" style="vertical-align: middle"></span>
                </div>
                <div class="wpjb-overlay-buttons">
                    <a href="#" class="wpjb-overlay-button wpjb-overlay-close wpjb-glyphs wpjb-icon-cancel" title="<?php _e("Close", "wpjobboard") ?>"></a>
                </div>
            </div>
             
            <div id="wpjb-media-library" class="wpjb-overlay-content">
                <ul tabindex="-1" class="wpjb-attachments">

                </ul>
            </div>
            
            <div class="wpjb-overlay-footer" style="padding: 10px">
                <a href="#" class="wpjb-media-library-add <?php echo $bclass ?>"><?php _e("Add Files", "wpjobboard") ?></a>
                <a href="#" class="wpjb-media-library-cancel <?php echo $bclass ?>"><?php _e("Cancel", "wpjobboard") ?></a>
                <span class="wpjb-media-library-stat wpjb-none">
                    <span class="wpjb-media-library-count"></span> <?php _e("selected", "wpjobboard") ?></span>
                </span>
            </div>
            
        </div>
    </div>
    <!-- END: Media library overlay -->
    
    <?php
}

function wpjb_subscribe() {
    
    $instance = Wpjb_Project::getInstance();
    
    $view = new stdClass();
    $view->param = $instance->env("search_params");
    $view->feed_url = $instance->env("search_feed_url");
    $view->alerts = wpjb_candidate_alert_stats();
    
    $shortcode = new Wpjb_Shortcode_Dynamic;
    $shortcode->view = $view;
    echo $shortcode->render("job-board", "subscribe");
}

function wpjb_meta_register($object, $name, $params = array()) {
    $query = new Daq_Db_Query();
    $query->from("Wpjb_Model_Meta t");
    $query->where("meta_object = ?", $object);
    $query->where("name = ?", $name);
    $query->limit(1);
    
    $result = $query->execute();
    
    if(isset($result[0])) {
        $meta = $result[0];
    } else {
        $meta = new Wpjb_Model_Meta;
        $meta->meta_object = $object;
        $meta->name = $name;
        $meta->meta_type = 2;
        
        if(!empty($params) && is_array($params)) {
            $meta->meta_value = serialize($params);
        }
        $meta->save();
    }
    
    return $meta;
}

function wpjb_meta_unregister($object, $name) {
    $query = new Daq_Db_Query();
    $query->from("Wpjb_Model_Meta t");
    $query->where("meta_object = ?", $object);
    $query->where("name = ?", $name);
    $query->limit(1);
    
    $result = $query->execute();
    
    if(isset($result[0])) {
        $meta = $result[0];
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_MetaValue t");
        $query->where("meta_id = ?", $meta->id);
        
        $result = $query->execute();
        foreach($result as $mv) {
            $mv->delete();
        }
        
        $meta->delete();
    }
}

function wpjb_glob($pattern, $flags = 0) {
    $list = glob($pattern, $flags);
    
    if(empty($list)) {
        return array();
    } else {
        return $list;
    }
}

function wpjb_rename_dir($old, $new) {
    
    $old = rtrim($old, "/");
    $new = rtrim($new, "/");
    
    if(!is_dir($old)) {
        return false;
    }
    
    $wpupload = wp_upload_dir();
    $stat = @stat($wpupload["basedir"]);
    $perms = $stat['mode'] & 0007777;

    $moved = @rename( $old, $new );
    
    if ( ! $moved ) {
        if( ! wpjb_recursive_copy( $old, $new, $perms ) ) {
            wpjb_recursive_delete( $new );
            return false;
        }
        wpjb_recursive_delete( $old );
    } 
        
    chmod($new, $perms);
    
    foreach(wpjb_glob($new) as $sub) {
        chmod($sub, $perms);
    }
    
    return $moved;
}

function wpjb_recursive_copy( $source, $dest, $perms = 0755 ) {

    if ( ! is_dir($dest) ) {
        if ( ! mkdir($dest, $perms, true) ) {
            return false;
        }
    }
    $directoryIterator = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
    $recursiveIterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);
    foreach ( $recursiveIterator as $item ) {
        if ($item->isDir()) {
            if ( ! mkdir($dest . DIRECTORY_SEPARATOR . $recursiveIterator->getSubPathName(), $perms) ) {
                return false;
            }
        } else {
            if ( ! copy($item, $dest . DIRECTORY_SEPARATOR . $recursiveIterator->getSubPathName()) ) {
                return false;
            }
        }
    }
    return true;
}

function wpjb_bulb($object) {
    
    if($object instanceof Wpjb_Model_Job) {
        $data = array(
            Wpjb_Model_Job::STATUS_ACTIVE => array("class"=>"wpjb-bulb-active", "title"=>__("Active", "wpjobboard")),
            Wpjb_Model_Job::STATUS_AWAITING => array("class"=>"wpjb-bulb-awaiting", "title"=>__("Awaiting Approval", "wpjobboard")),
            Wpjb_Model_Job::STATUS_PAYMENT => array("class"=>"wpjb-bulb-awaiting", "title"=>__("Awaiting Payment", "wpjobboard")),
            Wpjb_Model_Job::STATUS_EXPIRED => array("class"=>"wpjb-bulb-expired", "title"=>__("Expired", "wpjobboard")),
            Wpjb_Model_Job::STATUS_EXPIRING => array("class"=>"wpjb-bulb-expiring", "title"=>__("Expiring", "wpjobboard")),
            Wpjb_Model_Job::STATUS_INACTIVE => array("class"=>"wpjb-bulb-expired", "title"=>__("Inactive", "wpjobboard")),
            Wpjb_Model_Job::STATUS_NEW => array("class"=>"wpjb-bulb-new", "title"=>__("New", "wpjobboard")),
        );
    } else {
        throw new Exception("Invalid object type [".get_class($object)."]");
    }
    
    $st = array();
    $ignore = array(
        Wpjb_Model_Job::STATUS_NEW,
        Wpjb_Model_Job::STATUS_PAYMENT,
        Wpjb_Model_Job::STATUS_AWAITING,
    );

    foreach($object->status() as $status) {
        
        if(!is_admin() && in_array($status, $ignore)) {
            continue;
        }
        
        $c = $data[$status]["class"];
        $t = $data[$status]["title"];
        $st[] = "<span class=\"wpjb-bulb  $c\">$t</span>";
    }

    return $st;
                
}

function wpjb_google_map_url($object) {
    
    $key = wpjb_conf("google_api_key");
    
    if($key) {
    
        $mode = "place";

        $query_array = array(
            "key" => $key,
            "q" => $object->location(),
            "zoom" => "15"
        );
        
        $query = http_build_query( apply_filters( "wpjb_google_map_query", $query_array, $key ) );

        return "https://www.google.com/maps/embed/v1/$mode?$query";
        
    } else {
        
        $query_array = array(
            "ie" => "UTF8",
            "t" => "m",
            "near" => $object->location(),
            "ll" => $object->getGeo()->lnglat,
            "spn" => "0.107734,0.686646",
            "z" => "15",
            "output" => "embed",
            "iwloc" => "near",
        );
        
        if(is_ssl()) {
            $protocol = "https";
        } else {
            $protocol = "http";
        }
        
        $query = http_build_query( apply_filters( "wpjb_google_map_query", $query_array, $key ) );

        return "$protocol://maps.google.com/?$query";
    }
}

function wpjb_scheme_get($scheme, $path) {
    return wpjb_array_path($scheme["field"], $path);
}

function wpjb_array_path($arr, $path) {
    
    if(stripos($path, ".")===false) {
        if(isset($arr[$path])) {
            return $arr[$path];
        } else {
            return null;
        }
    }
    
    list($top, $rest) = explode(".", $path);

    if(isset($arr[$top])) {
        return wpjb_array_path($arr[$top], $rest);
    }
    
}

function wpjb_scheme_handle($scheme, $name) {
    
    if(wpjb_scheme_get($scheme, $name.".visibility")>0) {
        return true;
    } elseif(wpjb_scheme_get($scheme, $name.".render_callback")) {
        call_user_func(wpjb_scheme_get($scheme, $name.".render_callback"));
        return true;
    }
    
    return false;
}



function wpjb_custom_menu_key_to_url($key) {
    list($k, $v) = explode(":", $key);

    if($v == "step_add") {
        $id = wpjb_conf("urls_link_job_add");
    } else {
        $id = null;
    }

    switch($k) {
        case "frontend":
            $url = wpjb_link_to($v, null, array(), $id);
            break;
        case "resumes":
            $url = wpjr_link_to($v, null, array(), null);
            break;
        case "page":
            $url = get_permalink($v);
            break;
        case "http":
        case "https":
            $url = $key;
            break;
    }

    return $url;
}



function wpjb_custom_menu_show_link($link) {

    if(!isset($link["menu-item-visibility"])) {
        return false;
    }

    $v = $link["menu-item-visibility"];

    if(isset($v["unregistered"]) && $v["unregistered"]==1 && get_current_user_id()<1) {
        return true;
    }

    if(isset($v["loggedin"]) && $v["loggedin"]==1 && get_current_user_id()>0) {
        return true;
    }

    if(isset($v["candidate"]) && $v["candidate"]==1 && current_user_can("manage_resumes")) {
        return true;
    }

    if(isset($v["employer"]) && $v["employer"]==1 && current_user_can("manage_jobs")) {
        return true;
    }

    return false;
}

/**
 * Displays detail templates for My Resume form.
 * 
 * This function is assigned in Wpjb_Module_Resumes_Index::myresumeAction() method,
 * to wp_footer action.
 * 
 * @since 4.4.3
 * @return void
 * 
 */
function wpjb_myresume_templates() {
    
    $form = new Wpjb_Form_Resume();
    $groups = array_keys($form->getGroups());
    
    if(is_admin()) {
        $bclass = "button";
    } else {
        $bclass = "wpjb-button";
    }

    ?>

    <?php if(in_array("experience", $groups)): ?>
    <script type="text/html" id="tmpl-Wpjb_Form_Resumes_Experience">
    <?php
        $view = new stdClass();
        $view->page_class = "wpjb-form-nested wpjb-form-resume-experience";
        $view->action = "#";
        $view->submit = null;
        $view->form = new Wpjb_Form_Resumes_Experience();
        $buttons = array();
        $buttons[] = array(
            "tag" => "a", 
            "class" => $bclass . " wpjb-form-nested-save",
            "href" => "#save", 
            "html" => __("Save", "wpjobboard")
        );
        $buttons[] = array(
            "tag" => "a", 
            "class" => $bclass . " wpjb-form-nested-close",
            "href" => "#cancel", 
            "html" => __("Cancel", "wpjobboard")
        );
        $buttons[] = array(
            "tag" => "span",
            "class" => "wpjb-form-nested-progress wpjb-icons wpjb-animate-spin wpjb-icon-cw",
            "html" => ""
        );
        $view->buttons = $buttons;
        $shortcode = new Wpjb_Shortcode_Dynamic;
        $shortcode->view = $view;
        echo $shortcode->render("default", "form");
    ?>
    </script>

    <script type="text/html" id="tmpl-wpjb-utpl-experience">
        <# if ( ! data.is_current ) data.is_current = 0; #>
        <div class="wpjb-myresume-detail">

            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][id]" value="{{ data.id }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][type]" value="{{ data.type }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][is_current]" value="{{ data.is_current }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][detail_title]" value="{{ data.detail_title }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][grantor]" value="{{ data.grantor }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][started_at]" value="{{ data.started_at }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][completed_at]" value="{{ data.completed_at }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][detail_description]" value="{{ data.detail_description }}" />
            <?php do_action("wpjb_myresume_detail_experience_tmpl_inputs") ?>

            <div class="wpjb-myresume-detail-actions">
                <a href="#" title="<?php _e("Edit", "wpjobboard") ?>" class="<?php echo $bclass ?> wpjb-myresume-detail-edit wpjb-glyphs wpjb-icon-pencil"></a>
                <a href="#" title="<?php _e("Delete", "wpjobboard") ?>" class="<?php echo $bclass ?> wpjb-myresume-detail-remove wpjb-glyphs wpjb-icon-trash"></a>
            </div>

            <div class="wpjb-resume-detail-head">
                <strong>{{ data.detail_title }}</strong>

                <# if ( typeof data.grantor !== 'undefined' && data.grantor.length > 0 ) { #>
                <span class="wpjb-resume-detail-grantor">@ {{ data.grantor }}</span>
                <# } #>
            </div>


            <div class="wpjb-resume-detail-date-range wpjb-motif">
                <# started_at = WPJB.myresume.date_my(data.started_at) #>
                <# completed_at = WPJB.myresume.date_my(data.completed_at) #>
                <# if ( data.is_current == 1 ) completed_at = "<?php echo __("Current", "wpjobboard") ?>" #>
                {{ started_at }} - {{ completed_at }}
            </div>

            <# if ( data.detail_description ) { #>
            <span class="wpjb-resume-detail-description">{{ data.detail_description }}</span>
            <# } #>
        </div>
    </script>
    <?php endif; ?>

    <?php if(in_array("education", $groups)): ?>
    <script type="text/html" id="tmpl-Wpjb_Form_Resumes_Education">
    <?php
        $view = new stdClass();
        $view->page_class = "wpjb-form-nested wpjb-form-resume-education";
        $view->action = "#";
        $view->submit = null;
        $view->form = new Wpjb_Form_Resumes_Education();
        $buttons = array();
        $buttons[] = array(
            "tag" => "a", 
            "class" => $bclass . " wpjb-form-nested-save",
            "href" => "#save", 
            "html" => __("Save", "wpjobboard")
        );
        $buttons[] = array(
            "tag" => "a", 
            "class" => $bclass . " wpjb-form-nested-close",
            "href" => "#cancel", 
            "html" => __("Cancel", "wpjobboard")
        );
        $buttons[] = array(
            "tag" => "span",
            "class" => "wpjb-form-nested-progress wpjb-icons wpjb-animate-spin wpjb-icon-cw",
            "html" => ""
        );
        $view->buttons = $buttons;
        $shortcode = new Wpjb_Shortcode_Dynamic;
        $shortcode->view = $view;
        echo $shortcode->render("default", "form");
    ?>
    </script>

    <script type="text/html" id="tmpl-wpjb-utpl-education">
        <div class="wpjb-myresume-detail">

            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][id]" value="{{ data.id }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][type]" value="{{ data.type }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][is_current]" value="{{ data.is_current }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][detail_title]" value="{{ data.detail_title }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][grantor]" value="{{ data.grantor }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][started_at]" value="{{ data.started_at }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][completed_at]" value="{{ data.completed_at }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][detail_description]" value="{{ data.detail_description }}" />
            <?php do_action("wpjb_myresume_detail_education_tmpl_inputs") ?>

            <div class="wpjb-myresume-detail-actions">
                <a href="#" title="<?php _e("Edit", "wpjobboard") ?>" class="<?php echo $bclass ?> wpjb-myresume-detail-edit wpjb-glyphs wpjb-icon-pencil"></a>
                <a href="#" title="<?php _e("Delete", "wpjobboard") ?>" class="<?php echo $bclass ?> wpjb-myresume-detail-remove wpjb-glyphs wpjb-icon-trash"></a>
            </div>

            <div class="wpjb-resume-detail-head">
                <strong>{{ data.detail_title }}</strong>

                <# if ( typeof data.grantor !== 'undefined' && data.grantor.length > 0 ) { #>
                <span class="wpjb-resume-detail-grantor">@ {{ data.grantor }}</span>
                <# } #>
            </div>


            <div class="wpjb-resume-detail-date-range wpjb-motif">
                <# started_at = WPJB.myresume.date_my(data.started_at) #>
                <# completed_at = WPJB.myresume.date_my(data.completed_at) #>
                <# if ( data.is_current == 1 ) completed_at = "<?php echo __("Current", "wpjobboard") ?>" #>
                {{ started_at }} - {{ completed_at }}
            </div>

            <# if ( data.detail_description ) { #>
            <span class="wpjb-resume-detail-description">{{ data.detail_description }}</span>
            <# } #>
        </div>
    </script>
    <?php endif; ?>
    
    <script type="text/html" id="tmpl-wpjb-partial-undo">
        <div class="wpjb-partial-undo">
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][id]" value="{{ data.id }}" />
            <input type="hidden" name="{{data._conf.detail}}[{{data._conf.key}}][_delete]" value="1" />

            <span class="wpjb-icons wpjb-icon-trash-1"></span>
            <?php _e("Item <strong>{{ data.detail_title }}</strong> deleted.", "wpjobboard") ?>
            <a href="#" class="wpjb-myresume-detail-undo">
                <?php _e("Undo", "wpjobboard") ?>
            </a>
        </div>
    </script>
    <?php
    
    $partials = Wpjb_Utility_Registry::get("myresume-partials");
    
    ?>
    <script type="text/javascript">
    jQuery(function($) {
        WPJB.myresume.load_partials(<?php echo json_encode($partials) ?>);
    });
    </script>
    <?php
}

function wpjb_get_partials($object, $by = null, $value = null) {
    $partials = array();
    $partials["resume"] = array();
    
    $partials["resume"][] = array(
        "type" => 1,
        "name" => "experience",
        "form" => "Wpjb_Form_Resumes_Experience",
    );
    $partials["resume"][] = array(
        "type" => 2,
        "name" => "education",
        "form" => "Wpjb_Form_Resumes_Education",
    );
    //$partials = apply_filters( 'wpjb_partials_types', $partials );
    
    if(!isset($partials[$object])) {
        return null;
    }
    
    $partial = $partials[$object];
    
    if(!$by) {
        return $partial;
    }
    
    foreach($partial as $p) {
        if($p[$by] == $value) {
            return $p;
        }
    }
    
    return null;
}

function wpjb_alerts_templates() {
    
    $countries_list = Wpjb_List_Country::getAll();
    $countries = array();
    foreach($countries_list as $country){
        $countries[$country['code']] = $country['name'];
    }   
    
    $q = Daq_Db_Query::create();
    $tags_list = $q->select()->from("Wpjb_Model_Tag t")->execute();
    $tags = array();
    foreach($tags_list as $tag) {
        $tags[$tag->id] = $tag->title;
    }
    
    ?>
    <script type="text/html" id="tmpl-wpjb-utpl-alert">

        <# var countries = <?php echo json_encode($countries) ?> #>
        <# var tags = <?php echo json_encode($tags) ?> #>
        
            <!-- div class="wpjb-grid-row wpjb-manage-item wpjb-manage-alert" data-id="{{ data.id }}" -->
                <div class="wpjb-grid-col wpjb-col-100">
                    <div class="wpjb-manage-header">
                        
                        <input type="hidden" name="_wpjb_action" value="wpjb_candidate_add_alert" />
                        <input type="hidden" name="alert[{{data._conf.key}}][id]" value="{{ data.id }}" />
                        <input type="hidden" name="alert[{{data._conf.key}}][email]" value="{{ data.email }}" />
                        <input type="hidden" name="alert[{{data._conf.key}}][frequency]" value="{{ data.frequency }}" />
                        <input type="hidden" name="alert[{{data._conf.key}}][last_run]" value="{{ data.last_run }}" />
                        <input type="hidden" name="alert[{{data._conf.key}}][created_at]" value="{{ data.created_at }}" />
                        <input type="hidden" name="alert[{{data._conf.key}}][keyword]" value="{{ data.keyword }}" />
                        <input type="hidden" name="alert[{{data._conf.key}}][params]" value="{{ serialize(data.params) }}" />

                        <span class="wpjb-manage-header-left wpjb-line-major wpjb-manage-title">
                            {{ data.email }}
                        </span>

                        <ul class="wpjb-manage-header-right">
                            <li>
                                <span class="wpjb-glyphs wpjb-icon-clock" title="<?php _e("Frequency", "wpjobboard") ?>"></span>
                                <span class="wpjb-manage-header-right-item-text" title="<?php _e("Frequency", "wpjobboard") ?>">
                                    <# if(data.frequency == 1) { #>
                                    <?php _e("Daily", "wpjobboard"); ?>
                                    <# } else { #>
                                    <?php _e("Weekly", "wpjobboard"); ?> 
                                    <# } #>
                                </span>
                            </li>

                            <li>
                                <span class="wpjb-glyphs wpjb-icon-paper-plane" title="<?php _e("Last Run", "wpjobboard") ?>"></span>
                                    <span class="wpjb-manage-header-right-item-text" title="<?php _e("Last Run", "wpjobboard") ?>">
                                    <# if(data.last_run == "0000-00-00 00:00:00" || !data.last_run) { #>
                                    <?php _e("Never", "wpjobboard"); ?>
                                    <# } else { #>
                                    <# 
                                    function timeSince(date) {

                                        var seconds = Math.floor((new Date() - date) / 1000);

                                        var interval = Math.ceil(seconds / 31536000);
                                        if (interval > 1) {
                                          return interval + " years ago";
                                        }
                                        interval = Math.ceil(seconds / 2592000);
                                        if (interval > 1) {
                                          return interval + " months ago";
                                        }
                                        interval = Math.ceil(seconds / 86400);
                                        if (interval > 1) {
                                          return interval + " days ago";
                                        }
                                        interval = Math.ceil(seconds / 3600);
                                        if (interval > 1) {
                                          return interval + " hours ago";
                                        }
                                        interval = Math.ceil(seconds / 60);
                                        if (interval > 1) {
                                          return interval + " minutes ago";
                                        }
                                        return Math.floor(seconds) + " seconds";
                                    }
                                    #>
                                    {{ timeSince(new Date(data.last_run)) }}
                                    <# } #>
                                </span>
                            </li>

                            <li>
                                <span class="wpjb-glyphs wpjb-icon-calendar-plus-o" title="<?php _e("Created At", "wpjobboard") ?>"></span>
                                <span class="wpjb-manage-header-right-item-text" title="<?php _e("Created At", "wpjobboard") ?>">
                                    {{ data.created_at }}
                                </span>
                            </li>
                        </ul>
                    </div>


                    <div class="wpjb-manage-actions-wrap">

                        <span class="wpjb-manage-actions-left">

                            <a href="" class="wpjb-manage-action wpjb-alert-detail-edit">
                                <span class="wpjb-glyphs wpjb-icon-edit"></span>
                                <?php _e("Edit", "wpjobboard") ?>
                            </a>

                            <a href="" class="wpjb-manage-action wpjb-alert-detail-remove">
                                <span class="wpjb-glyphs wpjb-icon-trash"></span>
                                <?php _e("Remove", "wpjobboard") ?>
                            </a>

                            <a href="#" class="wpjb-manage-action wpjb-alert-show-params" data-id="{{ data.id }}">
                                <span class="wpjb-glyphs wpjb-icon-down-open"></span>
                                <?php _e("Show Params", "wpjobboard") ?>
                            </a>
                        </span>
                        <span class="wpjb-manage-actions-right">

                            <a href="#" class="wpjb-manage-action wpjb-manage-action-more">
                                <span class="wpjb-glyphs wpjb-icon-menu"></span>
                                <?php _e("More", "wpjobboard") ?>
                            </a>

                        </span>

                        <div class="wpjb-manage-actions-more">
                            
                        </div>
                    </div>

                </div>

                <div style="clear: both; overflow: hidden"></div>

                <div class="wpjb-alert-params wpjb-alert-params-{{ data.id }}" style="display: none;">
                    
                    <# if( !jQuery.isEmptyObject(data.params) ) { #>
                        <# for( var i in data.params ) { #>
                        <div class="wpjb-grid-row">
                            <div class="wpjb-grid-col wpjb-col-40" style="font-weight: bold;">            
                                <# var label = i.replace("_", " ").replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();}) #>
                                {{ label }}
                            </div>
                            <div class="wpjb-grid-col wpjb-col-60">
                                <# if( i == "category" || i == "type") { #>
                                    {{ tags[data.params[i]] }}
                                <# } else if(i == "job_country") { #>
                                    {{ countries[data.params[i]] }}
                                <# } else { #>
                                    {{ data.params[i] }}
                                <# } #>
                            </div>
                        </div>
                        <# } #>
                    <# } else { #>
                        <center style="padding: 10px 0px;"><?php _e("No params", "wpjobboard") ?></center>
                    <# } #>
                    
                </div>
                
                
            <!--/div-->
    </script>
    
    <script type="text/html" id="tmpl-Wpjb_Form_Alert">
    <?php
    $bclass = "";
    
        $form = new Wpjb_Form_Alert();
        $form->getGroup('alert')->title = "";
        $form->getGroup('params')->title = "";
    
        $view = new stdClass();
        $view->page_class = "wpjb-form-nested wpjb-form-resume-alerts";
        $view->action = "#";
        $view->submit = null;
        $view->form = $form;
        $buttons = array();
        $buttons[] = array(
            "tag" => "a", 
            "class" => $bclass . " wpjb-form-nested-save wpjb-button",
            "href" => "#save", 
            "html" => __("OK", "wpjobboard")
        );
        $buttons[] = array(
            "tag" => "a", 
            "class" => $bclass . " wpjb-form-nested-close wpjb-button",
            "href" => "#cancel", 
            "html" => __("Cancel", "wpjobboard")
        );
        /*$buttons[] = array(
            "tag" => "a", 
            "class" => $bclass . " wpjb-form-nested-add-param wpjb-button wpjb-glyphs wpjb-icon-plus",
            "href" => "#add_param", 
            "html" => __("Add New Param", "wpjobboard")
        );*/
        $buttons[] = array(
            "tag" => "span",
            "class" => "wpjb-form-nested-progress wpjb-icons wpjb-animate-spin wpjb-icon-cw",
            "html" => ""
        );
        $view->buttons = $buttons;
        $shortcode = new Wpjb_Shortcode_Dynamic;
        $shortcode->view = $view;
        echo $shortcode->render("resumes", "alert-details");
    ?>
    </script>
    
    <script type="text/html" id="tmpl-wpjb-alert-remove">
        <div class="wpjb-partial-undo">
            <input type="hidden" name="alert[{{data._conf.key}}][id]" value="{{ data.id }}" />
            <input type="hidden" name="alert[{{data._conf.key}}][_delete]" value="1" />

            <span class="wpjb-icons wpjb-icon-trash-1"></span>
            <?php _e("Alert deleted.", "wpjobboard") ?>
            <a href="#" class="wpjb-alert-undo">
                <?php _e("Undo", "wpjobboard") ?>
            </a>
        </div>
    </script>
    
    <?php $fields = wpjb_get_all_fileds(); ?>

    <script type="text/html" id="tmpl-wpjb-single-alert-param">
        <div class="wpjb-grid-col wpjb-col-30">
            &nbsp;
        </div>
        <div class="wpjb-grid-col wpjb-col-60">
            <select class="wpjb-new-alert-param-type">
                <option><?php _e("Select Param ...", "wpjobboard"); ?></option>
                <optgroup label="<?php _e("Default Fields", "wpjobboard"); ?>">
                <?php foreach($fields['default_fields'] as $name => $field): ?>
                    <option value="<?php echo esc_html($field['value']); ?>"><?php echo esc_html($field['label']); ?></option>
                <?php endforeach; ?>
                </optgroup>
                <optgroup label="<?php _e("Custom Fields", "wpjobboard"); ?>">
                <?php foreach($fields['custom_fields'] as $name => $field): ?>
                    <option value="<?php echo esc_html($field['value']); ?>"><?php echo esc_html($field['label']); ?></option>
                <?php endforeach; ?>
                </optgroup>
            </select>
        </div>
        <div class="wpjb-grid-col wpjb-col-10 wpjb-column-right" style="text-align: right;">
            <a href="" class="wpjb-button wpjb-remove-alert-param wpjb-glyphs wpjb-icon-trash"></a>
        </div>
    </script>

    <script type="text/html" id="tmpl-wpjb-single-alert-param-form">
        <label class="wpjb-label">{{ data.input_label }}</label>
        
        <div class="wpjb-grid-col wpjb-col-60">
        <# if ( data.input_type == "select" ) { #>
        <select name="{{ data.input_name }}" id="{{ data.input_name }}">
            <# if ( data.input_name != "job_country" ) { #>
                <# for ( var i in data.options) { #>
                    <# if( data.options[i].value == data.value) { #>
                        <option value="{{ data.options[i].value }}" selected="selected">{{ data.options[i].desc }}</option>    
                    <# } else { #>
                        <option value="{{ data.options[i].value }}">{{ data.options[i].desc }}</option>    
                    <# } #>
                <# } #>
            <# } else { #>
                <# var def = "<?php echo wpjb_locale(); ?>"; #>
                <# for ( var i in data.options) { #>
                    <# if( data.options[i].value == def) { #>
                        <option value="{{ data.options[i].value }}" selected="selected">{{ data.options[i].desc }}</option>    
                    <# } else { #>
                        <option value="{{ data.options[i].value }}">{{ data.options[i].desc }}</option>    
                    <# } #>
                <# } #>
            <# } #>
        </select>
        <# } else if ( data.input_type == "radio" || data.input_type == "checkbox" ) { #>
        <ul class="wpjb-options-list">
            <# for ( var i in data.options) { #>
            <li class='wpjb-input-cols wpjb-input-cols-1'>
                <# var value_arr = []; #>
                <# if( data.value ) { #>
                <# value_arr = data.value.split(", ") #>
                <# } #>
                <# if( jQuery.inArray( data.options[i].value, value_arr) !== -1 ) { #> 
                    <input type="{{ data.input_type }}" name="{{ data.input_name }}[]" id="{{ data.input_name }}" value="{{ data.options[i].value }}" checked="checked" /> 
                <# } else { #>
                    <input type="{{ data.input_type }}" name="{{ data.input_name }}[]" id="{{ data.input_name }}" value="{{ data.options[i].value }}" /> 
                <# } #>
                {{ data.options[i].desc }} 
            </li>
            <# } #>
        </ul>
        <# } else { #>
            <input type="{{ data.input_type }}" name="{{ data.input_name }}" id="{{ data.input_name }}" value="{{ data.value }}" />
        <# } #>
        </div>
        <div class="wpjb-grid-col wpjb-col-10 wpjb-column-right" style="text-align: right;">
            <a href="" class="wpjb-button wpjb-remove-alert-param wpjb-glyphs wpjb-icon-trash"></a>
        </div>
    </script>

    <?php
    
    $alerts = wpjb_get_alerts( wp_get_current_user()->ID );
    
    ?>
    <script type="text/javascript">
    jQuery(function($) {
        WPJB.alert.set_alerts(<?php echo json_encode($alerts) ?>, <?php echo json_encode($fields) ?>);
    });
    </script>
    <?php
    
}

?>
