<div class="wrap wpjb">
    

<h1>
    <?php if( Daq_Request::getInstance()->get("id") ): //$form->getElement("id")->getValue()): ?>
    <?php _e("Edit Application Status | ID: ", "wpjobboard"); echo Daq_Request::getInstance()->get("id"); ?> 
    <?php else: ?>
    <?php _e("Add Application Status", "wpjobboard"); ?>
    <?php endif; ?>
    <a class="add-new-h2" href="<?php echo wpjb_admin_url("applicationStatus"); ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a> 
</h1>
    
<?php $this->_include("flash.php"); ?>

<form action="" method="post" class="wpjb-form">
    
    <table class="form-table">
        <tbody>
        <?php echo daq_form_layout_config($form); ?>
        </tbody>
    </table>

    <p class="submit">
        <input type="submit" value="<?php _e("Save Changes", "wpjobboard") ?>" class="button-primary button" name="Submit" />
    </p>
</form>

<?php wp_enqueue_script("wpjb-color-picker", null, null, null, true); ?>
<?php wp_enqueue_style("wpjb-colorpicker-css", null, null, null, true); ?>
<script type="text/javascript">
    jQuery(function() {
        
        
        jQuery('.wpjb-color-picker').each(function(o) {
            var colorPicker = jQuery(this);
            var colorPickerInput = jQuery(this).find('input');
            var colorPickerPreview = jQuery(this).find('.wpjb-colorpicker-preview')
            jQuery(this).ColorPicker({
                    livePreview: true,
                    color: '#0000ff',
                    onShow: function (colpkr) {
                        jQuery(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        jQuery(colpkr).fadeOut(500);
                        return false;
                    },
                    onChange: function (hsb, hex, rgb) {
                        jQuery(colorPickerInput).val('#' + hex);
                        jQuery(colorPickerPreview).css("background-color", "#" + hex);
                    }
                });
        });
    });
    
</script>

</div>