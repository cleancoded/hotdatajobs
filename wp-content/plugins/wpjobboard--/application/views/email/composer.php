<div class="wrap wpjb">
    
    
    <h2 class="nav-tab-wrapper">
        <a href="<?php esc_attr_e(wpjb_admin_url("email")) ?>" class="nav-tab"><?php _e("Emails", "wpjobboard") ?></a>
        <a href="<?php esc_attr_e(wpjb_admin_url("email", "composer")) ?>" class="nav-tab nav-tab-active"><?php _e("Live Composer", "wpjobboard") ?></a>
        <a href="<?php esc_attr_e(wpjb_admin_url("email", "editor")) ?>" class="nav-tab"><?php _e("HTML Editor", "wpjobboard") ?></a>
    </h2>

    <?php $this->_include("flash.php"); ?>
    
    <?php wp_enqueue_style("wpjb-glyphs") ?>
    <?php wp_enqueue_script("wpjb-admin-config-email-composer") ?>

    <form action="" method="post">
        <div id="wpjb-email-template-overlay" class="" style="">

            <div class="edit-attachment-frame">

                <div class="media-frame-content">
                    <div class="save-ready">
                        <div class="attachment-media-view landscape">
                            <div class="wpjb-email-frame-tools" style="">

                                <span id="wpjb-email-preview-desktop" class="dashicons dashicons-desktop active"></span>
                                <span id="wpjb-email-preview-mobile" class="dashicons dashicons-smartphone"></span>

                            </div>

                            <div id="wpjb-email-frame-wrap">
                                <iframe id="wpjb-email-frame" src="<?php echo admin_url('admin-ajax.php') ?>?action=wpjb_email_template" class="wpjb-email-template-preview"></iframe>
                            </div>
                        </div>
                        <div class="attachment-info">

                            <!--span class="settings-save-status">
                                <span class="spinner"></span>
                                <span class="saved">Saved.</span>
                            </span-->

                            <div class="settings wpjb-iris-settings">

                                <strong class="wpjb-setting-header"><?php _e("Colors", "wpjobboard") ?></strong>

                                <span class="setting">
                                    <span class="name"><?php _e("Background", "wpjobboard") ?></span>
                                    <div class="wpjb-iris-box">
                                        <input type="text" name="color_background" autocomplete="off" class="wpjb-iris-input" id="wpjb-email-color-background" value="<?php echo esc_attr($form->value("color_background")) ?>" data-color-default="#f6f6f6" />
                                        <a href="#" id="wpjb-email-restore-background" class="button-secondary"><span class="wpjb-glyphs wpjb-icon-ccw"></span></a>
                                    </div>
                                </span>

                                <span class="setting">
                                    <span class="name"><?php _e("Body Background", "wpjobboard") ?></span>
                                    <div class="wpjb-iris-box">
                                        <input type="text" name="color_background_body" autocomplete="off" class="wpjb-iris-input" id="wpjb-email-color-background-body" value="<?php echo esc_attr($form->value("color_background_body")) ?>" data-color-default="#ffffff" />
                                        <a href="#" id="wpjb-email-restore-background-body" class="button-secondary"><span class="wpjb-glyphs wpjb-icon-ccw"></span></a>
                                    </div>
                                </span>

                                <span class="setting">
                                    <span class="name"><?php _e("Text", "wpjobboard") ?></span>
                                    <div class="wpjb-iris-box">
                                        <input type="text" name="color_text" autocomplete="off" class="wpjb-iris-input" id="wpjb-email-color-text" value="<?php echo esc_attr($form->value("color_text")) ?>" data-color-default="#000000" />
                                        <a href="#" id="wpjb-email-restore-color-text" class="button-secondary"><span class="wpjb-glyphs wpjb-icon-ccw"></span></a>
                                    </div>
                                </span>

                                <span class="setting">
                                    <span class="name"><?php _e("Link", "wpjobboard") ?></span>
                                    <div class="wpjb-iris-box">
                                        <input type="text" name="color_link" autocomplete="off" class="wpjb-iris-input" id="wpjb-email-color-link" value="<?php echo esc_attr($form->value("color_link")) ?>" data-color-default="#000000" />
                                        <a href="#" id="wpjb-email-restore-color-link" class="button-secondary"><span class="wpjb-glyphs wpjb-icon-ccw"></span></a>
                                    </div>
                                </span>

                                <span class="setting">
                                    <span class="name"><?php _e("Header Text", "wpjobboard") ?></span>
                                    <div class="wpjb-iris-box">
                                        <input type="text" name="color_text_header" autocomplete="off" class="wpjb-iris-input" id="wpjb-email-color-text-header" value="<?php echo esc_attr($form->value("color_text_header")) ?>" data-color-default="#000000" />
                                        <a href="#" id="wpjb-email-restore-text-header" class="button-secondary"><span class="wpjb-glyphs wpjb-icon-ccw"></span></a>
                                    </div>
                                </span>

                                <span class="setting">
                                    <span class="name"><?php _e("Footer Text", "wpjobboard") ?></span>
                                    <div class="wpjb-iris-box">
                                        <input type="text" name="color_text_footer" autocomplete="off" class="wpjb-iris-input" id="wpjb-email-color-text-footer" value="<?php echo esc_attr($form->value("color_text_footer")) ?>" data-color-default="#999999" />
                                        <a href="#" id="wpjb-email-restore-text-footer" class="button-secondary"><span class="wpjb-glyphs wpjb-icon-ccw"></span></a>
                                    </div>
                                </span>

                                <span class="setting">
                                    <span class="name"><?php _e("Button", "wpjobboard") ?></span>
                                    <div class="wpjb-iris-box">
                                        <input type="text" name="color_button" autocomplete="off" class="wpjb-iris-input" id="wpjb-email-color-button" value="<?php echo esc_attr($form->value("color_button")) ?>" data-color-default="#3498db" />
                                        <a href="#" id="wpjb-email-restore-button" class="button-secondary"><span class="wpjb-glyphs wpjb-icon-ccw"></span></a>
                                    </div>
                                </span>

                                <span class="setting">
                                    <span class="name"><?php _e("Button Text", "wpjobboard") ?></span>
                                    <div class="wpjb-iris-box">
                                        <input type="text" name="color_button_text" autocomplete="off" class="wpjb-iris-input" id="wpjb-email-color-button-text" value="<?php echo esc_attr($form->value("color_button_text")) ?>" data-color-default="#ffffff" />
                                        <a href="#" id="wpjb-email-restore-button-text" class="button-secondary"><span class="wpjb-glyphs wpjb-icon-ccw"></span></a>
                                    </div>
                                </span>



                            </div>

                            <div class="settings">

                                <strong class="wpjb-setting-header"><?php _e("Content", "wpjobboard") ?></strong>

                                <label class="setting" data-setting="url">
                                    <span class="name"><?php _e("Admin E-mail", "wpjobboard") ?></span>
                                    <input type="text" name="admin_email" value="<?php echo esc_attr($form->value("admin_email")) ?>" placeholder="<?php echo esc_attr(get_option("admin_email")) ?>" style="width:99.5%; box-sizing: border-box" />
                                </label>

                                <label class="setting" data-setting="url">
                                    <span class="name"><?php _e("Logo URL", "wpjobboard") ?></span>
                                    <input type="text" name="email_logo" id="wpjb-email-logo" style="width:99.5%; box-sizing: border-box" value="<?php echo esc_attr($form->value("email_logo")) ?>" />
                                </label>

                                <label class="setting" data-setting="url">
                                    <span class="name"><?php _e("Footer Text", "wpjobboard") ?></span>
                                    <textarea name="email_footer" id="wpjb-email-footer" style="width:100%" placeholder="<?php echo esc_attr($footer_default) ?>"><?php echo esc_textarea($form->value("email_footer")) ?></textarea>
                                </label>

                            </div>

                            <div class="settings">
                                <input type="submit" class="button-primary" value="<?php _e("Update") ?>" />
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

</div>