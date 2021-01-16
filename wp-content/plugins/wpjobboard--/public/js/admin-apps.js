// Init global namespace
var WPJB = WPJB || {};

WPJB.Admin = WPJB.Admin || {};
WPJB.Admin.Manage = WPJB.Admin.Manage || {};

WPJB.Admin.Manage.Apps = {
    
};

WPJB.Admin.Manage.Apps.Rating = function(item) {
    this.current_rating = 0;
    this.try_rating = null;
    
    this.rating = item;
    
    this.stars = this.rating.find(".wpjb-star-rating");
    this.loader = this.rating.find(".wpjb-star-rating-loader");
    
    this.stars.on( "click", jQuery.proxy( this.rating_click, this ) );
};

WPJB.Admin.Manage.Apps.Rating.prototype.rating_click = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }
    
    this.try_rating = parseInt(jQuery(e.target).data("value"));
    this.loader.show();
    
    jQuery.each(this.stars, jQuery.proxy( this.stars_check, this ));
    
    var data = {
        action: "wpjb_applications_rate",
        application: this.rating.data("id"),
        value: this.try_rating
    };
    
    jQuery.ajax({
        url: ajaxurl,
        type: "post",
        data: data,
        dataType: "json",
        success: jQuery.proxy( this.rating_success, this ),
        error: jQuery.proxy( this.rating_error, this )
    });
    
    
};

WPJB.Admin.Manage.Apps.Rating.prototype.stars_check = function(index, item) {
    if(this.try_rating >= parseInt(jQuery(item).data("value"))) {
        jQuery(item).addClass("wpjb-star-checked");
    } else {
        jQuery(item).removeClass("wpjb-star-checked");
    }
};

WPJB.Admin.Manage.Apps.Rating.prototype.rating_success = function(response) {
    this.loader.hide();
    this.current_rating = this.try_rating;
    this.try_rating = null;
    
    
    
};

WPJB.Admin.Manage.Apps.StatusChange = function(item) {
    this.current = {
        option: null,
        notify: null
    };
    

    
    this.element = {};
    this.element.main = item;
    this.element.label = item.find(".wpjb-app-status-label");
    this.element.dropdown = item.find(".wpjb-app-status-dropdown");
    this.element.button = item.find(".wpjb-app-status-button");
    this.element.switch = item.find(".wpjb-app-status-switch");
    this.element.ok = item.find(".wpjb-app-status-ok");
    this.element.cancel = item.find(".wpjb-app-status-cancel");
    this.element.notify_section = item.find(".wpjb-app-status-notify-section");
    this.element.notify_icon = item.find(".wpjb-app-status-notify-icon");
    this.element.notify = item.find(".wpjb-app-status-notify");
    
    this.element.dropdown.on("change", jQuery.proxy(this.dropdown_changed, this));
    
    this.element.button.on("click", jQuery.proxy(this.button_clicked, this));
    this.element.ok.on("click", jQuery.proxy(this.ok_clicked, this));
    this.element.cancel.on("click", jQuery.proxy(this.cancel_clicked, this));
    
    this.default = {
        option: this.element.dropdown.find("option:selected").attr("value"),
        notify: this.element.notify.is(":checked")
    };
    
    this.element.dropdown.change();
    this.element.ok.click();
};


WPJB.Admin.Manage.Apps.StatusChange.prototype.dropdown_changed = function() {
    var selected = this.element.dropdown.find("option:selected");
    
    if(selected.data("notify") == "1") {
        this.element.notify_section.show();
        this.element.notify.attr("disabled", null);
    } else {
        this.element.notify_section.hide();
        this.element.notify.attr("disabled", "disabled");
    }
    
    if(selected.data("active") == "1") {
        this.element.notify.attr("checked", true);
    } else {
        this.element.notify.attr("checked", false);
    }
};

WPJB.Admin.Manage.Apps.StatusChange.prototype.button_clicked = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }
    
    this.element.button.hide();
    this.element.switch.show();
};

WPJB.Admin.Manage.Apps.StatusChange.prototype.ok_clicked = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }
    
    var selected = this.element.dropdown.find("option:selected");
    
    this.element.button.show();
    this.element.switch.hide();
    
    this.current.option = selected.attr("value");
    this.current.notify = this.element.notify.is(":checked");
    
    this.element.label.text(selected.text());
    
    if(this.default.option != this.current.option && this.current.notify) {
        this.element.notify_icon.show();
    } else {
        this.element.notify_icon.hide();
    }
};

WPJB.Admin.Manage.Apps.StatusChange.prototype.cancel_clicked = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }
    
    this.element.button.show();
    this.element.switch.hide();
    
    this.element.dropdown.val(this.current.option);
    
    if(this.current.notify == "1") {
        this.element.notify.attr("checked", "checked");
    } else {
        this.element.notify.attr("checked", null);
    }
};

jQuery(function($) {
    $(".wpjb-star-ratings").each(function(index,item) {
        new WPJB.Admin.Manage.Apps.Rating($(item));
    });
    
    if($(".wpjb-app-status-section").length > 0) {
        new WPJB.Admin.Manage.Apps.StatusChange($(".wpjb-app-status-section"));
    }
});