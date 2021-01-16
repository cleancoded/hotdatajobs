var WPJB = WPJB || {};

WPJB.alert = {
    uid: function() {
        if (!Date.now) {
            return new Date().getTime();
        } else {
            return Date.now()
        }
    },
    set_alerts: function(partials, fields) {
        
        var $ = jQuery;
        this.detail  = partials;
        $.each(partials, function(index, partial) {
            
            // Prepare alert object
            var item = new WPJB.alert.partial({
                saved: partial.saved,       // data entered and saved
                id: partial.id,             // unique CSS id for detail
                key: partial.key,           // use detail.id is possible
                detail: partial.details,
                owner: partial.owner,       // where to insert this item
                view: partial.view,         // view template id
                form: partial.form,         // form template id
                input: partial.input
            });
            
            
            // Add params
            if(typeof item.detail !== 'undefined' && typeof item.detail.params !== 'undefined') {
                $.each(item.detail.params, function(label, detail) {

                    var tmp_input = {};
                    var flag = false; 

                    // Create params objects 
                    $.each(fields.default_fields, function(f_id, f_details) {
                        if(!flag) {
                            tmp_input = jQuery.parseJSON( atob( f_details.value ) );
                        }
                        
                        if(tmp_input.input_name == label && !flag) {
                            flag = true;
                        } else if(!flag) {
                            tmp_input = {};
                        }
                    });
                    
                    if(!flag) {
                        $.each(fields.custom_fields, function(f_id, f_details) {
                            if(!flag) {
                                tmp_input = jQuery.parseJSON( atob( f_details.value ) );
                            }

                            if(tmp_input.input_name == label && !flag) {
                                flag = true;
                            } else if(!flag) {
                                tmp_input = {};
                            }
                        });
                    }
                    
                    tmp_input.value = detail;
                    
                    //var uid = WPJB.alert.uid();
                    var uid = label + "-" + WPJB.alert.uid();
                    var param = new WPJB.alert.alert_param({
                        saved: false,                           // data entered and saved
                        id: "wpjb-alert-param-"+uid,            // unique CSS id for detail
                        key: uid,                               // use detail.id is possible
                        owner: "wpjb-alert-params",             // where to insert this item
                        view: "wpjb-single-alert-param",        // view template id
                        form: "wpjb-single-alert-param-form",   // form template id
                        alert_id: item.id,
                        input: tmp_input,
                        value: detail
                    });

                    item.alert_params.push(param);
                });
            }
            

            if(partial.errors.length > 0) {
                var errors = {
                    result: -1,
                    form_error: wpjb_alert_lang.form_error,
                    form_errors: partial.errors
                }
                //item.form_show();
                //item.form_validate(errors);
            //} else if(partial.delete) {
                //item.undo_show();
            } else {
                item.view_show();
            }

            WPJB.alert.detail.push(item);
            
            var form = $("#"+partial.owner).closest("form");
            if(form.data("pre-validate-once") != "1") {
                form.data("pre-validate-once", "1");
                //form.on("submit", WPJB.alert.pre_submit);
            }    
        });
    },
    
    detail: []
}

WPJB.alert.alert_param = function(data) {
    
    this.id = data.id;
    this.field = data.field;
    this.value = data.value;
    this.remove = 0;
    this.owner = data.owner;
    this.alert_id = data.alert_id;
    this.input = data.input;
    
    this.template = {
        form: data.form,
        view: data.view
    };

    this.construct();
};

WPJB.alert.alert_param.prototype.construct = function(e) {
    var $ = jQuery;
    if( typeof this.alert_id !== 'undefined' ) {
        $("#"+this.alert_id).find("#"+this.owner).append($("<div></div>").addClass('wpjb-grid-row wpjb-alert-param').attr("id", this.id).data("id", this.id));
    } else {
        $("#"+this.owner).append($("<div></div>").addClass('wpjb-grid-row wpjb-alert-param').attr("id", this.id).data("id", this.id));
    }
    
    
};

WPJB.alert.alert_param.prototype.form_show = function(e) {
    
    var $ = jQuery;
    var template = wp.template( this.template.form );
    var input = this.input; 

    this.form = jQuery(template(input));

    $("#" + this.id).html(this.form);
    
    this.form.hide();
    this.form.fadeIn("fast");
};

WPJB.alert.alert_param.prototype.view_show = function(e) {
    
    var $ = jQuery;
    var template = wp.template( this.template.view );
    var input = this.input; 

    this.view = jQuery(template(input));    
    this.view.find(".wpjb-remove-alert-param").on( "click", jQuery.proxy( this.remove_param, this ) );
    this.view.find(".wpjb-new-alert-param-type").on( "change", jQuery.proxy( this.param_selected, this ) ); 

    $("#" + this.id).html(this.view);
};

WPJB.alert.alert_param.prototype.remove_param = function(e) {
    e.preventDefault();
    var $ = jQuery; 
    this.value = null;
    $("#"+this.id).remove();
};

WPJB.alert.alert_param.prototype.param_selected = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }
    var $ = jQuery;
    
    var template = wp.template( this.template.form );
    
    if(this.input.length === 0) {
        var params = jQuery.parseJSON( atob( this.view.find(".wpjb-new-alert-param-type").val() ) ); 
        this.input = {
            input_name : params["input_name"],
            input_type : params["input_type"],
            input_label : params["input_label"],
            options : params["options"],
            value : params["value"]
        }
    }
    
    this.view = jQuery(template(this.input));   
    this.view.find(".wpjb-remove-alert-param").on( "click", jQuery.proxy( this.remove_param, this ) );
    
    $("#" + this.id).html(this.view);
};



WPJB.alert.partial = function(data) {
    
    this.saved = data.saved;
    this.busy = false;
    this.id = data.id;
    this.owner = data.owner;
    this.detail = data.detail;
    this.key = data.key;
    this.remove = 0;
    this.alert_params = [];  
    
    this.input = data.input;
    this.template = {
        form: data.form,
        view: data.view
    };
    this.action = {
        form: { },
        view: { }
    };
    
    //this.form = null;
    this.view = null;
    
    this.construct();
};

WPJB.alert.partial.prototype.construct = function() {
    var $ = jQuery;
    $("#"+this.owner).append($("<div></div>").addClass('wpjb-grid-row wpjb-manage-item wpjb-manage-alert').attr("id", this.id).data("id", this.id));
};

WPJB.alert.partial.prototype.destroy = function() {
    var $ = jQuery;
    var $this = this;
    
    $.each(WPJB.alert.detail, function(index, detail) {
        if($this.id == detail.id) {
            WPJB.alert.detail.splice(index, 1);
            return false;
        }
    });
    
    $("#"+this.id).remove();
};

WPJB.alert.partial.prototype.data = function() {
    var $ = jQuery;
    var input = {};

    $.each($("#" + this.id + " .wpjb-form-nested .wpjb-form").serializeArray(), function(index, item) {
        if(item.name.indexOf("[]") > -1) {
            input[item.name.replace("[]", "")] = [item.value];
        } else {
            input[item.name] = item.value;
        }
    });
    
    var params = {};
    $.each(this.alert_params, function(index, item) {
        if( typeof item.value !== 'undefined' ) {
            params[item.input.input_name] = item.value;
        }
    });
        
    
    //var php = require('js-php-serialize');
    if( !jQuery.isEmptyObject( params ) ) {
        input["params"] = params;
    }

    return input;
};

WPJB.alert.partial.prototype.form_show = function() {
    var $ = jQuery;
    var template = wp.template( this.template.form );
    var input = this.input; 

    this.form = jQuery(template(input));

    $("#" + this.id).html(this.form);
    
    this.form.hide();
    this.form.fadeIn("fast");

    this.form.find(".wpjb-form-nested-save").on("click", $.proxy(this.form_save, this));
    this.form.find(".wpjb-form-nested-close").on("click", $.proxy(this.form_cancel, this));
    this.form.find(".wpjb-form-nested-add-param").on("click", $.proxy(this.form_add_param, this));
    
    var container = jQuery('<div id="wpjb-alert-params"></div>');
    this.form.find(".wpjb-fieldset-params").append(container);
    
    $.each(this.alert_params, function(index, item) {
        if(item.value) {
            item.input.value = item.value;
            item.construct();
            item.param_selected();
        }
    });
    
    var $this = this;
    
    $.each(this.input, function(index, item) {
        $this.form.find("input[type='text'][name='"+index+"']").val(item);
        $this.form.find("input[type='hidden'][name='"+index+"']").val(item);
        $this.form.find("input[type='checkbox'][name='"+index+"[]'][value='"+item+"']").attr("checked", "checked");
        $this.form.find("input[type='radio'][name='"+index+"'][value='"+item+"']").attr("checked", "checked");
        $this.form.find("select[name='"+index+"'] option[value='"+item+"']").attr("selected", "selected");
        $this.form.find("textarea[name='"+index+"']").val(item);      
    });
     
    this.view = null;
};


WPJB.alert.partial.prototype.form_save = function(e) {
    e.preventDefault();

    if(this.busy) {
        return;
    }
    
    var $ = jQuery;
    var $this = this;
    
    $.each($this.alert_params, function(index, item) {
        
        item.value = null;
        $this.input.params = {};
        var tmp_val = [];
        if(item.input.input_type == "checkbox") {
            $("#" + $this.id + " #" + item.input.input_name + ":checked").each(function() {
                tmp_val.push($(this).val());
            });
            //= $().find.val();
        } else {
            tmp_val = $("#" + $this.id).find("#" + item.input.input_name).val();
        }
        item.value = tmp_val;
        
        $this.input.params[item.input.input_name] = item.value;
        //$this.input._conf.detail.params[item.input.input_name] = item.value;
        
        if(typeof $this.input.params_count === 'undefined') {
            $this.input.params_count = 0;
        }
        $this.input.params_count++;
    });
    
    var data = {
        action: "wpjb_myresume_validate",
        form: this.template.form,
        input: this.data()
    };

    this.busy = true;
    $("#" + this.id + " .wpjb-form-nested-progress").css("visibility", "visible");

    $.ajax(wpjb_alert_lang.ajaxurl, {
        type: "POST",
        data: data,
        dataType: "json",
        success: $.proxy(this.form_validate, this)
    });

    return false;
};

WPJB.alert.partial.prototype.form_validate = function(response) {
    var $ = jQuery;
    
    $("#" + this.id + " .wpjb-form-nested-progress").css("visibility", "hidden");
    this.busy = false;

    if(response.result == -1) {
        var form = new WPJB.form("#" + this.id + " .wpjb-form-nested");

        form.clearErrors();
        form.addError(response.form_error);

        $.each(response.form_errors, function(index, item) {
           form.addFieldError(".wpjb-element-name-"+index, item);
        });
    } else {
        this.input = this.data();
        this.saved = true;
        this.view_show();
    }
}

WPJB.alert.partial.prototype.form_cancel = function(e) {
    e.preventDefault();
    
    if(this.busy) {
        return;
    }
    
    var add_btn = jQuery("#wpjb-add-new-alert");
    add_btn.data("alertcur", add_btn.data("alertcur") - 1 );
    if( add_btn.data('alertmax') > add_btn.data('alertcur') ) { 
        add_btn.show();
    }
    
    if(this.saved) {
        this.view_show();
    } else {
        this.destroy();
    }
};

WPJB.alert.partial.prototype.form_add_param = function(e) {
    e.preventDefault();
    
    var container = jQuery('<div id="wpjb-alert-params"></div>');
    //this.form.find(".wpjb-fieldset-params").append(container);
    
    var uid = WPJB.alert.uid();
    var param = new WPJB.alert.alert_param({
        saved: false,                           // data entered and saved
        id: "wpjb-alert-param-"+uid,            // unique CSS id for detail
        key: uid,                               // use detail.id is possible
        owner: "wpjb-alert-params",             // where to insert this item
        view: "wpjb-single-alert-param",        // view template id
        form: "wpjb-single-alert-param-form",   // form template id
        input: []
    });
    
    this.alert_params.push(param);

    param.view_show();
};


WPJB.alert.partial.prototype.view_show = function() {
    var template = wp.template( this.template.view );
    var input = this.input; 
    
    input._conf = {
        key: this.key,
        detail: this.detail
    };
    
    this.view = jQuery(template(input));
    this.view.find(".wpjb-alert-detail-edit").on("click", jQuery.proxy(this.view_edit, this));
    this.view.find(".wpjb-alert-detail-remove").on("click", jQuery.proxy(this.undo_click, this));
    this.view.find(".wpjb-alert-show-params").on("click", jQuery.proxy(this.toggle_params, this));
    if(this.view.find(".wpjb-manage-actions-more").find(".wpjb-manage-action").length < 1) {
        this.view.find(".wpjb-manage-action-more").hide();
    }
    
    jQuery("#" + this.id).html(this.view);

    this.form = null;
};

WPJB.alert.partial.prototype.view_edit = function(e) {
    e.preventDefault();
    this.form_show();
};

WPJB.alert.partial.prototype.undo_click = function(e) {
    e.preventDefault();
    this.undo_show();
};

WPJB.alert.partial.prototype.undo_show = function() {
    
    var template = wp.template( 'wpjb-alert-remove' );
    var input = this.input; 
    
    input._conf = {
        key: this.key,
        detail: this.detail,
    };
    
    this.view = jQuery(template(input));
    this.view.find(".wpjb-alert-undo").on("click", jQuery.proxy(this.undo, this));

    jQuery("#" + this.id).html(this.view);
    
    this.form = null;
    this.view = null;
};

WPJB.alert.partial.prototype.undo = function(e) {
    e.preventDefault();
    this.view_show();
};

WPJB.alert.partial.prototype.toggle_params = function(e) {
    e.preventDefault();
    var $ = jQuery;
    
    $("#" + this.id).find(".wpjb-alert-params").slideToggle('fast');
};

jQuery(function($) {
    
    // Alert Widget
    $(".wpjb-widget-alert-save").click(function(e) {
        
        e.preventDefault();
        
        var frequency = 1;
        var has_meta = false;
        var meta = {};
        var criteria = {
            keyword: $(".wpjb-widget-alert-keyword").val()
        };
        
        // Collect data from default fields
        $(".wpjb-widget-alert-param").each(function(index, item) {
            var $this = $(item);
            criteria[$this.attr("name")] = $this.val();
        });
        
        // Collect data from meta fields
        $(".wpjb-widget-alert-meta").each(function(index, item) {
            var $this = $(item);
            meta[$this.attr("name")] = $this.val();
            has_meta = true;
        });
        
        // Collect data from multiselect input fields
        $(".wpjb-widget-alert .daq-multiselect-holder").each(function(index, item) {
            var $this = $(item);
            var $input = $this.find(".daq-multiselect-input");
            
            if($input.attr("id") == "type" || $input.attr("id") == "category") {
                criteria[$input.attr("id")] = [];
                $this.find(".daq-multiselect-options input[type=checkbox]:checked").each(function() {
                    criteria[$input.attr("id")].push($(this).val());
                });
            } else {
                meta[$input.attr("id")] = [];
                $this.find(".daq-multiselect-options input[type=checkbox]:checked").each(function() {
                    meta[$input.attr("id")].push($(this).val());
                });
                has_meta = true;
            }
        });
        
        if($(".wpjb-widget-alert-frequency").length > 0) {
            frequency = $(".wpjb-widget-alert-frequency").val();
        }
        
        if(has_meta) {
            criteria.meta = meta;
        }
        
        
        var data = {
            action: "wpjb_main_subscribe",
            email: $(".wpjb-widget-alert-email").val(),
            frequency: frequency,
            criteria: criteria
        };
        
        $(".wpjb-widget-alert-result").hide();
        
	$.post(ajaxurl, data, function(response) {
            

            var span = $(".wpjb-widget-alert-result");
            
            span.text(response.msg);
            span.removeClass("wpjb-flash-info");
            span.removeClass("wpjb-flash-error");
            
            $("#wpjb_widget_alerts li").each(function(index) {
                $(this).css('background-color', 'transparent');
            });
            $(".wpjb-widget-error").remove();
            
            if(response.result == "1") {
                span.addClass("wpjb-flash-info");
            } else {
                span.addClass("wpjb-flash-error"); 
                for (var k in response.errors){
                    $("#"+k).after('<span class="wpjb-widget-error">'+response.errors[k][0]+'</span>');
                    $("#"+k).parent().css('background-color', 'cornsilk');
                    $("#"+k).parent().css('color', '#DE5400');
                }
            }
            
            span.fadeIn("fast");
            
	}, "json");
        
        return false;
    }); 
    
    
    
    $("#wpjb-add-new-alert").click(function(e) {
        e.preventDefault();
        
        var uid = WPJB.alert.uid();
        var partial = new WPJB.alert.partial({
            saved: false,                       // data entered and saved
            id: "wpjb-alert-"+uid,              // unique CSS id for detail
            key: uid,                           // use detail.id is possible
            owner: $(this).data("before"),      // where to insert this item
            view: $(this).data("template"),     // view template id
            form: $(this).data("form"),         // form template id
            input: { }
        });

        partial.form_show();

        WPJB.alert.detail.push(partial);
        
        $(this).data('alertcur', $(this).data('alertcur') + 1 );
        
        if( $(this).data('alertmax') <= $(this).data('alertcur') ) { 
            $(this).hide();
        }
        
    });
    
    $("#wpjb-save-alerts-form").submit( function( )  {
        
        var is_valid = true;
        
        $(".wpjb-form-resume-alerts").each( function(index, item) {
            is_valid = false;
        } );
        
        if( !is_valid ) {
            
            $(".wpjb-flash-alert-error").show();
            return false; 
        }
        
        return true;
    } );
});