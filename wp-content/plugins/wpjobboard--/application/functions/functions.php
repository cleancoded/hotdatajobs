<?php
/* 
 * General Template Functions
 */

function wpjb_conf($param, $default = null) {
    return Wpjb_Project::getInstance()->conf($param, $default);
}

/**
 * Returns a link to one of WPJB pages
 * 
 * @param string                $key        Route key (from application/config/frontend-routes.ini file)
 * @param Daq_Db_OrmAbstract    $object     Object for which the link will be created
 * @param array                 $param      Additional $_GET params that will be added to the URL
 * @param int                   $forced_id  ID of a Page 
 * @return string
 */
function wpjb_link_to($key, $object = null, $param = array(), $forced_id = null) {
    $instance = Wpjb_Project::getInstance();
    $isSingle = in_array($key, array("job", "company"));

    if($isSingle) {
        $url = get_permalink($object->post_id);
        
        if(!empty($param)) {
            $glue = stripos($url, "?") ? "&" : "?";
            $url = $url . $glue . http_build_query($param);
        }
    } else {
        $rewrite = $instance->env("rewrite");
        $page = $rewrite->getDefaultPageFor($key, "frontend");
        $url = $rewrite->linkTo($key, $object, $param, $forced_id ? $forced_id : $page);
    } 
    
    return apply_filters("wpjb_link_to", $url, array("key"=>$key, "object"=>$object, "param"=>$param));
}

/**
 * Returns URL to main [wpjb_jobs_list]
 * 
 * @return string   URL to main [wpjb_jobs_list]
 */
function wpjb_url() {
    return get_permalink(wpjb_conf("urls_link_job"));
}

function wpjb_is_routed_to($path, $module = "frontend") {
    $routed = Wpjb_Project::getInstance()->rewrite->isRoutedTo($path, $module);
    return apply_filters("wpjb_is_routed_to", $routed, $path, $module);
}

/**
 * Returns session object
 * 
 * @since 4.4.7
 * @return Wpjb_Session_Interface   Returns session object
 */
function wpjb_session() {
    return Wpjb_Project::getInstance()->env("session");
}

function wpjb_find_jobs(array $options = null) {
    return Wpjb_Model_JobSearch::search($options);
}

function wpjb_find_resumes(array $options = null) {
    return Wpjb_Model_ResumeSearch::search($options);
}

/**
 * 
 * @deprecated 5.0
 * @param string $date_format
 * @param Wpjb_Model_Job $job
 * @return string
 */
function wpjb_job_created_at($date_format, $job) {
    _deprecated_function(__FUNCTION__, "5.0");
    return wpjb_date_display($date_format, $job->job_created_at);
}

/**
 * 
 * @deprecated 5.0
 * @param string $param
 * @param mixed $value
 */
function wpjb_view($param, $default = null) {
    _deprecated_function(__FUNCTION__, "5.0");
    
    if($param == "job") {
        if(!is_singular("job")) {
            $form = new Wpjb_Form_AddJob();
            $id = "wpjb_session_".str_replace("-", "_", wpjb_transient_id());
            $transient = wpjb_session()->get($id);
            $jobArr = $transient["job"];
            $form->isValid($jobArr);

            return $form->buildModel();
        } else {
            return wpjb_get_object_from_post_id(get_the_ID(), "job");
        }
    } else if($param == "current_step") {
        if(wpjb_is_routed_to("step_preview")) {
            return 2;
        } else if(wpjb_is_routed_to("step_save")) {
            return 3;
        } else {
            return 1;
        }
    } else {
        return null;
    }
}

/**
 * 
 * @deprecated 5.0
 * @param string $param
 * @param mixed $value
 */
function wpjb_view_set($param, $value) {
    _deprecated_function(__FUNCTION__, "5.0");
}

/**
 * 
 * @deprecated 5.0
 * @return boolean
 */
function wpjb_user_can_post_job() {
    _deprecated_function(__FUNCTION__, "5.0");
    return true;
}

/**
 * 
 * @deprecated 5.0
 */
function wpjb_job_template() {
    _deprecated_function(__FUNCTION__, "5.0");
    $view = new Wpjb_Shortcode_Dynamic();
    $view->view = new stdClass();
    
    echo $view->render("job-board", "job");
}

/**
 * @deprecated 5.0
 */
function wpjb_add_job_steps() {
    _deprecated_function(__FUNCTION__, "5.0");
    $view = new Wpjb_Shortcode_Dynamic();
    echo $view->render("job-board", "step");
}

function wpjb_api_url($action, $param = null) {
    global $wp_rewrite;
    
    /* @var $wp_rewrite WP_Rewrite */
    
    if($wp_rewrite->using_permalinks()) {

        $url = home_url()."/wpjobboard/".trim($action, "/")."/";
    
        if(!empty($param)) {
            $url .= "?".http_build_query($param);
        }
        
    } else {
        
        $url = home_url()."?wpjobboard=".urlencode(trim($action, "/")."/");
        
        if(!empty($param)) {
            $url .= "&".http_build_query($param);
        }
    }
    
    return apply_filters("wpjb_api_url", $url, $action, $param);
}

function wpjb_paginate_links($url, $count, $page, $query = null, $format = null)
{
    $instance = Wpjb_Project::getInstance();
    $glue = "?";
    if(stripos($url.$format, "?")) {
        $glue = "&";
    }

    if($format === null) {
        $format = $glue.'pg=%#%';
    }
    
    if(is_front_page() && !get_option('permalink_structure')) {
        $format = "?page=%#%";
    }
    
    if(empty($query)) {
        $query = "";
    } elseif(is_array($query)) {
        $query = $glue.http_build_query($query);
    } elseif(is_string($query)) {
        $query = $glue.$query;
    }
    
    if ( get_option('permalink_structure') ) {
        $base = rtrim($url, "/")."/%_%".$query;
    } else {
        $base = rtrim($url, "/")."%_%".$query;
    }
    
    echo paginate_links( array(
        'base' => $base,
        'format' => $format,
        'prev_text' => '<span class="wpjb-glyphs wpjb-icon-left-open"></span>',
        'next_text' => '<span class="wpjb-glyphs wpjb-icon-right-open"></span>',
        'total' => $count,
        'current' => $page,
        'add_args' => false
    ));
}

function wpjb_flash()
{
    if(is_object(Wpjb_Project::getInstance()->placeHolder)) {
        $flash = Wpjb_Project::getInstance()->placeHolder->_flash;
    } else {
        $flash = new Wpjb_Utility_Session();
    }
    
    foreach($flash->getInfo() as $info):
    ?>
    <div class="wpjb-flash-info">
        <span class="wpjb-glyphs <?php esc_attr_e($flash->getInfoIcon()) ?>"><?php echo apply_filters( "wpjb_flash_message", $info, "info" ); ?></span>
    </div>
    <?php
    endforeach;

    foreach($flash->getError() as $error):
    ?>
    <div class="wpjb-flash-error">
        <span class="wpjb-glyphs <?php esc_attr_e($flash->getErrorIcon()) ?>"><?php echo apply_filters( "wpjb_flash_message", $error, "error" ); ?></span>
    </div>
    <?php
    endforeach;
    
    $flash->dispose();
    $flash->save();

}

function wpjb_breadcrumbs($breadcrumbs) {
    $content = "";
    
    foreach($breadcrumbs as $crumb) {

        $glyph = $crumb["glyph"];
        $span = new Daq_Helper_Html("span", array("class"=>"wpjb-glyphs $glyph"), "");
        if(!isset($crumb["url"]) || empty($crumb["url"])) {
            $link = new Daq_Helper_Html("span", array(), $crumb["title"]);
        } else {
            $link = new Daq_Helper_Html("a", array("href"=>$crumb["url"]), $crumb["title"]);
        }
        
        $span->forceLongClosing(true);
        $link->forceLongClosing(true);
        
        $content .= $span->render()." ".$link->render();
    }
    
    $bc = new Daq_Helper_Html("div", array("class"=>"wpjb-breadcrumb"), $content);
    
    echo apply_filters("wpjb_breadcrumbs", $bc->render(), $breadcrumbs);
}

function wpjb_date_display($format, $date, $relative = false) {
    
    $p = array(
        "format" => $format,
        "date" => $date,
        "relative" => $relative
    );
    
    extract(apply_filters("wpjb_date_display", $p));
    
    $time = time();
    $ptime = strtotime(date("Y-m-d H:i:s", $time))-strtotime(date("Y-m-d", $time));
    $ytime = strtotime("yesterday", $time)+$ptime+(get_option( 'gmt_offset' ) * HOUR_IN_SECONDS);
    $jtime = strtotime($date)+$ptime+(get_option( 'gmt_offset' ) * HOUR_IN_SECONDS);
    
    if($relative && date_i18n("Y-m-d", $time) == date_i18n("Y-m-d", $jtime)) {
        return __("Today", "wpjobboard");
    } elseif($relative && date_i18n("Y-m-d", $time) == date_i18n("Y-m-d", $ytime)) {
        return __("Yesterday", "wpjobboard");
    } else {
        return date_i18n($format, $jtime);
    }
}



function wpjb_time_ago($date, $format = "{time_ago}")
{
    if(!is_numeric($date)) {
        $date = strtotime($date);
    }
    
    echo str_replace(
        array("{time_ago}", "{date}"),
        array(
            daq_time_ago_in_words($date),
            date("Y-m-d")),
        $format
    );
}

function wpjb_job_features(Wpjb_Model_Job $job = null)
{
    if(!$job) {
       return;
    }
    
    if($job->is_featured) {
        echo " wpjb-featured";
    }
    if($job->is_filled) {
        echo " wpjb-filled";
    }
    if($job->isNew()) {
        echo " wpjb-new";
    }
    if($job->isFree()) {
        echo " wpjb-free";
    }
    if(isset($job->tag->type) && is_array($job->tag->type)) {
        foreach($job->tag->type as $type) {
            echo " wpjb-type-".$type->slug;
        }
    }
    if(isset($job->tag->category) && is_array($job->tag->category)) {
        foreach($job->tag->category as $category) {
            echo " wpjb-category-".$category->slug;
        }
    }
}

function wpjb_panel_features(Wpjb_Model_Job $job) 
{
    if($job->expired()) {
        echo " wpjb-expired";
    } elseif(time()-strtotime($job->job_expires_at) > 24*3600*3) {
        echo " wpjb-expiring";
    }
}

function wpjb_job_company(Wpjb_Model_Job $job = null)
{
    $company = esc_html($job->company_name);
    if(strlen($job->company_url) > 0) {
        $url = esc_html($job->company_url);
        echo '<a href="'.$url.'" class="wpjb-job-company">'.$company.'</a>';
    } else {
        echo $company;
    }
}

function wpjb_job_company_profile($company, $text = null)
{
    /* @var $company Wpjb_Model_Company */

    if(!$company instanceof Wpjb_Model_Company) {
        return;
    }

    if(!$company->hasActiveProfile()) {
        return;
    }

    $link = wpjb_link_to("company", $company);

    if($text === null) {
        $text = __("view profile", "wpjobboard");
    }

    echo " (<a href=\"".esc_attr($link)."\">".esc_html($text)."</a>)";

}

/**
 * Add Job Form
 */

function wpjb_form_render_hidden($form)
{
    /* @var $form Daq_Form_Abstract */
    echo $form->renderHidden();
}

function wpjb_form_render_input(Daq_Form_Abstract $form, Daq_Form_Element $input)
{   
    if($input->hasRenderer()) {
        $callback = $input->getRenderer();
        call_user_func($callback, $input, $form);
    } else {
        echo $input->render();
    }
}

function wpjb_form_input_features(Daq_Form_Element $e)
{
    $cl = array();
    if(count($e->getErrors())>0) {
        $cl[] = "wpjb-error";
    }
    
    $cl[] = "wpjb-element-".$e->getTypeTag();
    $cl[] = "wpjb-element-name-".$e->getName();
    
    echo join(" ", $cl);
}

function wpjb_form_input_hint(Daq_Form_Element $e, $tag = "span", $class = "wpjb-hint")
{
    $hint = $e->getHint();
    if(!empty($hint)) {
        $hint = esc_html($hint); 
        echo "<$tag class=\"$class\">$hint</$tag>";
    }
}

function wpjb_form_input_errors(Daq_Form_Element $e, $wrap1 = "ul", $wrap2 = "li")
{
    $err = $e->getErrors();

    if(count($err) == 0) {
        return null;
    }

    $html = "";
    if($wrap1) {
        $html .= "<".$wrap1." class=\"wpjb-errors\">";
    }
    foreach($err as $e) {
        if($wrap2) {
            $html .= "<$wrap2>".esc_html($e)."</$wrap2>";
        } else {
            $html .= esc_html($e);
        }
    }
    if($wrap1) {
        $html .= "</$wrap1>";
    }
    echo $html;
}

function wpjb_form_input_classes()
{
    $class = array();
    if(wpjb_form_input_errors()) {
        $class[] = "wpjb_error";
    }

    $input = wpjb_form_element();
    $class[] = "wpjb-".$input->getTypeTag();

    return join(" ", $class);
}



// resumes functions

/**
 * Returns a link to one of WPJB pages
 * 
 * @param string                $key        Route key (from application/config/resumes-routes.ini file)
 * @param Daq_Db_OrmAbstract    $object     Object for which the link will be created
 * @param array                 $param      Additional $_GET params that will be added to the URL
 * @param int                   $forced_id  ID of a Page 
 * @return string
 */
function wpjr_link_to($key, $object = null, $param = array(), $forced_id = null) {
    $instance = Wpjb_Project::getInstance();
    $isSingle = in_array($key, array("resume"));

    if($isSingle) {
        $url = get_permalink($object->post_id);
        
        if(!empty($param)) {
            $glue = stripos($url, "?") ? "&" : "?";
            $url = $url . $glue . http_build_query($param);
        }
    } else {
        $rewrite = $instance->env("rewrite");
        $page = $rewrite->getDefaultPageFor($key, "resumes");
        $url = $rewrite->linkTo($key, $object, $param, $forced_id ? $forced_id : $page);
    } 

    return apply_filters("wpjr_link_to", $url, array("key"=>$key, "object"=>$object, "param"=>$param));
}

function wpjb_block_resume_details()
{
    $basedir = basename(Wpjb_Project::getInstance()->getBaseDir());

    $img = new Daq_Helper_Html("img", array(
        "alt" => "lock",
        "src" => plugins_url("$basedir/public/images/icon-padlock.gif")
    ));
    
    $m  = $img->render()." ";
    $m .= __("<i>locked</i>", "wpjobboard");
    
    return $m;
}

function wpjb_resume_degree($resume)
{
    $d = Wpjb_Form_Admin_Resume::getDegrees();
    echo $d[$resume->degree];
}

function wpjb_resume_experience($resume)
{
    $d = Wpjb_Form_Admin_Resume::getExperience();
    echo $d[$resume->years_experience];
}

function wpjr_url()
{
    $instance = Wpjb_Project::getInstance();

    if($instance->conf("urls_mode") == 2) {
        return get_permalink(wpjb_conf("urls_link_resume"));
    } else {
        return Wpjb_Project::getInstance()->getApplication("resumes")->getUrl();
    }
}

function is_wpjb()
{
    $valid = array("wpjb_alerts", "wpjb_apply_form", "wpjb_jobs_add", "wpjb_jobs_list", "wpjb_jobs_search", "wpjb_employer_panel");
    $instance = Wpjb_Project::getInstance();
    $shortcode = $instance->env("doing_shortcode");

    if(is_page($instance->conf("link_jobs")) || ($shortcode && in_array($shortcode, $valid))) {
        return true;
    } else {
        return false;
    }
    
}

function is_wpjr()
{
    $valid = array("wpjb_resumes_list", "wpjb_resumes_search", "wpjb_candidate_panel", "wpjb_candidate_register");
    $instance = Wpjb_Project::getInstance();
    $shortcode = $instance->env("doing_shortcode");

    if(is_page($instance->conf("link_resumes")) || ($shortcode && in_array($shortcode, $valid))) {
        return true;
    } else {
        return false;
    }
}

function wpjb_hide_scroll_hash() {
    ?>

    <script type="text/javascript">wpjb_hide_scroll_hash();</script>

    <?php
}

/**
 * Returns resume status based on is_active propery.
 * 
 * @param Wpjb_Model_Resume $resume
 * @return string Resume status
 */
function wpjb_resume_status($resume) {
    $object = $resume;

    if($object->is_active == 1) {
        return __("Approved", "wpjobboard");
    } else {
        return __("Pending approval", "wpjobboard");
    }
}

function wpjb_rich_text($text, $format = "text") {
    $allowed = wp_kses_allowed_html();
    $custom = array(
        "div", "ol", "ul", "li", "p", "span", "br", 
        "h1", "h2", "h3", "h4", "h5", "h6", "img",
        "ins", "pre"
    );
    
    $allowed += array_fill_keys($custom, array("style"=>1));
    $allowed["img"] = array("title"=>1, "src"=>1, "alt"=>1);

    $allowed = apply_filters("wpjb_rich_text_kses_allowed_html", $allowed);
    
    if($format == "html") {
        $text = wpautop($text);
        $text = wp_kses($text, $allowed);
    } else {
        $text = nl2br(esc_html($text));
    }

    echo $text;
}


function wpjb_format_bytes($size) {
    $units = array(' bytes', ' kB', ' MB', ' GB', ' TB');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) {
        $size /= 1024;
    }
    return round($size, 2).$units[$i];
}

function wpjb_get_categories($options = null) {
    
    return Wpjb_Utility_Registry::getCategories();
}

function wpjb_get_jobtypes($options = null) {
    
    return Wpjb_Utility_Registry::getJobTypes();
}

/**
 * FORM HELPERS
 */


function wpjb_locale() {
    $cc = "";
    $locale = explode("_", get_locale());
    $lang = $locale[0];
    
    if(isset($locale[1])) {
        $cc = $locale[1];
    }
    
    $country = Wpjb_List_Country::getAll();
    if(isset($country[$cc])) {
        $r = $country[$cc]["code"];
    } else {
        $r = 840;
    }
    
    $r = apply_filters("wpjb_locale", $r);
    
    return $r;
}

function wpjb_recaptcha_form() {

    $captchaType = wpjb_conf("front_recaptcha_type", "re-captcha");
    
    if($captchaType == "re-captcha") {
        if(!function_exists("recaptcha_get_html")) {
            $rc = "/application/vendor/recaptcha/recaptchalib.php";
            $rc = Wpjb_Project::getInstance()->getBaseDir().$rc;
            require_once $rc;
        }
        echo '<style type="text/css">#recaptcha_widget_div div { padding: 0px; margin: 0px }</style>';
        echo recaptcha_get_html(Wpjb_Project::getInstance()->getConfig("front_recaptcha_public"), null, is_ssl());
    } else {
        $key = Wpjb_Project::getInstance()->getConfig("front_recaptcha_public");
        $html = new Daq_Helper_Html("div", array(
            "class" => "g-recaptcha",
            "data-sitekey" => wpjb_conf("front_recaptcha_public"),
            "data-theme" => wpjb_conf("front_recaptcha_theme", "light"),
            "data-type" => wpjb_conf("front_recaptcha_media", "image"),
        ));
        $html->forceLongClosing(true);
        
        echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
        echo $html->render();
        echo '<input type="hidden" name="recaptcha_response_field" value="1" />';
    }

}

function wpjb_recaptcha_check() {
    
    if(wpjb_conf("front_recaptcha_type", "re-captcha") == "re-captcha") {
    
        if(!function_exists("recaptcha_get_html")) {
            $rc = "/application/vendor/recaptcha/recaptchalib.php";
            include_once Wpjb_Project::getInstance()->getBaseDir().$rc;
        }

        $recaptcha_challenge_field = null;
        if(isset($_POST["recaptcha_challenge_field"])) {
            $recaptcha_challenge_field = $_POST["recaptcha_challenge_field"];
        }

        $recaptcha_response_field = null;
        if(isset($_POST["recaptcha_response_field"])) {
            $recaptcha_response_field = $_POST["recaptcha_response_field"];
        }

        $resp = recaptcha_check_answer (
            Wpjb_Project::getInstance()->getConfig("front_recaptcha_private"),
            $_SERVER["REMOTE_ADDR"],
            $recaptcha_challenge_field,
            $recaptcha_response_field
        );

        if (!$resp->is_valid) {
            return $resp->error;
        } else {
            return true;
        }
        
    } else {
        
        $query = array(
            "secret" => wpjb_conf("front_recaptcha_private"),
            "response" => $_POST["g-recaptcha-response"],
            "remoteip" => $_SERVER["REMOTE_ADDR"]
        );
        
        $response = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?".http_build_query($query));
        
        if(is_wp_error($response)) {
            return $response->get_error_message();
        } 
        
        $data = json_decode($response["body"]);
        
        if($data->success) {
            return true;
        }
        
        $ec = 'error-codes';
        
        $errors = array(
            "missing-input-secret" => __("The secret parameter is missing.", "wpjobboard"),
            "invalid-input-secret" => __("The secret parameter is invalid or malformed.", "wpjobboard"),
            "missing-input-response" => __("The response parameter is missing.", "wpjobboard"),
            "invalid-input-response" => __("The response parameter is invalid or malformed.", "wpjobboard"),
        );
        
        foreach($errors as $key => $err) {
            if(isset($data->$ec) && in_array($key, $data->$ec)) {
                return $err; 
            }
        }
        
        return null;
    }

}

function wpjb_form_helper_listing(Daq_Form_Element $field, $form)
{
    $group_titles = array();
    $groups = array(
        Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP => array("item"=>array(), "title"=>__("Purchased Membership", "wpjobboard")),
        Wpjb_Model_Pricing::PRICE_SINGLE_JOB => array("item"=>array(), "title"=>__("Single Job Posting", "wpjobboard")),
        
    );
    foreach($field->getOptions() as $o) {
        list($price_for, $package_id, $id) = explode("_", $o["value"]);
        
        $groups[$price_for]["item"][] = $o;
        $group_titles[$price_for] = 1;
    }
    
    $group_titles = array_sum($group_titles)>1 ? true : false;
    
    foreach($groups as $k => $group) {
        
        if($group_titles && !empty($group["item"])) {
            echo "<div class='wpjb-listing-group'>".$group["title"]."</div>";
        }
        
        foreach($group["item"] as $option) {

            $lid = $option["value"];
            
            list($price_for, $membership_id, $id) = explode("_", $lid);
            
            if($membership_id > 0) {
                $membership = new Wpjb_Model_Membership($membership_id);
                $usage = $membership->package();
                $usage = $usage[Wpjb_Model_Pricing::PRICE_SINGLE_JOB];
                foreach($usage as $k => $use) {

                    if($k == $id) {
                        $u = $use;
                    }
                    
                    if($k == $id && $use["status"] == "limited") {
                        $credits = $use["usage"]-$use["used"];
                        break;
                    }
                }
            } else {
                $membership = null;
            }
            
            $l = new Wpjb_Model_Pricing($id);
        ?>
    
    <label class="wpjb-listing-type-x">
        <div class="wpjb-listing-type wpjb-listing-radio">
            <label class="wpjb-cute-input wpjb-cute-radio ">
                <input name="listing" class="wpjb-listing-type-input" id="listing_<?php echo $lid ?>" type="radio" value="<?php echo $lid ?>" <?php checked($field->getValue()==$lid) ?> />
                <div class="wpjb-cute-input-indicator"></div>
            </label>
        </div>
        <div class="wpjb-listing-type">
            <div class="wpjb-listing-type-name">
                <span class="wpjb-listing-type-title"><?php esc_html_e($option["desc"]) ?></span>
                <span class="wpjb-listing-type-cost wpjb-motif-bg">
                    <?php if($membership && $u["status"] == "limited"): ?>
                    <?php printf(_n("(1 ad left)", "(%d ads left)", $credits, "wpjobboard"), $credits) ?>
                    <?php elseif($membership && $u["status"] == "unlimited"): ?>
                    <?php _e("(Unlimited)", "wpjobboard") ?>
                    <?php elseif(!$membership): ?>
                    <?php esc_html_e(wpjb_price($l->price, $l->currency)) ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="wpjb-listing-type-features">
                <span class="wpjb-listing-type-feature-duration">
                    <span class="wpjb-glyphs wpjb-icon-clock"></span>
                    <?php $visible = (int)$l->meta->visible->value(); ?>
                    <?php if($visible > 0): ?>
                        <?php printf( _n("Visible 1 day", "Visible %d days", $visible, "wpjobboard"), $visible) ?>
                    <?php else: ?>
                        <?php _e("Never Expires", "wpjobboard"); ?>
                    <?php endif; ?>
                </span>
                <?php if($l->meta->is_featured->value()): ?>
                <span class="wpjb-listing-type-feature-featured wpjb-listing-border">
                    <span class="wpjb-glyphs wpjb-icon-flag">
                        <?php _e("Featured", "wpjobboard") ?>
                    </span>
                </span>
                <?php endif; ?>
                
                <?php do_action("wpjb_listing_helper_features", $l) ?>
            </div>
        </div>
        
    </label>
   

        <?php 

        }
    }
}


function wpjb_form_helper_membership(Daq_Form_Element $field, $form)
{
    $group_titles = array();
    $groups = array(
        Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP => array("item"=>array(), "title"=>__("Purchased Membership", "wpjobboard")),
        Wpjb_Model_Pricing::PRICE_SINGLE_JOB => array("item"=>array(), "title"=>__("Single Job Posting", "wpjobboard")),
        
    );
    foreach($field->getOptions() as $o) {
        list($price_for, $package_id, $id) = explode("_", $o["value"]);
        
        $groups[$price_for]["item"][] = $o;
        $group_titles[$price_for] = 1;
    }
    
    $group_titles = array_sum($group_titles)>1 ? true : false;
    
    foreach($groups as $k => $group) {
        
        if($group_titles && !empty($group["item"])) {
            echo "<em class=wpjb-listing-group>".$group["title"]."</em>";
        }
        
        foreach($group["item"] as $option) {

            $lid = $option["value"];
            
            list($price_for, $membership_id, $id) = explode("_", $lid);
            
            if($membership_id > 0) {
                $membership = new Wpjb_Model_Membership($membership_id);
                $usage = $membership->package();
                $usage = $usage[Wpjb_Model_Pricing::PRICE_SINGLE_JOB];
                foreach($usage as $k => $use) {

                    if($k == $id) {
                        $u = $use;
                    }
                    
                    if($k == $id && $use["status"] == "limited") {
                        $credits = $use["usage"]-$use["used"];
                        break;
                    }
                }
            } else {
                $membership = null;
            }
            
            $l = new Wpjb_Model_Pricing($id);
        ?>
            <label class="wpjb-listing-type-item" for="listing_<?php echo $lid ?>">
                <input name="listing" class="wpjb-listing-type-input" id="listing_<?php echo $lid ?>" type="radio" value="<?php echo $lid ?>" <?php checked($field->getValue()==$lid) ?> <?php //disabled($credits<1) ?> />
                <span class="wpjb-listing-type-item-s1"><?php esc_html_e($option["desc"]) ?></span>
                <span class="wpjb-listing-type-item-s2">
                    <?php if($membership && $u["status"] == "limited"): ?>
                    <?php printf(_n("(1 posting left)", "(%d postings left)", $credits, "wpjobboard"), $credits) ?>
                    <?php elseif(!$membership): ?>
                    <?php esc_html_e(wpjb_price($l->price, $l->currency)) ?>
                    <?php endif; ?>
                </span>

            </label>    

        <?php 

        }
    }
}

function wpjb_form_helper_cute_checkboxes($field, $form) {
    
    ?>
    <ul>
        <?php foreach($field->getOptions() as $jt): ?>
            <li>
    
                <label for="<?php esc_attr_e("wpjb-search-".$jt["value"]) ?>" class="wpjb-cute-input wpjb-cute-checkbox ">
                    <input type="checkbox" class="wpjb-ls-type" name="<?php echo esc_html($field->getName()) ?>[]" value="<?php echo esc_attr($jt["value"]) ?>" id="<?php esc_attr_e("wpjb-search-".$jt["key"]) ?>" <?php checked(in_array($jt["value"], (array)$field->getValue())) ?> /> 
                    <div class="wpjb-cute-input-indicator"></div>
                    <?php echo esc_html_e($jt["desc"]) ?>
                </label>
                

      
            </li>
        <?php endforeach; ?>
    </ul>
    <?php 
}

function wpjb_form_helper_resume_listing(Daq_Form_Element $field, $form)
{
    $group_titles = array();
    $groups = array(
        Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP => array("item"=>array(), "title"=>__("Purchased Membership", "wpjobboard")),
        Wpjb_Model_Pricing::PRICE_SINGLE_RESUME => array("item"=>array(), "title"=>__("Single Resume Access", "wpjobboard")),
        
    );
    foreach($field->getOptions() as $o) {
        list($price_for, $package_id, $id) = explode("_", $o["value"]);
        
        $groups[$price_for]["item"][] = $o;
        $group_titles[$price_for] = 1;
    }
    
    $group_titles = array_sum($group_titles)>1 ? true : false;
    
    foreach($groups as $k => $group) {
        
        if($group_titles && !empty($group["item"])) {
            echo "<em class=wpjb-listing-group>".$group["title"]."</em>";
        }
        
        foreach($group["item"] as $option) {

            $lid = $option["value"];
            
            list($price_for, $membership_id, $id) = explode("_", $lid);
            
            if($membership_id > 0) {
                $membership = new Wpjb_Model_Membership($membership_id);
                $usage = $membership->package();
                $usage = $usage[Wpjb_Model_Pricing::PRICE_SINGLE_RESUME];
                foreach($usage as $k => $use) {

                    if($k == $id) {
                        $u = $use;
                    }
                    
                    if($k == $id && $use["status"] == "limited") {
                        $credits = $use["usage"]-$use["used"];
                        break;
                    }
                }
            } else {
                $membership = null;
            }
            
            $l = new Wpjb_Model_Pricing($id);
        ?>
            <label class="wpjb-listing-type-item" for="listing_<?php echo $lid ?>">
                <input name="<?php esc_attr_e($field->getName()) ?>" class="wpjr-listing-type-input <?php if(!$membership && $l->price>0): ?>wpjr-payment-required<?php endif; ?>" id="listing_<?php echo $lid ?>" type="radio" value="<?php echo $lid ?>" <?php checked($field->getValue()==$lid) ?> <?php //disabled($credits<1) ?> />
                <span class="wpjb-listing-type-item-s1"><?php esc_html_e($option["desc"]) ?></span>
                <span class="wpjb-listing-type-item-s2">
                    <?php if($membership && $u["status"] == "limited"): ?>
                    <?php printf(_n("(1 resume left)", "(%d resumes left)", $credits, "wpjobboard"), $credits) ?>
                    <?php elseif(!$membership): ?>
                    <?php esc_html_e(wpjb_price($l->price, $l->currency)) ?>
                    <?php endif; ?>
                </span>
            </label>    

        <?php 

        }
    }
}

function wpjb_dropdown_pages($e) {
    $args = array(
        'selected' => $e->getValue(), 
        'echo' => 1,
	'name' => $e->getName(), 
        'id' => $e->getName(),
	'show_option_none' => ' ',
        'option_none_value' => 0
    );
    
    wp_dropdown_pages( $args );
}

/**
 * Applies membership to User
 * 
 * This function applies trial membership(s) defined in wp-admin / Settings (WPJB) 
 * / Pricing / Employer Membership panel to user with id $user_id.
 * 
 * @since 4.4.4
 * @param int $user_id      User ID (to whom trial will be applied)
 * @return array            Array of applied Membership IDs
 */
function wpjb_apply_trial($user_id) {
    
    $query = new Daq_Db_Query();
    $query->from("Wpjb_Model_Meta m");
    $query->select("v.object_id AS pricing_id");
    $query->join("m.value v");
    $query->where("m.meta_object = ?", "pricing");
    $query->where("m.name = ?", "is_trial");
    $query->where("v.value = ?", "1");
    $result = $query->fetchAll();

    $applied = array();
    
    foreach($result as $pr) {
        // Apply trial membership.
        $pricing = new Wpjb_Model_Pricing($pr->pricing_id);

        $member = new Wpjb_Model_Membership();
        $member->user_id = $user_id;
        $member->package_id = $pricing->id;
        $member->started_at = "0000-00-00";
        $member->expires_at = "0000-00-00";
        $member->deriveFrom($pricing);
        $member->paymentAccepted();
        $member->save();
        
        $applied[] = $member->id;
    } 
    
    return $applied;
}

function wpjb_mobile_notification_jobs(Wpjb_Model_Job $job) {
    
    $googleApiKey = wpjb_conf("google_api_key");
    $ids = array();
    
    $query = new Daq_Db_Query;
    $query->from("Wpjb_Model_Alert t");
    $query->where("user_id IS NOT NULL");
    $query->where("frequency = 0");
    
    $list = $query->execute();
    
    foreach($list as $alert) {
        
        $params = unserialize($alert->params);
        $params["query"] = $params["keyword"];
        $params["id"] = $job->id;
        $params["count_only"] = true;
        
        $jobs = wpjb_find_jobs($params);

        if($jobs == 1) {
            
            $mobile = get_user_meta($alert->user_id, "wpjb_mobile_device", true);
            
            foreach($mobile->device as $device) {
                if($device["mobile_os"] == "android") {
                    
                    $ids[] = $device["mobile_id"];
                    
                    $alert->last_run = date_i18n("Y-m-d H:i:s");
                    $alert->save();
                } // endif;
            } // endforeach;
        } // endif;
    }

    if(empty($ids)) {
        return;
    }

    // prep the bundle
    $msg = array (
        'message' 	=> 'here is a message. message',
        'title'		=> 'This is a title. title',
        'subtitle'	=> 'This is a subtitle. subtitle',
        'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
        'vibrate'	=> 0,
        'sound'		=> 0,
        'largeIcon'	=> 'large_icon',
        'smallIcon'	=> 'small_icon'
    );

    $fields = array(
        'registration_ids' 	=> $ids,
        'data'			=> $msg
    );

    $headers = array(
        'Authorization' => 'key=' . $googleApiKey,
        'Content-Type' => 'application/json'
    );

    $response = wp_remote_post('https://android.googleapis.com/gcm/send', array(
        "headers" => $headers,
        "body" => json_encode($fields)
        
    ));
    
    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
       echo "Something went wrong: $error_message";
    } else {
       echo 'Response:<pre>';
       print_r( $response );
       echo '</pre>';
    }
    
    
    return;
    
    $ch = curl_init();
    curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
    curl_setopt( $ch,CURLOPT_POST, true );
    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
    $result = curl_exec($ch );
    curl_close( $ch );

    echo $result;
}

/**
 * Checks if user can browse resume details
 * 
 * @param int $id Wpjb_Model_Resume::$id
 * @return boolean True if user has access to resume
 */
function wpjr_can_browse($id = null) {
    
    $access = wpjb_conf("cv_access", 1);
    $hasPriv = false;
    $company = Wpjb_Model_Company::current();
    $candidate = Wpjb_Model_Resume::current();

    if($candidate && $candidate->id == $id) {
        // candidate can always see his resume
        // $this->view->can_browse = true;
        return true;
    }
    if($id && $company && $company->canViewResume($id)) {
        // this $company received at least one application from user who owns
        // resume identified with $id, and employers ability to browse full
        // applicants resumes is enabled
        return true;
    }
    if(current_user_can('manage_options')) {
        // admin can see anything
        return true;
    }

    if($access == 1) {
        // to all
        $hasPriv = true;
    } elseif($access == 2) {
        // registered members
        if(get_current_user_id()>0) {
            $hasPriv = true;
        }
    } elseif($access == 3) {
        // employers
        if(current_user_can("manage_jobs")) {
            $hasPriv = true;
        }
    } elseif($access == 4) {
        // employers verified
        if(current_user_can("manage_jobs") && $company && $company->is_verified == 1) {
            $hasPriv = true;
        }
    } elseif($access == 5) {
        // premium
        $hasPriv = wpjr_has_premium_access($id);
    } elseif($access == 6) {
        // Admin Only
        if( array_intersect( array( 'administrator' ), wp_get_current_user()->roles ) ) {
            $hasPriv = true;
        }
    }


    //$this->view->can_browse = $hasPriv;

    return $hasPriv;
}

/**
 * Returns an error message if current user does not have access to resume details
 * 
 * This function is executed when user does not have access to view resumes,
 * it will show an error if
 * - "Resume Privacy" is set to "Hide Resumes List and Details"
 * - "Grant Resumess Access" set to value different then "To All"
 * 
 * @return mixed    Either NULL or string (error message)
 */
function wpjr_can_browse_err() {
    $c = (int)wpjb_conf("cv_privacy")."/".(int)wpjb_conf("cv_access");

    switch($c) {
        case "1/2": 
            return __("Only registered members can browse resumes.", "wpjobboard");
            break;
        case "1/3":
            return __("Only Employers can browse resumes.", "wpjobboard");
            break;
        case "1/4":
            $m = __('Only <strong>verified</strong> Employers can browse resumes. <a href="%s">Verify your account</a>.', "wpjobboard");
            return sprintf($m, wpjb_link_to("employer_verify"));
            break;
        case "1/5":
            return __("Resumes browsing requires premium access.", "wpjobboard");
            break;
        case "1/6":
            return __("Resume details visible only for employers with at least one application from this candidate.");
            break;
    }
    
    return null;
}

/**
 * Checks if current user has access to resume $id.
 * 
 * This function checks for valid membership and hash in order to determine if
 * current user has access to resume identified with $id.
 * 
 * @since 4.3.4
 * @param int $id Resume ID
 * @return boolean True if current user has access to resume $id.
 */
function wpjr_has_premium_access($id) {
    
    $request = Daq_Request::getInstance();
    $hash = $request->get("hash");
    $price_for_c = Wpjb_Model_Pricing::PRICE_SINGLE_RESUME;
    $mlist = array();

    if(Wpjb_Model_Company::current()) {
        $mlist = Wpjb_Model_Company::current()->membership();
    }

    foreach($mlist as $membership) {
        $package = new Wpjb_Model_Pricing($membership->package_id);
        $data = $membership->package();

        if(!isset($data[$price_for_c])) {
            continue;
        }

        foreach($data[$price_for_c] as $pid => $use) {

            $pricing = new Wpjb_Model_Pricing($pid);

            if(!$pricing->exists()) {
                continue;
            }

            if($use["status"] == "unlimited") {
                return true;
            }
        }
    }

    if(get_current_user_id() > 0) {
        $check = "[" . get_current_user_id() . "#";
    } elseif($request->get("hash_id") && $request->get("hash")) {
        $check = "[" . $request->get("hash_id") . "#" . $request->get("hash") . "]";
    }  else {
        $check = null;
    }
    
    if($check && $id) {
        $resume = new Wpjb_Model_Resume($id);
        $ak = $resume->meta->access_keys->value();

        if(stripos($ak, $check) !== false) {
            return true;
        }
    }
    
    // DEPRECATED: To remove on 2016-08-29
    if(get_current_user_id() > 0) {
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Payment t");
        $query->where("object_type = ?", Wpjb_Model_Payment::FOR_RESUMES);
        $query->where("object_id = ?", $id);
        $query->where("user_id = ?", get_current_user_id());
        $query->where("status = 2");
        $query->limit(1);

        $result = $query->execute();

        if(!empty($result)) {
            return true;
        }
    } elseif($hash) {

        // "{$payment->id}|{$payment->object_id}|{$payment->object_type}|{$payment->paid_at}";

        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Payment t");
        $query->where("MD5(CONCAT_WS('|', t.id, t.object_id, t.object_type, t.paid_at)) = ?", $hash);
        $query->where("status = 2");
        $query->limit(1);

        $result = $query->execute();

        if(!empty($result)) {
            return true;
        }
    }
    // END DEPRECATED

    return false;
}

/**
 * Get alerts for user with provided ID
 * 
 * @param int $user_id
 * @return Array
 */
function wpjb_get_alerts($user_id) {
    
    $q = Daq_Db_Query::create();
    $alerts = $q->select()->from("Wpjb_Model_Alert t")->where("user_id = ?", $user_id)->execute();
    
    $alerts_final = array();
    foreach($alerts as $key => $alert) {
        $a = new stdClass();
        $a->id = $alert->id;
        $a->email = $alert->email;
        $a->frequency = $alert->frequency;
        $a->last_run = $alert->last_run;
        $a->created_at = esc_html(wpjb_date($alert->created_at));
        $a->params = array();
        $a->params_count = 0;
        
        $alert_params = maybe_unserialize($alert->params);
        if( is_array( $alert_params ) && count( $alert_params ) > 0 ) {
           
            foreach($alert_params as $k => $vArr) {
                if(in_array($k, array("filter")) || empty($vArr)) { continue; }

                if( is_array( $vArr ) ) {
                    $v = join(", ", $vArr);
                } else {   
                    $v = $vArr;
                }

                $a->params[esc_html($k)] = $v;
                $a->params_count++;
            }
        }
        
        $f_alert = new stdClass();
        $f_alert->details = $a;
        $f_alert->view = 'wpjb-utpl-alert';
        $f_alert->form = 'Wpjb_Form_Alert';
        $f_alert->owner = 'wpjb-alerts-list';
        $f_alert->id = 'wpjb-alert-' . $a->id;
        $f_alert->errors = array();
        $f_alert->input = $a;
        $f_alert->key = $a->id;
        $f_alert->saved = true;
        $alerts_final[] = $f_alert;
    }
    
    return apply_filters( "wpjb_get_alerts", $alerts_final );
}

/**
 * Get all fields for alerts
 * 
 * @return Array
 */
function wpjb_get_all_fileds() {
    
    $form = new Wpjb_Form_Alert();
    
    $fields = array();
    $fields['default_fields'] = array();
    $fields['custom_fields'] = array();
    $job = new Wpjb_Model_Job();
    $allowed_fields = array('keyword', 'location', 'is_featured', 'employer_id', 'job_country', 'job_state', 'job_zip_code', 'job_city', 'category', 'type');
    foreach($form->get_job_subform()->getFields() as $key => $field) {
        
        if( $field->getType() == "file" ) {
            continue;
        }

        $f = array(
            'input_name'    => $field->getName(),
            'input_type'    => $field->getType(),
            'input_label'   => $field->getLabel(),
            'options'       => null,
        );
        if( in_array( $f['input_type'], array('select', 'radio', 'checkbox') ) ) {
            $f['options'] = $field->getOptions();
        }
        
        if( isset( $job->meta->{$field->getName()} ) ) {
            $fields['custom_fields'][$field->getName()] = array('value' => base64_encode(json_encode($f)), 'label' => $field->getLabel() );
        } elseif( in_array( $field->getName(), $allowed_fields ) ) {
            $fields['default_fields'][$field->getName()] = array('value' => base64_encode(json_encode($f)), 'label' => $field->getLabel() );
        }
    }

    usort($fields['default_fields'], function($a, $b) {
        return $a['label'] >= $b['label'];
    });

    usort($fields['custom_fields'], function($a, $b) {
        return $a['label'] >= $b['label'];
    });

    return apply_filters( "wpjb_alert_param_fields_list", $fields );
}

/**
 * Check if Candidate have access to provided page
 * 
 * @param int $page_id
 * @param int $resume_id
 * @return boolean
 */
function wpjb_candidate_have_access( $page_id, $resume_id = null ) {
    
    
    $restricted_pages = wpjb_conf( "cv_members_restricted_pages", array() );
    
    if($restricted_pages === null) {
        $restricted_pages = array();
    }
    
    if( !in_array( $page_id, $restricted_pages ) && !in_array( get_post_type( $page_id ), $restricted_pages ) ) {
        return true;
    }
    
    $access_config = wpjb_conf("cv_members_have_access");
    
    // Anyone has access
    if( $access_config == 0) {
        return true;
    }
    
    // Is Admin or Employer
    if( current_user_can("manage_jobs") || current_user_can( 'manage_options' ) ) {
        return true;
    }
    
    if( $resume_id == null ) {
        $resume = Wpjb_Model_Resume::current(); 
        if( $resume ) {
            $resume_id = $resume->id;
        }
    }
    
    
    // Registred candidates 
    if( $access_config == 1 && $resume_id > 0 ) {
        return true;
    } elseif( $access_config == 1 ) {
        return false;
    }
    
    //$is_premium = false;
    
    $query = new Daq_Db_Query();
    $query->from("Wpjb_Model_Pricing t");
    $query->where("price_for = ?", Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP);

    $result = $query->execute();
    
    if (!empty($result)) {
        $pages_with_access = array();
        foreach($result as $pricing) {
            $summary = Wpjb_Model_Membership::getPackageSummary($pricing->id, wpjb_get_current_user_id("candidate"));
            if( $pricing->is_active == 0 || !is_object($summary) ) {
                continue;
            }
            
            $tmp = explode( ",", $pricing->meta->have_access->value() );
            $pages_with_access = array_merge( $pages_with_access, $tmp );
        }
    }  
    
    // Premium members
    if( $access_config == 2 && ( in_array( $page_id, $pages_with_access ) || in_array( get_post_type( $page_id ), $pages_with_access ) ) ) {
        return true;
    }
    
    return false;  
}

/**
 * Returns package of Candidate Membership
 * 
 * @param Wpjb_Model_Resume $resume
 * @return boolean
 */
function wpjb_candidate_membership_package( $resume = null ) {
    
    if( $resume == null ) {
        $resume = Wpjb_Model_Resume::current(); 
    }
    
    if( !$resume ) {
        return false;
    }
    
    $values = array(
        'featured_level' => 0,
        'is_searchable' => 1,
        'can_apply'     => 1,
    );
    
    
    if( wpjb_conf( "cv_members_are_searchable", 0 ) == 1 ) {
        $values['is_searchable'] = 0;
    }
    
    if( wpjb_conf( "cv_members_can_apply", 0 ) == 1 ) {
        $values['can_apply'] = 0;
    }
    
    $query = new Daq_Db_Query();
    $query->from("Wpjb_Model_Pricing t");
    $query->where("price_for = ?", Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP);

    $result = $query->execute();
    
    if (!empty($result)) {
        foreach($result as $pricing) {
            $summary = Wpjb_Model_Membership::getPackageSummary($pricing->id, wpjb_get_current_user_id("candidate"));
            if( $pricing->is_active == 0 || !is_object($summary) ) {
                continue;
            }
            
            if( $pricing->meta->is_searchable->value() == 1) {
                $values['is_searchable'] = 1;
            }
            
            if( $pricing->meta->can_apply->value() == 1) {
                $values['can_apply'] = 1;
            }
            
            if( $pricing->meta->featured_level->value() > $values['featured_level'] ) {
                $values['featured_level'] = $pricing->meta->featured_level->value();
            }
        }
    }  
    
    return $values;
}

/**
 * Provide statistics of alerts for provided Candidate
 * 
 * @param Wpjb_Model_Resume $resume
 * @return int
 */
function wpjb_candidate_alert_stats( $resume = null ) {
    
    if( $resume == null ) {
        $resume = Wpjb_Model_Resume::current(); 
    }
    
    $alerts = array(
        'max'       => wpjb_conf("cv_alerts_limit", -1),
        'current'   => 0,    
    );
    
    
    // No limits
    if( $alerts['max'] == -1 ) {
        return $alerts;
    }
    
    // Anonymous user 
    if( $resume == null ) {
        $alerts['current'] = -1;
        return $alerts;
    }
    
    $q = Daq_Db_Query::create();
    $alerts['current'] = $q->select("COUNT(*) AS cnt")->from("Wpjb_Model_Alert t")
                                                      ->where("t.user_id = ?", $resume->user_id )
                                                      ->fetchColumn(); 
    
     
    $query = new Daq_Db_Query();
    $query->from("Wpjb_Model_Pricing t");
    $query->where("price_for = ?", Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP);

    $result = $query->execute();
    
    if (!empty($result)) {
        foreach($result as $pricing) {
            $summary = Wpjb_Model_Membership::getPackageSummary($pricing->id, wpjb_get_current_user_id("candidate"));
            if( $pricing->is_active == 0 || !is_object($summary) ) {
                continue;
            }
            
            if( $pricing->meta->alert_slots->value() >  $alerts['max']) {
                $alerts['max'] = $pricing->meta->alert_slots->value();
            }
        }
    }  
    
    return apply_filters( "wpjb_candidate_alerts_stats", $alerts );    
}

/**
 * Function return list of pages containging provided shortcode
 * 
 * @param string $shortcode
 * @param array $args
 * @return array
 */
function pages_with_shortcode($shortcode, $args = array()) {
    if(!shortcode_exists($shortcode)) {
        // shortcode was not registered (yet?)
        return null;
    }

    // replace get_pages with get_posts
    // if you want to search in posts
    $pages = get_pages($args);
    $list = array();

    foreach($pages as $page) {
        if(has_shortcode($page->post_content, $shortcode)) {
            $list[] = $page;
        }
    }

    return $list;
}

/**
 * Function return number of applications with provided status
 * 
 * @param int $id
 * @return int
 */
function wpjb_count_applications( $id ) {
    
    $q = new Daq_Db_Query();
    $applications = $q->select( "COUNT(*) AS cnt")
                      ->from( "Wpjb_Model_Application t")
                      ->where( "t.status  = ?", $id )
                      ->fetchColumn();
    
    return $applications;
}

function wpjb_get_pricing_listing( $price_for ) {
    
    if( $price_for == Wpjb_Model_Pricing::PRICE_SINGLE_JOB ) {
        return "single-job";
    } elseif( $price_for == Wpjb_Model_Pricing::PRICE_SINGLE_RESUME ) {
        return "single-resume";
    } elseif( $price_for == Wpjb_Model_Pricing::PRICE_EMPLOYER_MEMBERSHIP ) {
        return "employer-membership";
    } elseif( $price_for == Wpjb_Model_Pricing::PRICE_CANDIDATE_MEMBERSHIP ) {
        return "candidate-membership";
    }  
}
?>