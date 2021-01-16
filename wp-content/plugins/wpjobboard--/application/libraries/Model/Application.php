<?php
/**
 * Description of Application
 *
 * @author greg
 * @package
 */

class Wpjb_Model_Application extends Daq_Db_OrmAbstract
{
    const STATUS_REJECTED = 0;
    const STATUS_NEW = 1;
    const STATUS_ACCEPTED =2;
    const STATUS_READ = 3;
    
    protected $_name = "wpjb_application";

    protected $_metaTable = "Wpjb_Model_Meta";
    
    protected $_metaName = "apply";
    
    protected $_fields = array();

    protected $_textareas = array();
    
    /**
     * Copy of original Application data before doing save().
     *
     * @var array
     */
    protected $_copy = null;
    
    protected function _load($id) {
        parent::_load($id);
        $this->_copy = $this->toArray();
    }

    protected function _init()
    {
        $this->_reference["job"] = array(
            "localId" => "job_id",
            "foreign" => "Wpjb_Model_Job",
            "foreignId" => "id",
            "type" => "ONE_TO_ONE"
        );
        $this->_reference["meta"] = array(
            "localId" => "id",
            "foreign" => "Wpjb_Model_MetaValue",
            "foreignId" => "object_id",
            "type" => "ONE_TO_ONCE"
        );
    }
    
    public function __get($key) 
    {
        if($key == "file") {
            $uploads = $this->_getFileUploads("application");
            $files = $this->_getFileLinks($uploads);

            return $files;
        } else {
            return parent::__get($key);
        }
    }

    public function addFile($file)
    {
        $path = Wpjb_List_Path::getPath("apply_file");
        $path.= "/".$this->id."/";

        if(!is_dir($path)) {
            mkdir($path);
        }
        
        copy($file, $path.basename($file));
    }

    public function getFiles()
    {
        $upload = wpjb_upload_dir("application", "*", $this->id);
        $baseurl = $upload["baseurl"];
        $upload = $upload["basedir"]."/*";
        $files = wpjb_glob($upload);     

        $fArr = array();
        foreach($files as $file) {
            $dir = basename(dirname($file));
            $f = new stdClass;
            $f->basename = basename($file);
            $f->url = str_replace("*", $dir, $baseurl)."/".$f->basename;
            $f->size = filesize($file);
            $f->ext = pathinfo($file, PATHINFO_EXTENSION);
            $f->dir = $file;
            $fArr[] = $f;
        }

        $file = maybe_unserialize($this->meta->file->value());
        if(is_array($file)) {
            foreach($file as $link) {
                
                $file = wp_prepare_attachment_for_js($link["id"]);

                $obj = new stdClass();
                $obj->basename = $file["filename"];
                $obj->url = $link["url"];
                $obj->size = $file["filesizeInBytes"];
                $obj->ext = pathinfo($obj->basename, PATHINFO_EXTENSION);
                $obj->dir = get_attached_file( $link["id"] );

                $fArr[] = $obj;
            }
        }
        
        foreach($this->_getFileLinks() as $fileList) {
            foreach($fileList as $file) {
                $f = $file;
                $f->ext = pathinfo($f->basename, PATHINFO_EXTENSION);
                $f->dir = $f->path;
                unset($f->path);
                $fArr[] = $f;
            }
        }

        return $fArr;
    }
    
    public function delete()
    {
        $dir = wpjb_upload_dir("application", "", $this->id, "basedir");
        if(is_dir($dir)) {
            wpjb_recursive_delete($dir);
        }
        
        parent::delete();
    }
    
    public function save()
    {  
        if($this->exists()) {
            $isNew = false;
            $oldId = $this->_copy["id"];
            $oldSt = $this->_copy["status"];
            
            $status = wpjb_get_application_status($this->status);
            
            if($oldSt != $this->status && isset($status["notify_applicant_email"]) && !empty($status["notify_applicant_email"])) {
                
                if(is_numeric( $status["notify_applicant_email"] ) ) {
                    $template = new Wpjb_Model_Email( $status["notify_applicant_email"] );
                    $template_name = $template->name;
                } else {
                    $template_name = $status["notify_applicant_email"];    
                }
                
                $notify = $this->email;
                if(!$notify && $this->user_id > 0) {
                    $notify = get_userdata($this->user_id)->user_email;
                }
                
                $mail = Wpjb_Utility_Message::load( $template_name );
                $mail->assign("application", $this);
                $mail->assign("job", $this->getJob(true));
                $mail->assign("status", wpjb_application_status($this->status));
                $mail->setTo($notify);
                $mail->send();
            }
            
        } else {
            $isNew = true;
        }
        
        $id = parent::save();
        
        if($isNew) {
            do_action("wpjb_application_published", $this);
        }
        
        do_action("wpjb_application_save", $this);
        return $id;
    }
    
    public static function import($item) 
    {
        $result = array(
            "type" => "application",
            "action" => "", // insert | update | fail
            "id" => "",
            "title" => "",
            "admin_url" => "",
            "messages" => array()
        );

        $default = new stdClass();
        $default->job_id = null;
        $default->user_id = null;
        $default->applied_at = date("Y-m-d");
        $default->applicant_name = "";
        $default->message = "";
        $default->email = "";
        $default->status = self::STATUS_NEW;
        
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
        
        $job_id = (int)$item->job_id;
        $job = new Wpjb_Model_Job($job_id);
        if($job_id > 0 && !$job->exists()) {
            $result["messages"][] = array(
                "type" => "warning",
                "text" => sprintf(__("Job identified with ID %d does not exist.", "wpjobboard"), $job_id)
            );

            $job_id = null;
        }
        
        $user_id = (int)$item->user_id;
        $user = new Wpjb_Model_User($user_id);
        if($user_id > 0 && !$user->exists()) {
            $result["messages"][] = array(
                "type" => "warning",
                "text" => sprintf(__("User identified with ID %d does not exist.", "wpjobboard"), $user_id)
            );

            $user_id = null;
        }
        
        $status = (int)$item->status;
        $sList = wpjb_get_application_status();
        if(!isset($sList[$status])) {
            $result["messages"][] = array(
                "type" => "warning",
                "text" => sprintf(__("Status identified with ID %d does not exist.", "wpjobboard"), $status)
            );

            $status = 0;
        }
        
        $object->job_id = $job_id;
        $object->user_id = $user_id;
        $object->applied_at = (string)$item->applied_at;
        $object->applicant_name = (string)$item->applicant_name;
        $object->message = (string)$item->message;
        $object->email = (string)$item->email;
        $object->status = $status;
        $object->save();

        $result["id"] = $object->id;
        $result["title"] = $object->applicant_name;
        $result["admin_url"] = wpjb_admin_url("application", "edit", $object->id);
        
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
                $upload = wpjb_upload_dir("application", $path, $object->id, "basedir");
                wp_mkdir_p($upload);
                file_put_contents($upload."/".$filename, base64_decode((string)$file->content));
            }
        }
        
        do_action("wpjb_imported_application", $object->id, $exists, $item);
        
        return $result;
    }
    
    public static function importCsv($data) 
    {
        $key = "application";
        $form = new Wpjb_Form_Admin_Application();
        $object = new self();
        $fields = $object->getFieldNames();
        
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
            }
        }
        
        return self::import($import);
    }
    
    
    public function export(Daq_Helper_Xml $xml = null) 
    {
        if($xml === null) {
            $xml = new Daq_Helper_Xml();
        }
        
        $xml->open("application");
        $xml->tagIf("id", $this->id);
        $xml->tagIf("job_id", $this->job_id);
        $xml->tagIf("user_id", $this->user_id);
        $xml->tagIf("applied_at", $this->applied_at);
        $xml->tagIf("applicant_name", $this->applicant_name);
        $xml->tagCIf("message", $this->message);
        $xml->tagIf("email", $this->email);
        $xml->tagIf("status", $this->status);

        
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
        
        $xml->close("application");
    }
    
    public function csv($columns) {
        
        $o = array(
            "application" => $this,
            "job" => $this->getJob(true)
        );
        $a = array(
            "application" => array()
        );
        
        $data = array_fill_keys($columns, "");
 
        foreach($columns as $column) {
            list($object, $property) = explode(".", $column);
            
            if($o[$object]->get($property)) {
                $data[$column] = $o[$object]->get($property);
            } elseif(isset($o[$object]->file->{$property}[0])) {
                $upload = wpjb_upload_dir($object, str_replace("_", "-", $property), $o[$object]->id);
                $file = wpjb_glob($upload["basedir"]."/[!_]*");
                $data[$column] = $upload["baseurl"]."/".basename($file[0]);
            } elseif(isset($o[$object]->meta->$property) && is_object($o[$object]->meta->$property)) {
                $data[$column] = join(";", $o[$object]->meta->$property->values());
            } elseif(isset($o[$object]->tag->{$property}[0])) {
                $data[$column] = $o[$object]->tag->{$property}[0]->title;
            } elseif(isset($a[$object][$property])) {
                $data[$column] = $a[$object][$property];
            }
            
        }
        
        return $data;
    }
    
    protected static function _found($query, $grouped)
    {
        if($grouped || $query->get("having")) {
            return count($query->fetchAll());
        } else {
            return $query->fetchColumn();
        }
    }
    
    public static function search($params = array())
    {
        $page = null;
        $query = null;
        $job = null;

        /**
         * @var $count int
         * items per page or maximum number of elements to return
         */
        $count = 25;
        $date_from = null;
        $date_to = null;
        
        /**
         * @var $sort_order mixed
         * string or array, specify sort column and order (either DESC or ASC),
         * you can add more then one sort order. 
         */
        $sort_order = "t1.applied_at DESC, t1.id DESC";
        
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
        
        /**
         * @var status int  Status ID
         */
        $status = null;
        
        /**
         * @var $owned_by int ID of a company who owns the applications
         */
        $owned_by = null;
        
        extract($params);
        
        $groupResults = false;
        
        $select = new Daq_Db_Query();
        $select->select();
        $select->from("Wpjb_Model_Application t1");
        
        $public_ids = array();
        $status_key = array();
        foreach(wpjb_get_application_status() as $application_status) {
            if($application_status["public"] == 1) {
                $public_ids[] = $application_status["id"];
            }
            $status_key[$application_status["key"]] = $application_status["id"];
        }
        
        switch($filter) {
            case "public"  :$select->where("status IN(?)", $public_ids);
        }
        
        if($filter != "public" && isset($status_key[$filter])) {
            $select->where("status = ?", (int)$status_key[$filter]);
        }
        
        if(!empty($status) && is_array($status)) {
            $select->where("status IN (?)", array_map("absint", $status));
        } else if($status && is_numeric($status)) {
            $select->where("status = ?", absint($status));
        }
        
        if($job) {
            $select->where("job_id = ?", $job);
        }

        if($query) {
            $select->where("(applicant_name LIKE ? OR email LIKE ?)", "%$query%");
        }
        
        if($owned_by) {
            $select->join("t1.job owned_by");
            $select->where("owned_by.employer_id = ?", $owned_by);
        }
        
        if(!empty($meta)) {
            $app = new Wpjb_Model_Application();
            $m = 1;
            foreach($meta as $k => $v) {
                if(!is_numeric($k)) {
                    $k = $app->meta->$k->id;
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
        
        if($date_from) {
            $select->where("applied_at >= ?", $date_from);
        }

        if($date_to) {
            $select->where("applied_at <= ?", $date_to);
        }
        
        if($sort_order) {
            $select->order($sort_order);
        }
        
        $select->select("COUNT(*) AS cnt");
        $itemsFound = self::_found($select, $groupResults);

        $select->select("*");
        $select = apply_filters("wpjb_applications_query", $select);
        
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
        $response->application = $list;
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
    
    public function getResume()
    {
        if(!$this->user_id) {
            return null;
        }
        
        $query = new Daq_Db_Query;
        $query->from("Wpjb_Model_Resume t");
        $query->where("user_id = ?", $this->user_id);
        $query->limit(1);
        
        $result = $query->execute();
        
        if(isset($result[0])) {
            return $result[0];
        } else {
            return null;
        }
    }
    
    public function doScheme($name)
    {
        $scheme = apply_filters("wpjb_scheme", get_option("wpjb_form_apply"), $this);
        
        if(wpjb_scheme_get($scheme, $name.".visibility")>0) {
            return true;
        } elseif(wpjb_scheme_get($scheme, $name.".render_callback")) {
            call_user_func(wpjb_scheme_get($scheme, $name.".render_callback"), $this);
            return true;
        }

        return false;
    }
    
}

?>