jQuery(function($) {
    
    $(".wpjb-am-box-method").each(function(index) {
        
        if($(this).hasClass("wpjb-am-active")) {
            $(this).children(".wpjb-am-box-config").slideToggle();
        }
        
    });
    
    
    $(".wpjb-am-method").click(function(event) {
        event.preventDefault();
        
        var key = $(this).data("wpjb-am-key");
        var value = $(this).data("wpjb-am-value");
        
        var chBox = $(this).parent().children(".wpjb-am-chbox");
        
        
        if($(this).parent().hasClass("wpjb-am-active")) {
            $(this).parent().removeClass("wpjb-am-active");
            chBox.removeAttr("checked");
        } else {
            $(this).parent().addClass("wpjb-am-active");
            chBox.attr("checked", "checked");
        }
        
        
        $(this).parent().children(".wpjb-am-box-config-"+value).slideToggle();
    });
    
    $(".wpjb-am-chbox-label").click(function(event) {
        event.preventDefault();
        
        var key = $(this).parent().children('.wpjb-am-method').data("wpjb-am-key");
        var value = $(this).parent().children('.wpjb-am-method').data("wpjb-am-value");
        
        var chBox = $(this).parent().children(".wpjb-am-chbox");
        
        
        if($(this).parent().hasClass("wpjb-am-active")) {
            $(this).parent().removeClass("wpjb-am-active");
            chBox.removeAttr("checked");
        } else {
            $(this).parent().addClass("wpjb-am-active");
            chBox.attr("checked", "checked");
        }
        
        
        $(this).parent().children(".wpjb-am-box-config-"+value).slideToggle();
    });
    
    $(".wpjb-am-box-config-email").on("click", ".wpjb_am_remove_email", function(event) {
        event.preventDefault();
        $(this).parent().remove();
    });
    
    $(".wpjb_am_remove_email").click(function(event) {
        event.preventDefault();
        $(this).parent().remove();
    });
        
   
    /*$(".wpjb-am-box-method").click(function(event) {
        event.preventDefault();
        
        var key = $(this).children('.wpjb-am-method').data("wpjb-am-key");
        var value = $(this).children('.wpjb-am-method').data("wpjb-am-value");
        
        var chBox = $(this).children(".wpjb-am-chbox");
        
        
        if($(this).hasClass("wpjb-am-active")) {
            $(this).removeClass("wpjb-am-active");
            chBox.removeAttr("checked");
        } else {
            $(this).addClass("wpjb-am-active");
            chBox.attr("checked", "checked");
        }
        
        
        $(this).children(".wpjb-am-box-config-"+value).slideToggle();
        
    });*/
    
    $("#wpjb_am_add_email").click(function(event) {
        event.preventDefault();
        
        var div = $("<div>");
        
        var email_field = $("<input>").addClass("wpjb-am-stop-propagation").addClass("wpjb-am-email-mock");
        email_field.attr("placeholder", wpjobboard_am_lang.email_placeholder).css("width", "85%");
        email_field.attr("type", "text");
        
        var remove_btn = $("<a>");
        remove_btn.addClass("wpjb-am-stop-propagation").addClass("wpjb-button").addClass("wpjb_am_remove_email");
        remove_btn.addClass("wpjb-icons").addClass("wpjb-icon-minus").addClass("button");
        
        div.append(email_field).append(remove_btn);
        
        $("#wpjb_am_add_email").before(div);
        
    });
    
    $('.wpjb-am-box-method').on('click', '.wpjb-am-stop-propagation', function(e){
        e.stopPropagation();    
    });

    $('form.wpjb-form').submit(function(event) {
        //event.preventDefault();
         
        if($("#wpjb-am-method-chbox-email").is(':checked')) {
            var emails = "";
            $.each($('.wpjb-am-email-mock'), function(index, element) {
                emails += $(element).val() + ",";
            });
            emails = emails.replace(/,\s*$/, "");
            $("#wpjb-am-email").val(emails);
        }
        
        if($("#wpjb-am-method-chbox-url").is(':checked')) {
             
        }
        
        //$(this).submit();
    });

    /*$('form.wpjb-form').submit(function(event) {

        var email_ok = true;
        var url_ok = true;
        
        if(!$("#wpjb-am-method-chbox-email").is(':checked')) {
            email_ok = true;
        } else {
            var emails = "";
            $.each($('.wpjb-am-email-mock'), function(index, element) {
                var val = $(element).val()
                $(this).parent().children(".wpjb-am-error").remove();
                
                if(validateEmail(val) && val.length > 0) {
                    emails += $(element).val() + ",";
                    if($(this).hasClass("error")) {
                        $(this).removeClass("error");
                    }
                } else {
                    email_ok = false;
                    $(this).addClass("error");
                    var msg = $("<small>").css("color", "red").text("Provide valid e-mail!").addClass("wpjb-am-error");
                    $(this).before(msg);
                }
            });
            emails = emails.replace(/,\s*$/, "");
        }
        
        if($("#wpjb-am-method-chbox-url").is(':checked')) {
            $("#wpjb-am-url").parent().children(".wpjb-am-error").remove();
            
            if(validateURL($("#wpjb-am-url").val())) {
                if($("#wpjb-am-url").hasClass("error")) {
                    $("#wpjb-am-url").removeClass("error");
                }
                url_ok = true;
            } else {
                url_ok = false;
                var msg = $("<small>").css("color", "red").html("<br/>Provide valid URL!").addClass("wpjb-am-error");
                $("#wpjb-am-url").before(msg);
                $("#wpjb-am-url").addClass("error");
            }
        } else {
            url_ok = true;
        }
        
       
        if(email_ok) {
            $("#wpjb-am-email").val(emails);
        } 
        
        if(!email_ok || !url_ok) {
            event.preventDefault();
            
            $('html, body').animate({
                scrollTop: $("#wpjb-am-method-chbox-email").offset().top
            }, 1000);
        }     
        
    });
    
    function validateEmail(email) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }
    
    function validateURL(textval) {
        var urlregex = new RegExp('^(https?:\/\/)?'+ // protocol
        '((([a-z\d]([a-z\d-]*[a-z\d])*)\.)+[a-z]{2,}|'+ // domain name
        '((\d{1,3}\.){3}\d{1,3}))'+ // OR ip (v4) address
        '(\:\d+)?(\/[-a-z\d%_.~+]*)*'+ // port and path
        '(\?[;&a-z\d%_.~+=-]*)?'+ // query string
        '(\#[-a-z\d_]*)?$','i'); // fragment locater
        return urlregex.test(textval);
    }*/
});