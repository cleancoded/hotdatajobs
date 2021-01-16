INSERT INTO `{$wpdb->prefix}wpjb_meta` (`id`, `name`, `meta_object`, `meta_type`, `meta_value`) VALUES (NULL, 'package', 'pricing', 1, '') ; --

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpjb_membership`(
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`user_id` INT(11) UNSIGNED NOT NULL,
`package_id` INT(11) UNSIGNED NOT NULL,
`started_at` DATE NOT NULL,
`expires_at` DATE NOT NULL,
`package` TEXT,
PRIMARY KEY(`id`),
KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8; --

INSERT INTO `{$wpdb->prefix}wpjb_mail` (`id`, `name`, `is_active`, `sent_to`, `format`, `mail_title`, `mail_body_text`, `mail_body_html`, `mail_from`, `mail_from_name`, `mail_bcc`) VALUES
(19, 'notify_applicant_status_change', 1, 3, 'text/plain', 'Application status changed', 'Hi there!\r\n\r\nThis is an automated email notifying you that your application for job "{$job.job_title}" has been reviewed. Your application status has been changed to {$status}.\r\n\r\nBest regards,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''); --

ALTER TABLE  `{$wpdb->prefix}wpjb_payment` DROP INDEX `object`, ADD INDEX  `object`( `object_type`, `object_id`); --
