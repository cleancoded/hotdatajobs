<?php
/**
 * Description of Import
 *
 * @author greg
 * @package 
 */

class Wpjb_Module_Admin_Import extends Wpjb_Controller_Admin
{
    public function init()
    {
        $this->_virtual = apply_filters( "wpjb_bulk_actions_functions",array(
           "redirectAction" => array(
               "accept" => array(),
               "object" => "import"
           ),
           "addAction" => array(
                "form" => "Wpjb_Form_Admin_ScheduleImport",
                "info" => __("New job import has been scheduled.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard"),
                "url" => wpjb_admin_url("import", "edit", "%d")
            ),
            "editAction" => array(
                "form" => "Wpjb_Form_Admin_ScheduleImport",
                "info" => __("Form saved.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "deleteAction" => array(
                "info" => __("Scheduled import #%d deleted.", "wpjobboard"),
                "page" => "import"
            ),
            "_delete" => array(
                "model" => "Wpjb_Model_Import",
                "info" => __("Import deleted.", "wpjobboard"),
                "error" => __("There are errors in your form.", "wpjobboard")
            ),
            "_multi" => array(
                "delete" => array(
                    "success" => __("Number of deleted schedules: {success}", "wpjobboard")
                )
            ),
            "_multiDelete" => array(
                "model" => "Wpjb_Model_Import"
            )
        ), "import" );
    }
    
    public function indexAction()
    {
        $this->view->filter = null;
        
        $page = (int)$this->_request->get("p", 1);
        if($page < 1) {
            $page = 1;
        }
        $perPage = $this->_getPerPage();
        
        $query = new Daq_Db_Query();
        $query->select();
        $query->from("Wpjb_Model_Import t");
        $query->limitPage($page, $perPage);
        $result = $query->execute();

        $total = (int)$query->select("COUNT(*) as `total`")->limit(1)->fetchColumn();
        
        $this->view->current = $page;
        $this->view->total = ceil($total/$perPage);
        $this->view->data = $result;
    }
    
    public function addAction($param = array())
    {
        if($this->_request->post("Once")) {
            extract($this->_virtual[__FUNCTION__]);
            $form = new $form();
            /* @var $form Daq_Form_Abstract */
            if($form->isValid($this->_request->getAll())) {
                $import = new Wpjb_Model_Import();
                $import->engine = $form->value("engine");
                $import->keyword = $form->value("keyword");
                $import->category_id = $form->value("category_id");
                $import->country = $form->value("country");
                $import->location = $form->value("location");
                $import->posted = $form->value("posted");
                $import->add_max = $form->value("add_max");
                $import->run();
                
                $this->_addInfo(__("Import finished successfully.", "wpjobboard"));
            } else {
                $this->_addError($error);
            }
            
            $this->view->form = $form;
            
        } else {
            parent::addAction();
        }
    }
    
    public function xmluploadAction()
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $dir = wp_upload_dir();
        
        // Settings
        $targetDir = $dir["basedir"];
        //$targetDir = 'uploads';

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Uncomment this one to fake upload time
        // usleep(5000);

        // Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

        // Clean the fileName for security reasons
        $fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
                $count++;

            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        // Create target dir
        if (!file_exists($targetDir))
            @mkdir($targetDir);

        // Remove old temp files	
        if ($cleanupTargetDir) {
            if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
                while (($file = readdir($dir)) !== false) {
                    $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                    // Remove temp file if it is older than the max age and is not the current file
                    if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
                        @unlink($tmpfilePath);
                    }
                }
                closedir($dir);
            } else {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }
        }	

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"]))
            $contentType = $_SERVER["CONTENT_TYPE"];

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = @fopen($_FILES['file']['tmp_name'], "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else
                        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                    @fclose($in);
                    @fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                } else
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            } else
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
        } else {
                // Open temp file
                $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = @fopen("php://input", "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096))
                                fwrite($out, $buff);
                    } else
                        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

                    @fclose($in);
                    @fclose($out);
                } else
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off 
            rename("{$filePath}.part", $filePath);
			$this->_handle($filePath);
        }

        die('{"jsonrpc" : "2.0", "result" : "", "id" : "id"}');
    }
	
    protected function _handle($file) 
    {
        $dir = wp_upload_dir();
        $basedir = $dir["basedir"]."/wpjobboard-import";
        $basename = basename($file);

        $pathparts = pathinfo($file);
        $ext = $pathparts["extension"];

        if(!is_dir($basedir)) {
            wp_mkdir_p($basedir);
        }

        if($ext == "zip") {
            $zip = new ZipArchive;
            if ($zip->open($file) === TRUE) {
                $zip->extractTo($basedir);
                $zip->close();
            }
            unlink($file);
        } else {
            rename($file, $basedir."/".$basename);
        }
    }

    public function csvcountAction()
    {
        $row = -1;
        
        $request = Daq_Request::getInstance();
        $dir = wp_upload_dir();
        $files = glob($dir["basedir"]."/wpjobboard-import/*.csv");
        $file = $files[0];
        
        $fp = fopen($file, "r");
        
        if($fp === false) {
            echo $file;
            exit;
        }
        
        
        while (($data = fgetcsv($fp, 0)) !== FALSE)  {
            $row++;
            unset($data);
        }
        
        fclose ($fp);
        
        echo $row;
        exit;
    }
    
    public function csvimportAction()
    {
        $request = Daq_Request::getInstance();
        $dir = wp_upload_dir();
        $files = glob($dir["basedir"]."/wpjobboard-import/*.csv");
        $file = $files[0];
        
        $objects = array(
            "job" => "Wpjb_Model_Job",
            "application" => "Wpjb_Model_Application",
            "company" => "Wpjb_Model_Company",
            "resume" => "Wpjb_Model_Resume"
        );
        
        $class = $objects[$request->post("model")];
        
        $fp = fopen($file, "r");
        $headers = fgetcsv($fp, 0);
        $pointer = 0;
        $i = 0;
        
        $response = new stdClass();
        $response->file = basename($file);
        $response->row = (int)$request->post("did", 0);
        $response->imports = array();
        $response->increment = 0;
        
        $max = 20 + $response->row;
        
        for($i=0; $i<$response->row; $i++) {
            fgetcsv($fp, 0);
        }
        

        while (($line = fgetcsv($fp, 0)) !== FALSE)  {
            
            $data = array_combine($headers, $line);
            
            // import code here
            $response->imports[] = call_user_func(array($class, "importCsv"), $data);
            $response->row++;
            
            $i++;
            $response->increment++;
            
            if($i >= $max) {
                $response->hasMore = 1;
                echo json_encode($response);
                exit;
            }
            
            unset($line);
            unset($data);
        }
        
        fclose ($fp);
        unlink($file);
        
        $response->hasMore = 0;
        echo json_encode($response);
        exit;
    }
    
    public function clearqueueAction() 
    {
        $dir = wp_upload_dir();
        $queue = array();
        $basedir = $dir["basedir"]."/wpjobboard-import";
        
        foreach(wpjb_glob($basedir."/*") as $path) {
            $q = array(
                "filename" => basename($path),
                "url" => str_replace($dir["basedir"], $dir["baseurl"], $path),
                "success" => 1,
                "error" => ""
            );
            
            ob_start();
            $deleted = @unlink($path);
            $error = ob_get_clean();
            
            if(!$deleted) {
                $q["success"] = 0;
                $q["error"] = $error;
            }
            
            $queue[] = $q;
        }

        echo json_encode(array("result"=>1, "queue"=>$queue));
        exit;
    }
    
    public function xmlcountAction()
    {
        $dir = wp_upload_dir();
        $basedir = $dir["basedir"]."/wpjobboard-import";

        $files = count((array)glob($basedir."/*.xml"));

        echo $files;
        exit;
    }
    
    public function xmlimportAction()
    {

        $dir = wp_upload_dir();

        $basedir = $dir["basedir"]."/wpjobboard-import";
        $list = (array)glob($basedir."/*.xml");
        $file = $list[0];

        $response = new stdClass();
        $response->increment = 1;

        if(count($list) > 1) {
            $response->hasMore = "1";
        } else {
            $response->hasMore = "0";
        }

        $response->file = basename($file);
        $response->imports = array();

        libxml_use_internal_errors(true);
        
        $xml = simplexml_load_file($file);

        if($xml === false) {
            foreach(libxml_get_errors() as $error) {
                echo $error->message . "\r\n";
            }
            exit;
        }
        
        // Meta Tags
        if(!empty($xml->metas->meta)) {
            foreach ($xml->metas->meta as $item) {
                $response->imports[] = Wpjb_Model_Meta::import($item);
            }
        }

        // Jobs
        if(!empty($xml->jobs->job)) {
            foreach ($xml->jobs->job as $job) {
                $response->imports[] = Wpjb_Model_Job::import($job);
            }
        }

        // Applications
        if(!empty($xml->applications->application)) {
            foreach ($xml->applications->application as $item) {
                $response->imports[] = Wpjb_Model_Application::import($item);
            }
        }

        // Companies
        if(!empty($xml->companies->company)) {
            foreach ($xml->companies->company as $item) {
                $response->imports[] = Wpjb_Model_Company::import($item);
            }
        }

        // Candidates
        if(!empty($xml->candidates->candidate)) {
            foreach ($xml->candidates->candidate as $item) {
                $response->imports[] = Wpjb_Model_Resume::import($item);
            }
        }

        unset($xml);
        unlink($file);

        echo json_encode($response);

        exit;
    }
	
    public function csvAction()
    {
        $dir = wp_upload_dir();
        $queue = array();
        $basedir = $dir["basedir"]."/wpjobboard-import";
        
        foreach(wpjb_glob($basedir."/*") as $path) {
            $queue[] = array(
                "filename" => basename($path),
                "url" => str_replace($dir["basedir"], $dir["baseurl"], $path)
            );
        }

        $this->view->queue = $queue;
    }
    
    public function xmlAction()
    {
        if(class_exists("ZipArchive")) {
            $this->view->canUnzip = true;
        } else {
            $this->_addInfo(__("Your hosting does not support <strong>ZipArchive library</strong>, you can upload only XML files.", "wpjobboard")); 
            $this->view->canUnzip = false;
        }
        
        $dir = wp_upload_dir();
        $queue = array();
        $basedir = $dir["basedir"]."/wpjobboard-import";
        
        foreach(wpjb_glob($basedir."/*") as $path) {
            $queue[] = array(
                "filename" => basename($path),
                "url" => str_replace($dir["basedir"], $dir["baseurl"], $path)
            );
        }

        $this->view->queue = $queue;

    }
    
    public function exmlAction()
    {
        $count = array(
            "meta" => Wpjb_Model_Meta::search(array("filter"=>"all", "count_only"=>true)),
            "application" => Wpjb_Model_Application::search(array("filter"=>"all", "count_only"=>true)),
            "company" => Wpjb_Model_Company::search(array("filter"=>"all", "count_only"=>true)),
            "job" => Wpjb_Model_JobSearch::search(array("filter"=>"all", "count_only"=>true)),
            "resume" => Wpjb_Model_ResumeSearch::search(array("filter"=>"all", "count_only"=>true))
        );
        
        $this->view->count = $count;
    }
   
    protected function _getUniqueSlug($title)
    {
        $slug = sanitize_title_with_dashes($title);
        $newSlug = $slug;
        $isUnique = true;

        $query = new Daq_Db_Query();
        $query->select("t.job_slug")
            ->from("Wpjb_Model_Job t")
            ->where("(t.job_slug = ?", $newSlug)
            ->orWhere("t.job_slug LIKE ? )", $newSlug."%");

        $list = array();
        $c = 0;
        foreach($query->fetchAll() as $q) {
            $list[] = $q->t__job_slug;
            $c++;
        }

        if($c > 0) {
            $isUnique = false;
            $i = 1;
            do {
                $i++;
                $newSlug = $slug."-".$i;
                if(!in_array($newSlug, $list)) {
                    $isUnique = true;
                }
            } while(!$isUnique);
        }

        return $newSlug;
    }
}

?>