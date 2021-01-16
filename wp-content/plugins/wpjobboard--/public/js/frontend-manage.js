// Init global namespace
var WPJB = WPJB || {};

WPJB.Manage = {
    
};

WPJB.Manage.Actions = function( actions ) {
    this.actions = actions;
    this.input = { };
    this.element = { };
    
    this.element.remove = jQuery(this.actions).find(".wpjb-manage-delete-confirm");
    this.element.more = jQuery(this.actions).find(".wpjb-manage-actions-more");
    this.element.spinner = jQuery(this.actions).find(".wpjb-manage-action-spinner");
    
    this.input.remove = jQuery(this.actions).find(".wpjb-manage-action-delete");
    this.input.remove_yes = jQuery(this.actions).find(".wpjb-manage-action-delete-yes");
    this.input.remove_no = jQuery(this.actions).find(".wpjb-manage-action-delete-no");
    this.input.more = jQuery(this.actions).find(".wpjb-manage-action-more");
    
    //this.input.remove.click( jQuery.proxy( this.remove, this ) );
    this.input.remove_yes.click( jQuery.proxy( this.removeYes, this ) );
    this.input.remove_no.click( jQuery.proxy( this.removeNo, this ) );
    this.input.more.click( jQuery.proxy( this.more, this ) );
    
    // check if has more items
    if(this.element.more.find(".wpjb-manage-action").length < 1) {
        this.input.more.hide();
    }
};

WPJB.Manage.Actions.prototype.remove = function(e) {
    e.preventDefault();
    this.element.remove.css("display", "inline-block");
    this.element.spinner.hide();
    this.input.remove_yes.show();
    this.input.remove_no.show();
    this.input.remove.hide();
};

WPJB.Manage.Actions.prototype.removeYes = function(e) {
    e.preventDefault();
    
    this.element.spinner.show();
    this.input.remove_yes.hide();
    this.input.remove_no.hide();
    
    var data = {
        action: "adverts_delete",
        ajax: "1",
        _ajax_nonce: this.input.remove.data("nonce"),
        id: this.input.remove.data("id")
    };

    jQuery.ajax({
        url: wpjb_manage_lang.ajaxurl,
        context: this,
        type: "post",
        dataType: "json",
        data: data,
        success: jQuery.proxy( this.removeSuccess, this ),
        error: jQuery.proxy( this.removeError, this )
    });
};

WPJB.Manage.Actions.prototype.removeNo = function(e) {
    e.preventDefault();
    this.element.remove.attr("style", "");
    this.input.remove.show();
};

WPJB.Manage.Actions.prototype.removeSuccess = function(response) {
    if(response.result == "1") {
        
        var a = jQuery("<a></a>").attr("href", "#").html(wpjb_manage_lang.ok).click(function(e) {
            e.preventDefault();
            jQuery(this).closest(".advert-manage-deleted").fadeOut("fast");
        });
        
        jQuery(this.actions)
            .hide()
            .addClass("advert-manage-deleted adverts-icon-trash")
            .html(response.message)
            .append(" ")
            .append(a)
            .fadeIn("fast");
    } else {
        this.element.spinner.hide();
        this.input.remove_yes.show();
        this.input.remove_no.show();
        alert(response.error);
    }
};

WPJB.Manage.Actions.prototype.removeError = function(response) {
    if(response.result) {
        alert(response.eror);
    } else {
        alert(response);
    }
};

WPJB.Manage.Actions.prototype.more = function(e) {
    e.preventDefault();
    this.element.more.slideToggle("fast");
};

jQuery(function($) {
    
    $(".wpjb-manage-item").each(function(index, item) {
        new WPJB.Manage.Actions(item);
    });
    
    
});