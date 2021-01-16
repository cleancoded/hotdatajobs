<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Export
 *
 * @author Grzegorz
 */
class Wpjb_Module_Ajax_Export {
    
    protected static $t1 = null;
    
    public static function deriveParams($query, $object = null)
    {
        $matches = null;
        $param = array("meta"=>array());
        $replace = str_replace(array("[", "]"), " ", $query);
        $replace = "[query:".preg_replace("#([a-zA-Z0-9_]*)\:#", "][\$1:", $replace)."]";
        $replace = str_replace(" ]", "]", $replace);
        preg_match_all("/\[([a-zA-Z0-9_]+):([^\]]+)\]/", $replace, $matches, PREG_SET_ORDER);
        
        foreach($matches as $match) {
            if(!isset($match[2]) || empty($match[2])) {
                continue;
            }
            
            if(isset($object->meta->{$match[1]})) {
                $param["meta"][$match[1]] = $match[2];
            } else {
                $param[$match[1]] = $match[2];
            }
        }
        
        return $param;
    }
    
    public static function mapping()
    {
        $map = array(
            "meta" => array(
                "tag" => "metas",
                "model" => "Wpjb_Model_Meta",
                "ids" => array(),
                "links" => array()
            ),
            "job" => array(
                "tag" => "jobs",
                "model" => "Wpjb_Model_Job",
                "ids" => array(),
                "links" => array(
                    "employer_id" => "company"
                )
            ),
            "company" => array(
                "tag" => "companies",
                "model" => "Wpjb_Model_Company",
                "ids" => array(),
                "links" => array()
            ),
            "application" => array(
                "tag" => "applications",
                "model" => "Wpjb_Model_Application",
                "ids" => array(),
                "links" => array(
                    "job_id" => "job"
                )
            ),
            "candidate" => array(
                "tag" => "candidates",
                "model" => "Wpjb_Model_Resume",
                "ids" => array(),
                "links" => array()
            ),
        );
        
        return array(
            "order" => array(),
            "fields" => array(),
            "map" => $map,
        );
    }
    
    public static function filename($name, $ext = "dat") 
    {
        $dir = wp_upload_dir();
        $file = $dir["basedir"] . "/wpjobboard-export/" . $name . "." . $ext;

        if(!is_dir($dir["basedir"] . "/wpjobboard-export/")) {
            wp_mkdir_p($dir["basedir"] . "/wpjobboard-export/");
        }
        
        return $file;
    }
    
    public function downloadAction()
    {
        $request = Daq_Request::getInstance();
        $name = $request->get("name");
        
        $realname = str_replace(".dat", ".*", self::filename($name));
        $files = glob($realname);
        if(empty($files)) {
            wp_die("No Export File Found!");
            exit;
        }
        
        $filename = $files[0];
        $pathinfo = pathinfo($filename);
        $format = $pathinfo["extension"];
        
        if($format == "zip") {
            $newname = "wpjb-".date("Y-m-d").".zip";
            
            header('Content-disposition: attachment; filename="'.$newname.'"');
            header('Content-type: "application/zip"; charset="utf8"');

            readfile($filename);
        } elseif($format == "xml") {
            $newname = "wpjb-".date("Y-m-d").".xml";

            header('Content-disposition: attachment; filename="'.$newname.'"');
            header('Content-type: "text/xml"; charset="utf8"');

            readfile($filename);
        } elseif($format == "csv") {
            $newname = "wpjb-".date("Y-m-d").".csv";

            header('Content-disposition: attachment; filename="'.$newname.'"');
            header('Content-type: "application/csv"; charset="utf8"');
            
            readfile($filename);
        }
        
        wp_delete_file($filename);
        exit;
    }
    
    public function allAction()
    {
        $params = array("ids_only"=>true, "filter"=>"all");
        $total = 0;
        
        $transient = get_current_user_id() . "-" . uniqid();
        $mapping = self::mapping();
        $mapping["order"] = array("meta", "job", "application", "company", "candidate");
        $mapping["closed"] = true;
        $mapping["zipped"] = false;
        $mapping["files"] = 1;
        
        $ids = Wpjb_Model_Meta::search($params)->meta;
        $total += count($ids);
        $mapping["map"]["meta"]["ids"] = array_combine($ids, $ids);
        $mapping["map"]["meta"]["links"] = array();
        unset($ids);
        
        $ids = Wpjb_Model_JobSearch::search($params)->job;
        $total += count($ids);
        $mapping["map"]["job"]["ids"] = array_combine($ids, $ids);
        $mapping["map"]["job"]["links"] = array();
        unset($ids);
        
        $ids = Wpjb_Model_Application::search($params)->application;
        $total += count($ids);
        $mapping["map"]["application"]["ids"] = array_combine($ids, $ids);
        $mapping["map"]["application"]["links"] = array();
        unset($ids);
        
        $ids = Wpjb_Model_Company::search($params)->company;
        $total += count($ids);
        $mapping["map"]["company"]["ids"] = array_combine($ids, $ids);
        $mapping["map"]["company"]["links"] = array();
        unset($ids);
        
        $ids = Wpjb_Model_ResumeSearch::search($params)->resume;
        $total += count($ids);
        $mapping["map"]["candidate"]["ids"] = array_combine($ids, $ids);
        $mapping["map"]["candidate"]["links"] = array();
        unset($ids);
        
        $mapping = apply_filters("wpjb_export_mapping", $mapping, "all");
        
        wpjb_session()->set($transient, $mapping);
        
        $filename = self::filename($transient);
        file_put_contents($filename, "");
        
        $xml = new Daq_Helper_Xml($filename);
        $xml->declaration();
        $xml->open("wpjb");
        
        $response = new stdClass();
        $response->name = $transient;
        $response->count = $total;
        
        echo json_encode($response);
        
        exit;
    }
    
    public static function companiesAction()
    {
        $request = Daq_Request::getInstance();
        
        $query = $request->post("query");
        
        $param = self::deriveParams($query, new Wpjb_Model_Company());
        $param["filter"] = $request->post("filter", "all");
        $param["page"] = 1;
        $param["count"] = null;
        $param["posted"] = null;
        $param["ids_only"] = true;
        
        if($request->post("posted")) {
            $p = $request->post("posted");
            $df = date("Y-m-01", strtotime($p));
            $param["date_from"] = $df;
            $param["date_to"] = date("Y-m-t", strtotime($df));
        }
        
        $ids = Wpjb_Model_Company::search($param)->company;
        
        $order = $request->post("objects");
        $fields = $request->post("fields", array());
        
        $transient = get_current_user_id() . "-" . uniqid();
        $mapping = self::mapping();
        $mapping["order"] = $order;
        $mapping["fields"] = $fields;
        $mapping["map"]["company"]["ids"] = array_combine($ids, $ids);
        $mapping["closed"] = true;
        $mapping["zipped"] = false;
        $mapping["files"] = 1;
        
        $mapping = apply_filters("wpjb_export_mapping", $mapping, "companies");
        
        wpjb_session()->set($transient, $mapping);
        

        if($request->post("format") == "csv") {
            $filename = self::filename($transient, "csv");
            $fp = fopen($filename, 'w');
            fputcsv($fp, $mapping["fields"]);
            fclose($fp);
        } else {
            $filename = self::filename($transient);
            file_put_contents($filename, "");
            
            $xml = new Daq_Helper_Xml($filename);
            $xml->declaration();
            $xml->open("wpjb");
        }
        
        $response = new stdClass();
        $response->name = $transient;
        $response->count = count($ids);
        
        echo json_encode($response);
        exit;
    }
    
    public static function candidatesAction()
    {
        $request = Daq_Request::getInstance();
        
        $query = $request->post("query");
        
        $param = self::deriveParams($query, new Wpjb_Model_Resume);
        $param["filter"] = $request->post("filter", "all");
        $param["page"] = 1;
        $param["count"] = null;
        $param["posted"] = null;
        $param["ids_only"] = true;
        
        if($request->post("posted")) {
            $p = $request->post("posted");
            $df = date("Y-m-01", strtotime($p));
            $param["date_from"] = $df;
            $param["date_to"] = date("Y-m-t", strtotime($df));
        }
        
        $ids = Wpjb_Model_ResumeSearch::search($param)->resume;
        
        $order = $request->post("objects");
        $fields = $request->post("fields", array());
        
        $transient = get_current_user_id() . "-" . uniqid();
        $mapping = self::mapping();
        $mapping["order"] = $order;
        $mapping["fields"] = $fields;
        $mapping["map"]["candidate"]["ids"] = array_combine($ids, $ids);
        $mapping["closed"] = true;
        $mapping["zipped"] = false;
        $mapping["files"] = 1;
        $mapping["done"] = 0;
        
        $mapping = apply_filters("wpjb_export_mapping", $mapping, "candidates");
        
        wpjb_session()->set($transient, $mapping);
        

        if($request->post("format") == "csv") {
            $filename = self::filename($transient, "csv");
            $fp = fopen($filename, 'w');
            fputcsv($fp, $mapping["fields"]);
            fclose($fp);
        } else {
            $filename = self::filename($transient);
            file_put_contents($filename, "");
            
            $xml = new Daq_Helper_Xml($filename);
            $xml->declaration();
            $xml->open("wpjb");
        }
        
        $response = new stdClass();
        $response->name = $transient;
        $response->count = count($ids);
        
        echo json_encode($response);
        exit;
    }
    
    public static function applicationsAction()
    {
        $request = Daq_Request::getInstance();
        
        $query = $request->post("query");
        
        $param = self::deriveParams($query, new Wpjb_Model_Application);
        $param["filter"] = $request->post("filter", "all");
        $param["page"] = 1;
        $param["count"] = null;
        $param["posted"] = null;
        $param["ids_only"] = true;
        
        if(!isset($param["job"])) {
            $param["job"] = null;
        }
        
        if($request->post("posted")) {
            $p = $request->post("posted");
            $df = date("Y-m-01 00:00:00", strtotime($p));
            $param["date_from"] = $df;
            $param["date_to"] = date("Y-m-t 23:59:59", strtotime($df));
            $param["posted"] = $p;
        }
        
        $ids = Wpjb_Model_Application::search($param)->application;
        
        $order = $request->post("objects");
        $fields = $request->post("fields", array());
        
        $transient = get_current_user_id() . "-" . uniqid();
        $mapping = self::mapping();
        $mapping["order"] = $order;
        $mapping["fields"] = $fields;
        $mapping["map"]["application"]["ids"] = array_combine($ids, $ids);
        $mapping["closed"] = true;
        $mapping["zipped"] = false;
        $mapping["files"] = 1;
        
        $mapping = apply_filters("wpjb_export_mapping", $mapping, "applications");
        
        wpjb_session()->set($transient, $mapping);
        

        if($request->post("format") == "csv") {
            $filename = self::filename($transient, "csv");
            $fp = fopen($filename, 'w');
            fputcsv($fp, $mapping["fields"]);
            fclose($fp);
        } else {
            $filename = self::filename($transient);
            file_put_contents($filename, "");
            
            $xml = new Daq_Helper_Xml($filename);
            $xml->declaration();
            $xml->open("wpjb");
        }
        
        $response = new stdClass();
        $response->name = $transient;
        $response->count = count($ids);
        
        echo json_encode($response);
        exit;


    }
    
    public static function jobsAction() 
    {
        $request = Daq_Request::getInstance();
        
        $q = $request->post("query");
        
        if($request->get("employer")) {
            $q .= " employer_id:".$request->post("employer");
        }
        
        $param = array(
            "filter" => "all",
            "location" => "",
            "posted" => "",
            "sort" => "",
            "order" => ""
        );

        $query = array_merge($param, self::deriveParams($q, new Wpjb_Model_Job));
        
        if($request->post("filter")) {
            $query["filter"] = $request->post("filter");
        }

        if($request->post("posted")) {
            $p = $request->post("posted");
            $query["date_from"] = date("Y-m-01", strtotime($p));
            $query["date_to"] = date("Y-m-t", strtotime($query["date_from"]));
        }

        $query["page"] = 1;
        $query["count"] = null;
        $query["hide_filled"] = false;
        $query["ids_only"] = true;
  
        $ids = Wpjb_Model_JobSearch::search($query)->job;
        $order = $request->post("objects");
        $fields = $request->post("fields", array());
        
        $transient = get_current_user_id() . "-" . uniqid();
        $mapping = self::mapping();
        $mapping["order"] = $order;
        $mapping["fields"] = $fields;
        $mapping["map"]["job"]["ids"] = array_combine($ids, $ids);
        $mapping["closed"] = true;
        $mapping["zipped"] = false;
        $mapping["files"] = 1;
        
        $mapping = apply_filters("wpjb_export_mapping", $mapping, "jobs");
        
        wpjb_session()->set($transient, $mapping);
        

        if($request->post("format") == "csv") {
            $filename = self::filename($transient, "csv");
            $fp = fopen($filename, 'w');
            fputcsv($fp, $mapping["fields"]);
            fclose($fp);
        } else {
            $filename = self::filename($transient);
            file_put_contents($filename, "");
            
            $xml = new Daq_Helper_Xml($filename);
            $xml->declaration();
            $xml->open("wpjb");
        }
        
        $response = new stdClass();
        $response->name = $transient;
        $response->count = count($ids);
        
        echo json_encode($response);
        
        exit;
    }
    
    public static function endRequest()
    {
        $mLimit = apply_filters("wpjb_export_max_memory", 64);
        $mUsage = round(memory_get_usage(true)/1048576,2);
        
        if($mUsage > $mLimit) {
            return true;
        }
        
        $tLimit = apply_filters("wpjb_export_max_time", 15);
        $tUsage = time() - self::$t1;
        
        if($tUsage > $tLimit) {
            return true;
        }
        
        return false;
    }
    
    public static function zip($name)
    {
        $transient = wpjb_session()->get($name);
        
        $dir = wp_upload_dir();
        $basedir = $dir["basedir"];

        $zipfile = $basedir . "/wpjobboard-export/" . $name . ".zip";
        $datfile = $basedir . "/wpjobboard-export/" . $name . ".dat";
        $newfile = str_pad($transient["files"], 3, 0, STR_PAD_LEFT) . "-" . $name . ".xml";

        if(!is_dir($basedir . "/wpjobboard-export/")) {
            wp_mkdir_p($basedir . "/wpjobboard-export/");
        }
        
        if(class_exists("ZipArchive")) {
            $zip = new ZipArchive;
            
            if(!is_file($zipfile)) {
                $flags = ZipArchive::CREATE;
            } else {
                $flags = null;
            }
            
            if($zip->open($zipfile, $flags) === TRUE) {
                $zip->addFile($datfile, $newfile);
                $zip->close();
                // file_put_contents ($datfile, "");
                unlink($datfile);
            } 
        }
    }
    
    public static function xmlAction()
    {   
        self::$t1 = time();
        
        $request = Daq_Request::getInstance();
        $name = $request->post("name");
        $transient = wpjb_session()->get($name);
        $maxSize = apply_filters("wpjb_export_max_file_size", 10);
        
        $filename = self::filename($name);
        $todo = 0;
        $did = 0;
        $download = null;
        
        $order = $transient["order"];
        $mapping = $transient["map"];
        
        $xml = new Daq_Helper_Xml($filename);
        
        foreach($order as $key) {
            $map = $mapping[$key];
            
            if($transient["zipped"]) {
                $xml->declaration();
                $xml->open("wpjb");
                $transient["zipped"] = false;
            }
            
            if($transient["closed"]) {
                $xml->open($map["tag"]);
            }
            
            foreach($map["ids"] as $id) {
                $model = $map["model"];
                $object = new $model($id);
                $object->export($xml);
                
                $transient["done"]++;
                array_shift($transient["map"][$key]["ids"]);
                
                foreach($map["links"] as $column => $link) {
                    if($object->$column>0 && isset($mapping[$link]) && in_array($link, $order)) {
                        $mapping[$link]["ids"][$object->$column] = $object->$column;
                        $transient["map"][$link]["ids"][$object->$column] = $object->$column;
                    }
                }
                
                wpjb_session()->set($name, $transient);
                unset($object);
                $did++;
                
                if($xml->size() > $maxSize) {
                    $xml->close($map["tag"]);
                    $xml->close("wpjb");
                    
                    self::zip($name);
                    
                    $transient["zipped"] = true;
                    $transient["files"]++;
                    wpjb_session()->set($name, $transient);
                    break 2;
                    
                }
                
                if(self::endRequest() && !empty($transient["map"][$key]["ids"])) {
                    $transient["closed"] = false;
                    wpjb_session()->set($name, $transient);
                    break 2;
                }
                

            }
            $xml->close($map["tag"]);
            $transient["closed"] = true;
        }
        
        foreach($transient["map"] as $tr) {
            if(is_array($tr["ids"])) {
                $todo += count($tr["ids"]);
            }
        }
        
        if($todo == 0) {
            
            if($transient["closed"] == true && $transient["files"] == 1) {
                $xml->close("wpjb");
            }
            if($transient["closed"] == true && $transient["files"] > 1 && $transient["zipped"] == false) {
                $xml->close("wpjb");
            }
            
            if($transient["files"] > 1 && $transient["zipped"] == false) {
                self::zip($name);
            }
            
            if($transient["files"] > 1) {
                $ext = "zip";
                $newname = str_replace(".dat", ".zip", $filename);
            } else {
                $ext = "xml";
                $newname = str_replace(".dat", ".xml", $filename);
                rename($filename, $newname);
            }
            
            wpjb_session()->delete($name);
            
            if(filesize($newname) > 50000000) {
                $download = "direct";
                $dir = wp_upload_dir();
                $baseurl = $dir["baseurl"];
                $url = $baseurl . "/wpjobboard-export/" . $name . "." . $ext;
            } else {
                $download = "push";
                $url = admin_url("admin-ajax.php") . "?action=wpjb_export_download&name=" . $name;
            }
            
        }
        
        $response = new stdClass();
        $response->name = $name;
        $response->todo = $todo;
        $response->done = $transient["done"];
        $response->count = $transient["done"]+$todo;
        $response->download = $download;
        $response->url = $url;
        
        echo json_encode($response);
        exit;
    }
    
    public static function csvAction()
    {
        self::$t1 = time();
        
        $request = Daq_Request::getInstance();
        $name = $request->post("name");
        $transient = wpjb_session()->get($name);
        $maxSize = apply_filters("wpjb_export_max_file_size", 10);
        
        $filename = self::filename($name, "csv");
        $todo = 0;
        $did = 0;
        $download = null;
        
        $order = $transient["order"];
        $key = $order[0];
        $mapping = $transient["map"];
        $map = $mapping[$order[0]];
        
        foreach($map["ids"] as $id) {
            
            $model = $map["model"];
            $object = new $model($id);
            $csv = apply_filters("wpjb_export_csv_row", $object->csv($transient["fields"]), $model, $id, $object);
            
            $fp = fopen($filename, "a");
            fputcsv($fp, $csv);
            fclose($fp);
            
            $transient["done"]++;
            array_shift($transient["map"][$key]["ids"]);

            wpjb_session()->set($name, $transient);
            
            unset($object);
            
            $did++;
            
            if(self::endRequest() && !empty($transient["map"][$key]["ids"])) {
                break;
            }
        }
        
        $todo = count($transient["map"][$key]["ids"]);
        
        if($todo == 0) {
            $download = "push";
        }
        
        $response = new stdClass();
        $response->name = $name;
        $response->todo = $todo;
        $response->done = $transient["done"];
        $response->count = $transient["done"]+$todo;
        $response->download = $download;
        
        echo json_encode($response);
        exit;
    }
    
}
