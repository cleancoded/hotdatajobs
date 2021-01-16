<div class="wrap wpjb">
    

    <h1>
        <?php _e("Add Email Template", "wpjobboard"); ?> 
        <a class="add-new-h2" href="<?php esc_attr_e(wpjb_admin_url("email")) ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a> 
    </h1>
<?php $this->_include("flash.php"); ?>

<form action="" method="post" class="wpjb-form">

    <?php echo daq_form_layout_config($form) ?>
    
    <p class="submit">
    <input type="submit" value="<?php _e("Save Changes", "wpjobboard") ?>" class="button-primary button" name="Submit"/>
    </p>

</form>
    

    
</div>
    <script type="text/javascript">
    jQuery(function() {
        jQuery(".wpjb-mail-var").click(function() {
            
            var tpl = jQuery(this).text();
            
            if(jQuery(".wpjb-mail-body-select").val() == "text/plain") {
                var textarea = jQuery('#mail_body_text');
                var pos = textarea.prop("selectionStart");
                var txt = textarea.val();
                var t1 = txt.slice(pos);
                var t2 = txt.slice(0, pos);

                textarea.val(t2+tpl+t1);
                textarea.focus();
                textarea[0].setSelectionRange(tpl.length+pos, tpl.length+pos); 
            } else {
                var ed = tinyMCE.get('mail_body_html');  
                ed.execCommand('mceInsertContent', false, tpl); 
                ed.focus();
            }
            
            return false;
        });
        
        jQuery(".wpjb-mail-body-select").change(function() {
            if(jQuery(this).val() == "text/plain") {
                jQuery("#wp-mail_body_html-wrap").closest("tr").hide();
                jQuery("#mail_body_text").closest("tr").show();
                var tr = jQuery("#mail_body_text").closest("tr").show();
            } else {
                jQuery("#wp-mail_body_html-wrap").closest("tr").show();
                jQuery("#mail_body_text").closest("tr").hide();
                var tr = jQuery("#wp-mail_body_html-wrap").closest("tr").show();
            }

        });
        
        jQuery(".wpjb-mail-body-select").change();
        jQuery("#wp-mail_body_html-wrap").css("width", "600px");
        
        jQuery(".widget-top").click(function() {
            jQuery(this).closest("div.widget").find(".widget-inside").toggle();
            return false;
        });


    });

    

    </script>
    