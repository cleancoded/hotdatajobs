<div class="wrap wpjb">
   
<h1>
    <?php esc_html_e($form->name) ?>
    <a class="add-new-h2" href="<?php echo wpjb_admin_url("config"); ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a>
</h1>

<?php $this->_include("flash.php"); ?>
    
<?php if($show_form && in_array($section, array("urls"))): ?>

<style type="text/css">
.wpjb-form-group-shortcoded label {
    padding: 3px 5px 2px;
    margin: 0 1px;
    background: #eaeaea;
    background: rgba(0,0,0,.07);
    font-size: 9px;
    line-height: 19px;
    display: inline-block;
    font-family: Consolas,Monaco,monospace !important;
    unicode-bidi: embed;
}
</style>
<form action="" method="post" enctype="multipart/form-data" class="wpjb-form">
    <div id="poststuff" >
            <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">

            <?php daq_form_layout($form, array("exclude_fields"=>"payment_method", "exclude_groups"=>"_internal")) ?>

            </div>
            <p class="submit">
                <input type="submit" value="<?php _e("Update", "wpjobboard") ?>" class="button-primary button" name="Submit"/>
            
            </p>
        </div>
    </div>
</form>
    
<?php elseif($show_form): ?>
<form action="" method="post" enctype="multipart/form-data" class="wpjb-form">
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <?php daq_form_layout($form) ?>
            </div>
        </div>

        <div style="clear:both;"></div>
    </div>

    <p class="submit">
        <input type="submit" value="<?php esc_attr_e($submit_title) ?>" class="button-primary button" name="Submit"/>
        <?php if($section == "linkedin"): ?>
            <input type="submit" name="linkedin_share_test" class="button" value="<?php _e("Send Test Share", "wpjobboard" ) ?>" />
        <?php endif; ?>
        <?php if($section == "twitter"): ?>
            <input type="submit" value="<?php _e("Save and send test tweet", "wpjobboard") ?>" class="button" name="saventest"/>
        <?php endif; ?>
    </p>
</form>
<?php endif; ?>

<?php if($show_form && in_array($section, array("spam"))): ?>
    <h3><?php echo __("Last 100 logged messages.", "wpjobboard") ?></h3>
    <div style="max-width: 800px; max-height:350px; overflow-y:scroll">
        <?php $logs = maybe_unserialize(get_option("wpjb_antispam_log")) ?>
        <?php if(!is_array($logs)): ?>
        <div><?php _e("Error log is empty.", "wpjobboard") ?></div>
        <?php else: ?>
            <?php foreach($logs as $log): ?>
                <div class="wpjb-bulb wpjb-bulb-bigger wpjb-bulb-log"><?php echo esc_html($log) ?></div><br/>
            <?php endforeach ?>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
