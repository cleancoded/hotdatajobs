ALTER TABLE  `{$wpdb->prefix}wpjb_tag` 
    ADD  `parent_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0',
    ADD  `order` INT( 11 ) UNSIGNED NOT NULL DEFAULT  '0'; --

INSERT INTO `{$wpdb->prefix}wpjb_meta` (`id`, `name`, `meta_object`, `meta_type`, `meta_value`) VALUES (NULL, 'is_trial', 'pricing', 1, '') ; --
