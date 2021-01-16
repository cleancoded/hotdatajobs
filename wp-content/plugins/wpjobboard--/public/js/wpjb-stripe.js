var WPJB = WPJB || {};

WPJB.stripe = {
    
    loadOnce: function() {
        var $ = jQuery;
        
        if($(".wpjb-script-external-stripe").length > 0) {
            return;
        }
        
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.src = "https://js.stripe.com/v2/"; 
        script.className = "wpjb-script-external-stripe";
        document.getElementsByTagName("body")[0].appendChild(script);
    },
    
    error: function(error) {
        var field = null;
        
        switch(error.code) {
            case "invalid_cvc":
            case "incorrect_cvc":
                field = "cvc";
                break;
            case "invalid_expiry_month":
            case "invalid_expiry_year":
            case "expired_card":
                field = "expiration";
                break;
            default:
                field = "card_number";
        }
        
        return field;
    },
    
    response: function(status, response) {
        var $ = jQuery;
        var $form = $('.wpjb-payment-form');

        $form.find(".wpjb-flash-error").remove();
        $form.find(".wpjb-flash-info").remove();

        if (response.error) {
            
            var field = WPJB.stripe.error(response.error);
            var form = new WPJB.form(".wpjb-payment-form");
            
            form.addError(wpjb_payment_lang.form_error);
            form.addFieldError(".wpjb-element-name-"+field, response.error.message);

            $(".wpjb-place-order-wrap .wpjb-place-order").show();
            $(".wpjb-place-order-wrap .wpjb-icon-spinner").css("visibility", "hidden");
            
            return;
        } 

        $form.find("form").append($("<input />").attr("type", "hidden").attr("name", "stripe_token").val(response.id));
            
        WPJB.order.placeOrder(undefined, {context: WPJB.stripe});
    },
    
    charge: function(response) {    
        var $ = jQuery;
        var data = {
            echo: "1",
            action: "wpjb_payment_accept",
            engine: "Stripe",
            id: response.payment_id,
            token_id: response.token_id,
            token_type: response.token_type
        };

        $.ajax({
            url: wpjb_payment_lang.ajaxurl,
            cache: false,
            type: "POST",
            data: data,
            dataType: "json",
            success: function(response) {
                var result = $("#wpjb-checkout-success");
                
                result.find(".wpjb-stripe-pending").hide();
                
                if(response.external_id) {
                    result.find(".wpjb-flash-info").removeClass("wpjb-none");
                    result.find(".wpjb-flash-info .wpjb-flash-body").html(response.message);
                } else {
                    result.find(".wpjb-flash-error").removeClass("wpjb-none");
                    result.find(".wpjb-flash-error .wpjb-flash-body").html(response.message);
                }

            }
        });
        
    },
    
    placeOrder: function(e) {
        
        e.preventDefault();
        var $ = jQuery;
        
        if( $("#recurring-agree").is(':checked') === false && $("#recurring-agree").val() == 5 ) {
            $("#recurring-agree-box").css("border", "1px solid red");
            $("#recurring-agree-box").css("background-color", "#FFF8DC");

            $('html, body').animate({
                scrollTop: $("#recurring-agree-box").offset().top - 50
            }, 100);
            
            return;
        }
        
        var $form = $('.wpjb-payment-form');
        
        $(".wpjb-place-order-wrap .wpjb-place-order").fadeOut("fast");
        $(".wpjb-place-order-wrap .wpjb-icon-spinner").css("visibility", "visible");
        
        var form = new WPJB.form(".wpjb-payment-form");
        var cc = $("#saved_credit_card").val();
        
        form.clearErrors();
        
        if(cc != "-1") {
            WPJB.order.placeOrder(undefined, {context: WPJB.stripe});
        } else {
            Stripe.setPublishableKey($("#stripe_publishable_key").val());
            Stripe.card.createToken({
                number: $form.find('.wpjb-stripe-cc[data-stripe="number"]').val(),
                cvc: $form.find('.wpjb-stripe-cc[data-stripe="cvc"]').val(),
                exp_month: $form.find('.wpjb-stripe-cc[data-stripe="exp-month"]').val(),
                exp_year: $form.find('.wpjb-stripe-cc[data-stripe="exp-year"]').val(),
                name: $form.find("#fullname").val()
            }, WPJB.stripe.response);
        }


    },
    
    placeOrderSuccess: function(response) {
        
        var $ = jQuery;
        var charge = $.extend(response, {
            payment_id: $("#wpjb-stripe-payment-id").val(),
            token_id: $("#wpjb-stripe-id").val(),
            token_type: $("#wpjb-stripe-type").val()
        });
        
        WPJB.stripe.charge(charge);
    }
}

jQuery(function($) {
    
    $("#saved_credit_card").change(function(e) {
        var option = $(this).val();
        
        if(option != "-1") {
            $(".wpjb-element-name-card_number").hide();
            $(".wpjb-element-name-cvc").hide();
            $(".wpjb-element-name-expiration").hide();
            $(".wpjb-element-name-options").hide();
        } else {
            $(".wpjb-element-name-card_number").show();
            $(".wpjb-element-name-cvc").show();
            $(".wpjb-element-name-expiration").show();
            $(".wpjb-element-name-options").show();
        }
    });
    
    if($("#saved_credit_card > option").length == 1) {
        $(".wpjb-element-name-saved_credit_card").hide();
    }
    
    $(".wpjb-place-order").unbind("click").bind("click", WPJB.stripe.placeOrder);
    
    WPJB.stripe.loadOnce();
    
});
