<?php
/**
 * Description of Employer
 *
 * @author greg
 * @package 
 */

class Wpjb_Model_Company extends Daq_Db_OrmAbstract
{
    
    const ACCESS_UNSET = 0;
    const ACCESS_PENDING = -1;
    const ACCESS_DECLINED = -2;
    const ACCESS_GRANTED = 1;
    
    const ACCOUNT_FULL_ACCESS = 4;
    const ACCOUNT_DECLINED = 3;
    const ACCOUNT_REQUEST = 2;
    const ACCOUNT_ACTIVE = 1;
    const ACCOUNT_INACTIVE = 0;
    
    const DELETE_PARTIAL = 1;
    const DELETE_FULL = 2;

    protected $_name = "wpjb_company";
    
    protected $_metaTable = "Wpjb_Model_Meta";
    
    protected $_metaName = "company";
    
    protected static $_current = null;
    
    public static $skip = array('geo_loc'=>false);

    protected function _init()
    {
        $this->_reference["user"] = array(
            "localId" => "user_id",
            "foreign" => "Wpjb_Model_User",
            "foreignId" => "ID",
            "type" => "ONE_TO_ONE"
        );
        $this->_reference["usermeta"] = array(
            "localId" => "user_id",
            "foreign" => "Wpjb_Model_UserMeta",
            "foreignId" => "user_id",
            "type" => "ONE_TO_ONE"
        );
        $this->_reference["meta"] = array(
            "localId" => "id",
            "foreign" => "Wpjb_Model_MetaValue",
            "foreignId" => "object_id",
            "type" => "ONE_TO_ONCE"
        );
        $this->_reference["job"] = array(
            "localId" => "id",
            "foreign" => "Wpjb_Model_Job",
            "foreignId" => "employer_id",
            "type" => "ONE_TO_ONCE"
        );
    }
    
    public function __get($key) 
    {
        if($key == "file") {
            $uploads = $this->_getFileUploads("company");
            $files = $this->_getFileLinks($uploads);

            return $files;
        } else {
            return parent::__get($key);
        }
    }

    /**
     * Checks if current employer can view resume details.
     * 
     * The resume is identified by wp_wpjb_resume.id, function checks if current 
     * has access to the resume details, this is the case if:
     * - On Application	-> Allow Employer to view whole user Resume
     * - Employer received job application from user who owns the resume.
     * 
     * @param int $resumeId Resume->id
     * @return boolean True if current user can view resume details
     */
    public function canViewResume($resumeId)
    {
        $resume = new Wpjb_Model_Resume($resumeId);
        
        if(!is_array(wpjb_conf("cv_show_applicant_resume"))) {
            return false;
        }
        
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Application t");
        $query->join("t.job t2");
        $query->where("t.user_id = ?", $resume->user_id);
        $query->where("t2.employer_id = ?", $this->id);
        $query->limit(1);
        
        $result = $query->fetch();
        
        if($result === null) {
            return false;
        } else {
            return true;
        }
    }
    
    public function hasActiveProfile()
    {
        if(!$this->is_active) {
            return apply_filters("wpjb_company_has_active_profile", false, $this);
        }

        if(!$this->is_public) {
            return apply_filters("wpjb_company_has_active_profile", false, $this);
        }

        return apply_filters("wpjb_company_has_active_profile", true, $this);
    }

    /**
     * Returns currently loggedin user employer object
     *
     * @return Wpjb_Model_Company
     */
    public static function current()
    {
        if(self::$_current instanceof self) {
            return self::$_current;
        }

        $current_user = wp_get_current_user();
        
        if($current_user->ID < 1) {
            return null;
        }

        $query = new Daq_Db_Query();
        $object = $query->select()->from(__CLASS__." t")
            ->where("user_id = ?", $current_user->ID)
            ->limit(1)
            ->execute();

        if(isset($object[0]) && $object[0]) {
            $current = $object[0];
        } else {
            $current = null;
        }
        
        self::$_current = apply_filters("wpjb_model_company_current", $current);

        return self::$_current;
    }
    
    public static function currentUserId()
    {
        return apply_filters("wpjb_model_company_current_user_id", get_current_user_id());
    }

    public function getLogoUrl($resize = null)
    {
        global $wp_version;
        
        $upload = wpjb_upload_dir("company", "company-logo", $this->id);
        $file = wpjb_glob($upload["basedir"]."/[!_]*");
        
        if(!isset($file[0])) {
            $link = maybe_unserialize($this->meta->company_logo->value());
            
            if(isset($link[0]["url"])) {
                return $link[0]["url"];
            } else {
                return null;
            }
        }
        
        $filename = basename($file[0]);
        $altfile = "__".$resize."_".basename($file[0]);
        
        if($resize && version_compare($wp_version, "3.5.0")>=0) {
                
            if(!is_file($upload["basedir"]."/".$altfile)) {
                list($max_w, $max_h) = explode("x", $resize);
                $editor = wp_get_image_editor($upload["basedir"]."/".$filename);

                if(!is_wp_error($editor)) {
                    $editor->resize($max_w, $max_h, false);
                    $editor->set_quality(100);
                    $result = $editor->save($upload["basedir"]."/".$altfile);

                    rename($result["path"], $upload["basedir"]."/".$altfile);

                    $filename = $altfile;
                } // endif is_wp_error
            } else {
                $filename = $altfile;
            }
        } 
        
        return $upload["baseurl"]."/".$filename;
    }

    public function delete($delete = self::DELETE_FULL)
    {
        $user = new WP_User($this->user_id);
        if($user->exists() && $delete == self::DELETE_FULL) {
            require_once(ABSPATH . 'wp-admin/includes/user.php');
            if($this->user_id == get_current_user_id()) {
                @wp_logout();
            }
            wp_delete_user($this->user_id);
        }
        
        wpjb_bubble_delete(wpjb_upload_dir("company", "", $this->id, "basedir"));
        
        if($this->post_id) {
            wp_delete_post($this->post_id, true);
        }
        
        if(!is_null(self::$_current) && self::$_current->id==$this->id) {
            self::$_current = null;
        }
        
        parent::delete();
    }

    public function addAccess($days)
    {
        $activeUntil = $this->access_until;
        $activeUntil = strtotime($activeUntil);

        if($activeUntil<time()) {
            $activeUntil = time();
        }

        $extend = $days*3600*24;

        $this->access_until = date("Y-m-d H:i:s", $activeUntil+$extend);
    }

    public function isEmployer()
    {
        if($this->user_id < 1) {
            return false;
        }
        return current_user_can("manage_jobs");
    }

    public function isActive()
    {
        $isActive = $this->is_active;

        if($isActive == self::ACCOUNT_ACTIVE) {
            return true;
        }

        if($isActive == self::ACCOUNT_FULL_ACCESS) {
            return true;
        }

        return false;
    }
    
    public function isVisible()
    {
        $visible = true;
        
        if(!$this->is_public) {
            $visible = false;
        }

        if(!$this->is_active) {
            $visible = false;
        }

        /*
        if(!$this->jobs_posted) {
            return false;
        }
        */

        return apply_filters("employer_is_visible", $visible);
    }
    
    /**
     * Returns geolocation parameters for the job
     * 
     * @return stdClass 
     */
    public function getGeo()
    {
        $this->geolocate();
        
        $obj = new stdClass;
        $obj->geo_status = $this->meta->geo_status->value();
        $obj->geo_latitude = $this->meta->geo_latitude->value();
        $obj->geo_longitude = $this->meta->geo_longitude->value();
        
        $obj->status = $this->meta->geo_status->value();
        $obj->lnglat = $obj->geo_latitude.",".$obj->geo_longitude;
        
        return $obj;
    }
    
    public function geolocate($force = false) 
    {
        $arr = array(
            Wpjb_Service_GoogleMaps::GEO_MISSING,
            Wpjb_Service_GoogleMaps::GEO_FOUND,
        );
        
        if(in_array($this->meta->geo_status->value(), $arr) && !$force) {
            return;
        }
        
        $geo = Wpjb_Service_GoogleMaps::locate($this->location());

        $meta = $this->meta->geo_status->getFirst();
        $meta->value = $geo->geo_status;
        $meta->save();
        
        $meta = $this->meta->geo_latitude->getFirst();
        $meta->value = $geo->geo_latitude;
        $meta->save();
        
        $meta = $this->meta->geo_longitude->getFirst();
        $meta->value = $geo->geo_longitude;
        $meta->save();
        
    }
    
    public function location()
    {
        $country = Wpjb_List_Country::getByCode($this->company_country);
        $country = trim($country['name']);
        
        $addr = array(
            $this->company_location,
            $this->company_zip_code,
            $this->company_state,
            $country
        );
        
        $addr = apply_filters("wpjb_geolocate", $addr, $this);
        
        return join(", ", $addr);
    }
    
    public function locationToString()
    {
        $arr = array();
        $country = Wpjb_List_Country::getByCode($this->company_country);
        $country = trim($country['name']);

        if(strlen(trim($this->company_location))>0) {
            $arr[] = $this->company_location;
        }

        if($this->company_country == 840 && strlen(trim($this->company_state))>0) {
            $arr[] = $this->company_state;
        } else if(strlen($country)>0) {
            $arr[] = $country;
        }

        return apply_filters("wpjb_location_display", implode(", ", $arr), $this);
    }
    
    public function save()
    {
        $id = parent::save();
                
        $this->meta(true);
        if(!self::$skip['geo_loc']) {
            $this->geolocate(true);
        }
        
        return $id;
    }
    
    public static function import($item) 
    {
        global $wpdb;
        
        $result = array(
            "type" => "company",
            "action" => "", // insert | update | fail
            "id" => "",
            "title" => "",
            "admin_url" => "",
            "messages" => array()
        );
        
        $user = get_user_by("login", (string)$item->user_login);
        if($user === false) {
            $user_id = wp_create_user(
                (string)$item->user_login, 
                (string)$item->user_password, 
                (string)$item->user_email
            );

            if($user_id instanceof WP_Error) {
                
                $result["action"] = "fail";
                $result["title"] = (string)$item->company_name;
                $result["messages"][] = array(
                    "type" => "fatal",
                    "text" => $user_id->get_error_message()
                );

                return $result;
            }

            $wpdb->update($wpdb->users, array("user_pass"=>(string)$item->user_password), array("ID"=>$user_id));

        } else {
            $user_id = $user->ID;
        }
        
        if( isset( $item->company_slug ) ) {
            $slug = (string)$item->company_slug;
        } elseif( isset( $item->company_name ) ) {
            $slug = Wpjb_Utility_Slug::generate( Wpjb_Utility_Slug::MODEL_COMPANY, (string)$item->company_name );
        } else {
            $slug = wp_generate_password(); //"undefined";
        }
        
        $default = new stdClass();
        $default->post_id = null;
        $default->user_id = $user_id;
        $default->company_slogan = "";
        $default->company_slug = $slug;
        $default->company_name = "";
        $default->company_website = "";
        $default->company_info = "";
        $default->company_country = "";
        $default->company_state = "";
        $default->company_zip_code = "";
        $default->company_location = "";
        $default->jobs_posted = 0;
        $default->is_public = 1;
        $default->is_active = 1;
        $default->is_verified = 0;
        
        if(isset($item->id)) {
            $id = (int)$item->id;
        } else {
            $id = null;
        }
        
        $object = new self($id);
        $exists = $object->exists();
        
        if($exists) {
            $result["action"] = "update";
            foreach($object->getFieldNames() as $key) {
                if(!isset($item->$key)) {
                    $item->$key = $object->$key;
                }
            }
        } else {
            $result["action"] = "insert";
            foreach($default as $key => $value) {
                if(!isset($item->$key)) {
                    $item->$key = $value;
                }
            }
        }
        
        if($exists) {
            $exclude = array("id"=>$id);
        } else {
            $exclude = array();
        }
        
        $result["title"] = (string)$item->company_name;
        
        $vSlug = new Daq_Validate_Db_NoRecordExists(__CLASS__, "company_slug", $exclude);
        
        if(!$vSlug->isValid((string)$item->company_slug)) {
            
            $result["action"] = "fail";
            $result["messages"][] = array(
                "type" => "fatal",
                "text" => sprintf(__("Item with slug %s already exist.", "wpjobboard"), (string)$item->company_slug)
            );
            
            return $result;
        }
        
        $object->user_id = (int)$item->user_id;
        $object->company_name = (string)$item->company_name;
        $object->company_slogan = (string)$item->company_slogan;
        $object->company_slug = (string)$item->company_slug;
        $object->company_website = (string)$item->company_website;
        $object->company_info = (string)$item->company_info;
        $object->company_country = (string)$item->company_country;
        $object->company_state = (string)$item->company_state;
        $object->company_zip_code = (string)$item->company_zip_code;
        $object->company_location = (string)$item->company_location;
        $object->jobs_posted = (int)$item->jobs_posted;
        $object->is_public = (int)$item->is_public;
        $object->is_active = (string)$item->is_active;
        $object->is_verified = (string)$item->is_verified;
        $object->save();
        
        $object->jobs_posted = wpjb_find_jobs(array("count_only"=>true, "employer_id"=>$object->id));
        $object->save();
        
        wp_update_user(array(
            "ID" => $user_id,
            "user_email" => (string)$item->user_email,
        ));
		
        $caps = get_user_meta($user_id, 'wp_capabilities', true);
        $roles = array_keys((array)$caps);

        if($roles[0] == "subscriber") {
            wp_update_user(array(
                "ID" => $user_id,
                "role" => "employer"
            ));
        }
        
        $result["id"] = $object->id;
        $result["title"] = $object->company_name;
        $result["admin_url"] = wpjb_admin_url("employers", "edit", $object->id);
        
        if(isset($item->metas->meta)) {
            foreach($item->metas->meta as $meta) {
                $name = (string)$meta->name;
                $value = (string)$meta->value;
                $varr = array();

                if($meta->values) {
                    foreach($meta->values->value as $v) {
                        $varr[] = (string)$v;
                    }
                } else {
                    $varr[] = (string)$meta->value;
                }
                
                if(!isset($object->meta->$name) || !is_object($object->meta->$name)) {
                    $result["messages"][] = array(
                        "type" => "warning",
                        "text" => sprintf(__("Custom field '%s' does not exist.", "wpjobboard"), $name)
                    );

                    continue;
                }
                
                $vlist = $object->meta->$name->getValues();
                $c = count($varr);
                
                for($i=0; $i<$c; $i++) {
                    if(isset($vlist[$i])) {
                        $vlist[$i]->value = $varr[$i];
                        $vlist[$i]->save();
                    } else {
                        $mv = new Wpjb_Model_MetaValue;
                        $mv->meta_id = $object->meta->$name->getId();
                        $mv->object_id = $object->id;
                        $mv->value = $varr[$i];
                        $mv->save();
                    }
                }
                

            }
        }

        if(isset($item->files->file)) {
            foreach($item->files->file as $file) {
                list($path, $filename) = explode("/", (string)$file->path);
                $upload = wpjb_upload_dir("company", $path, $object->id, "basedir");
                wp_mkdir_p($upload);
                file_put_contents($upload."/".$filename, base64_decode((string)$file->content));
            }
        }
        
        do_action("wpjb_imported_company", $object->id, $exists, $item);
        do_action("wpjb_company_saved", new Wpjb_Model_Company($object->id));
        
        return $result;
    }
    
    public function export(Daq_Helper_Xml $xml = null) 
    {
        if($xml === null) {
            $xml = new Daq_Helper_Xml();
        }
        
        $user = $this->getUser(true);
        
        $xml->open("company");
        $xml->tagIf("id", $this->id);
        $xml->tagIf("user_login", $user->user_login);
        $xml->tagIf("user_password", $user->user_password);
        $xml->tagIf("user_email", $user->user_email);
        $xml->tagIf("company_name", $this->company_name);
        $xml->tagIf("company_slogan", $this->company_slogan);
        $xml->tagIf("company_website", $this->company_website);
        $xml->tagIf("company_info", $this->company_info);
        $xml->tagIf("company_country", $this->company_country);
        $xml->tagIf("company_state", $this->company_state);
        $xml->tagIf("company_zip_code", $this->company_zip_code);
        $xml->tagIf("company_location", $this->company_location);
        $xml->tagIf("is_public", $this->is_public);
        $xml->tagIf("is_active", $this->is_active);
        $xml->tagIf("is_verified", $this->is_verified);
        
        $xml->open("metas");
        foreach($this->meta as $key => $value) {
            foreach($value->values() as $v) {
                $type = $value->conf("type");
                
                if($v) {
                    $xml->open("meta");
                    $xml->tag("name", $key);
                    if($type == "ui-input-textarea") {
                        $xml->tagCIf("value", $v);
                    } else {
                        $xml->tag("value", $v);
                    }
                    $xml->close("meta");
                }
            }
        }
        $xml->close("metas");
        
        $xml->open("files");
        foreach($this->file as $category => $files) {
            
            foreach($files as $file) {
                
                if(stripos($file->basename, "__") === 0) {
                    continue;;
                }

                $filename = $category . "/" . $file->basename;

                $xml->open("file");
                $xml->tag("path", str_replace("_", "-", $filename));
                $xml->tag("content", base64_encode(file_get_contents($file->path)));
                $xml->close("file");
            }
        }
        $xml->close("files");
        
        $xml->open("post");
        foreach(get_post_meta($this->post_id) as $key => $meta) {
            
            if(stripos($key, "_") === 0) {
                continue;
            }
            
            foreach($meta as $mv) {
                $xml->open("meta");
                $xml->tag("name", $key);
                $xml->tag("value", $mv);
                $xml->close("meta");
            }
        }
        $xml->close("post");
        
        $xml->close("company");
    }
    
    public static function importCsv($data) 
    {
        $key = "company";
        $form = new Wpjb_Form_Admin_Company();
        $object = new self();
        $user = array("user_login", "user_password", "user_email", "first_name", "last_name", "user_url");
        $fields = array_merge($object->getFieldNames(), $user);

        $import = new stdClass();
        $import->metas = new stdClass();
        $import->metas->meta = array();
        $import->tags = new stdClass();
        $import->tags->tag = array();
        $import->files = new stdClass();
        $import->files->file = array();
        
        foreach($data as $temp => $value) {
            list($tkey, $k) = explode(".", $temp);
            
            if($tkey != $key) {
                continue;
            }
            
            if(in_array($k, $fields)) {
                $import->$k = $value;
            } elseif($form->hasElement($k) && $form->getElement($k)->getType() == "file") {
                $content = null;
                if(filter_var($value, FILTER_VALIDATE_URL) || stripos($value, "http")===0) {
                    $response = wp_remote_get($value);
                    $filename = basename($value);
                    if(is_array($response)) {
                        $content = base64_encode($response["body"]);
                    }  
                } else {
                    $filename = "image.png";
                    $content = $value;
                }
                
                if($content) {
                    $file = new stdClass();
                    $file->path = str_replace("_", "-", $k) . "/" . $filename;
                    $file->content = $content;
                    $import->files->file[] = $file;
                }
            } elseif(isset($object->meta->$k) && $object->meta->$k) {
                if(stripos($value, ";")) {
                    $value = explode(";", $value);
                } elseif(empty($value)) {
                    $value = array();
                } else {
                    $value = array($value);
                }
                
                foreach($value as $v) {
                    $meta = new stdClass();
                    $meta->name = $k;
                    $meta->value = $v;
                    $import->metas->meta[] = $meta;
                }
            } elseif(isset($object->tag->$k) && $object->tag->$k) {
                $tag = new stdClass();
                $tag->type = $k;
                $tag->slug = Wpjb_Utility_Slug::generate($k, $value);
                $tag->title = $value;
                $import->tags->tag[] = $tag;
            }
        }
        
        return self::import($import);
    }
    
    public function csv($columns) {
        
        $o = array(
            "company" => $this
        );
        $a = array(
            "company" => array(
                "user_login" => $this->getUser(true)->user_login,
                "user_password" => $this->getUser(true)->user_password,
                "first_name" => get_user_meta($this->user_id, "first_name", true),
                "last_name" => get_user_meta($this->user_id, "last_name", true),
                "user_email" => $this->getUser(true)->user_email,
                "user_url" => $this->getUser(true)->user_url,
                
            )
        );
        $ommit = array("company.company_logo");
        
        $data = array_fill_keys($columns, "");
 
        foreach($columns as $column) {
            list($object, $property) = explode(".", $column);
            
            if($o[$object]->get($property)) {
                $data[$column] = $o[$object]->get($property);
            } elseif(isset($o[$object]->file->{$property}[0])) {
                $upload = wpjb_upload_dir($object, str_replace("_", "-", $property), $o[$object]->id);
                $file = wpjb_glob($upload["basedir"]."/[!_]*");
                $data[$column] = $upload["baseurl"]."/".basename($file[0]);
            } elseif(isset($o[$object]->meta->$property) && is_object($o[$object]->meta->$property) && !in_array($column, $ommit)) {
                $data[$column] = join(";", $o[$object]->meta->$property->values());
            } elseif(isset($o[$object]->tag->{$property}[0])) {
                $data[$column] = $o[$object]->tag->{$property}[0]->title;
            } elseif(isset($a[$object][$property])) {
                $data[$column] = $a[$object][$property];
            }
            
        }
        
        return $data;
    }
    
    public function membership()
    {
        $query = new Daq_Db_Query();
        $query->from("Wpjb_Model_Membership t");
        $query->where("user_id = ?", $this->user_id);
        $query->where("started_at <= ?", date("Y-m-d"));
        $query->where("expires_at >= ?", date("Y-m-d"));
        
        return $query->execute();
    }
    
    /**
     * 
     * @deprecated since 4.0.1
     * @return type
     */
    public function getUsers()
    {
        return $this->user;
    }
    
    public static function search($params = array())
    {
        $company_name = null;
        $query = null;
        $location = null;
        
        /**
         * @var $radius string
         * location radius either in km (for example "5 km") or miles ("5 mi.")
         */
        $radius = null;

        /**
         * @var $count int
         * items per page or maximum number of elements to return
         */
        $count = 25;
        $page = null;
        $date_from = null;
        $date_to = null;
        
        /**
         * @var $email string
         * user email
         */
        $email = null;
        
        /**
         * @var $login string
         * user login
         */
        $login = null;
        
        /**
         * @var $sort_order mixed
         * string or array, specify sort column and order (either DESC or ASC),
         * you can add more then one sort order. 
         */
        $sort_order = "t1.id DESC";
        
        /**
         * @var $count_only boolean
         * Count jobs only
         */
        $count_only = false;
        
        /**
         * Return only list of job ids instead of objects
         * @var $ids_only boolean
         */
        $ids_only = false;
        
        /**
         * @var $filter string
         * narrow jobs to certain type:
         * - all: all resumes
         * - active: only active resumes
         * - inactive: inactive resumes
         */
        $filter = "active";
        
        extract($params);
        
        $groupResults = false;
        
        $select = new Daq_Db_Query();
        $select->select();
        $select->from("Wpjb_Model_Company t1");
        $select->join("t1.user t2");
        
        switch($filter) {
            case "active": $select->where("t1.is_active=1 AND t1.is_public=1"); break;
            case "public": $select->where("t1.is_active=1 AND t1.is_public=1"); break;
            case "pending":$select->where("t1.is_active=?", 2); break;
        }

        if($query) {
            $select->where("(company_name LIKE ? OR company_info LIKE ?)", "%$query%");
        }
        
        if($company_name) {
            $select->where("(company_name LIKE ?)", "%$company_name%");
        }
        
        if($radius && $location) {
            list($distance, $dunit) = explode(" ", trim($radius));
            $select->having("distance < " . intval($distance));
            $select->order("distance ASC");
        } elseif($location) {
            $select->where("(company_state LIKE ? OR company_zip_code LIKE ? OR company_location LIKE ?)", "%$location%");
        }
        
        if($login) {
            $select->where("t2.user_login LIKE ?", "%$login%");
        }
        
        if($email) {
            $select->where("t2.user_email LIKE ?", "%$email%");
        }
        
        if(!empty($meta)) {
            $company = new Wpjb_Model_Company();
            $m = 1;
            foreach($meta as $k => $v) {
                if(!is_numeric($k)) {
                    $k = $company->meta->$k->id;
                }
                
                $metaObject = new Wpjb_Model_Meta($k);
                $metaType = $metaObject->conf("type");
                $match = "";
                
                // match: all, one-or-more, exact, like
                
                if(in_array($metaType, array("ui-input-text", "ui-input-textarea"))) {
                    $select->join("t1.meta t3m$m");
                    $t1 = Daq_Db::getInstance()->quoteInto("t3m$m.meta_id = ?", $k);
                    $select->where("($t1 AND t3m$m.value LIKE ?)", "%$v%");
                } else {
                    $select->join("t1.meta t3m$m");
                    $t1 = Daq_Db::getInstance()->quoteInto("t3m$m.meta_id = ?", $k);
                    $select->where("($t1 AND t3m$m.value IN(?))", $v);
                }
                    
                $m++;
            }
            $select->group("t1.id");
            $groupResults = true;
        }
        
        if($sort_order) {
            $select->order($sort_order);
        }
        
        $custom_columns = "";
        
        if($radius && $location) {

            list($distance, $dunit) = explode(" ", trim($radius));

            if($dunit == "km") {
                $u = 6371;
            } else {
                $u = 3959;
            }

            $addr = Wpjb_Service_GoogleMaps::locate($location);

            $lng = $addr->geo_longitude;
            $lat = $addr->geo_latitude;
            $prefix = $select->getDb()->prefix;

            $qLng = "(SELECT `value` FROM ".$prefix."wpjb_meta_value AS tmp_lng WHERE tmp_lng.object_id=t1.id AND meta_id=11 LIMIT 1)";
            $qLat = "(SELECT `value` FROM ".$prefix."wpjb_meta_value AS tmp_lat WHERE tmp_lat.object_id=t1.id AND meta_id=10 LIMIT 1)";

            $custom_columns .= ", @lng := $qLng AS `lng`, @lat := $qLat AS `lat`, ($u*acos(cos(radians($lat))*cos( radians(@lat))*cos(radians(@lng)-radians($lng))+sin(radians($lat))*sin(radians(@lat)))) AS `distance`";
        }
        
        $select->select("COUNT(*) AS cnt".$custom_columns);
        $itemsFound = self::_found($select, $groupResults);
        

        $select->select("*".$custom_columns);
        
        $select = apply_filters("wpjb_companies_query", $select, $custom_columns, $params);
        
        if($page && $count) {
            $select->limitPage($page, $count);
        }
        
        if($count_only) {
            return $itemsFound;    
        }
        
        if($ids_only) {
            $select->select("t1.id");
            $list = $select->getDb()->get_col($select->toString());
        } else {   
            $list = $select->execute();
        }
        
        $response = new stdClass;
        $response->company = $list;
        $response->page = (int)$page;
        $response->perPage = (int)$count;
        $response->count = count($list);
        $response->total = (int)$itemsFound;
        
        if($response->perPage > 0) {
            $response->pages = ceil($response->total/$response->perPage);
        } else {
            $response->pages = 1;
        }
        
        return $response;
    }
    
    public function doScheme($name)
    {
        $scheme = apply_filters("wpjb_scheme", get_option("wpjb_form_company"), $this);
        
        if(wpjb_scheme_get($scheme, $name.".visibility")>0) {
            return true;
        } elseif(wpjb_scheme_get($scheme, $name.".render_callback")) {
            call_user_func(wpjb_scheme_get($scheme, $name.".render_callback"), $this);
            return true;
        }

        return false;
    }
    
    protected static function _found($query, $grouped)
    {
        if($grouped || $query->get("having")) {
            return count($query->fetchAll());
        } else {
            return $query->fetchColumn();
        }
    }
    
    public function url()
    {
        return wpjb_link_to("company", $this);
    }
    
    public function cpt() 
    {
        if($this->isVisible()) {
            $status = "publish";
        } else {
            $status = "wpjb-disabled";
        }
        
        if(!$this->post_id || !get_post($this->post_id)) {
            // create new
            $post_id = wp_insert_post(array(
                "post_title" => $this->company_name,
                "post_name" => $this->company_slug,
                "post_type" => "company",
                "post_status" => $status,
                "comment_status" => "closed",
                "post_content" => $this->company_info
            ));
            
            $this->post_id = $post_id;
            parent::save();
            
        } else {
            // edit company
            $post_id = wp_update_post(array(
                "ID" => $this->post_id,
                "post_title" => $this->company_name,
                "post_name" => $this->company_slug,
                "post_modified" => current_time("mysql"),
                "post_modified_gmt" => current_time("mysql", true),
                "post_type" => "company",
                "post_status" => $status,
                "post_content" => $this->company_info
            ));
        }
        
        do_action("wpjb_cpt", $this, $post_id);
        
        return $post_id;
    }
    
    public function toArray() 
    {
        $arr = parent::toArray();
        
        $arr["url"] = wpjb_link_to("company", $this);
        $arr["admin_url"] = wpjb_admin_url("employers", "edit", $this->id);
        
        $arr["country"] = Wpjb_List_Country::getByCode($this->company_country);
        $arr["user"] = $this->getUser(true)->toArray();
        
        return $arr;
    }
}

?>