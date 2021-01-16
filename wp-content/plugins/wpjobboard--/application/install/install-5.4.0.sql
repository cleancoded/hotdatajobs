CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpjb_application_log`(
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`action` VARCHAR(260) UNSIGNED NOT NULL,
`action_description` TEXT UNSIGNED NOT NULL,
`add_date` TIMESTAMP UNSIGNED NOT NULL,
`status_id` INT(11) UNSIGNED NOT NULL,
`application_id` INT(11) UNSIGNED NOT NULL,
`user_id` INT(11) UNSIGNED NOT NULL,
PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8; --

INSERT INTO `{$wpdb->prefix}wpjb_meta` (`id`, `name`, `meta_object`, `meta_type`, `meta_value`) VALUES (NULL, 'can_apply', 'pricing', 1, '') ; --