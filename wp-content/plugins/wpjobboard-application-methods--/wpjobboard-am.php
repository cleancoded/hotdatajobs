<?php
/*
Plugin Name: WPJobBoard - Application Methods
Plugin URI: http://wpjobboard.net/
Version: 1.1.1
Author: Simpliko, Mark Winiarski
Author URI: https://simpliko.pl
Description: Allows to select application method fors each job
Text Domain: wpjobboard-am
Domain Path: /languages
*/

// Bail if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once dirname( __FILE__ ) . '/includes/class-wpjobboard-am.php';

$wpjobboard_am = Wpjobboard_Am::get_instance();
