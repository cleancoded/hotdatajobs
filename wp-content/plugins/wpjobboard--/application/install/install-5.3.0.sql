ALTER TABLE `{$wpdb->prefix}wpjb_resume` ADD `featured_level` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `is_active` ; --


INSERT INTO `{$wpdb->prefix}wpjb_meta` (`id`, `name`, `meta_object`, `meta_type`, `meta_value`) VALUES (NULL, 'have_access', 'pricing', 1, '') ; --
INSERT INTO `{$wpdb->prefix}wpjb_meta` (`id`, `name`, `meta_object`, `meta_type`, `meta_value`) VALUES (NULL, 'is_searchable', 'pricing', 1, '') ; --
INSERT INTO `{$wpdb->prefix}wpjb_meta` (`id`, `name`, `meta_object`, `meta_type`, `meta_value`) VALUES (NULL, 'featured_level', 'pricing', 1, '') ; --
INSERT INTO `{$wpdb->prefix}wpjb_meta` (`id`, `name`, `meta_object`, `meta_type`, `meta_value`) VALUES (NULL, 'alert_slots', 'pricing', 1, '') ; --