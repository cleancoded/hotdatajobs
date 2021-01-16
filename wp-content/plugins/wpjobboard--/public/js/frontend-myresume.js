var WPJB = WPJB || {};

WPJB.myresume = {
    uid: function() {
        if (!Date.now) {
            return new Date().getTime();
        } else {
            return Date.now()
        }
    },
    date_my: function(date) {
        var date = new Date(date);
        var month = wpjb_myresume_lang.month_abbr[date.getMonth()+1];
        var year = date.getFullYear();
        return month + " " + year;
    },
    load_partials: function(partials) {
        
        var $ = jQuery;
        
        $.each(partials, function(index, partial) {
            var item = new WPJB.myresume.partial({
                saved: partial.saved,                       // data entered and saved
                id: partial.id,            // unique CSS id for detail
                key: partial.key,                           // use detail.id is possible
                detail: partial.detail,
                owner: partial.owner,      // where to insert this item
                view: partial.view,     // view template id
                form: partial.form,         // form template id
                input: partial.input
            });

            if(partial.errors.length > 0) {
                var errors = {
                    result: -1,
                    form_error: wpjb_myresume_lang.form_error,
                    form_errors: partial.errors
                }
                item.form_show();
                item.form_validate(errors);
            } else if(partial.delete) {
                item.undo_show();
            } else {
                item.view_show();
            }

            WPJB.myresume.detail.push(item);
            
            var form = $("#"+partial.owner).closest("form");
            if(form.data("pre-validate-once") != "1") {
                form.data("pre-validate-once", "1");
                form.on("submit", WPJB.myresume.pre_submit);
            } 
        });
    },
    
    pre_submit: function(e) {
        var $ = jQuery;
        var error = false;
        $.each(WPJB.myresume.detail, function(index, item) {
            if(item.form !== null) {
                error = item.owner;
            }
        });
        
        if(error) {
            e.preventDefault();
            var flash = $("<div></div>").addClass("wpjb-flash-error").text(wpjb_myresume_lang.close_or_save_all);
            $("#"+error).closest("form").find("fieldset:last").before(flash);
        }
    },
    
    detail: []
};

WPJB.myresume.partial = function(data) {
    
    this.saved = data.saved;
    this.busy = false;
    this.id = data.id;
    this.key = data.key;
    this.owner = data.owner;
    this.detail = data.detail;
    this.remove = 0;
    
    this.input = data.input;
    this.template = {
        form: data.form,
        view: data.view
    };
    this.action = {
        form: { },
        view: { }
    };
    
    this.form = null;
    this.view = null;
    
    this.construct();
};

WPJB.myresume.partial.prototype.construct = function() {
    var $ = jQuery;
    $("#"+this.owner).after($("<div></div>").attr("id", this.id));
};

WPJB.myresume.partial.prototype.destroy = function() {
    var $ = jQuery;
    var $this = this;
    
    $.each(WPJB.myresume.detail, function(index, detail) {
        if($this.id == detail.id) {
            WPJB.myresume.detail.splice(index, 1);
            return false;
        }
    });
    
    $("#"+this.id).remove();
};

WPJB.myresume.partial.prototype.data = function() {
    var $ = jQuery;
    var input = {};

    $.each($("#" + this.id + " .wpjb-form-nested .wpjb-form").serializeArray(), function(index, item) {
        if(item.name.indexOf("[]") > -1) {
            input[item.name.replace("[]", "")] = [item.value];
        } else {
            input[item.name] = item.value;
        }
    });

    return input;
};

WPJB.myresume.partial.prototype.form_show = function() {
    var $ = jQuery;
    var template = wp.template( this.template.form );
    var input = this.input; 

    this.form = jQuery(template(input));

    $("#" + this.id).html(this.form);
    
    this.form.hide();
    this.form.fadeIn("fast");

    this.form.find(".wpjb-form-nested-save").on("click", $.proxy(this.form_save, this));
    this.form.find(".wpjb-form-nested-close").on("click", $.proxy(this.form_cancel, this));
    
    var $this = this;
    
    $.each(this.input, function(index, item) {
        $this.form.find("input[type='text'][name='"+index+"']").val(item);
        $this.form.find("input[type='hidden'][name='"+index+"']").val(item);
        $this.form.find("input[type='checkbox'][name='"+index+"[]'][value='"+item+"']").attr("checked", "checked");
        $this.form.find("input[type='radio'][name='"+index+"'][value='"+item+"']").attr("checked", "checked");
        $this.form.find("select[name='"+index+"'] option[value='"+item+"']").attr("selected", "selected");
        $this.form.find("textarea[name='"+index+"']").val(item);
        
    });
    
    $this.form.find("input[type='checkbox'][name='is_current[]']").on("change", jQuery.proxy(this.is_current, this));
    $this.form.find("input[type='checkbox'][name='is_current[]']").change();
    
    // Form Scripts Here
    $("#" + this.id).find(".wpjb-date-picker, .daq-date-picker").DatePicker({
        format:wpjb_myresume_lang.date_format,
        date: "",
        current: "",
        starts: 1,
        position: 'l',
        onBeforeShow: function(param){
            $(this).addClass(param.id);
            var v = $(this).val();
            if(v.length > 0) {
                $(this).DatePickerSetDate(v, true);
            }
        },
        onChange: function(formated, dates){
            if($("#"+this.id+" tbody.datepickerDays").is(":visible")) {
                $("."+this.id).attr("value", formated).DatePickerHide();
            } 
            
            
        }
    });
    
    this.view = null;
};

WPJB.myresume.partial.prototype.is_current = function(e) {
    if(this.form.find("input[type='checkbox'][name='is_current[]']").is(":checked")) {
        this.form.find(".wpjb-element-name-completed_at").hide();
    } else {
        this.form.find(".wpjb-element-name-completed_at").show();
    }
};

WPJB.myresume.partial.prototype.form_save = function(e) {
    e.preventDefault();

    if(this.busy) {
        return;
    }

    var $ = jQuery;
    var data = {
        action: "wpjb_myresume_validate",
        form: this.template.form,
        input: this.data()
    };

    this.busy = true;
    $("#" + this.id + " .wpjb-form-nested-progress").css("visibility", "visible");

    $.ajax(wpjb_myresume_lang.ajaxurl, {
        type: "POST",
        data: data,
        dataType: "json",
        success: $.proxy(this.form_validate, this)
    });

    return false;
};

WPJB.myresume.partial.prototype.form_validate = function(response) {
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

WPJB.myresume.partial.prototype.form_cancel = function(e) {
    e.preventDefault();
    
    if(this.busy) {
        return;
    }
    
    if(this.saved) {
        this.view_show();
    } else {
        this.destroy();
    }
};

WPJB.myresume.partial.prototype.view_show = function() {
    var template = wp.template( this.template.view );
    var input = this.input; 
    
    input._conf = {
        key: this.key,
        detail: this.detail
    };
    
    this.view = jQuery(template(input));
    this.view.hide();
    this.view.find(".wpjb-myresume-detail-edit").on("click", jQuery.proxy(this.view_edit, this));
    this.view.find(".wpjb-myresume-detail-remove").on("click", jQuery.proxy(this.undo_click, this));
    
    jQuery("#" + this.id).html(this.view);
    
    this.view.fadeIn("fast");
    this.form = null;
};

WPJB.myresume.partial.prototype.view_edit = function(e) {
    e.preventDefault();
    this.form_show();
};

WPJB.myresume.partial.prototype.undo_click = function(e) {
    e.preventDefault();
    this.undo_show();
};

WPJB.myresume.partial.prototype.undo_show = function() {
    
    var template = wp.template( 'wpjb-partial-undo' );
    var input = this.input; 
    
    input._conf = {
        key: this.key,
        detail: this.detail,
        remove: this.remove
    };
    
    this.view = jQuery(template(input));
    this.view.find(".wpjb-myresume-detail-undo").on("click", jQuery.proxy(this.undo, this));

    jQuery("#" + this.id).html(this.view);
    
    this.form = null;
    this.view = null;
};

WPJB.myresume.partial.prototype.undo = function(e) {
    e.preventDefault();
    this.view_show();
};

jQuery(function($) {
    $(".wpjb-myresume-detail-add").click(function(e) {
        e.preventDefault();
        
        var type = $(this).data("detail");
        var opened = false;
        
        $.each(WPJB.myresume.detail, function(index, detail) {
            if(detail.saved == false && detail.detail == type) {
                opened = true;
                return false;
            }
        });
        
        if(opened) {
            return;
        }
        
        var uid = WPJB.myresume.uid();
        var partial = new WPJB.myresume.partial({
            saved: false,                       // data entered and saved
            id: "wpjb-partial-"+uid,            // unique CSS id for detail
            key: uid,                           // use detail.id is possible
            detail: $(this).data("detail"),
            owner: $(this).data("before"),      // where to insert this item
            view: $(this).data("template"),     // view template id
            form: $(this).data("form"),         // form template id
            input: { }
        });

        partial.form_show();
        
        WPJB.myresume.detail.push(partial);
        
    });
    
    $(".wpjb-fieldset-null > a.wpjb-button").click(function(e) {
        e.preventDefault();
        $(this).closest("fieldset").find(".wpjb-myresume-detail-add").click();
    });
});

