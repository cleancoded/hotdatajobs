<div class="wrap wpjb"> 
    <h1>
        <?php esc_html_e($form->name) ?>
        <a class="add-new-h2" href="<?php echo wpjb_admin_url("config"); ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a> 
    </h1>

    <?php $this->_include("flash.php"); ?>

    <form action="<?php esc_attr_e($submit_action) ?>" method="post" class="wpjb-form">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <?php daq_form_layout($form) ?>
                </div>
            </div>

            <div style="clear:both;"></div>
        </div>

        <div 

        <p class="submit">
            <?php if(wpjb_conf("facebook_access_token")): ?>
                <input type="submit" value="<?php _e("Save", "wpjobboard") ?>" class="button-primary button" name="Submit"/>
                <a href="<?php echo wpjb_admin_url("config", "facebooktest", null, array("noheader"=>1)) ?>" class="button"><?php _e("Send Test Share", "wpjobboard") ?></a>
                <a href="<?php echo wpjb_admin_url("config", "facebookreset", null, array("noheader"=>1)) ?>" class="button"><?php _e("Reset Facebook Configuration", "wpjobboard") ?></a>
            <?php elseif(wpjb_conf("facebook_app_id") && wpjb_conf("facebook_app_secret")): ?>
                <a href="<?php echo Wpjb_Module_Admin_Config_Facebook::getLoginUrl() ?>" class="button-primary"><?php _e("Log in with Facebook!", "wpjobboard") ?></a>
                <a href="<?php echo wpjb_admin_url("config", "facebookreset", null, array("noheader"=>1)) ?>" class="button"><?php _e("Reset Facebook Configuration", "wpjobboard") ?></a>
            <?php else: ?>
                <input type="submit" value="<?php echo esc_html("Save", "wpjobboard") ?>" class="button-primary button" name="Submit"/>
            <?php endif; ?>
        </p>
    </form>
</div>
