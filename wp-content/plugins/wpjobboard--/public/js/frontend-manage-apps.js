// Init global namespace
var WPJB = WPJB || {};

WPJB.Manage = WPJB.Manage || {};

WPJB.Manage.Apps = {
    
};

WPJB.Manage.Apps.Item = function(item) {
    this.item = item;
    this.element = { };
    this.input = { };
    
    this.status = new WPJB.Manage.Apps.Item.StatusChange(this.item);
    this.rating = new WPJB.Manage.Apps.Item.Rating(this.item.find(".wpjb-star-ratings"))
    this.element.more = this.item.find(".wpjb-manage-actions-more");
    
    this.input.more = this.item.find(".wpjb-manage-action-more");
    
    // check if has more items
    if(this.element.more.find(".wpjb-manage-action").length < 1) {
        this.input.more.hide();
    }
};

WPJB.Manage.Apps.Item.Rating = function(item) {
    this.current_rating = 0;
    this.try_rating = null;
    
    this.rating = item;
    
    this.stars = this.rating.find(".wpjb-star-rating");
    this.loader = this.rating.find(".wpjb-star-rating-loader");
    
    this.stars.on( "click", jQuery.proxy( this.rating_click, this ) );
};

WPJB.Manage.Apps.Item.Rating.prototype.rating_click = function(e) {
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
        url: wpjb_manage_apps_lang.ajaxurl,
        type: "post",
        data: data,
        dataType: "json",
        success: jQuery.proxy( this.rating_success, this ),
        error: jQuery.proxy( this.rating_error, this )
    });
    
    
};

WPJB.Manage.Apps.Item.Rating.prototype.stars_check = function(index, item) {
    if(this.try_rating >= parseInt(jQuery(item).data("value"))) {
        jQuery(item).addClass("wpjb-star-checked");
    } else {
        jQuery(item).removeClass("wpjb-star-checked");
    }
};

WPJB.Manage.Apps.Item.Rating.prototype.rating_success = function(response) {
    this.loader.hide();
    this.current_rating = this.try_rating;
    this.try_rating = null;
    
    
    
};

WPJB.Manage.Apps.Item.StatusChange = function(item) {
    this.id = item.data("id");
    
    this.element = { };
    this.element.item = item;
    this.element.button = item.find(".wpjb-manage-app-status-change");
    this.element.button_label = item.find(".wpjb-application-status-current-label")
    this.element.slider = item.find(".wpjb-application-change-status");
    this.element.dropdown = item.find(".wpjb-application-change-status-dropdown");
    this.element.checkbox = item.find(".wpjb-application-change-status-checkbox");
    this.element.label = item.find(".wpjb-application-change-status-label");
    this.element.loader = item.find(".wpjb-application-change-status-loader");
    this.element.submit = item.find(".wpjb-application-change-status-submit");
    
    this.element.button.on( "click", jQuery.proxy( this.button_click, this ) );
    this.element.dropdown.on( "click", jQuery.proxy( this.dropdown_change, this ) );
    this.element.submit.on( "click", jQuery.proxy( this.submit_click, this ) );
};

WPJB.Manage.Apps.Item.StatusChange.prototype.button_click = function(e) {
    if(typeof e != "undefined") {
        e.preventDefault();
    }
    
    this.element.slider.slideToggle("fast");
    this.dropdown_change();
};

WPJB.Manage.Apps.Item.StatusChange.prototype.dropdown_change = function(e) {
    if(this.element.dropdown.find("option:selected").data("can-notify") == "1") {
        this.element.checkbox.attr("disabled", false);
        this.element.checkbox.show();
        this.element.label.show();
    }  else {
        this.element.checkbox.attr("disabled", "disabled");
        this.element.checkbox.hide();
        this.element.label.hide();
    }
};

WPJB.Manage.Apps.Item.StatusChange.prototype.submit_click = function(e) {
    e.preventDefault();
    
    this.element.loader.removeClass("wpjb-none");
    
    var notify = 0;
    if(!this.element.checkbox.is(":disabled") && this.element.checkbox.is(":checked")) {
        notify = 1;
    }
    
    var data = {
        action: "wpjb_applications_status",
        id: this.id,
        status: this.element.dropdown.val(),
        notify: notify
    };
    
    jQuery.ajax({
        url: wpjb_manage_apps_lang.ajaxurl,
        type: "post",
        dataType: "json",
        data: data,
        success: jQuery.proxy( this.submit_success, this ),
        error: jQuery.proxy( this.submit_error, this )
    });
};

WPJB.Manage.Apps.Item.StatusChange.prototype.submit_success = function(response) {
    this.element.loader.addClass("wpjb-none");
    this.element.button_label.text(this.element.dropdown.find("option:selected").text());
    
    this.element.item.removeClass (function (index, className) {
        return (className.match (/(^|\s)wpjb-application-status-\S+/g) || []).join(' ');
    });
    this.element.item.addClass("wpjb-application-status-"+response.status.key);
    this.button_click();
};

jQuery(function($) {
    $(".wpjb-manage-application").each(function(index, item) {
        new WPJB.Manage.Apps.Item(jQuery(item));
    });
    
    $(".wpjb-button-submit").on("click", function(e) {
        e.preventDefault();
        jQuery(this).closest("form").submit();
    });
});