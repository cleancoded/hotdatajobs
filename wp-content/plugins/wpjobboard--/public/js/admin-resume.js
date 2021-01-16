jQuery(function($) {
    jQuery("#resume_created_at").DatePicker({
        format:wpjb_admin_resume_lang.date_format,
        date: jQuery("#resume_created_at").val(),
        current: jQuery("#resume_created_at").val(),
        starts: 1,
        position: 'r',
        onBeforeShow: function(){
            jQuery("#resume_created_at").DatePickerSetDate(jQuery("#resume_created_at").val(), true);
        },
        onChange: function(formated, dates){
            jQuery("#resume_created_at").DatePickerHide();
            jQuery("#resume_created_at").attr("value", formated);
            jQuery(".resume_created_date").text(formated);
        }
    });
    
    $("#resume_created_at_link").click(function() {
        $("#resume_created_at").click();
        return false;
    }); 
    
    jQuery("#resume_modified_at").DatePicker({
        format:wpjb_admin_resume_lang.date_format,
        date: jQuery("#resume_modified_at").val(),
        current: jQuery("#resume_modified_at").val(),
        starts: 1,
        position: 'r',
        onBeforeShow: function(){
            jQuery("#resume_modified_at").DatePickerSetDate(jQuery("#resume_modified_at").val(), true);
        },
        onChange: function(formated, dates){
            jQuery("#resume_modified_at").DatePickerHide();
            jQuery("#resume_modified_at").attr("value", formated);
            jQuery(".resume_modified_date").text(formated);
        }
    });
    
    $("#resume_modified_at_link").click(function() {
        $("#resume_modified_at").click();
        return false;
    }); 
});