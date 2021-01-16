<div class="wrap wpjb">
    
    
    <h2 class="nav-tab-wrapper">
        <a href="<?php esc_attr_e(wpjb_admin_url("email")) ?>" class="nav-tab"><?php _e("Emails", "wpjobboard") ?></a>
        <a href="<?php esc_attr_e(wpjb_admin_url("email", "composer")) ?>" class="nav-tab"><?php _e("Live Composer", "wpjobboard") ?></a>
        <a href="<?php esc_attr_e(wpjb_admin_url("email", "editor")) ?>" class="nav-tab nav-tab-active"><?php _e("HTML Editor", "wpjobboard") ?></a>
    </h2>

    
    <?php wp_enqueue_script("wpjb-admin-config-email-editor") ?>
    <?php wp_enqueue_style("wp-jquery-ui-dialog") ?>

    <!-- START: Media library overlay -->
    <div id="wpjb-email-template-overlay" class="" style="">

        <div class="edit-attachment-frame">
            
            <div class="wpjb-email-edit-error"></div>

            <div class="media-frame-content">
                <div class="save-ready">
                    <div class="attachment-media-view landscape" style="width:75%">
                        <div id="wpjb-email-ace"></div>
                    </div>
                    <div class="attachment-info" style="width:25%">

                        <div class="settings wpjb-email-file-settings">

                            <strong class="wpjb-setting-header">
                                <?php _e("Files", "wpjobboard") ?>
                                
                                <span class="settings-save-status">
                                    <span class="spinner" style="margin: 0 10px 0"></span>
                                </span>
                            
                            </strong>
                            
                            <span class="setting">
                                <a href="#" class="wpjb-email-edit-file default" data-file="template"><?php _e("Email Template", "wpjobboard") ?></a><br/>
                                <em>email-template.html</em>
                                <a href="#" class="wpjb-email-edit-file-restore dashicons dashicons-image-rotate" title="<?php _e("Restore default file", "wpjobboard") ?>"></a>
                            </span>
                            
                            <span class="setting">
                                <a href="#" class="wpjb-email-edit-file" data-file="demo"><?php _e("Demo Content Template", "wpjobboard") ?></a><br/>
                                <em>email-demo-content.html</em>
                                <a href="#" class="wpjb-email-edit-file-restore dashicons dashicons-image-rotate" title="<?php _e("Restore default file", "wpjobboard") ?>"></a>
                                
                            </span>
                            
                            <span class="setting">
                                <a href="#" class="wpjb-email-edit-file" data-file="css"><?php _e("Template Stylesheet", "wpjobboard") ?></a><br/>
                                <em>email-stylesheet.css</em>
                                <a href="#" class="wpjb-email-edit-file-restore dashicons dashicons-image-rotate" title="<?php _e("Restore default file", "wpjobboard") ?>"></a>
                            </span>


                        </div>

                        <div class="settings">
                            
                            <a href="#" id="wpjb-update-template" class="button-secondary"><?php _e("Update") ?></a>
                            <span class="wpjb-email-edit-ok dashicons dashicons-yes"></span>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <div id="wpjb-email-dialog-restore" style="display:none" title="<?php _e("Restore File", "wpjobboard") ?>">
        <p><?php _e("Do you want to delete your custom file and replace it with default?", "wpjobboard") ?></p>
    </div>
    
    <!-- END: Media library overlay -->

</div>