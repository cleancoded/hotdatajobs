CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpjb_shortlist`(
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`user_id` INT(11) UNSIGNED NOT NULL,
`object` VARCHAR(20) NOT NULL,
`object_id` INT(11) NOT NULL,
`shortlisted_at` DATE NOT NULL,
PRIMARY KEY(`id`),
KEY `user_list` (`user_id`, `object`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8; --

ALTER TABLE  `{$wpdb->prefix}wpjb_discount` ADD  `discount_for` TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT  '101' AFTER  `id` ; --

ALTER TABLE  `{$wpdb->prefix}wpjb_application` CHANGE  `job_id`  `job_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL ; --