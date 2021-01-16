ALTER TABLE  `{$wpdb->prefix}wpjb_payment` CHANGE  `message`  `message` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  ''; --
ALTER TABLE  `{$wpdb->prefix}wpjb_payment` ADD  `status` TINYINT( 1 ) UNSIGNED NOT NULL AFTER  `is_valid` ; --
ALTER TABLE  `{$wpdb->prefix}wpjb_payment` ADD  `pricing_id` INT( 11 ) UNSIGNED NULL AFTER  `engine` , ADD INDEX (  `pricing_id` ) ; --
ALTER TABLE  `{$wpdb->prefix}wpjb_payment` 
    ADD  `fullname` VARCHAR( 250 ) NOT NULL DEFAULT  '' AFTER  `email` ,
    ADD  `user_ip` VARCHAR( 120 ) NOT NULL DEFAULT  '' AFTER  `fullname` ; --
ALTER TABLE  `{$wpdb->prefix}wpjb_payment` ADD  `params` TEXT NOT NULL DEFAULT  ''; --

ALTER TABLE  `{$wpdb->prefix}wpjb_job` DROP  `applications`, DROP  `cache` ; --

ALTER TABLE  `{$wpdb->prefix}wpjb_job` 
    ADD  `membership_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0',
    ADD  `pricing_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0'; --

INSERT INTO  `{$wpdb->prefix}wpjb_meta` (`id`, `name` , `meta_object`, `meta_type`, `meta_value`) VALUES (NULL ,  'access_keys',  'resume',  '1',  ''); --
