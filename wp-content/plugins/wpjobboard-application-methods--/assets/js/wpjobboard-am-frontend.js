jQuery(function($) {
   
    //if(wpjobboard_am_lang.has_methods !== "0") {
   
        var container = $("#wpjb-scroll div").first();

        if(typeof wpjobboard_am_lang.app_methods.email === 'undefined') {
            $("#wpjb_am_emails").val(wpjobboard_am_lang.app_methods.email);
            container.children(".wpjb-form-job-apply").remove();
        }

        if(typeof wpjobboard_am_lang.app_methods.url !== 'undefined') {
            var apply_url = $("<a>").addClass("wpjb-button").attr("href", wpjobboard_am_lang.app_methods.url).html(wpjobboard_am_lang.label_url);

            if(typeof container.children(".wpjb-form-job-apply") !== 'undefined' && container.children(".wpjb-form-job-apply").is(":visible")) {
                container.children(".wpjb-form-job-apply").after(apply_url);
            } else {
                container.prepend(apply_url);
            }
        }

        if(typeof wpjobboard_am_lang.app_methods.linkedin === 'undefined') {
            container.children(".wpjb-linkedin-request-token").remove();
        }
    //}
});