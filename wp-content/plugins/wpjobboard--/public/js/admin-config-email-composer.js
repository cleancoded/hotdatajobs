var WPJB = WPJB || {};

WPJB.ConfigEmail = {
    List: []
};

WPJB.ConfigEmail.Iris = function(data) {
    
    this.input = data.input;
    this.reset = data.restore;
    
    this.func = {
        
    };
    this.func.change = data.change;
    
    if(this.input.val().length === 0) {
        this.input.val(this.input.data("color-default"));
    }
    
    this.input.iris({
        change: jQuery.proxy( this.change, this )
    });
    
    this.reset.on("click", jQuery.proxy( this.restore, this) );
    this.input.closest('.edit-attachment-frame').on("click", jQuery.proxy( this.dispose, this ) );
    
    this.func.change(this.input.iris('color'));
};

WPJB.ConfigEmail.Iris.prototype.change = function(event, ui) {
    this.func.change(ui.color.toString());
};

WPJB.ConfigEmail.Iris.prototype.restore = function(event) {
    event.preventDefault();
    
    var color = this.input.data("color-default");
    
    this.input.val(color);
    this.func.change(color);
};

WPJB.ConfigEmail.Iris.prototype.dispose = function(event) {
    if(event.target.id != this.input.attr("id") ) {
        this.input.iris('hide');  
    } else {
        this.input.iris('show');  
    }
};

jQuery(function($){
    
    WPJB.ConfigEmail.List.push(new WPJB.ConfigEmail.Iris({
        input: $("#wpjb-email-color-background"),
        restore: $("#wpjb-email-restore-background"),
        change: function(color) {
            frame = jQuery("iframe").contents();
            frame.find("html, table.body, .grey").css("background", color);
        }
    }));
    
    WPJB.ConfigEmail.List.push(new WPJB.ConfigEmail.Iris({
        input: $("#wpjb-email-color-background-body"),
        restore: $("#wpjb-email-restore-background-body"),
        change: function(color) {
            frame = jQuery("iframe").contents();
            frame.find(".main").css("background", color);
        }
    }));
    
    WPJB.ConfigEmail.List.push(new WPJB.ConfigEmail.Iris({
        input: $("#wpjb-email-color-text"),
        restore: $("#wpjb-email-restore-color-text"),
        change: function(color) {
            frame = jQuery("iframe").contents();
            frame.find("p, li").css("color", color);
        }
    }));
    
    WPJB.ConfigEmail.List.push(new WPJB.ConfigEmail.Iris({
        input: $("#wpjb-email-color-link"),
        restore: $("#wpjb-email-restore-color-link"),
        change: function(color) {
            frame = jQuery("iframe").contents();
            jQuery.each(frame.find("a"), function(index, item) {
                if($(item).closest(".btn").length == 0) {
                    $(item).css("color", color);
                }
            });
        }
    }));
    
    WPJB.ConfigEmail.List.push(new WPJB.ConfigEmail.Iris({
        input: $("#wpjb-email-color-text-header"),
        restore: $("#wpjb-email-restore-text-header"),
        change: function(color) {
            frame = jQuery("iframe").contents();
            frame.find("h1, h2, h3, h4, h5, h6").css("color", color);
        }
    }));
    
    WPJB.ConfigEmail.List.push(new WPJB.ConfigEmail.Iris({
        input: $("#wpjb-email-color-text-footer"),
        restore: $("#wpjb-email-restore-text-footer"),
        change: function(color) {
            frame = jQuery("iframe").contents();
            frame.find(".footer td, .footer span, .footer a").css("color", color);
        }
    }));
    
    WPJB.ConfigEmail.List.push(new WPJB.ConfigEmail.Iris({
        input: $("#wpjb-email-color-button"),
        restore: $("#wpjb-email-restore-button"),
        change: function(color) {
            frame = jQuery("iframe").contents();

            frame.find(".btn a, .btn table td").css("background-color", color);
            frame.find(".btn a").css("border-color", color);
        }
    }));
    
    WPJB.ConfigEmail.List.push(new WPJB.ConfigEmail.Iris({
        input: $("#wpjb-email-color-button-text"),
        restore: $("#wpjb-email-restore-button-text"),
        change: function(color) {
            frame = jQuery("iframe").contents();
            frame.find(".btn a").css("color", color);
        }
    }));

    $("#wpjb-email-footer").on("blur keyup", function(e) {
        frame = jQuery("iframe").contents();
        if($(this).val().length > 0) {
            frame.find(".footer .content-block span").html($(this).val());
        } else {
            frame.find(".footer .content-block span").html($(this).attr("placeholder"));
        }
    });
    
    $("#wpjb-email-logo").on("blur keyup", function(e) {
        frame = jQuery("iframe").contents();
        if($(this).val().length > 0) {
            frame.find(".logo img").attr("src", $(this).val());
            frame.find(".logo").show();
        } else {
            frame.find(".logo img").attr("src", "#");
            frame.find(".logo").hide();
        }
    });
    
    $("#wpjb-email-preview-desktop").on("click", function(e) {
        e.preventDefault();
        
        $("#wpjb-email-preview-desktop").addClass("active");
        $("#wpjb-email-preview-mobile").removeClass("active");
        
        $("#wpjb-email-frame").css("width", "100%");
    });
    
    $("#wpjb-email-preview-mobile").on("click", function(e) {
        e.preventDefault();
        
        $("#wpjb-email-preview-desktop").removeClass("active");
        $("#wpjb-email-preview-mobile").addClass("active");
        
        $("#wpjb-email-frame").css("width", "420px");
    });
    



    $('iframe').on('load', function(){
        $("#wpjb-email-footer").blur();
        $("#wpjb-email-logo").blur();
        
        jQuery.each(WPJB.ConfigEmail.List, function(index, item) {
            item.func.change(item.input.iris('color'));
        });
    });



});