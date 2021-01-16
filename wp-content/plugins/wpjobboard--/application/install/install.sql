CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpjb_job`(
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`employer_id` INT(11) UNSIGNED,
`job_title` VARCHAR(120) NOT NULL DEFAULT "",
`job_slug` VARCHAR(120) NOT NULL DEFAULT "",
`job_description` TEXT NOT NULL,
`job_created_at` DATE NOT NULL,
`job_modified_at` DATE NOT NULL,
`job_expires_at` DATE NOT NULL,
`job_country` SMALLINT(5) UNSIGNED NOT NULL,
`job_state` VARCHAR(40) NOT NULL,
`job_zip_code` VARCHAR(20) NOT NULL,
`job_city` VARCHAR(20) NOT NULL,
`company_name` VARCHAR(120) NOT NULL DEFAULT "",
`company_url` VARCHAR(120) NOT NULL DEFAULT "",
`company_email` VARCHAR(120) NOT NULL DEFAULT "",
`is_approved` TINYINT(1) UNSIGNED NOT NULL,
`is_active` TINYINT(1) UNSIGNED NOT NULL,
`is_filled` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
`is_featured` TINYINT(1) UNSIGNED NOT NULL,
`applications` INT (11) UNSIGNED NOT NULL DEFAULT 0,
`read` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
`cache` TEXT NOT NULL,
PRIMARY KEY (`id`),
UNIQUE (`job_slug`),
KEY (`employer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8; --

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpjb_meta`(
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`name` VARCHAR(40) NOT NULL,
`meta_object` VARCHAR(20) NOT NULL COMMENT 'job, resume, company',
`meta_type` TINYINT(1) UNSIGNED NOT NULL COMMENT '1:builtin; 2:registered; 3:visual',
`meta_value` TEXT NOT NULL,
PRIMARY KEY(`id`),
UNIQUE `object_name` (`meta_object`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8; --

CREATE TABLE IF NOT EXISTS `{$wpjb->prefix}wpjb_meta_value`(
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`meta_id` INT(11) UNSIGNED NOT NULL,
`object_id` INT(11) UNSIGNED NOT NULL,
`value` TEXT NOT NULL,
PRIMARY KEY(`id`),
KEY(`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8; --

CREATE TABLE IF NOT EXISTS `{$wpjb->prefix}wpjb_job_search` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`job_id` INT(11) UNSIGNED NOT NULL,
`title` VARCHAR(120) NOT NULL,
`description` TEXT NOT NULL,
`company` VARCHAR(120) NOT NULL,
`location` VARCHAR(200) NOT NULL,
PRIMARY KEY(`id`),
UNIQUE (`job_id`),
FULLTEXT KEY `search` (`title`, `description`, `company`, `location`),
FULLTEXT KEY `search_location` (`location`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8; --

CREATE TABLE IF NOT EXISTS `{$wpjb->prefix}wpjb_tag` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`type` VARCHAR(20) NOT NULL COMMENT "category; type",
`slug` VARCHAR(120) NOT NULL,
`title` VARCHAR(120) NOT NULL,
PRIMARY KEY(`id`),
UNIQUE `type_slug` (`type`, `slug`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8; --

CREATE TABLE IF NOT EXISTS `{$wpjb->prefix}wpjb_tagged` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`tag_id` INT(11) UNSIGNED NOT NULL,
`object` VARCHAR(20) NOT NULL,
`object_id` INT(11) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE `search` (`tag_id`, `object`, `object_id`),
KEY `quick_load` (`object`, `object_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8; --

CREATE TABLE IF NOT EXISTS `{$wpjb->prefix}wpjb_mail` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`name` varchar(40) NOT NULL,
`is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
`sent_to` tinyint(1) unsigned NOT NULL COMMENT '1:admin; 2:job poster; 3: other',
`format` varchar(20) NOT NULL DEFAULT 'text/plain',
`mail_title` varchar(120) NOT NULL,
`mail_body_text` text NOT NULL,
`mail_body_html` text NOT NULL,
`mail_from` varchar(120) NOT NULL,
`mail_from_name` varchar(120) DEFAULT NULL,
`mail_bcc` varchar(250) NOT NULL,
PRIMARY KEY  (`id`),
UNIQUE (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ; --

CREATE TABLE IF NOT EXISTS `{$wpjb->prefix}wpjb_pricing` (
`id` int(11) unsigned NOT NULL auto_increment,
`title` VARCHAR(80) NOT NULL,
`price_for` TINYINT(3) UNSIGNED NOT NULL COMMENT '101:single-job; 201:single-resume', 
`price` FLOAT(8,2) unsigned NOT NULL,
`currency` VARCHAR(3) NOT NULL,
`is_active` TINYINT(1) UNSIGNED NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ; --

CREATE TABLE IF NOT EXISTS `{$wpjb->prefix}wpjb_payment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL COMMENT 'foreign key: wp_users.ID',
  `email` VARCHAR(120) NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `object_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1:job; 2:resume',
  `engine` varchar(40) NOT NULL,
  `external_id` varchar(80) NOT NULL,
  `is_valid` tinyint(1) NOT NULL DEFAULT 0 COMMENT '-1:failed; 0:new; 1:success',
  `message` varchar(120) NOT NULL,
  `created_at` datetime NOT NULL,
  `paid_at` datetime DEFAULT NULL,
  `payment_sum` float(10,2) unsigned NOT NULL,
  `payment_paid` float(10,2) unsigned NOT NULL,
  `payment_discount` float(10,2) unsigned NOT NULL DEFAULT 0,
  `payment_currency` varchar(3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `object` (`object_type`, `object_id`),
  KEY `object_id` (`object_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ; --

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpjb_company` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT 'foreign key wp_users.id',
  `company_name` varchar(120) NOT NULL DEFAULT '',
  `company_website` varchar(120) NOT NULL DEFAULT '',
  `company_info` text NOT NULL,
  `company_country` smallint(5) unsigned NOT NULL,
  `company_state` varchar(40) NOT NULL,
  `company_zip_code` varchar(20) NOT NULL,
  `company_location` varchar(250) NOT NULL DEFAULT '',
  `jobs_posted` int(11) unsigned NOT NULL DEFAULT '0',
  `is_public` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '-2: declined, -1: pending, 0: unset, 1: verified',
  PRIMARY KEY (`id`),
  UNIQUE `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ; --

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpjb_resume` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT 'foreign key wp_users.id',
  `candidate_slug` varchar(80) NOT NULL,
  `phone` varchar(80) NOT NULL,
  `headline` varchar(250) NOT NULL,
  `description` TEXT NOT NULL,
  `created_at` DATE NOT NULL,
  `modified_at` DATE NOT NULL,
  `candidate_country` smallint(5) unsigned NOT NULL,
  `candidate_state` varchar(40) NOT NULL,
  `candidate_zip_code` varchar(20) NOT NULL,
  `candidate_location` varchar(250) NOT NULL DEFAULT '',
  `is_public` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '',
  PRIMARY KEY (`id`),
  UNIQUE (`candidate_slug`),
  UNIQUE `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ; --

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpjb_resume_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resume_id` int(11) unsigned NOT NULL,
  `type` TINYINT(1) UNSIGNED NOT NULL COMMENT '1:experience; 2:education',
  `started_at` DATE NOT NULL,
  `completed_at` DATE DEFAULT NULL,
  `is_current` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `grantor` VARCHAR(120) NOT NULL,
  `detail_title` VARCHAR(120) NOT NULL,
  `detail_description` TEXT,
  PRIMARY KEY (`id`),
  KEY `resume_id` (`resume_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ; --

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpjb_resume_search` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resume_id` int(11) unsigned NOT NULL,
  `fullname` VARCHAR(120) NOT NULL,
  `location` VARCHAR(180) NOT NULL,
  `details` TEXT NOT NULL,
  `details_all` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE `resume_id` (`resume_id`),
  FULLTEXT KEY `search_narrow` (`details`),
  FULLTEXT KEY `search_broad` (`details_all`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ; --

CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wpjb_discount`(
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(120) NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `discount` DECIMAL(10,2) UNSIGNED NOT NULL,
  `type` TINYINT(1) UNSIGNED NOT NULL COMMENT '1=%; 2=$',
  `currency` VARCHAR(3) NOT NULL,
  `expires_at` DATE NOT NULL,
  `is_active` TINYINT(1) UNSIGNED NOT NULL,
  `used` INT(11) NOT NULL ,
  `max_uses` INT(11) NOT NULL,
  PRIMARY KEY(`id`),
  UNIQUE(`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ; --

CREATE TABLE `{$wpdb->prefix}wpjb_application` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED,
  `applied_at` DATETIME NOT NULL,
  `applicant_name` VARCHAR(120) NOT NULL,
  `message` TEXT NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `status` TINYINT(1) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  KEY(`job_id`),
  KEY(`user_id`)
) ENGINE=InnoDB CHARSET=utf8 ; --

CREATE TABLE `{$wpdb->prefix}wpjb_import` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `engine` VARCHAR(20) NOT NULL,
  `keyword` VARCHAR(80) NOT NULL,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `country` VARCHAR(10) NOT NULL,
  `location` VARCHAR(80) NOT NULL,
  `posted` TINYINT(3) UNSIGNED NOT NULL,
  `add_max` TINYINT(3) UNSIGNED NOT NULL,
  `last_run` DATETIME NOT NULL,
  `success` TINYINT(1) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  KEY(`last_run`)
) ENGINE=InnoDB CHARSET=utf8 ; --

CREATE TABLE `{$wpdb->prefix}wpjb_alert` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED DEFAULT NULL,
  `keyword` VARCHAR(80) NOT NULL,
  `email` VARCHAR(80) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `last_run` DATETIME NOT NULL,
  `frequency` TINYINT(1) UNSIGNED NOT NULL COMMENT '1: daily, 2: weekly',
  `params` TEXT NOT NULL,
  PRIMARY KEY(`id`),
  KEY(`last_run`),
  KEY `last_run_freq` (`last_run`, `frequency`)
) ENGINE=InnoDB CHARSET=utf8 ; --

INSERT INTO `{$wpdb->prefix}wpjb_mail` (`id`, `name`, `is_active`, `sent_to`, `format`, `mail_title`, `mail_body_text`, `mail_body_html`, `mail_from`, `mail_from_name`, `mail_bcc`) VALUES
(1, 'notify_admin_new_job', 1, 1, 'text/plain', 'New Job has been posted.', 'Greetings!\r\n\r\nThis is an automated email letting you know a new job has been\r\nposted to the job board. You can read the details below:\r\n\r\nThe job "{$job.job_title}" ({$job.id}) has been posted in {$category} {loop="job.tag.category"}{$value.title}{/loop} by ({$job.company_name} {$job.company_email}). \r\n\r\n{if condition="$payment.id>0"} Listing cost: {function="wpjb_price($payment.payment_sum, $payment.payment_currency)"} \r\n{else}\r\nThe listing was posted as a free listing. \r\n{/if}\r\n\r\nRead the original job listing here: {$job.url}\r\nSee this listing as an administrator here: {$job.admin_url}\r\n\r\nBest regards,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(2, 'notify_admin_payment_received', 1, 1, 'text/plain', 'Payment Received.', 'Hi there!\r\n\r\nThis is an automated email notifying you that a client has sent you\r\na payment. Read the details below.\r\n\r\nYou have received {$payment.payment_sum} from {$payment.user_id}\r\n({$payment.email}) for the listing {$payment.id}.\r\n\r\nBest regards,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(3, 'notify_employer_new_job', 1, 2, 'text/plain', 'Your job listing has been saved.', 'Hello!\r\n\r\nThis is an automated message from the WordPress JobBoard letting you know your job listing has been saved. You can read the details below:\r\n\r\nTitle: {$job.job_title} \r\nURL: {$job.url}\r\n\r\n{if="$payment.payment_sum==0 && $job.is_approved==0"}\r\nYour job listing is awaiting moderation. We will notify you be email when admin will approve it.\r\n{/if}\r\n{if="$payment.payment_sum>0"}\r\nIf you haven''t made payment yet, you can do it using URL below:\r\n{$payment.url}\r\n{/if}\r\n\r\nBest regards,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(5, 'notify_employer_job_expires', 1, 2, 'text/plain', 'Listing will expire soon.', 'Hi there,\r\n\r\nThis is a friendly automated reminder from the WordPress JobBoard. We just wanted to let you know that your job listing for the {$job.job_title} position will expire soon --- on {$job.job_expires_at}, to be exact.\r\n\r\nNeed to see the listing? Click here: {$job.url}\r\n\r\nBest regards,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(6, 'notify_admin_new_application', 1, 1, 'text/plain', 'Application for: {$job.job_title}', 'Hello!\r\n\r\nThis is an automated email from the WordPress JobBoard Support team. We just wanted to let you know that an application for the {$job.job_title} position with the company {$job.company_name} has been submitted. Read the details below:\r\n\r\nOriginal job listing: {$job.url}\r\nApplication ID: {$application.id}\r\nApplicant email address: {$application.email}\r\nApplicant message: \r\n{$application.message}\r\n\r\nBest regards,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(8, 'notify_applicant_applied', 1, 3, 'text/plain', 'Your application has been sent', 'Hey there,\r\n\r\nThis is an automated email from the WordPress JobBoard Support team letting you know your job application for the {$job.job_title} position with {$job.company_name} has been sent successfully.\r\n\r\nIf the employer is interested in your qualifications or would like to set up an interview, he or she will contact you shortly.\r\n\r\nYou can view the original job listing here: {$job.url}\r\n\r\nThank you for using the WordPress Job Board. Good luck!\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(9, 'notify_employer_register', 1, 2, 'text/plain', 'Your login and password', 'Hello!\r\n\r\nWelcome to the WordPress JobBoard. We''re happy to have you. Below you''ll find your username and password --- just in case you forget.\r\n\r\nUsername: {$username}\r\nPassword: {$password}\r\n\r\n{if="$manual_verification == 1"}Your account requires verification by the administrator. You will receive an e-mail when your account will be verified. {else}Ready to get started? Click here to log in: {$login_url}{/if}\r\n\r\nHappy hunting,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(10, 'notify_canditate_register', 1, 3, 'text/plain', 'Your login and password', 'Hello!\r\n\r\nWelcome to the WordPress JobBoard. We''re happy to have you. Below you''ll find your username and password --- just in case you forget.\r\n\r\nUsername: {$username}\r\nPassword: {$password}\r\n\r\n{if="$manual_verification == 1"}Your account requires verification by the administrator. You will receive an e-mail when your account will be verified. {else}Ready to get started? Click here to log in: {$login_url}{/if}\r\n\r\nHappy hunting,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(13, 'notify_admin_grant_access', 1, 1, 'text/plain', 'Employer requesting verification', 'Hi there,\r\n\r\nThis is an automated email from the WordPress JobBoard Support Team letting you know that an employer has requested access to resumes. Read the details below:\r\n\r\nEmployer requesting verification: {$company.company_name} (Company ID: {$company.id})\r\n\r\nEmployer email: {$company.email}\r\n\r\nYou can view (and approve) the company profile here:\r\n{$company_edit_url}.\r\n\r\nBest regards,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(14, 'notify_employer_verify', 1, 2, 'text/plain', 'Verification request', 'Hello,\r\n\r\nYou are receiving this email because you requested manual verification for a company profile. \r\n{if condition="$company.is_verified==1"} Your account was verified successfully. You now have full access to resumes. {else} \r\nThe administrator has declined you access to resumes. Try updating your profile and requesting verification again.{/if}\r\n\r\nBest regards,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(15, 'notify_employer_new_application', 1, 2, 'text/plain', 'Application for position {$job.job_title}', 'Hi there,\r\n\r\nThis is an automated email from the WordPress JobBoard Support team letting you know that an application (Applicant ID: {$application.id}) for the {$job.job_title} position you posted on {$job.job_created_at} has been submitted.\r\n\r\nSee the original listing here: {$job.url}\r\n\r\nBest regards,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(16, 'notify_job_alerts', 1, 4, 'text/plain', 'Your email alert.', 'Greetings from the WordPress JobBoard!\r\n\r\nHere are some new job listings for you to check out.\r\n\r\n{loop="jobs"}\r\n {$value.job_title} at {$value.company_name}\r\n {$value.url}\r\n --------------------------------------------------------------\r\n{/loop}\r\n\r\nNo longer wish to receive email notifications about new postings on the WordPress JobBoard? Click here to unsubscribe from this list: \r\n{$unsubscribe_url}\r\n\r\nBest regards,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(17, 'notify_employer_job_paid', 1, 2, 'text/plain', 'Your job has been activated', 'Hi there,\r\n\r\nThis is an automated email from the WordPress JobBoard Support team letting you know your job "{$job.job_title}" has been activated.\r\n\r\nYou can see it live at: {$job.url}\r\n\r\nBest regards,\r\nJob Board Support', '', 'test@example.com', 'Admin', ''),
(18, 'notify_employer_resume_paid', 1, 2, 'text/plain', 'Your resume access has been granted.', 'Hi there,\r\n\r\nThis is an automated email from the WordPress JobBoard Support team letting you know you have been granted access to "{$resume.headline}" resume.\r\n\r\nYou can see it live at:\r\n{$resume_unique_url}\r\n\r\nBest regards,\r\nJob Board Support', '', 'test@example.com', 'Admin', '') ; --

INSERT INTO `{$wpdb->prefix}wpjb_meta` (`id`, `name`, `meta_object`, `meta_type`, `meta_value`) VALUES
(1, 'color', 'tag', 2, ''),
(2, 'is_featured', 'pricing', 1, ''),
(3, 'visible', 'pricing', 1, ''),
(4, 'geo_status', 'job', 1, ''),
(5, 'geo_latitude', 'job', 1, ''),
(6, 'geo_longitude', 'job', 1, ''),
(8, 'job_description_format', 'job', 1, ''),
(9, 'geo_status', 'company', 1, ''),
(10, 'geo_latitude', 'company', 1, ''),
(11, 'geo_longitude', 'company', 1, ''),
(12, 'geo_status', 'resume', 1, ''),
(13, 'geo_latitude', 'resume', 1, ''),
(14, 'geo_longitude', 'resume', 1, ''),
(15, 'company_info_format', 'company', 1, ''),
(16, 'job_source', 'job', '1', ''); --

INSERT INTO `{$wpdb->prefix}wpjb_pricing` (`id`, `title`, `price_for`, `price`, `currency`, `is_active`) VALUES
(1, 'Free', 101, 0.00, 'USD', 1),
(2, 'Premium', 101, 10.00, 'USD', 1); --

INSERT INTO `{$wpdb->prefix}wpjb_meta_value` (`id`, `meta_id`, `object_id`, `value`) VALUES
(1, 2, 1, '0'),
(2, 3, 1, '30'),
(3, 2, 2, '1'),
(4, 3, 2, '30'); --

INSERT INTO `{$wpdb->prefix}wpjb_tag` (`id`, `type`, `slug`, `title`) VALUES
(1, 'category', 'default', 'Default'),
(3, 'type', 'full-time', 'Full-time'),
(4, 'type', 'part-time', 'Part-time'),
(5, 'type', 'freelance', 'Freelance'),
(6, 'type', 'internship', 'Internship'); --
