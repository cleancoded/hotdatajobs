<div class="wrap wpjb">
    <h1>
        <?php esc_html_e($title) ?>
        <a class="add-new-h2" href="<?php echo wpjb_admin_url("config"); ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a> 
    </h1>

    <?php $this->_include("flash.php"); ?>

    <form action="" method="post" class="wpjb-form">
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <?php daq_form_layout($form) ?>
                </div>
            </div>

            <div style="clear:both;"></div>
        </div>

        <p class="submit">
            <input type="submit" value="<?php _e("Update", "wpjobboard") ?>" class="button-primary" name="Submit"/>
        </p>

    </form>
</div>