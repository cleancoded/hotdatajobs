<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ScreenOptions
 *
 * @author Grzegorz
 */
class Wpjb_Utility_ScreenOptions 
{
    public $base = array(
        "toplevel_page_wpjb-job-index" => "job",
        "job-board_page_wpjb-application-index" => "application",
        "job-board_page_wpjb-employers-index" => "employer",
        "job-board_page_wpjb-resumes-index" => "resume",
    );
    
    /**
     * Cached columns
     *
     * @var array
     */
    protected static $_cols = array();


    public function setScreenOptions($status, $option, $value) {

	if ( 'wpjb_screen' != $option ) { 
            return $value;
	}
        
        $key = $value;
        $value = get_user_option( 'wpjb_screen' );
        
        if( !is_array($value) ) {
            $value = array();
        }
        
        if( isset($_POST["wpjb_screen"][$key])) {
            $value[$key] = $_POST["wpjb_screen"][$key];
        }
        
	return $value;
    }
    
    public function showScreen($show_screen, $screen) {
        $action = Daq_Request::getInstance()->get("action", "index");
        $page = Daq_Request::getInstance()->get("page", "");

        if(stripos($page, "wpjb-") !== 0) {
            return $show_screen;
        }

        if($this->getScreen($screen->base."-".$action) !== null) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getScreen($base) {
        
        $menu = Wpjb_Utility_Registry::get("admin_menu");
        
        if(!is_array($menu)) {
            return null;
        }
        
        foreach($menu as $item) {
            if($base == $item["id"] . "-index" && isset($item["screen"])) {
                return $item["screen"];
            }
        }
        
        return null;
    }
    
    public function dataApplication()
    {
        return array(
            "fields" => array(
                "id" => array("label"=>__("ID"), "checked"=>0),
                "__gravatar" => array("label"=>__("Avatar", "wpjobboard"), "checked"=>0),
                "applicant_name" => array("label"=>__("Applicant Name", "wpjobboard"), "checked"=>0),
                "email" => array("label"=>__("Applicant Email", "wpjobboard"), "checked"=>0),
                "__job" => array("label"=>__("Job", "wpjobboard"), "checked"=>0),
                "file" => array("label"=>__("Files", "wpjobboard"), "checked"=>0),
                "applied_at" => array("label"=>__("Posted", "wpjobboard"), "checked"=>0),
                "message" => array("label"=>__("Message", "wpjobboard"), "checked"=>0),
                "__rating" => array("label"=>__("Rating", "wpjobboard"), "checked"=>0),
                "__status" => array("label"=>__("Status", "wpjobboard"), "checked"=>0),
            ),
            "columns" => array(
                "id" => 0, 
                "__gravatar" => 1,
                "applicant_name" => 1, 
                "email" => 1, 
                "__job" => 1,
                "file" => 1, 
                "applied_at" => 1, 
                "message" => 0,
                "__rating" => 1,
                "__status" => 1
            ),
            "form" => "Wpjb_Form_Admin_Application"
        );
    }
    
    public function doScreenApplication() {
        
        $key = "application";
        $data = $this->dataApplication();
                
        add_action("wpjb_custom_columns_head", array($this, "customColumnsHead"), 10, 2);
        add_action("wpjb_custom_columns_body", array($this, "customColumnsBody"), 10, 2);     
        
        $cols = $this->_findColumns($key, $data);
        return $this->_renderHtml($key, $cols);
        
    }
    
    public function dataJob() {
        return array(
            "fields" => array(
                "id" => array( "label" => __("ID"), "checked" => 0 ),
                "company_logo" => array( "label" => __("Logo", "wpjobboard"), "checked" => 0 ),
                "job_title" => array( "label" => __("Position Title", "wpjobboard"), "checked" => 0 ),
                "company_name" => array( "label" => __("Company Name", "wpjobboard"), "checked" => 0 ),
                "company_email" => array( "label" => __("Contact Email", "wpjobboard"), "checked" => 0 ),
                "is_approved" => array( "label" => __("Approved", "wpjobboard"), "checked" => 0 ),
                "is_active" => array( "label" => __("Active", "wpjobboard"), "checked" => 0 ),
                "is_filled" => array( "label" => __("Is Filled", "wpjobboard"), "checked" => 0 ),
                "is_featured" => array( "label" => __("Is Featured", "wpjobboard"), "checked" => 0 ),
                "company_url" => array( "label" => __("Website", "wpjobboard"), "checked" => 0 ),
                "__location" => array( "label" => __("Location", "wpjobboard"), "checked" => 0 ),
                "job_country" => array( "label" => __("Country", "wpjobboard"), "checked" => 0 ),
                "job_state" => array( "label" => __("State", "wpjobboard"), "checked" => 0 ),
                "job_zip_code" => array( "label" => __("Zip-Code", "wpjobboard"), "checked" => 0 ),
                "job_city" => array( "label" => __("City", "wpjobboard"), "checked" => 0 ),
                "category" => array( "label" => __("Category", "wpjobboard"), "checked" => 0 ),
                "type" => array( "label" => __("Job Type", "wpjobboard"), "checked" => 0 ),
                "__price" => array( "label" => __("Price", "wpjobboard"), "checked" => 0 ),
                "payment_method" => array( "label" => __("Payment Method", "wpjobboard"), "checked" => 0 ),
                "job_created_at" => array( "label" => __("Created", "wpjobboard"), "checked" => 0 ),
                "job_expires_at" => array( "label" => __("Expires", "wpjobboard"), "checked" => 0 ),
                "job_description" => array( "label" => __("Description", "wpjobboard"), "checked" => 0 ),
                "__applications" => array( "label" => __("Applications", "wpjobboard"), "checked" => 0 ),
                "__status" => array( "label" => __("Status", "wpjobboard"), "checked" => 0 )
            ),
            "columns" => array(
                "id" => 0,
                "company_logo" => 0,
                "job_title" => 1,
                "company_name" => 1,
                "company_email" => 0,
                "is_approved" => 0,
                "is_active" => 0,
                "is_filled" => 0,
                "is_featured" => 0,
                "company_url" => 0,
                "__location" => 0,
                "job_country" => 0,
                "job_state" => 0,
                "job_zip_code" => 0,
                "job_city" => 0,
                "category" => 1,
                "type" => 1,
                "__price" => 1,
                "payment_method" => 0,
                "job_created_at" => 0,
                "job_expires_at" => 1,
                "job_description" => 0,
                "__applications" => 1,
                "__status" => 1
            ),
            "form" => "Wpjb_Form_Admin_AddJob"
        );
    }
    
    public function doScreenJob() {

        $key = "job";
        $data = $this->dataJob();
        
        add_action("wpjb_custom_columns_head", array($this, "customColumnsHead"), 10, 2);
        add_action("wpjb_custom_columns_body", array($this, "customColumnsBody"), 10, 2);
        
        $cols = $this->_findColumns($key, $data);
        return $this->_renderHtml($key, $cols);
    }
    
    public function dataResume() {
        return array(
            "fields" => array(
                "id" => array("label"=>__("ID"), "checked"=>0),
                "image" => array("label"=>__("Photo", "wpjobboard"), "checked"=>0),
                "__name" => array("label"=>__("Name", "wpjobboard"), "checked"=>0),
                "first_name" => array("label"=>__("First Name", "wpjobboard"), "checked"=>0),
                "last_name" => array("label"=>__("Last Name", "wpjobboard"), "checked"=>0),
                "headline" => array("label"=>__("Headline", "wpjobboard"), "checked"=>0),
                "user_email" => array("label"=>__("E-mail", "wpjobboard"), "checked"=>0),
                "user_login" => array("label"=>__("Login", "wpjobboard"), "checked"=>0),
                "phone" => array("label"=>__("Phone", "wpjobboard"), "checked"=>0),
                "user_url" => array("label"=>__("Website", "wpjobboard"), "checked"=>0),
                "__location" => array( "label" => __("Location", "wpjobboard"), "checked" => 0 ),
                "candidate_country" => array( "label" => __("Country", "wpjobboard"), "checked" => 0 ),
                "candidate_state" => array( "label" => __("State", "wpjobboard"), "checked" => 0 ),
                "candidate_zip_code" => array( "label" => __("Zip-Code", "wpjobboard"), "checked" => 0 ),
                "candidate_location" => array( "label" => __("City", "wpjobboard"), "checked" => 0 ),
                "category" => array( "label" => __("Category", "wpjobboard"), "checked" => 0 ),
                "created_at" => array("label"=>__("Created", "wpjobboard"), "checked"=>0),
                "modified_at" => array("label"=>__("Updated (By Owner)", "wpjobboard"), "checked"=>0),
                "description" => array("label"=>__("Profile Summary", "wpjobboard"), "checked"=>0),
                "is_public" => array("label"=>__("Privacy", "wpjobboard"), "checked"=>0),
                "__status" => array("label"=>__("Status", "wpjobboard"), "checked"=>0),

            ),
            "columns" => array(
                "id" => 0, 
                "image" => 0, 
                "__name" => 1, 
                "first_name" => 0,
                "last_name" => 0,
                "headline" => 0,
                "user_email" => 1, 
                "user_login" => 0, 
                "phone" => 1,
                "user_url" => 0,
                "__location" => 0,
                "candidate_country" => 0,
                "candidate_state" => 0,
                "candidate_zip_code" => 0,
                "candidate_location" => 0,
                "category" => 0,
                "created_at" => 0,
                "modified_at" => 1,
                "description" => 0,
                "is_public" => 0,
                "__status" => 1,
            ),
            "form" => "Wpjb_Form_Admin_Resume"
        );
    }
    
    public function dataEmployer()
    {
        return array(
            "fields" => array(
                "id" => array("label"=>__("ID"), "checked"=>0),
                "company_logo" => array("label"=>__("Logo", "wpjobboard"), "checked"=>0),
                "company_name" => array("label"=>__("Company Name", "wpjobboard"), "checked"=>0),
                "__location" => array("label"=>__("Location", "wpjobboard"), "checked"=>0),
                "company_country" => array("label"=>__("Company Country", "wpjobboard"), "checked"=>0),
                "company_state" => array("label"=>__("Company State", "wpjobboard"), "checked"=>0),
                "company_zip_code" => array("label"=>__("Company Zip-Code", "wpjobboard"), "checked"=>0),
                "company_location" => array("label"=>__("Company Location", "wpjobboard"), "checked"=>0),
                "user_email" => array("label"=>__("E-mail", "wpjobboard"), "checked"=>0),
                "user_login" => array("label"=>__("Representative", "wpjobboard"), "checked"=>0),
                "company_website" => array("label"=>__("Company Website", "wpjobboard"), "checked"=>0),
                "company_info" => array("label"=>__("Company Info", "wpjobboard"), "checked"=>0),
                "__jobs_posted" => array("label"=>__("Jobs Posted", "wpjobboard"), "checked"=>0),
                "is_public" => array("label"=>__("Profile", "wpjobboard"), "checked"=>0),
                "__status" => array("label"=>__("Status", "wpjobboard"), "checked"=>0),

            ),
            "columns" => array(
                "id" => 1, 
                "company_logo" => 0,
                "company_name" => 1, 
                "__location" => 1, 
                "company_country" => 0,
                "company_state" => 0,
                "company_zip_code" => 0,
                "company_location" => 0,
                "user_email" => 0,
                "user_login" => 1,
                "company_website" => 0,
                "company_info" => 0,
                "__jobs_posted" => 1,
                "is_public" => 0,
                "__status" => 1,
            ),
            "form" => "Wpjb_Form_Admin_Company"
        );
    }
    
    public function doScreenEmployer() {
    
        $key = "employer";
        $data = $this->dataEmployer();
        
        add_action("wpjb_custom_columns_head", array($this, "customColumnsHead"), 10, 2);
        add_action("wpjb_custom_columns_body", array($this, "customColumnsBody"), 10, 2);
        
        $cols = $this->_findColumns($key, $data);
        return $this->_renderHtml($key, $cols);
    }

    public function doScreenResume() {
        
        $key = "resume";
        $data = $this->dataResume();
        
        add_action("wpjb_custom_columns_head", array($this, "customColumnsHead"), 10, 2);
        add_action("wpjb_custom_columns_body", array($this, "customColumnsBody"), 10, 2);
        
        $cols = $this->_findColumns($key, $data);
        return $this->_renderHtml($key, $cols);
    }
    
    
    protected function _findColumns($key, $data) {
        
        if(isset(self::$_cols[$key])) {
            return self::$_cols[$key];
        }
        
        $columns = get_user_option("wpjb_screen");
        $fields = $data["fields"];
        $form = new $data["form"];
        
        if(!isset($columns[$key]["cols"])) {
            $columns = $data["columns"];
        } else {
            $columns = array_combine($columns[$key]["cols"], array_fill(0, count($columns[$key]["cols"]), 1));
        }
        
        foreach($data["fields"] as $k => $v) {
            if(isset($columns[$k]) && $columns[$k]==1) {
                $fields[$k]["checked"] = 1;
            }
        }
        
        $groups = $form->getReordered();
        foreach($groups as $group) {
            foreach($group->getReordered() as $field) {
                
                if(isset($columns[$field->getName()]) && $columns[$field->getName()]==1) {
                    $checked = 1;
                } else {
                    $checked = 0;
                }
                
                $exclude = array("hidden", "password");
                
                if(in_array($field->getType(), $exclude)) {
                    continue;
                }
                
                if(!isset($fields[$field->getName()])) {
                    $fields[$field->getName()] = array("label" => $field->getLabel(), "checked" => 0 );
                }
                    
                $fields[$field->getName()]["checked"] = $checked;
                
            }
        }
        
        self::$_cols[$key] = $fields;
        
        return $fields;
    }
        
        
    protected function _renderHtml($key, $fields) {
        
        $button = get_submit_button( __( 'Apply' ), 'button', 'screen-options-apply', false );
        $columns = get_user_option("wpjb_screen");
        
        if(isset($columns[$key]["count"]) && is_numeric($columns[$key]["count"])) {
            $count = (int)$columns[$key]["count"];
        } else {
            $count = 20;
        }
        
        $screen = "
            <fieldset>
            <h5>".__("Show on screen")."</h5>
            <div class='metabox-prefs'>
            <div><input type='hidden' name='wp_screen_options[option]' value='wpjb_screen' /></div>
            <div><input type='hidden' name='wp_screen_options[value]' value='$key' /></div>
            <div class='wpjb-screen-fields'>
        ";
        
        foreach($fields as $name => $f) {
            $input = new Daq_Helper_Html("input", array(
                "id" => "wpjb_column_".$name,
                "type" => "checkbox",
                "name" => "wpjb_screen[$key][cols][]",
                "value" => $name,
                "checked" => $f["checked"]
            ));

            $label = new Daq_Helper_Html("label", array("for"=>"wpjb_column_".$name), $input->render().' '.$f["label"]);

            $screen .= $label->render();
        }

        $screen .= "
            </div>
            </div>
            <h5>".__("Items per page", "wpjobboard")."</h5>
            <div class='screen-options'>
                <input type='number' step='1' min='1' max='999' maxlength='3' name='wpjb_screen[$key][count]' value='$count' />
            </div>
            </fieldset>
            <br class='clear'>
            $button
        ";
        
        return $screen;
    }
    

    public function screenSettings( $status, $screen ) {
        
        $action = Daq_Request::getInstance()->get("action", "index");
        $scr = $this->getScreen($screen->base."-".$action);
        
        if($scr === null) {
            return;
        }
        
        $func = "doScreen".$scr;
        return $this->$func();
        

    }
    
    public function get($key, $option, $default = null) {
        
        $screen = get_user_option("wpjb_screen");
        
        if(!isset($screen[$key]) || !isset($screen[$key][$option])) {
            return $default;
        } else {
            return $screen[$key][$option];
        }
    }
    
    public function show($key, $column) {
        
        $func = "data".ucfirst($key);
        $data = $this->$func();
        
        $cols = $this->_findColumns($key, $data);
        //var_dump($cols);
        if(isset($cols[$column]["checked"]) && $cols[$column]["checked"]) {
            return true;
        } else {
            return false;
        }
        
    }
    
    public function customColumnsHead($key) {
        
        $func = "data".ucfirst($key);
        $data = $this->$func();
        $cols = $this->_findColumns($key, $data);
        
        $default = array_keys($data["fields"]);
        $all = array_keys($cols);
        
        $custom = array_diff($all, $default);


        foreach($custom as $column) {
            if($cols[$column]["checked"]) {
                echo "<th>".esc_html($cols[$column]["label"])."</th>";
            }
        }
    }
    
    public function customColumnsBody($key, $object) {
        
        $func = "data".ucfirst($key);
        $data = $this->$func();
        $cols = $this->_findColumns($key, $data);
        
        $default = array_keys($data["fields"]);
        $all = array_keys($cols);
        
        $custom = array_diff($all, $default);

        
        foreach($custom as $column) {
            if( $cols[$column]["checked"] && isset( $object->meta->{$column} ) ) {
                $v = join(", ", (array)$object->meta->{$column}->values());
                
                $html = new Daq_Helper_Html("td", array(
                    "data-colname" => $object->meta->{$column}->name
                ), esc_html( $v ? $v : "—" ) );
                
                echo $html->render();
            }
        }
    }
}


