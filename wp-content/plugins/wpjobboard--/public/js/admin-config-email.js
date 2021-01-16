var WPJB = WPJB || {};

WPJB.Email = {
    List: []
};

WPJB.Email.Preview = function(args) {
    this.button = args.button;
    this.dialog = args.dialog;
    
    this.type = args.type;
    this.get_content = args.get_content;
    
    this.preloader = this.dialog.find(".wpjb-email-preview-preloader");
    this.iframe = this.dialog.find(".wpjb-email-preview-iframe");
    
    this.button.on("click", jQuery.proxy(this.dialog_open, this));
    this.iframe.on("load", jQuery.proxy(this.iframe_loaded, this));
    
    this.control = {
        desktop: this.dialog.find(".wpjb-email-preview-desktop"),
        mobile: this.dialog.find(".wpjb-email-preview-mobile"),
        download: this.dialog.find(".wpjb-email-preview-download"),
        close: this.dialog.find(".wpjb-email-preview-close")
    };
    
    this.control.desktop.on("click", jQuery.proxy(this.control_desktop, this));
    this.control.mobile.on("click", jQuery.proxy(this.control_mobile, this));
    this.control.download.on("click", jQuery.proxy(this.control_download, this));
    this.control.close.on("click", jQuery.proxy(this.control_close, this));
};

WPJB.Email.Preview.prototype.dialog_open = function(e) {
    e.preventDefault();
    
    this.preloader.show();
    this.iframe.hide();
    
    this.dialog.dialog({
        dialogClass: 'wpjb-preview-dialog',
        resizable: false,
        width: 700,
        modal: true,
        buttons: []
    });
   
    this.iframe.attr("src", ajaxurl + "?action=wpjb_email_preview&type=" + this.type);
};

WPJB.Email.Preview.prototype.iframe_loaded = function() {
    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'wpjb_email_parse',
            template: jQuery("#id").val(),
            data: this.get_content(),
            type: this.type
        },
        dataType: 'json',
        type: 'post',
        success: jQuery.proxy(this.dialog_success, this)
    });
};

WPJB.Email.Preview.prototype.dialog_success = function(response) {
    this.iframe.contents().find("#wpjb-iframe-beacon").html(response.html);
    this.preloader.hide();
    this.iframe.show();
};

WPJB.Email.Preview.prototype.control_desktop = function() {
    this.control.desktop.addClass("active");
    this.control.mobile.removeClass("active");
    this.iframe.css("width", "100%");
};

WPJB.Email.Preview.prototype.control_mobile = function() {
    this.control.desktop.removeClass("active");
    this.control.mobile.addClass("active");
    this.iframe.css("width", "400px");
};

WPJB.Email.Preview.prototype.control_download = function() {
    
    if(jQuery("form#wpjb-form-control-download-submit").length > 0) {
        jQuery("form#wpjb-form-control-download-submit").remove();
    }
    
    var form = jQuery("<form></form>");
    form.attr("id", "wpjb-form-control-download-submit");
    form.css("display", "none");
    form.attr("action", ajaxurl);
    form.attr("method", "post");
    
    form.append('<input type="text" name="type" value="'+this.type+'" />');
    form.append('<input type="text" name="action" value="wpjb_email_download" />');
    form.append('<input type="text" name="template" value="'+jQuery("#id").val()+'" />');
    form.append(jQuery("<input type='text' />").attr("name", "data").attr("value", this.get_content()));
    
    form.appendTo("body").submit();
};

WPJB.Email.Preview.prototype.control_close = function() {
    this.dialog.dialog("close");
};

jQuery(function($) {
    
    new WPJB.Email.Preview({
        button: $("#wpjb-email-html-preview"),
        dialog: $("#wpjb-email-html-preview-dialog"),
        type: 'text/html',
        get_content: function() {
            return tinyMCE.get('mail_body_html').getContent();
        }
    });
    
    new WPJB.Email.Preview({
        button: $(".wpjb-email-text-preview"),
        dialog: $("#wpjb-email-text-preview-dialog"),
        type: 'plain/text',
        get_content: function() {
            return jQuery("#mail_body_text").val();
        }
    });
    
    new WPJB.Email.Preview({
        button: $(".wpjb-email-advanced-preview"),
        dialog: $("#wpjb-email-advanced-preview-dialog"),
        type: 'text/html-advanced',
        get_content: function() {
            return ace.edit("mail_body_html_advanced").getValue();
        }
    });
    
    $(".wpjb-email-html-insert-var").on("click", function(e) {
        e.preventDefault();
        
        $("#wpjb-email-variable-dialog").data("opener", "html");
        $("#wpjb-email-variable-dialog").dialog({
            dialogClass: 'wpjb-variable-dialog',
            resizable: false,
            height: 500,
            width: 800,
            modal: true,
            buttons: []
       });
    });
    
    $(".wpjb-email-advanced-insert-var").on("click", function(e) {
        e.preventDefault();
        
        $("#wpjb-email-variable-dialog").data("opener", "html-advanced");
        $("#wpjb-email-variable-dialog").dialog({
            dialogClass: 'wpjb-variable-dialog',
            resizable: false,
            height: 500,
            width: 800,
            modal: true,
            buttons: []
       });
    });
    
    $(".wpjb-email-text-insert-var").on("click", function(e) {
        e.preventDefault();
        
        $("#wpjb-email-variable-dialog").data("opener", "plain");
        $("#wpjb-email-variable-dialog").dialog({
            dialogClass: 'wpjb-variable-dialog',
            resizable: false,
            height: 500,
            width: 800,
            modal: true,
            buttons: []
       });
    });
    
    $(".wpjb-mail-var-insert").click(function(e) {
        e.preventDefault();
        var tpl = jQuery(this).find("span").text();

        $("#wpjb-email-variable-dialog").dialog("close");

        if($("#wpjb-email-variable-dialog").data("opener") == "plain") {
            var textarea = jQuery('#mail_body_text');
            var pos = textarea.prop("selectionStart");
            var txt = textarea.val();
            var t1 = txt.slice(pos);
            var t2 = txt.slice(0, pos);

            textarea.val(t2+tpl+t1);
            textarea[0].setSelectionRange(tpl.length+pos, tpl.length+pos);
            textarea.focus();
        } else if($("#wpjb-email-variable-dialog").data("opener") == "html-advanced") {
            ace.edit("mail_body_html_advanced").insert(tpl);
        } else {
            var ed = tinyMCE.get('mail_body_html');  
            ed.execCommand('mceInsertContent', false, tpl); 
            ed.focus();
        }

        return false;
    });
    
    $(".wpjb-mail-body-select").change(function(e) {
        if($(this).val() == "text/plain") {
            $("#wp-mail_body_html-wrap").closest("tr").hide();
            $("#mail_body_html_advanced").closest("tr").hide();
        } else if($(this).val() == "text/html") {
            $("#wp-mail_body_html-wrap").closest("tr").show();
            $("#mail_body_html_advanced").closest("tr").hide();
        } else {
            $("#wp-mail_body_html-wrap").closest("tr").hide();
            $("#mail_body_html_advanced").closest("tr").show();
        }
    });
    
    jQuery(".widget-top").click(function() {
        jQuery(this).closest("div.widget").find(".widget-inside").toggle();
        return false;
    });
    
    $(".wpjb-mail-body-select").change();
    
    var editor = ace.edit("mail_body_html_advanced");
    editor.getSession().setMode("ace/mode/html");
    editor.getSession().on("change", function () {
        jQuery("textarea[name='mail_body_html_advanced']").val(editor.getSession().getValue());
    });
});