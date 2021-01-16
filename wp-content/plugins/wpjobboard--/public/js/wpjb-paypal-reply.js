jQuery(function($) {
    
    $("#wpjb-paypal-overlay").show();
    $("#wpjb-paypal-overlay").css("height", $(document).height());
    $("#wpjb-paypal-overlay").css("width", $(document).width());

    var c = $("#wpjb-paypal-overlay > div");
    c.css("position","absolute");
    c.css("top", Math.max(0, (($(window).height() - c.outerHeight()) / 2) + $(window).scrollTop()) + "px");
    c.css("left", Math.max(0, (($(window).width() - c.outerWidth()) / 2) +  $(window).scrollLeft()) + "px");
    
    $(".wpjb-overlay").unbind("click");
    
    var PayPalStandardXHR = null;
    var PayPalStandardIntervalID = setInterval(function () {

       if( PayPalStandardXHR ) {
           PayPalStandardXHR.abort();
       }
       
       PayPalStandardXHR = $.ajax({
           url: wpjb_paypal_reply.ajaxurl,
           data: {
               action: 'wpjb_payment_check',
               payment_id: wpjb_paypal_reply.payment_id,
               external_id: wpjb_paypal_reply.external_id
           },
           type: "post",
           dataType: "json",
           success: function(response) {
               if(response.status == -1) {
                   $(".wpjb-paypal-reply-pending").hide();
                   $(".wpjb-paypal-reply-failed").show();
                   $(".wpjb-paypal-reply-failed .wpjb-paypal-reply-message").html(response.message);
                   window.clearInterval(PayPalStandardIntervalID);
               } else if(response.status == 1) {
                   $(".wpjb-paypal-reply-pending").hide();
                   $(".wpjb-paypal-reply-complete").show();
                   $(".wpjb-paypal-reply-complete .wpjb-paypal-reply-message").html(response.message);
                   window.clearInterval(PayPalStandardIntervalID);
               } else if (++wpjb_paypal_reply.interval_i === parseInt( wpjb_paypal_reply.interval_x ) ) {
                   $(".wpjb-paypal-reply-pending").hide();
                   $(".wpjb-paypal-reply-timedout").show();
                    window.clearInterval(PayPalStandardIntervalID);
               }
           }
       });


    }, wpjb_paypal_reply.interval); 
});