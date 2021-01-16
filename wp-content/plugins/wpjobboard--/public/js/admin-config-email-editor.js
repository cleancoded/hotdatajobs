var WPJB = WPJB || {};

WPJB.HTMLEmail = {
    List: []
};

WPJB.HTMLEmail.Update = function(e) {
    e.preventDefault();
    
    jQuery.each(WPJB.HTMLEmail.List, function(index, item) {
        if(item.active) {
            item.update();
            return false;
        }
    });
};

WPJB.HTMLEmail.DeactivateAll = function() {
    jQuery.each(this.List, function(index, item) {
        item.input.edit.removeClass("default");
        item.input.restore.hide();
        item.active = false;
    });
};

WPJB.HTMLEmail.Error = function(error) {
    jQuery(".wpjb-email-edit-error").fadeIn("fast");
    jQuery(".wpjb-email-edit-error").html(error);
};

WPJB.HTMLEmail.ClearError = function() {
    jQuery(".wpjb-email-edit-error").fadeOut("fast");
};

WPJB.HTMLEmail.Editor = function(args) {
    this.input = {
        edit: args.edit,
        restore: args.restore
    };
    this.active = false;
    this.input.edit.on("click", jQuery.proxy(this.edit, this));
    this.input.restore.on("click", jQuery.proxy(this.restore, this));
};

WPJB.HTMLEmail.Editor.prototype.spinner = function(show) {
    if(show === true) {
        jQuery(".spinner").css("visibility", "visible");
    } else {
        jQuery(".spinner").css("visibility", "hidden");
    }
};

WPJB.HTMLEmail.Editor.prototype.edit = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }
    
    WPJB.HTMLEmail.ClearError();
    WPJB.HTMLEmail.DeactivateAll();
    
    this.spinner(true);
    
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: "wpjb_email_source",
            edit: this.input.edit.data("file")
        },
        type: "post",
        dataType: "json",
        success: jQuery.proxy(this.edit_success, this),
        error: jQuery.proxy(this.edit_error, this)
    });
};

WPJB.HTMLEmail.Editor.prototype.restore = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }
    
    jQuery("#wpjb-email-dialog-restore").dialog({
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: [
            {
                text: wpjb_admin_config_email_editor.yes,
                click: jQuery.proxy(this.restore_execute, this)
            },
            {
                text: wpjb_admin_config_email_editor.cancel,
                click: function() {
                    jQuery( this ).dialog( "close" );
                }
            }
        ]
    });
};   
 
WPJB.HTMLEmail.Editor.prototype.restore_execute = function() {
    
    jQuery("#wpjb-email-dialog-restore").dialog("close");
    
    WPJB.HTMLEmail.ClearError();

    this.spinner(true);
    
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: "wpjb_email_restore",
            edit: this.input.edit.data("file")
        },
        type: "post",
        dataType: "json",
        success: jQuery.proxy(this.restore_success, this),
        error: jQuery.proxy(this.edit_error, this)
    });
    
};

WPJB.HTMLEmail.Editor.prototype.restore_success = function(response) {
    var editor = ace.edit("wpjb-email-ace");
    editor.getSession().setMode("ace/mode/" + response.type);
    editor.setValue(response.content); 
    editor.gotoLine(1);
    
    this.active = true;
    this.spinner(false);
    this.input.restore.show();
    this.input.edit.addClass("default");
    
    jQuery(".dashicons-yes").fadeIn("fast", function() {
        jQuery(".dashicons-yes").delay(1500).fadeOut("slow");
    });
};

WPJB.HTMLEmail.Editor.prototype.edit_success = function(response) {
    var editor = ace.edit("wpjb-email-ace");
    editor.getSession().setMode("ace/mode/" + response.type);
    editor.setValue(response.content); 
    editor.gotoLine(1);
    
    this.active = true;
    this.spinner(false);
    this.input.restore.show();
    this.input.edit.addClass("default");
};

WPJB.HTMLEmail.Editor.prototype.edit_error = function(response) {
    WPJB.HTMLEmail.Error("<strong>" + response.status + "</strong> " + response.statusText);
    this.spinner(false);
};

WPJB.HTMLEmail.Editor.prototype.update = function(e) {
    if(typeof e !== 'undefined') {
        e.preventDefault();
    }
    
    WPJB.HTMLEmail.ClearError();
    this.spinner(true);
    
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: "wpjb_email_save",
            edit: this.input.edit.data("file"),
            data: ace.edit("wpjb-email-ace").getValue()
        },
        type: "post",
        dataType: "json",
        success: jQuery.proxy(this.update_success, this),
        error: jQuery.proxy(this.update_error, this)
    });
};

WPJB.HTMLEmail.Editor.prototype.update_success = function(response) {
    this.spinner(false);
    jQuery(".dashicons-yes").fadeIn("fast", function() {
        jQuery(".dashicons-yes").delay(1500).fadeOut("slow");
    });
};

WPJB.HTMLEmail.Editor.prototype.update_error = function(response) {
    WPJB.HTMLEmail.Error("<strong>" + response.status + "</strong> " + response.statusText);
    this.spinner(false);
};

jQuery(function($) {
    
    $(".wpjb-email-edit-file").each(function(index, item) {
        WPJB.HTMLEmail.List.push(new WPJB.HTMLEmail.Editor({
            edit: $(item),
            restore: $(item).closest(".setting").find(".wpjb-email-edit-file-restore")
        }));
    });
    
    $("#wpjb-update-template").on("click", WPJB.HTMLEmail.Update);
    
    $(".wpjb-email-edit-file.default").click();
});