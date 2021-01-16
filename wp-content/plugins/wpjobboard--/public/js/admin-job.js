jQuery(function($) {
    
    $("#job_title").blur(function() {
        if(jQuery("#job_slug").val().length == 0) {
            $("#edit-slug-box").css("visibility", "visible");
            $(".wpjb-slug-buttons .edit-slug").click();
            $("#job-slug-temp").val($(this).val());
            $(".wpjb-slug-buttons .save").click();
        }
    });
    $(".edit-slug").click(function(e) {
        var span = $("#editable-post-name");
        var text = span.text();
        var input = $("<input type=\"text\" />");
        input.attr("id", "job-slug-temp");
        input.attr("value", text);
        
        $(".wpjb-slug-buttons .edit-slug").hide();
        $(".wpjb-slug-buttons .view-slug").hide();
        
        $(".wpjb-slug-buttons #edit-slug-btn").hide();
        $(".wpjb-slug-buttons #view-slug-btn").hide();
        $(".wpjb-slug-buttons #gets-slug-btn").hide();
        
        $(".wpjb-slug-buttons .save").show();
        $(".wpjb-slug-buttons .cancel").show();
        
        span.html(input);
        return false;
    });
    $(".wpjb-slug-buttons .save").click(function() {
        
        var title = "";
        if($("#job-slug-temp").length > 0 && $("#job-slug-temp").val().length == 0) {
            title = $("#job_title").val();
        } else {
            title = $("#job-slug-temp").val();
        }
        
        var id = null;
        
        if(typeof Wpjb.Id !== "undefined") {
            id = Wpjb.Id;
        }
        
        var data = {
            action: 'wpjb_main_slugify',
            object: "job",
            title: title,
            id: id
	};

	jQuery.post(ajaxurl, data, function(response) {
            $("#job_slug").val(response);
            $("#job-slug-temp").val(response);
            $("#editable-post-name").text(response);
            $(".wpjb-slug-buttons .cancel").click();
	});
        
        return false;
    });
    $(".wpjb-slug-buttons .cancel").click(function() {
        
        jQuery("#editable-post-name").text($("#job_slug").val());
        $(".wpjb-slug-buttons .edit-slug").show();
        $(".wpjb-slug-buttons .view-slug").show();
        
        $(".wpjb-slug-buttons #edit-slug-btn").show();
        $(".wpjb-slug-buttons #view-slug-btn").show();
        $(".wpjb-slug-buttons #gets-slug-btn").show();
        
        
        $(".wpjb-slug-buttons .save").hide();
        $(".wpjb-slug-buttons .cancel").hide();
        return false;
    });
    
    jQuery("#job_title").focus(function() {
        jQuery("#title-prompt-text").hide();
    });
    jQuery("#job_title").blur(function() {
        if(jQuery(this).val().length == 0) {
            jQuery("#title-prompt-text").show();
        }
    });
    
    jQuery("#job_created_at").DatePicker({
        format: wpjb_admin_job_lang.date_format,
        date: jQuery("#job_created_at").val(),
        current: jQuery("#job_created_at").val(),
        starts: 1,
        position: 'r',
        onBeforeShow: function(){
            jQuery("#job_created_at").DatePickerSetDate(jQuery("#job_created_at").val(), true);
        },
        onChange: function(formated, dates){
            jQuery("#job_created_at").DatePickerHide();
            jQuery("#job_created_at").attr("value", formated);
            wpjb_job_dates();
        }
    });
    
    $("#job_created_at_link").click(function() {
        $("#job_created_at").click();
        return false;
    });
    
    $("#job_expires_never").click(function() {
        $("#job_expires_at").val(wpjb_admin_job_lang.max_date);
        wpjb_job_dates();
        return false;
    });
    
    jQuery("#job_expires_at").DatePicker({
        format: wpjb_admin_job_lang.date_format,
        date: jQuery("#job_expires_at").val(),
        current: jQuery("#job_expires_at").val(),
        starts: 1,
        position: 'r',
        onBeforeShow: function(){
            var setDate = $("#job_expires_at").val();
            if(setDate == wpjb_admin_job_lang.max_date) {
                setDate = Today;
            }
            jQuery("#job_expires_at").DatePickerSetDate(setDate, true);
        },
        onChange: function(formated, dates){
            jQuery("#job_expires_at").DatePickerHide();
            jQuery("#job_expires_at").attr("value", formated);
            jQuery(".job-expires-at-formated").attr("title", formated);
            wpjb_job_dates();

        }
    });
    
    $("#job_expires_at_link").click(function() {
        $("#job_expires_at").click();
        return false;
    });
    
    $("#payment_method").change(function() {
        var $this = $(this);
        
        $(".payment-method").hide();
        $("#payment_method_link").show();
        
        if($this.val() == "none") {
            $(".payment_method").text(wpjb_admin_job_lang.free_listing);
            $(".wpjb-payment-method").addClass("misc-pub-section-last");
            $(".wpjb-payment-details").hide();
        } else {
            $(".payment_method").text($this.val());
            $(".wpjb-payment-method").removeClass("misc-pub-section-last");
            $(".wpjb-payment-details").show();
        }
    });
    $("#payment_method").change();
    
    $("#payment_method_link").click(function() {
        $(".payment-method").show();
        $(this).hide();
        return false;
    });
    $("#payment-method-cancel").click(function() {
        $(".payment-method").hide();
        $("#payment_method_link").show();
        return false;
    });

    $("#employer_id").change(function() {
        $(".company-edit-label").text($("#employer_id_text").val());
        $(".employer").addClass("hide-if-js");
    });
    $("#employer_id").change();

    $("#employer-cancel").click(function(e) {
        e.preventDefault();
        $(".employer").addClass("hide-if-js");
        $("#employer_id_text").val($(".company-edit-label").text());
    });
    $(".employer-edit").click(function(e) {
        e.preventDefault();
        $(".employer").removeClass("hide-if-js");
    });
    $("#employer_id_text").wpjb_suggest(ajaxurl + "?action=wpjb_suggest_employer", { 
        maxCacheSize: 0, 
        delimiter: '<!-- suggest delimeter -->' ,
        resultsClass: 'wpjb_ac_results',
        onSelect: function(e) {

            var id = $(".wpjb_ac_results .wpjb-ac-over > span").data("id");
            
            $(".company-edit-label").text($("#employer_id_text").val());
            $("#employer_id").val(id);

            $(".employer").addClass("hide-if-js");
        }
    } );
    
    $(".listing-type-change").hide();
    $("#listing-type-link").click(function() {
        $(".listing-type-change").show();
        $("#listing-type-link").hide();
        return false;
    });
    $("#listing-type-cancel").click(function() {
        $(".listing-type-change").hide();
        $("#listing-type-link").show();
        return false;
    });
    $("#listing_type").change(function() {
        $(".listing-type-change").hide();
        $("#listing-type-link").show();
        
        var price = null;
        
        $.each(Pricing, function(index, item) {
            if(item.id == $("#listing_type").val()) {
                price = item;
            }
        });
        
        $("b.listing-type").text(price.title);
        
        if(price.id == 0) {
            return;
        }
        
        var ex = new Date(jQuery("#job_created_at").val());
        ex.setDate(ex.getDate() + parseInt(price.visible)); 
        var month = ex.getMonth()+1;
        if(month < 10) {
            month = "0"+month;
        }
        jQuery("#job_expires_at").attr("value", ex.getFullYear()+"/"+month+"/"+ex.getDate());
        
        if(price.is_featured) {
            $("#is_featured").attr("checked", "checked");
        } else {
            $("#is_featured").attr("checked", null);
        }
        
        $("#payment_amount").val(price.amount);
        $("#payment_currency").val(price.currency);
        $("#payment_currency").val(price.currency);
        
        wpjb_job_dates();
        
    });
    $("#is_active-1").change(function() {
       var $this = $(this);
       if($this.is(":checked")) {
           $("#is_approved").val("1");
       } else {
           $("#is_approved").val("0");
       }
    });
    
    // form revalidate
    if($("#job_slug") && $("#job_slug").val().length>0) {
        $("#edit-slug-box").css("visibility", "visible");
        $(".wpjb-slug-buttons .edit-slug").click();
        $("#job-slug-temp").val($(this).val());
        $(".wpjb-slug-buttons .cancel").click();
    }
    
    $("#payment_method").change();
    $("#is_active-1").change();
    wpjb_job_dates();
});

function wpjb_sticky() {
    var sticky = jQuery(".wpjb-sticky");
    
    var window_top = jQuery(window).scrollTop();
    var div_top = jQuery('.wpjb-sticky-anchor').offset().top;
    if (window_top > div_top && !sticky.hasClass("wpjb-stick")) {
        sticky.css("top", sticky.offset().top);
        sticky.css("left", sticky.offset().left);
        sticky.addClass('wpjb-stick')
    } else {
        //sticky.removeClass('wpjb-stick');
    }
    return true;
}

function wpjb_job_dates() {
    var created = jQuery("#job_created_at").val();
    var expires = jQuery("#job_expires_at").val();
    var today = Today;
    
    var d = wpjb_date_diff(new Date(Today), new Date(created));
    var t = created;

    switch(d) {
        case -1: t = wpjb_admin_job_lang.yesterday; break;
        case  0: t = wpjb_admin_job_lang.immediately; break;
        case  1: t = wpjb_admin_job_lang.tomorrow; break;
            
    }

    jQuery(".job_created_date").text(created);
    jQuery(".job_expires_date").text(expires);


    jQuery(".job_created_at").text(t);
    
    d = wpjb_date_diff(new Date(created), new Date(expires));
    t = "";

    if(d == 1) {
        t = wpjb_admin_job_lang.day.replace("%d", d);
    } else {
        t = wpjb_admin_job_lang.days.replace("%d", d);
    }

    if(expires == wpjb_admin_job_lang.max_date) {
        jQuery(".job_expires_date").text("N/A");
    } else {
        //jQuery(".job_expires_date").text(t);
    }
}

jQuery(function($) {
    $(".wpjb-gfj-job-more-button").on("click", function(e) {
        e.preventDefault();
        $(".wpjb-gfj-job-more").slideToggle("fast");
    });
    $(".wpjb-gfj-job-validate-button").on("click", function(e) {
        e.preventDefault();
        $(".wpjb-gfj-job-submit").submit();
    })
    
});

function wpjb_date_diff(d1, d2) {
    var t2 = d2.getTime();
    var t1 = d1.getTime();

    return parseInt(Math.ceil(t2-t1)/(24*3600*1000));
}