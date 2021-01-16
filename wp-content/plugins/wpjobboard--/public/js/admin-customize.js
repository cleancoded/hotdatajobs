// Init global namespace
var WPJB = WPJB || {};

// Init Customize namespace
WPJB.Customize = WPJB.Customize || {};

// Custom Field Class
WPJB.Customize.CustomField = function(wrap) {
    this.Nav = { };
    this.Nav.Enabled = wrap.find(".wpjb-ctrl-cf-enabled");
    this.Nav.Holder = wrap.find(".wpjb-ctrl-cf-holder");
    this.Nav.Capability = wrap.find(".wpjb-ctrl-cf-capability");
    this.Nav.Options = wrap.find(".wpjb-ctrl-cf-options");
    this.Nav.Label = wrap.find(".wpjb-ctrl-cf-label");
    this.Nav.Display = wrap.find(".wpjb-ctrl-cf-display");
    this.Nav.Icon = wrap.find(".wpjb-ctrl-cf-icon");
    this.Nav.Glyph = wrap.find(".wpjb-ctrl-cf-icon > span");
    this.Nav.IconList = wrap.find(".wpjb-ctrl-cf-icon-list");
    
    this.Nav.Enabled.on("change", jQuery.proxy(this.Check, this));
    
    this.Nav.Capability.on("change", jQuery.proxy(this.TriggerChange, this));
    this.Nav.Label.on("blur", jQuery.proxy(this.TriggerChange, this));
    this.Nav.Display.on("change", jQuery.proxy(this.TriggerChange, this));
    
    this.Nav.Icon.on("click", jQuery.proxy(this.BrowseIcons, this));
    
    this.Check();
};

WPJB.Customize.CustomField.prototype.Check = function() {
    if(this.Nav.Enabled.is(":checked")) {
        this.Nav.Options.fadeIn("fast");
    } else {
        this.Nav.Options.fadeOut("fast");
    }
    
    this.TriggerChange();
};

WPJB.Customize.CustomField.prototype.TriggerChange = function() {
    var enabled = "0";
    if(this.Nav.Enabled.is(":checked")) {
        enabled = "1";
    }
    
    var data = {
        enabled: enabled,
        capability: this.Nav.Capability.val(),
        title: this.Nav.Label.val(),
        display: this.Nav.Display.val(),
        icon: this.Nav.Icon.data("name")
    };
    
    this.Nav.Holder.val(JSON.stringify(data)).trigger('change');
};

WPJB.Customize.CustomField.prototype.BrowseIcons = function(e) {
    if(typeof e !== "undefined") {
        e.preventDefault();
    }
    
    if(this.Nav.Icon.hasClass("active")) {
        this.Nav.Icon.removeClass("active");
        this.Nav.IconList.html("");
    } else {
        var template = wp.template( 'wpjb-customizer-icon-select' );
        var tpl = jQuery( template( {} ) );

        tpl.show();
        tpl.find(".wpjb-image-icon-picker .button-secondary").on("click", jQuery.proxy(this.SelectIcon, this));
        tpl.find(".wpjb-category-icon-filter").on("keyup", jQuery.proxy(this.FilterIcons, this));

        this.Nav.Icon.addClass("active");
        this.Nav.IconList.html(tpl);
    }
};

WPJB.Customize.CustomField.prototype.SelectIcon = function(e) {
    e.preventDefault();
    
    var target = jQuery(e.currentTarget);
    var icon = target.data("name");
     
    this.Nav.Glyph.attr("class", "");
    this.Nav.Glyph.addClass("wpjb-glyphs wpjb-icon-"+icon);
            
    this.Nav.Icon.data("name", "wpjb-icon-"+icon);
    
    this.BrowseIcons();
    this.TriggerChange();
};

WPJB.Customize.CustomField.prototype.FilterIcons = function(e) {
    var val = jQuery.trim(jQuery(e.currentTarget).val());

    if(val.length > 0) {
        this.Nav.IconList.find(".wpjb-image-icon-picker > li").hide();
        this.Nav.IconList.find(".wpjb-image-icon-picker > li[data-name*='"+val+"']").show();
    } else {
        this.Nav.IconList.find(".wpjb-image-icon-picker > li").show();
    }
};

jQuery(function($) {
    jQuery(".wpjb-ctrl-cf-wrap").each(function(index, item) {
        new WPJB.Customize.CustomField(jQuery(item));
    });
});