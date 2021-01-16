// Init global namespace
var WPJB = WPJB || {};

WPJB.Admin = WPJB.Admin || {};
WPJB.Alert = WPJB.Alert || {};
WPJB.Admin.Manage = WPJB.Admin.Manage || {};

WPJB.Admin.Manage.Apps = {
    
};

WPJB.Admin.Manage.Apps.Alerts = function(item) {

    this.show_log_btn = item;
    this.show_log_btn.on( "click", jQuery.proxy( this.show_log, this ) );
};

WPJB.Admin.Manage.Apps.Alerts.prototype.show_log = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }
    
    var log_id = jQuery(e.target).data('id');
    jQuery('#wpjb-alert-log-'+log_id).slideToggle();
}

WPJB.Alert.Item = function(item) { 
    this.alert_param = item;
    
    this.remove_param_btn = this.alert_param.find(".wpjb-remove-alert-param");
    this.remove_param_btn.on( "click", jQuery.proxy( this.remove_click, this ) );
}

WPJB.Alert.Add = function(item) {
    this.alert = item;
    
    this.add_param_btn = this.alert.find('.wpjb-alert-params-add');
    this.add_param_btn.on( "click", jQuery.proxy( this.add_click, this ) );
}


WPJB.Alert.Item.prototype.remove_click = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }

    this.alert_param.find('input').val('');
    this.alert_param.find('input').removeAttr('checked');
    this.alert_param.find('select').empty();
    this.alert_param.fadeOut("fast");
    
};

WPJB.Alert.Add.prototype.remove_click = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }

    this.remove_btn_container.fadeOut("fast");
};

WPJB.Alert.Add.prototype.add_click = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }
    
    var template = wp.template( 'wpjb-single-alert-param' );
    var tpl = jQuery( template( {} ) );
    
    this.remove_btn_container = tpl;
    this.remove_btn = tpl.find(".wpjb-remove-alert-param").on( "click", jQuery.proxy( this.remove_click, this ) );
    
    tpl.find(".wpjb-new-alert-param-type").on( "change", jQuery.proxy( this.param_selected, this ) );
    this.alert.find(".wpjb-alerts-new-box").before(tpl);
    
    if( this.alert.find(".wpjb-alerts-empty") !== 'undefined' ) {
        this.alert.find(".wpjb-alerts-empty").remove();
    }
};

WPJB.Alert.Add.prototype.param_selected = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }
    
    var selected = jQuery(e.target);
    var params = jQuery.parseJSON( atob( selected.val() ) );   
    var template = wp.template( 'wpjb-single-alert-param-form' );
    var tpl = jQuery( template({
        input_name : params["input_name"],
        input_type : params["input_type"],
        input_label : params["input_label"],
        options : params["options"],
        value : this.value
    } ) );

    var label = jQuery("<label>").attr("for", params["input_name"]).html(params["input_label"]);
    
    jQuery(e.target).parents("tr").children("td.wpjb-single-alert-param-input").append(tpl);
    jQuery(e.target).parents("tr").children("th").append(label);
    jQuery(e.target).remove();
    
};


jQuery(function($) {
    $(".wpjb-alert-param").each(function(index, item) {
        new WPJB.Alert.Item($(item));
    });
    
    $(".wpjb-alert-params").each(function(index, item) {
        new WPJB.Alert.Add($(item));
    });
    
    $(".wpjb-alert-show-log").each(function(index, item) {
        new WPJB.Admin.Manage.Apps.Alerts($(item));
    });
});