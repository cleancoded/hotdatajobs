<?php
/* 
 * WPJobBoard uninstaller
 */

global $wpdb;

if(!defined("WP_UNINSTALL_PLUGIN")) {
    return;
}

$file = dirname(__FILE__)."/application/install/uninstall.sql";
$file = file_get_contents($file);

foreach(explode("; --", $file) as $sql) {
    $sql = str_replace('{$wpdb->prefix}', $wpdb->prefix, $sql);
    $wpdb->query($sql);
}

$optArr = array(
    'wpjb_config', 'wpjb_payment_method',
    'wpjb_form_apply', 'wpjb_form_job', 'wpjb_form_job_search',
    'wpjb_form_resume', 'wpjb_form_resume_search',
    
    "widget_wpjb-widget-alerts", "widget_wpjb-job-categories", "widget_wpjb-featured-jobs",
    "widget_wpjb-widget-feeds", "widget_wpjb-job-board-menu", "widget_wpjb-job-types",
    "widget_wpjb-recent-jobs", "widget_wpjb-recently-viewed", "widget_wpjb-resumes-menu",
    "widget_wpjb-search"
);
foreach($optArr as $option) {
    delete_option($option);
}

$wpdb->query("DELETE FROM ".$wpdb->prefix."options WHERE option_name LIKE '_transient_timeout_wpjb_session_%' ");
$wpdb->query("DELETE FROM ".$wpdb->prefix."options WHERE option_name LIKE '_transient_wpjb_session_%' ");

include_once dirname(__FILE__)."/application/functions/common.php";

$upload = wp_upload_dir();
wpjb_recursive_delete($upload["basedir"]."/wpjobboard/");

?>
