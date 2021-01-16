<?php

ob_start();

include "../../../wp-load.php";

global $wpjobboard;

$clean = ltrim($_GET["url"], "/.\\");

list($type, $id, $path) = explode("/", $clean, 3);

$file = wpjb_upload_dir($type, "", $id, "basedir")."".$path;
$finfo = wp_check_filetype_and_ext($file, basename($file));

$isAllowed = false;
$adminMenu = new Wpjb_Utility_AdminMenu();
$menu = $adminMenu->getLeftItems();

if($type == "application") {
    $application = new Wpjb_Model_Application($id);
    $job = new Wpjb_Model_Job($application->job_id);
    
    if(!is_null($job->employer_id) && $job->employer_id == Wpjb_Model_Company::current()->id) {
        $isAllowed = true;
    }
    if(current_user_can($menu["applications"]["access"])) {
        $isAllowed = true;
    }
    
} elseif($type == "resume") {
    
    if(wpjb_conf("cv_privacy") == "1" && wpjr_can_browse($id)) {
        $isAllowed = true;
    }

    if(wpjb_conf("cv_privacy") == "0" && ( stripos($path, "image/") === 0 || wpjr_can_browse($id) ) ) {
        $isAllowed = true;
    }
    
    if(current_user_can($menu["resumes_manage"]["access"])) {
        $isAllowed = true;
    }
}

if(current_user_can("edit_files")) {
    $isAllowed = true;
}

$isAllowed = apply_filters("wpjb_restrict", $isAllowed, $clean, $file);

if($isAllowed) {
    ob_end_clean();
    
    header('Content-type: '.$finfo["type"]);
    header('Content-Disposition: inline; filename="'.basename($file).'"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($file));
    header('Accept-Ranges: bytes');
    
    @readfile($file);
} else {
    wp_die(__("You are not allowed to access this file.", "wpjobboard"));
}



?>
