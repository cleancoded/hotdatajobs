<div class="wrap wpjb">
    

    <h1>
        <?php if($form->getObject()->id): ?>
        <?php _e("Edit Membership | ID: ", "wpjobboard"); echo $form->getObject()->id; ?> 
        <?php else: ?>
        <?php _e("Add Membership", "wpjobboard"); ?>
        <?php endif; ?>
        <a class="add-new-h2" href="<?php echo wpjb_admin_url("memberships"); ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a> 
    </h1>
<?php $this->_include("flash.php"); ?>

<script type="text/javascript">
    Wpjb.Id = <?php echo $form->getObject()->getId() ?>;
</script>

<form action="" method="post" class="wpjb-form">
    <table class="form-table">
        <tbody>
            <?php echo daq_form_layout_config($form) ?>
        </tbody>
    </table>

    <p class="submit">
    <input type="submit" value="<?php _e("Save Changes", "wpjobboard") ?>" class="button-primary button" name="Submit"/>
    </p>

</form>

<script type="text/javascript">
    
jQuery(function($) {
    $(".wpjb-membership-usage").change(function() {
        var val = $(this).val();
        if(val != "limited") {
            $(this).next().find("input").attr("readonly", "readonly").val("");
        } else {
            $(this).next().find("input").attr("readonly", null);
            
        }
    });
    
    $(".wpjb-membership-usage").change();
    
    $("#started_at").DatePicker({
        format:wpjb_admin_lang.date_format,
        date: $("#started_at").val(),
        current: $("#started_at").val(),
        starts: 1,
        position: 'r',
        onChange: function(formated, dates){
            $("#started_at").DatePickerHide();
            $("#started_at").attr("value", formated);
        }
    });
    
    $("#expires_at").DatePicker({
        format:wpjb_admin_lang.date_format,
        date: $("#expires_at").val(),
        current: $("#expires_at").val(),
        starts: 1,
        position: 'r',
        onChange: function(formated, dates){
            $("#expires_at").DatePickerHide();
            $("#expires_at").attr("value", formated);
        }
    });
});
</script>

</div>