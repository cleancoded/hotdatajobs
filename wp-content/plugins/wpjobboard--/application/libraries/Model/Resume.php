<?php
/**
 * Description of Employer
 *
 * @author greg
 * @package
 */

class Wpjb_Model_Resume extends Daq_Db_OrmAbstract
{
    const ACCOUNT_ACTIVE = 1;
    const ACCOUNT_INACTIVE = 0;

    const RESUME_PENDING  = 1;
    const RESUME_DECLINED = 2;
    const RESUME_APPROVED = 3;
    
    const DELETE_PARTIAL = 1;
    const DELETE_FULL = 2;

    protected $_name = "wpjb_resume";
    
    protected $_details = null;
    
    protected $_metaTable = "Wpjb_Model_Meta";
    
    protected $_metaName = "resume";
    
    protected $_tagTable = array("scheme"=>"Wpjb_Model_Tag", "values"=>"Wpjb_Model_Tagged");
    
    protected $_tagName = "resume";
    
    protected $_user = null;
    
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
        $this->_reference["search"] = array(
            "localId" => "id",
            "foreign" => "Wpjb_Model_ResumeSearch",
            "foreignId" => "resume_id",
            "type" => "ONE_TO_ONE"
        );
        $this->_reference["meta"] = array(
            "localId" => "id",
            "foreign" => "Wpjb_Model_MetaValue",
            "foreignId" => "object_id",
            "type" => "ONE_TO_ONCE"
        );
        $this->_reference["tagged"] = array(
            "localId" => "id",
            "foreign" => "Wpjb_Model_Tagged",
            "foreignId" => "object_id",
            "type" => "ONE_TO_ONE"
        );
        $this->_reference["membership"] = array(
            "localId" => "user_id",
            "foreign" => "Wpjb_Model_Membership",
            "foreignId" => "user_id",
            "type" => "ONE_TO_ONE"
        );
    }

    public function hasActiveProfile()
    {

        if(!$this->is_active) {
            return false;
        }

        if(!$this->is_public) {
            return false;
        }

        return true;
    }

    /**
     * Returns currently loggedin user employer object
     *
     * @return Wpjb_Model_Resume
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

        if(isset($object[0])) {
            self::$_current = $object[0];
            return self::$_current;
        }

        return null;
    }

    public function save()
    {
        $id = parent::save();
        
        $this->meta(true);
        if(!self::$skip['geo_loc']) {
            $this->geolocate(true);
        }
        
        Wpjb_Model_ResumeSearch::createFrom($this);
        
        return $id;
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
        
        $query = Daq_Db_Query::create();
        $query->from("Wpjb_Model_ResumeSearch t");
        $query->where("resume_id = ?", $this->id);
        foreach($query->execute() as $row) {
            $row->delete();
        }
        
        $query = Daq_Db_Query::create();
        $query->from("Wpjb_Model_ResumeDetail t");
        $query->where("resume_id = ?", $this->id);
        foreach($query->execute() as $row) {
            $row->delete();
        }
        
        $dir = wpjb_upload_dir("resume", "", $this->id, "basedir");
        if(is_dir($dir)) {
            wpjb_recursive_delete($dir);
        }
        
        if($this->post_id > 0) {
            wp_delete_post($this->post_id, true);
        }
        
        parent::delete();
    }

    
    public function allToArray()
    {
        $arr = parent::toArray();
        
        $field = (array)$this->getNonEmptyFields();
        $txtar = (array)$this->getNonEmptyTextareas();
        
        foreach($field as $f) {
            $arr["field_".$f->field_id] = $f->value;
        }
        
        foreach($txtar as $f) {
            $arr["field_".$f->field_id] = $f->value;
        }
        
        return $arr;
    }
    
    public function getDetails()
    {
        if($this->_details !== null) {
            return $this->_details;
        }
        
        $select = new Daq_Db_Query();
        $select->select();
        $select->from("Wpjb_Model_ResumeDetail t");
        $select->where("resume_id = ?", $this->id);
        $select->order("t.started_at DESC");
        $result = $select->execute();
        
        return $result;
    }
    
    public function getExperience()
    {
        $exp = array();

        foreach($this->getDetails(true) as $detail) {

            if($detail->type == Wpjb_Model_ResumeDetail::EXPERIENCE) {
                $exp[] = $detail;
            }
        }
        
        return $exp;
    }
    
    public function getEducation()
    {
        $edu = array();
        foreach($this->getDetails(true) as $detail) {
            if($detail->type == Wpjb_Model_ResumeDetail::EDUCATION) {
                $edu[] = $detail;
            }
        }
        
        return $edu;
    }
    
    public function getAvatarUrl($resize = null)
    {
        global $wp_version;
        
        $upload = wpjb_upload_dir("resume", "image", $this->id);
        $file = wpjb_glob($upload["basedir"]."/[!_]*");
        
        if(!isset($file[0])) {
            $link = maybe_unserialize($this->meta->image->value());
            
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
    
    public function location()
    {
        $country = Wpjb_List_Country::getByCode($this->candidate_country);
        $country = trim($country['name']);
        
        $addr = array(
            $this->candidate_location,
            $this->candidate_zip_code,
            $this->candidate_state,
            $country
        );
        
        $addr = apply_filters("wpjb_geolocate", $addr, $this);
        
        return join(", ", $addr);
    }
    
    public function locationToString()
    {
        $arr = array();
        $country = Wpjb_List_Country::getByCode($this->candidate_country);
        $country = trim($country['name']);

        if(strlen(trim($this->candidate_location))>0) {
            $arr[] = $this->candidate_location;
        }

        if($this->candidate_country == 840 && strlen(trim($this->candidate_state))>0) {
            $arr[] = $this->candidate_state;
        } else if(strlen($country)>0) {
            $arr[] = $country;
        }

        return apply_filters("wpjb_location_display", implode(", ", $arr), $this);
    }
    
    public static function import($item) 
    {
        global $wpdb;
	
        $result = array(
            "type" => "resume",
            "action" => "", // insert | update | fail
            "id" => "",
            "title" => "",
            "admin_url" => "",
            "messages" => array()
        );
        
        $first_name = "";
        $last_name = "";
        
        if(isset($item->first_name)) {
            $first_name = (string)$item->first_name;
        }
        
        if(isset($item->last_name)) {
            $last_name = (string)$item->last_name;
        }
        
        $fullname = trim($first_name." ".$last_name);
        
        $user = get_user_by("login", (string)$item->user_login);
        if($user === false) {
            $user_id = wp_create_user(
                (string)$item->user_login, 
                (string)$item->user_password, 
                (string)$item->user_email
            );

            if($user_id instanceof WP_Error) {
                
                $result["action"] = "fail";
                $result["title"] = (string)$fullname;
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
        
        if( isset( $item->candidate_slug ) ) {
            $slug = (string)$item->candidate_slug;
        } elseif( isset( $fullname ) ) {
            $slug = Wpjb_Utility_Slug::generate( Wpjb_Utility_Slug::MODEL_RESUME, $fullname );
        } else {
            $slug = wp_generate_password();
        }

        $default = new stdClass();
        $default->user_id = $user_id;
        $default->candidate_slug = $slug; //Wpjb_Utility_Slug::generate(Wpjb_Utility_Slug::MODEL_RESUME, $fullname);
        $default->phone = "";
        $default->headline = "";
        $default->description = "";
        $default->created_at = date("Y-m-d");
        $default->modified_at = date("Y-m-d");
        $default->candidate_country = "";
        $default->candidate_state = "";
        $default->candidate_zip_code = "";
        $default->candidate_location = "";
        $default->is_public = 1;
        $default->is_active = 1;

        
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
        
        $vSlug = new Daq_Validate_Db_NoRecordExists(__CLASS__, "candidate_slug", $exclude);
        
        if(!$vSlug->isValid((string)$item->candidate_slug)) {
            
            $result["action"] = "fail";
            $result["messages"][] = array(
                "type" => "fatal",
                "text" => sprintf(__("Item with slug %s already exist.", "wpjobboard"), (string)$item->candidate_slug)
            );
            
            return $result;
        }
        
        $date_match = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/";
        foreach(array("created_at", "modified_at") as $date) {
            if(isset($item->$date) && !empty($item->$date) && !preg_match($date_match, (string)$item->$date)) {
                $result["action"] = "fail";
                $result["messages"][] = array(
                    "type" => "fatal",
                    "text" => sprintf(__("Date format for field %s is invalid (should be YYYY-MM-DD).", "wpjobboard"), $date)
                );

                return $result;
            }
        }
        
        $object->user_id = (string)$item->user_id;
        $object->phone = (string)$item->phone;
        $object->headline = (string)$item->headline;
        $object->description = (string)$item->description;
        $object->created_at = (string)$item->created_at;
        $object->modified_at = (string)$item->modified_at;
        $object->candidate_country = (string)$item->candidate_country;
        $object->candidate_state = (string)$item->candidate_state;
        $object->candidate_zip_code = (string)$item->candidate_zip_code;
        $object->candidate_location = (string)$item->candidate_location;
        $object->is_public = (int)$item->is_public;
        $object->is_active = (int)$item->is_active;
	$object->candidate_slug = (string)$item->candidate_slug;
        $object->save();

        wp_update_user(array(
            "ID" => $user_id,
            "user_email" => (string)$item->user_email,
            "user_url" => (string)$item->user_url,
            "first_name" => (string)$item->first_name,
            "last_name" => (string)$item->last_name,
        ));
        
        Wpjb_Model_ResumeSearch::createFrom($object);

        $result["id"] = $object->id;
        $result["title"] = $fullname;
        $result["admin_url"] = wpjb_admin_url("resumes", "edit", $object->id);
        
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
        
        foreach($item->details->detail as $d) {
            if((int)$d->id > 0) {
                $detail = new Wpjb_Model_ResumeDetail((int)$d->id);
            } else {
                $detail = new Wpjb_Model_ResumeDetail;
            }
            
            if((string)$d->type == "experience") {
                $detail->type = 1;
            } else {
                $detail->type = 2;
            }
            
            $detail->resume_id = $object->id;
            $detail->started_at = (string)$d->started_at;
            $detail->completed_at = (string)$d->completed_at;
            $detail->is_current = (int)$d->is_current;
            $detail->grantor = (string)$d->grantor;
            $detail->detail_title = (string)$d->detail_title;
            $detail->detail_description = (string)$d->detail_description;
            $detail->save();
        }
        
        if(isset($item->tags->tag)) {
            foreach($item->tags->tag as $tag) {

                if($tag->id) {
                    $tid = (int)$tag->id;
                } else {
                    $tid = self::_resolve($tag);
                }

                $query = new Daq_Db_Query();
                $query->select("id");
                $query->from("Wpjb_Model_Tagged t");
                $query->where("tag_id = ?", $tid);
                $query->where("object = ?", "resume");
                $query->where("object_id = ?", $object->id);
                $query->limit(1);
                
                $tagExists = $query->fetchColumn();
                
                if(!$tagExists) {
                    $tagged = new Wpjb_Model_Tagged;
                    $tagged->tag_id = $tid;
                    $tagged->object = "resume";
                    $tagged->object_id = $object->id;
                    $tagged->save();
                }
            }  
        }
        
        if(isset($item->files->file)) {
            foreach($item->files->file as $file) {
                list($path, $filename) = explode("/", (string)$file->path);
                $upload = wpjb_upload_dir("resume", $path, $object->id, "basedir");
                wp_mkdir_p($upload);
                file_put_contents($upload."/".$filename, base64_decode((string)$file->content));
            }
        }
        

        do_action("wpjb_imported_resume", $object->id, $exists, $item);
        do_action("wpjb_resume_saved", new Wpjb_Model_Resume($object->id));
        
        return $result;
    }
    
    public function export(Daq_Helper_Xml $xml = null)
    {
        if($xml === null) {
            $xml = new Daq_Helper_Xml();
        }
        
        $user = $this->getUser(true);
        
        $xml->open("candidate");
        $xml->tagIf("id", $this->id);
        $xml->tagIf("user_login", $user->user_login);
        $xml->tagIf("user_password", $user->user_password);
        $xml->tagIf("user_email", $user->user_email);
        $xml->tagIf("user_url", $user->user_url);
        $xml->tagIf("first_name", get_user_meta($user->ID, "first_name", true));
        $xml->tagIf("last_name", get_user_meta($user->ID, "last_name", true));
        $xml->tagIf("phone", $this->phone);
        $xml->tagIf("headline", $this->headline);
        $xml->tagCIf("description", $this->description);
        $xml->tagIf("created_at", $this->created_at);
        $xml->tagIf("modified_at", $this->modified_at);
        $xml->tagIf("candidate_country", $this->candidate_country);
        $xml->tagIf("candidate_state", $this->candidate_state);
        $xml->tagIf("candidate_zip_code", $this->candidate_zip_code);
        $xml->tagIf("candidate_location", $this->candidate_location);
        $xml->tagIf("is_public", $this->is_public);
        $xml->tagIf("is_active", $this->is_active);
        
        $xml->open("details");
        foreach($this->getDetails() as $detail) {
            $xml->open("detail");
            $xml->tag("id", $detail->id);
            $xml->tag("type", ($detail->type==1) ? "experience" : "education");
            $xml->tag("started_at", $detail->started_at);
            $xml->tag("completed_at", $detail->completed_at);
            $xml->tag("is_current", intval($detail->is_current));
            $xml->tagIf("grantor", $detail->grantor);
            $xml->tagIf("detail_title", $detail->detail_title);
            $xml->tagCIf("detail_description", $detail->detail_description);
            $xml->close("detail");
        }
        $xml->close("details");
        
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
        
        $xml->open("tags");
        foreach($this->tag as $key => $tags) {
            foreach($tags as $tag) {
                $xml->open("tag");
                $xml->tag("type", $key);
                $xml->tag("title", $tag->title);
                $xml->tag("slug", $tag->slug);
                $xml->close("tag");
            }
        }
        $xml->close("tags");
        
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
        
        
        $xml->close("candidate");
    }
    
    public static function importCsv($data) 
    {
        $key = "candidate";
        $form = new Wpjb_Form_Admin_Resume();
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
                
                if(filter_var($value, FILTER_VALIDATE_URL) || stripos($value, "http://")===0) {
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
            } elseif(in_array($k, array("type", "category"))) {
                
                $tag = new stdClass();
                $tag->type = $k;
                $tag->title = $value;
                
                if($k == "category") {
                    $func = "wpjb_get_categories";
                } else {
                    $func = "wpjb_get_jobtypes";
                }
                
                foreach($func() as $t) {
                    if($t->title == $value) {
                        $tag->id = $t->id;
                    }
                }
                
                if(!isset($tag->id)) {
                    $tag->slug = Wpjb_Utility_Slug::generate($k, $value);
                }
                
                $import->tags->tag[] = $tag; 
            }
        }
        
        return self::import($import);
    }
    
    public function csv($columns) {
        
        $o = array(
            "candidate" => $this
        );
        $a = array(
            "candidate" => array(
                "user_login" => $this->getUser(true)->user_login,
                "user_password" => $this->getUser(true)->user_password,
                "first_name" => get_user_meta($this->user_id, "first_name", true),
                "last_name" => get_user_meta($this->user_id, "last_name", true),
                "user_email" => $this->getUser(true)->user_email,
                "user_url" => $this->getUser(true)->user_url,
                
            )
        );
        $ommit = array("candidate.image");
        
        $data = array_fill_keys($columns, "");
 
        foreach($columns as $column) {
            list($object, $property) = explode(".", $column);
            
            if($o[$object]->get($property)) {
                $data[$column] = $o[$object]->get($property);
            } elseif(isset($o[$object]->file->{$property}[0])) {
                $upload = wpjb_upload_dir("resume", str_replace("_", "-", $property), $o[$object]->id);
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
    
    protected static function _resolve($tag) 
    {
        $query = new Daq_Db_Query();
        $query->select();
        $query->from("Wpjb_Model_Tag t");
        $query->where("type = ?", $tag->type);
        $query->where("slug = ?", $tag->slug);
        $query->limit(1);
        
        $result = $query->execute();
        
        if(empty($result)) {
            $t = new Wpjb_Model_Tag;
            $t->type = $tag->type;
            $t->slug = $tag->slug;
            $t->title = $tag->title;
            
            if(isset($tag->order)) {
                $t->order = $tag->order;
            }
            if(isset($tag->parent_id)) {
                $t->parent_id = $tag->parent_id;
            }
            
            $t->save();
        } else {
            $t = $result[0];
        }
        
        return $t->id;
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
    
    public function addAccessKey($id, $hash) 
    {
        $meta = $this->meta->access_keys->getFirst();
        $meta->value = $meta->value . "[" . $id . "#" . $hash . "]\n";
        $meta->save();
    }
    
    /**
     * Accepts payment for single resume access
     * 
     * @since 4.0.0
     * @return boolean True if success
     */
    public function paymentAccepted(Wpjb_Model_Payment $payment)
    {
        $hash = md5("{$payment->id}|{$payment->object_id}|{$payment->object_type}|{$payment->paid_at}");
        
        if($payment->user_id > 0) {
            $id = $payment->user_id;
            $params = array("hash"=>$hash);
        } else {
            $id = $payment->email;
            $params = array("hash"=>$hash, "hash_id"=>$id);
        }
        
        $this->addAccessKey($id, $hash);
        
        $message = Wpjb_Utility_Message::load("notify_employer_resume_paid");
        $message->assign("resume", $this);
        $message->assign("resume_unique_url", wpjr_link_to("resume", $this, $params));
        $message->setTo($payment->email);
        $message->send();
        
        return true;
    }
    
    public function url()
    {
        return wpjr_link_to("resume", $this);
    }
    
    public function doScheme($name)
    {
        $scheme = apply_filters("wpjb_scheme", get_option("wpjb_form_resume"), $this);
        
        if(wpjb_scheme_get($scheme, $name.".visibility")>0) {
            return true;
        } elseif(wpjb_scheme_get($scheme, $name.".render_callback")) {
            call_user_func(wpjb_scheme_get($scheme, $name.".render_callback"), $this);
            return true;
        }

        return false;
    }
    
    public function __get($key) 
    {
        if($key == "file") {
            $uploads = $this->_getFileUploads("resume");
            $files = $this->_getFileLinks($uploads);

            return $files;
        } elseif($key == "user") {
            
            if(!$this->_user) {
                $this->_user = new WP_User($this->user_id);
            }
            
            return $this->_user;
            
        } else {
            return parent::__get($key);
        }
    }
    
    /**
     * Returns all resume files (uploaded + links)
     * 
     * @since 4.4.1
     * @return array List of files
     */
    public function getFiles()
    {
        $uploads = $this->_getFileUploads("resume");
        $files = $this->_getFileLinks($uploads);

        $file = maybe_unserialize($this->meta->image->value());
        if(is_array($file)) {
            foreach($file as $link) {
                
                $file = wp_prepare_attachment_for_js($link["id"]);

                $obj = new stdClass();
                $obj->basename = $file["filename"];
                $obj->url = $link["url"];
                $obj->size = $file["filesizeInBytes"];
                $obj->ext = pathinfo($obj->basename, PATHINFO_EXTENSION);
                $obj->dir = get_attached_file( $link["id"] );

                $files->image[] = $obj;
            }
        }

        return $files;
    }
    
    public function toArray()
    {
        $arr = parent::toArray();
        
        $arr["tag"] = array();
        
        foreach($this->tag() as $key => $tag) {
            $arr["tag"][$key] = array();
            foreach($tag as $t) {
                $arr["tag"][$key][] = $t->toArray();
            }
        }
        
        $fn = get_user_meta($this->user_id, "first_name", true);
        $ln = get_user_meta($this->user_id, "last_name", true);
        
        $arr["url"] = wpjr_link_to("resume", $this);
        $arr["admin_url"] = wpjb_admin_url("resumes", "edit", $this->id);
        $arr["full_name"] = trim(sprintf("%s %s", $fn, $ln));
        
        $arr["country"] = Wpjb_List_Country::getByCode($this->candidate_country);
        
        $arr["education"] = array();
        $arr["experience"] = array();
        
        foreach($this->getDetails(true) as $detail) {

            if($detail->type == Wpjb_Model_ResumeDetail::EXPERIENCE) {
                $d = "experience";
            } else {
                $d = "education";
            }
            
            if(!isset($arr[$d])) {
                $arr[$d] = array();
            }
            
            $arr[$d][] = $detail->toArray();
        }
        
        $upload = wpjb_upload_dir("resume", "", $this->id);

        foreach($this->file as $file => $flist) {
            foreach($flist as $data) {
                
                if(stripos($data->basename, "__") === 0) {
                    continue;
                }

                if(!isset($arr["file"])) {
                    $arr["file"] = array();
                }
                
                if(!isset($arr["file"][$file])) {
                    $arr["file"][$file] = array();
                }
                
                $data = array(
                    "basename" => $data->basename,
                    "url" => $data->url,
                    "size" => $data->size
                );
                
                $arr["file"][$file][] = $data;
            }
        }
        
        $arr["user"] = $this->getUser(true)->toArray();
        
        return $arr;
        
    }
    
    public function completed()
    {
        $form = new Wpjb_Form_Resume($this->id);
        $values = $form->getValues();
        $allowed = array("education", "experience");
        
        $filled = 0;
        $fields = 0;
        
        foreach($values as $k => $v) {
            $field = $form->getElement($k);
            $type = $field->getType();
            
            if(in_array($type, array("hidden", "checkbox", "radio"))) {
                continue;
            }
            
            if($type == "file" && isset($this->file->$k)) {
                $filled++;
            } elseif($type != "file" && !empty($v)) {
                $filled++;
            }
            
            $fields++;   
        }
        
        foreach($form->getGroups() as $group) {
            if(in_array($group->getName(), $allowed) && !$group->isTrashed()) {
                $fields++;
            }
        }
        
        if(count($this->getExperience())>0) {
            $filled++;
        }
        if(count($this->getEducation())>0) {
            $filled++;
        }
        
        $completed = round(($filled/$fields)*100);
        
        if( $completed > 100 ) {
            $completed = 100;
        }
        
        return $completed;
    }
    
    public function cpt() 
    {
        if($this->hasActiveProfile()) {
            $status = "publish";
        } else {
            $status = "wpjb-disabled";
        }
        
        $user = new WP_User($this->user_id);
        
        if(!$this->post_id || !get_post($this->post_id)) {
            // create new
            $post_id = wp_insert_post(array(
                "post_title" => trim($user->first_name." ".$user->last_name),
                "post_name" => $this->candidate_slug,
                "post_type" => "resume",
                "post_status" => $status,
                "comment_status" => "closed"
            ));
            
            $this->post_id = $post_id;
            parent::save();
            
        } else {
            
            $post_id = wp_update_post(array(
                "ID" => $this->post_id,
                "post_title" => trim($user->first_name." ".$user->last_name),
                "post_name" => $this->candidate_slug,
                "post_modified" => current_time("mysql"),
                "post_modified_gmt" => current_time("mysql", true),
                "post_type" => "resume",
                "post_status" => $status,
                "post_content" => $this->headline
            ));
        }
        
        do_action("wpjb_cpt", $this, $post_id);
        
        return $post_id;
    }
    
    
}

?>