ALTER TABLE  `{$wpdb->prefix}wpjb_job` ADD  `post_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL AFTER  `id`, ADD UNIQUE (`post_id`) ; --
ALTER TABLE  `{$wpdb->prefix}wpjb_company` ADD  `post_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL AFTER  `id`, ADD UNIQUE (`post_id`) ; --
ALTER TABLE  `{$wpdb->prefix}wpjb_resume` ADD  `post_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL AFTER  `id`, ADD UNIQUE (`post_id`) ; --

ALTER TABLE  `{$wpdb->prefix}wpjb_company` ADD  `company_slug` VARCHAR( 80 ) NULL AFTER  `company_name` ; --
UPDATE `{$wpdb->prefix}wpjb_company` SET `company_slug` = `id` ; --

ALTER TABLE  `{$wpdb->prefix}wpjb_job` CHANGE  `job_city`  `job_city` VARCHAR( 120 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;