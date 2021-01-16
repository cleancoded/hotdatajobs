var WPJB = WPJB || {};

WPJB.order = {
    loadScript: function(index, item) {
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.src = item; 
        script.className = "wpjb-payment-script-external";
        document.getElementsByTagName("body")[0].appendChild(script);
    },
    
    selectGateway: function(gateway) {
        var $ = jQuery;
        var data = {
            action: "wpjb_payment_render",
            gateway: gateway,
            defaults: $("#wpjb-checkout-defaults").data()
        };
        
        $(".wpjb-checkout-form").css("opacity", "0.5");
        
        $.ajax({
            url: wpjb_payment_lang.ajaxurl,
            type: "POST",
            data: data,
            dataType: "json",
            success: function(response) {
                $(".wpjb-tab-content").html(response.html);
                $(".wpjb-place-order").unbind("click").click(WPJB.order.placeOrder);
                
                $(".wpjb-payment-script-external").remove();
                $(".wpjb-checkout-form").css("opacity", "1");
                $.each(response.load, WPJB.order.loadScript);
            }
        });
    },
    
    placeOrder: function(e, extend) {        
        var $ = jQuery;

        if(typeof e !== 'undefined') {
           e.preventDefault(); 
        }
        
        if(typeof extend === 'undefined') {
           extend = {}; 
        }
                
        $(".wpjb-place-order-wrap .wpjb-place-order").fadeOut("fast");
        $(".wpjb-place-order-wrap .wpjb-icon-spinner").css("visibility", "visible");
        
        var form = {};
        
        $.each($(".wpjb-checkout-form form").serializeArray(), function(index, item) {
            if(item.name.indexOf("[]") > -1) {
                form[item.name.replace("[]", "")] = [item.value];
            } else {
                form[item.name] = item.value;
            }
        });
        
        var data = {
            action: "wpjb_payment_create",
            gateway: $("#wpjb-checkout-gateway .wpjb-tab-link.current").data("gateway"),
            object_id: $("#wpjb-checkout-defaults").data('object_id'),
            pricing_id: $("#wpjb-checkout-defaults").data('pricing_id'),
            discount_code: $(".wpjb-enter-discount-value").val(),
            payment_hash: $("#wpjb-checkout-defaults").data('payment_hash'),
            form: form
        };
        
        var ajax = $.extend({
            url: wpjb_payment_lang.ajaxurl,
            type: "POST",
            data: data,
            dataType: "json",
            success: WPJB.order.placeOrderSuccess
        }, extend);
        
        $.ajax(ajax);
        
    },
    
    placeOrderSuccess: function(response) {
        var $ = jQuery;
        // handle form error
        if(response.result == -1) {
            $(".wpjb-place-order-wrap .wpjb-place-order").show();
            $(".wpjb-place-order-wrap .wpjb-icon-spinner").css("visibility", "hidden");

            var form = new WPJB.form(".wpjb-payment-form");

            form.clearErrors();
            form.addError(response.form_error);

            $.each(response.form_errors, function(index, item) {
               form.addFieldError(".wpjb-element-name-"+index, item);
            });

        } else {

            $("#wpjb-checkout-gateway .wpjb-tab-link").unbind("click");
            $("#wpjb-checkout-gateway").hide();
            $(".wpjb-place-order-wrap .wpjb-icon-spinner").css("visibility", "hidden");
            $(".wpjb-checkout-form").hide();

            $("#wpjb-checkout-success").show().html(response.success);

            $(".wpjb-enter-discount").hide();
            $(".wpjb-enter-discount-form").hide();

            if($(".wpjb-payment-auto-submit").length > 0) {
                $(".wpjb-payment-auto-submit").submit();
            }

            if($(".wpjb-payment-auto-click").length > 0) {
                $(".wpjb-payment-auto-click").click();
            }

            if(this.placeOrderSuccess) {
                this.placeOrderSuccess(response);
            }
        }

    }
}

WPJB.order.discount = {
    free: function(isFree) {
        var $ = jQuery;
        
        if(isFree == "1") {
            $(".wpjb-checkout-free").show();
            $("#wpjb-checkout-gateway > li").hide();
            
            WPJB.order.selectGateway("");
            
        } else {
            $(".wpjb-checkout-free").hide();
            $("#wpjb-checkout-gateway > li").show();
            
            WPJB.order.selectGateway($("#wpjb-checkout-gateway .wpjb-tab-link.current").data("gateway"));
        }
    }
}

jQuery(function($) {
    
    // DISCOUNT
    
    $(".wpjb-enter-discount").click(function(e) {
        e.preventDefault();
        
        $(".wpjb-enter-discount-form").slideToggle("fast");
    });
    
    $(".wpjb-enter-discount-apply").click(function(e) {
        e.preventDefault();
        
        if($(".wpjb-enter-discount-value").val().length == 0) {
            $(".wpjb-enter-discount-form").hide();
            $(".wpjb-enter-discount-applied").hide();
            $(".wpjb-enter-discount-failed").hide();
            $(".wpjb-payment-discount").hide();
            
            $(".wpjb-payment-discount .wpjb-value").text($(".wpjb-payment-discount .wpjb-value").data("price-default"));
            $(".wpjb-payment-total .wpjb-value").text($(".wpjb-payment-total .wpjb-value").data("price-default"));
            
            WPJB.order.discount.free(false);
            
            $(".wpjb-enter-discount-start").show();
            
            return;
        }
        
        $(".wpjb-enter-discount-form .wpjb-icon-spinner").css("visibility", "visible");
        
        var data = {
            action: "wpjb_main_coupon",
            code: $(".wpjb-enter-discount-value").val(),
            id: $("#wpjb-checkout .wpjb-payment-item .wpjb-value").data("pricing-id")
        };
        
        $.ajax({
            url: wpjb_payment_lang.ajaxurl,
            type: "POST",
            data: data,
            dataType: "json",
            success: function(response) {
                $(".wpjb-enter-discount-form .wpjb-icon-spinner").css("visibility", "hidden");
                
                $(".wpjb-enter-discount-start").show();
                $(".wpjb-enter-discount-applied").hide();
                $(".wpjb-enter-discount-failed").hide();
                
                if(response.result == "1") {
                    $(".wpjb-enter-discount-form").hide();
                    
                    $(".wpjb-enter-discount-applied").show();
                    $(".wpjb-enter-discount-applied .wpjb-enter-discount-msg").html(response.msg);
                    
                    $(".wpjb-enter-discount-start").hide();
                    
                    $(".wpjb-payment-discount").show();
                    $(".wpjb-payment-discount .wpjb-value").text(response.discount);
                    
                    $(".wpjb-payment-total .wpjb-value").text(response.total);
                    $(".wpjb-payment-total .wpjb-value-subtotal").text(response.subtotal);
                    $(".wpjb-payment-total .wpjb-value-tax").text(response.tax);
                    
                    WPJB.order.discount.free(response.is_free);
                    
                } else {
                    $(".wpjb-enter-discount-failed").show();
                    $(".wpjb-enter-discount-failed .wpjb-enter-discount-msg").html(response.msg);
                    
                    $(".wpjb-payment-discount").hide();
                    
                    WPJB.order.discount.free(false);
                    
                    $(".wpjb-payment-discount .wpjb-value").text($(".wpjb-payment-discount .wpjb-value").data("price-default"));
                    $(".wpjb-payment-total .wpjb-value").text($(".wpjb-payment-total .wpjb-value").data("price-default"));
                    
                    $(".wpjb-payment-total .wpjb-value-subtotal").text($(".wpjb-payment-total .wpjb-value-subtotal").data("price-default"));
                    $(".wpjb-payment-total .wpjb-value-tax").text($(".wpjb-payment-total .wpjb-value-tax").data("price-default"));
                } // end if
            } // end success
        }); // end $.ajax
    }); // end $(".wpjb-discount-apply").click()
    
    // TABS
    
    $("#wpjb-checkout-gateway .wpjb-tab-link").click(function(e) {
        e.preventDefault();
        
        $("#wpjb-checkout-gateway .wpjb-tab-link").removeClass("current");
        $(this).addClass("current");
        
        WPJB.order.selectGateway($(this).data("gateway"));
    });
    
    WPJB.order.selectGateway($("#wpjb-checkout-gateway .wpjb-tab-link.current").data("gateway"));
    
});