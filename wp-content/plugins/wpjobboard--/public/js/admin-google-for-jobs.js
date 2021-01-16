var WPJB = WPJB || {};


WPJB.GFJ = function() {
    this.XHR = null;
    this.Job = [];
    this.JobId = null;
    this.DidOnce = false;

    this.Ace = ace.edit("wpjb-gfj-ace");
    this.Ace.getSession().setMode("ace/mode/html");
    this.Ace.setHighlightActiveLine(false);
    this.Ace.setShowFoldWidgets(false);
    this.Ace.setShowPrintMargin(false);
    this.Ace.setReadOnly(true);
    this.Ace.renderer.setShowGutter(false);

    this.Editor = jQuery(".wpjb-gfj-preview-editor");
    this.Editor.hide();
    
    this.Search = jQuery(".wpjb-gfj-preview-search");
    
    this.Suggest = jQuery("#wpjb-gfj-suggest");
    this.Suggest.on("keyup", jQuery.proxy( this.search, this ));
    
    this.Suggestions = jQuery("#wpjb-gfj-suggestions");
    this.Suggestions.hide();
    
    this.PreviewTitle = jQuery(".wpjb-gfj-preview-title");
    
    this.Change = jQuery(".wpjb-gfj-preview-change");
    this.Change.on("click", jQuery.proxy(this.change_click, this));
    
    this.View = jQuery(".wpjb-gfj-preview-view");
    
    this.Refresh = jQuery(".wpjb-gfj-preview-refresh");
    this.Refresh.on("click", jQuery.proxy(this.refresh_click, this));
    
    this.Validate = jQuery(".wpjb-gfj-preview-validate");
    this.Validate.on("click", jQuery.proxy(this.validate_click, this));
    
    this.Save = jQuery(".wpjb-gfj-preview-save");
    this.Save.on("click", jQuery.proxy(this.saveConfigClick, this));
    
    this.AutoRefresh = jQuery(".wpjb-gfj-preview-auto");
    
    this.Loader = jQuery(".wpjb-gfj-loader");
    this.Form = jQuery(".wpjb-gfj-submit");
    this.FormInput = jQuery(".wpjb-gfj-form-input");
    
    this.GlobalSuccess = jQuery("#wpjb-gfj-global-success");
    this.GlobalSuccessButton = jQuery("#wpjb-gfj-global-success a.wpjb-global-close");
    this.GlobalSuccessButton.on("click", jQuery.proxy(this.globalSuccessClose, this));
    
    this.GlobalError = jQuery("#wpjb-gfj-global-error");
    this.GlobalErrorButton = jQuery("#wpjb-gfj-global-error a.wpjb-global-close");
    this.GlobalErrorButton.on("click", jQuery.proxy(this.globalErrorClose, this));
    
    this.Required = new WPJB.GFJ.MissingHandle(jQuery("#wpjb-gfj-preview-error .wpjb-gfj-job-required"));
    this.Recommended = new WPJB.GFJ.MissingHandle(jQuery("#wpjb-gfj-preview-error .wpjb-gfj-job-recommended"));
    
};

WPJB.GFJ.MissingHandle = function(wrap) {
    this.handle = wrap;
    this.list = wrap.find(".wpjb-gfj-job-block-list");
    
    this.hide();
};

WPJB.GFJ.MissingHandle.prototype.toggle = function(missing) {
    if(missing.length == 0) {
        this.hide();
    } else {
        this.show(missing);
    }
};

WPJB.GFJ.MissingHandle.prototype.show = function(missing) {
    this.list.empty();
    jQuery.each(missing, jQuery.proxy(this.show_each, this));
    this.handle.show();
};

WPJB.GFJ.MissingHandle.prototype.show_each = function(index, item) {
    this.list.append(jQuery("<li></li>").text(item));
}

WPJB.GFJ.MissingHandle.prototype.hide = function() {
    this.list.empty();
    this.handle.hide();
};

WPJB.GFJ.prototype.globalSuccessOpen = function() {
    this.GlobalSuccess.removeClass("wpjb-none");
};

WPJB.GFJ.prototype.globalSuccessClose = function(e) {
    if(typeof e != "undefined") {
        e.preventDefault();
    }
    this.GlobalSuccess.addClass("wpjb-none");
};

WPJB.GFJ.prototype.globalErrorClose = function(e) {
    if(typeof e != "undefined") {
        e.preventDefault();
    }
    this.GlobalError.addClass("wpjb-none");
};

WPJB.GFJ.getLabelFor = function(path) {
    var label = null;
    
    if(path == "inherit") {
        return "Inherit";
    }
    
    jQuery.each(WPJB_GFJ_DATA.FIELD, function(j, item2) {
       if(item2.key == path) {
           label = item2.label;
           return false;
       } 
    });
    return label;
};

WPJB.GFJ.getTextFrom = function(path) {
  if(typeof path.text === "undefined") {
      return "";
  } else {
      return path.text;
  }
};

WPJB.GFJ.getUID = function() {
  return Math.round(new Date().getTime() + (Math.random() * 100));
};

WPJB.GFJ.prototype.saveConfigClick = function(e) {
    e.preventDefault();
    this.saveConfig();
};

WPJB.GFJ.prototype.saveConfig = function() {
    var form = {};
    var types = {};
    
    for(var i in WPJB_GFJ_MAP.Element) {
        var item = WPJB_GFJ_MAP.Element[i];
        
        if(item._render.find(".wpjb-gfj-item-edit").length > 0) {
            WPJB.GFJ.GlobalError("'Save' or 'discard' all mappings before saving.");
            return false;
        }
        
        form[item.getUID()] = item.getFormData(); 
    }
    
    jQuery("select.wpjb-gfj-job-type-map").each(function(index, item) {
        if(jQuery(item).val() != "") {
            types[jQuery(item).val()] = jQuery(item).data("google-type");
        }
    });
    
    this.Loader.show();
    
    var is_disabled = 0;
    if(jQuery("#disable_google_jobs").is(":checked")) {
        is_disabled = 1;
    }
    
    var data = {};
    data.action = "wpjb_main_googlejobssave";
    data.job_id = this.JobId;
    data.jsonld = form;
    data.types = types;
    data.is_disabled = is_disabled;
    data.template = jQuery(".wpjb-gfj-jdt-textarea").val();
    
    this.XHR = jQuery.ajax({
        type: "POST",
        data: data,
        url: ajaxurl,
        dataType: "json",
        success: jQuery.proxy( this.saveConfigSuccess, this ),
        error: WPJB.GFJ.GlobalError
    });
};

WPJB.GFJ.prototype.saveConfigSuccess = function(response) {
    this.Loader.hide();
    if(response.result == "1") {
        this.globalSuccessOpen();
        jQuery("#wpjb-gfj-global-error").addClass("wpjb-none");
    } else {
        WPJB.GFJ.GlobalError(response);
    }
};

WPJB.GFJ.GlobalError = function(response) {
    if(typeof response.statusText == "string" && response.statusText == "abort") {
        return;
    }
    
    jQuery("#wpjb-gfj-global-error").removeClass("wpjb-none");
    jQuery("#wpjb-gfj-global-error .wpjb-gfj-global-error-text").text(response);
    WPJB_GFJ.Loader.hide();
};

WPJB.GFJ.prototype.search = function() {
  
    if(this.XHR) {
        this.XHR.abort();
    }
                
    var data = {};
    data.action = "wpjb_main_googlejobs";
    data.query = this.Suggest.val();
               
    if(WPJB_GFJ_DATA.FORCED_ID > 0) {
        data.job_id = WPJB_GFJ_DATA.FORCED_ID;
        WPJB_GFJ_DATA.FORCED_ID = 0;
    }
    
    //$(".wpjb-job-list").css("opacity", "0.4");
                
    this.XHR = jQuery.ajax({
        type: "POST",
        data: data,
        url: ajaxurl,
        dataType: "json",
        success: jQuery.proxy( this.search_success, this ),
        error: WPJB.GFJ.GlobalError
    });
                
};

WPJB.GFJ.prototype.change_click = function(e) {
    e.preventDefault();
    
    this.Editor.hide();
    this.Search.show();
};

WPJB.GFJ.prototype.refresh_click = function(e) {
    e.preventDefault();
    this.refresh();
};

WPJB.GFJ.prototype.auto_refresh = function() {
    if(this.AutoRefresh.is(":checked")) {
        this.refresh();
    }
};

WPJB.GFJ.prototype.refresh = function() {
    if(this.XHR) {
        this.XHR.abort();
    }
    
    this.Loader.show();
    jQuery("#wpjb-gfj-ace").css("opacity", "0.5");
    
    var form = {};
    var types = {};
    
    jQuery.each(WPJB_GFJ_MAP.Element, function(index, item) {
       form[item.getUID()] = item.getFormData(); 
    });
    
    jQuery("select.wpjb-gfj-job-type-map").each(function(index, item) {
        if(jQuery(item).val() != "") {
            types[jQuery(item).val()] = jQuery(item).data("google-type");
        }
    });
    
    var data = {};
    data.action = "wpjb_main_googlejobsid";
    data.job_id = this.JobId;
    data.jsonld = form;
    data.types = types;
    data.template = jQuery(".wpjb-gfj-jdt-textarea").val();
           
    this.XHR = jQuery.ajax({
        type: "POST",
        data: data,
        url: ajaxurl,
        dataType: "json",
        success: jQuery.proxy( this.refresh_success, this )
    });
};

WPJB.GFJ.prototype.refresh_success = function(response) {
    if(response.job) {
        this.Editor.show();
        this.Search.hide();
        
        this.Ace.setValue(response.job.jsonld); 
        this.Ace.gotoLine(1);
        
        this.JobId = response.job.id;
        this.PreviewTitle.text(response.job.job_title);
        
        if(typeof response.job.validate.missing.required == "object") {
            this.Required.toggle(response.job.validate.missing.required);
        }
        if(typeof response.job.validate.missing.recommended == "object") {
            this.Recommended.toggle(response.job.validate.missing.recommended);
        }
    }
    
    this.Loader.hide();
    jQuery("#wpjb-gfj-ace").css("opacity", "1");
};

WPJB.GFJ.prototype.refresh_error = function(response) {
    this.Loader.hide();
    jQuery("#wpjb-gfj-ace").css("opacity", "1");
};

WPJB.GFJ.prototype.search_success = function(response) {
    
    this.Job = [];
    this.Suggestions.show();
    this.Suggestions.empty();
    jQuery.each(response.job, jQuery.proxy( this.search_success_each, this ));
    
    if(this.DidOnce === false) {
        this.Job[0].select.click();
        this.DidOnce = true;
    }
    
};

WPJB.GFJ.prototype.search_success_each = function(index, item) {

    var template = wp.template( "wpjb-gfj" );
    var tr = jQuery(template(item));
    var job = new WPJB.GFJ.Job(tr, item, this);
    

    this.Suggestions.append(tr);
    this.Job.push(job);
    
};

WPJB.GFJ.prototype.validate_click = function(e) {
    e.preventDefault();
    
    this.FormInput.val(this.Ace.getValue());
    this.Form.submit();
    
};

WPJB.GFJ.Types = function() {
    this.Intro = jQuery(".wpjb-form-group-types-map-intro");
    
    this.Editor = jQuery(".wpjb-form-group-map-types");
    this.Editor.find(".wpjb-gfj-types-close").on("click", jQuery.proxy(this.hide_clicked, this))
    this.Editor.hide();
    
    this.Intro.find(".wpjb-gfj-toggle-types").on("click", jQuery.proxy(this.show_clicked, this));
    
    this.Counter = this.Intro.find(".wpjb-gfj-toggle-set");
    
    this.calculate();
};

WPJB.GFJ.Types.prototype.calculate = function() {
    var count = 0;
    
    jQuery("select.wpjb-gfj-job-type-map").each(function(index, item) {
        if(jQuery(item).val() != "") {
            count++;
        }
    });
    
    this.Counter.text(count.toString());
};

WPJB.GFJ.Types.prototype.hide_clicked = function(e) {
    e.preventDefault();
    
    this.Intro.show();
    this.Editor.hide();
    this.calculate();
};

WPJB.GFJ.Types.prototype.show_clicked = function(e) {
    e.preventDefault();
    
    this.Intro.hide();
    this.Editor.show();
    this.calculate();
};

WPJB.GFJ.DescriptionTemplate = function() {
    this.Intro = jQuery(".wpjb-gfj-jdt-intro");
    this.Edit = jQuery(".wpjb-gfj-jdt-edit");
    
    this.Textarea = jQuery(".wpjb-gfj-jdt-textarea");
    this.ButtonEdit = jQuery(".wpjb-gfj-jdt-button-edit");
    this.ButtonOk = jQuery(".wpjb-gfj-jdt-button-ok");
    
    this.ButtonEdit.on("click", jQuery.proxy(this.ButtonEditClick, this));
    this.ButtonOk.on("click", jQuery.proxy(this.ButtonOkClick, this));
};

WPJB.GFJ.DescriptionTemplate.prototype.ButtonEditClick = function(e) {
    e.preventDefault();
    this.Intro.hide();
    this.Edit.show();
};

WPJB.GFJ.DescriptionTemplate.prototype.ButtonOkClick = function(e) {
    e.preventDefault();
    this.Intro.show();
    this.Edit.hide();
    
    WPJB_GFJ.refresh();
};

WPJB.GFJ.Job = function(item, data, gfj) {
    this._gfj = gfj;
    this.XHR = null;
    
    this.Ace = null;
    this.tr = item;
    this.Data = data;
    
    this.select = item.find(".wpjb-gfj-item-select");
    
    this.select.on("click", jQuery.proxy( this.select_click, this ));
};

WPJB.GFJ.Job.prototype.select_click = function(e) {
    e.preventDefault();
    
    if(this.XHR) {
        this.XHR.abort();
    }
    
    var form = {};
    var types = {};
    
    jQuery.each(WPJB_GFJ_MAP.Element, function(index, item) {
       form[item.getUID()] = item.getFormData(); 
    });
    
    jQuery("select.wpjb-gfj-job-type-map").each(function(index, item) {
        if(jQuery(item).val() != "") {
            types[jQuery(item).val()] = jQuery(item).data("google-type");
        }
    });
    
    var data = {};
    data.action = "wpjb_main_googlejobsid";
    data.job_id = this.Data.id;
    data.jsonld = form;
    data.types = types;
    data.template = jQuery(".wpjb-gfj-jdt-textarea").val();
           
    this.XHR = jQuery.ajax({
        type: "POST",
        data: data,
        url: ajaxurl,
        dataType: "json",
        success: jQuery.proxy( this.select_click_success, this )
    });
    

};

WPJB.GFJ.Job.prototype.select_click_success = function(response) {
    if(response.job) {
        this._gfj.Editor.show();
        this._gfj.Search.hide();
        
        this._gfj.Ace.setValue(response.job.jsonld); 
        this._gfj.Ace.gotoLine(1);
        this._gfj.Ace.blur();
        
        this._gfj.View.attr("href", response.job.admin_url);
        
        this._gfj.JobId = response.job.id;
        this._gfj.PreviewTitle.text(response.job.job_title);
        
        if(typeof response.job.validate.missing.required == "object") {
            this._gfj.Required.toggle(response.job.validate.missing.required);
        }
        if(typeof response.job.validate.missing.recommended == "object") {
            this._gfj.Recommended.toggle(response.job.validate.missing.recommended);
        }
    }
};

WPJB.GFJ.Map = function(selector) {
    this.Element = [];
    
    this.selector = selector;
    this.area = jQuery(".wpjb-gfj-map-area");
    
    this.selector.on("change", jQuery.proxy(this.selector_change, this));
    
    this.area.sortable();
    this.area.disableSelection();
};

WPJB.GFJ.Map.prototype.removeElement = function(element) {
    
    this.Element = jQuery.grep(this.Element, function(value) {
      return value != element;
    });
    
};

WPJB.GFJ.Map.prototype.selector_change = function() {
    
    var type = this.selector.find(":selected").data("type");
    var item = new WPJB.GFJ.Map.Item[type]({
        data: {
            name: this.selector.find("option:selected").val(),
            label: this.selector.find("option:selected").text()
        }
    });
    
    this.Element.push(item);
    this.area.append(item.edit(true));
    this.selector.val("");
};

WPJB.GFJ.Map.Item = function(item) {
    this._render = null;
    this._uid = WPJB.GFJ.getUID();
    
    this.type = item.type;
    this.template = "wpjb-gfj-map-" + item.type;
    
    this.data = item.data;
    this.value = item.value;
    
};

WPJB.GFJ.Map.Item.prototype.getUID = function() {
    return this._uid;
};

WPJB.GFJ.Map.Item.prototype.getFormData = function() {
    return null;
};

WPJB.GFJ.Map.Item.prototype.render = function(render) {
    if(this._render === null) {
        this._render = render;
    } else {
        this._render.replaceWith(render);
        this._render = render;
    }
    
    return this._render;
};

WPJB.GFJ.Map.Item.prototype.view = function() {
    
    var template = wp.template(this.template);
    var tpl = jQuery(template({
        _display: "view",
        _uid: this._uid,
        _dashicon: this._dashicon,
        name: this.data.name,
        label: this.data.label,
        value: this.value,
        description: ""
    }));
    
    tpl.find("a.wpjb-gfj-item-action-edit").on("click", jQuery.proxy(this.edit_clicked, this));
    tpl.find("a.wpjb-gfj-item-action-delete").on("click", jQuery.proxy(this.delete_clicked, this));
    
    return this.render(tpl);
};

WPJB.GFJ.Map.Item.prototype.edit = function(isNew) {
    
    if(typeof isNew === 'undefined') {
        isNew = false;
    }
    
    var template = wp.template(this.template);
    var tpl = jQuery(template({
        _display: "edit",
        _dashicon: this._dashicon,
        isNew: isNew,
        name: this.data.name,
        label: this.data.label,
        value: this.value,
        description: ""
    }));
    
    tpl.find("a.wpjb-gfj-item-action-cancel").on("click", jQuery.proxy(this.cancel_clicked, this));
    tpl.find("a.wpjb-gfj-item-action-save").on("click", jQuery.proxy(this.save_clicked, this));
    tpl.find("a.wpjb-gfj-item-action-discard").on("click", jQuery.proxy(this.discard_clicked, this));
    tpl.find("a.wpjb-gfj-item-action-delete").on("click", jQuery.proxy(WPJB.GFJ.Map.removeElement, WPJB.GFJ.Map, this));
    
    tpl.find(".wpjb-gfj-item-edit-input").each(function(index, item) {
        new WPJB.GFJ.Map.Item.Input(jQuery(item));
    });
    
    this.render(tpl)
    
    return this._render;
};

WPJB.GFJ.Map.Item.Input = function(input) {
    this.dropdown = input.find(".wpjb-gfj-internal-field-map");
    this.text = input.find(".wpjb-gfj-internal-text-value");
    
    this.dropdown.on("change", jQuery.proxy(this.Change, this));
    this.dropdown.change();
};

WPJB.GFJ.Map.Item.Input.prototype.Change = function() {
    if(this.dropdown.val() == "text") {
        this.text.slideDown("fast");
    } else {
        this.text.hide();
    }
};

WPJB.GFJ.Map.Item.prototype.error = function(e) {
    this._render.find(".wpjb-gfj-error").removeClass("wpjb-none").text(e.message);
};

WPJB.GFJ.Map.Item.prototype.delete = function() {
    
};

WPJB.GFJ.Map.Item.prototype.save_clicked = function(e) {
    e.preventDefault();
    try {
        this.value = this.save(this._render);
        this.view();
        WPJB_GFJ.auto_refresh();
    } catch(e) {
        this.error(e);
    }
};

WPJB.GFJ.Map.Item.prototype.cancel_clicked = function(e) {
    e.preventDefault();
    this.view();
    WPJB_GFJ.auto_refresh();
};

WPJB.GFJ.Map.Item.prototype.delete_clicked = function(e) {
    e.preventDefault();
    WPJB_GFJ_MAP.removeElement(this);
    WPJB_GFJ.auto_refresh();
    this.render("");
};

WPJB.GFJ.Map.Item.prototype.discard_clicked = function(e) {
    e.preventDefault();
    WPJB_GFJ_MAP.removeElement(this);
    this.render("");
};

WPJB.GFJ.Map.Item.prototype.edit_clicked = function(e) {
    e.preventDefault();
    
    this.edit();
};

/**
 * Represents Text Node in JobPosting scheme
 * 
 * @constructor
 * @param {type} item
 * @returns {WPJB.GFJ.Map.Item.Text}
 */
WPJB.GFJ.Map.Item.Text = function(item) {
    this._render = null;
    this._uid = WPJB.GFJ.getUID();
    this._dashicon = "dashicons-text";
    
    this.type = "Text";
    this.template = "wpjb-gfj-map-Text";
    
    this.data = item.data;
    this.value = {
        label: "",
        name: "",
        text: ""
    }; 
    
    if(typeof item.value != "undefined") {
        this.value = item.value;
    }
};

// Inherit from WPJB.GFJ.Map.Item
WPJB.GFJ.Map.Item.Text.prototype = Object.create(WPJB.GFJ.Map.Item.prototype);

// Correct the constructor pointer because it points to WPJB.GFJ.Map.Item
WPJB.GFJ.Map.Item.Text.prototype.constructor = WPJB.GFJ.Map.Item.Text;

/**
 * Returns Form Data
 * 
 * This function will be used to get data from the object befire. The data will
 * be used to either render preview or saved in the database
 * 
 * @returns {object}
 */
WPJB.GFJ.Map.Item.Text.prototype.getFormData = function() {
    var data = {
        order: this._render.index(),
        key: this.data.name,
        path: this.value.name
    };
    
    if(data.path == "text") {
        data.text = this.value.text;
    }
    
    return data;
};

/**
 * Saves Data
 * 
 * This function is being run when "Update" or "Add" button is clicked.
 * 
 * @param {type} item
 * @returns {object}
 */
WPJB.GFJ.Map.Item.Text.prototype.save = function(item) {
    if(item.find("option:selected").val().length === 0) {
        throw new Error("Select a field to map!");
    }

    var data = {
        label: item.find("option:selected").text(),
        name: item.find("option:selected").val(),
        text: ""
    };
    
    if(data.name == "text") {
        data.text = this._render.find(".wpjb-gfj-internal-text-value").val();;
    }
    
    return data;
};

/**
 * Represents Date Node in JobPosting scheme
 * 
 * @constructor
 * @param {type} item
 * @returns {WPJB.GFJ.Map.Item.Text}
 */
WPJB.GFJ.Map.Item.Date = function(item) {
    this._render = null;
    this._uid = WPJB.GFJ.getUID();
    this._dashicon = "dashicons-clock";
    
    this.type = "Date";
    this.template = "wpjb-gfj-map-Text";
    
    this.data = item.data;
    this.value = {
        label: "",
        name: "",
        text: ""
    }; 
    
    if(typeof item.value != "undefined") {
        this.value = item.value;
    }
};

// Inherit from WPJB.GFJ.Map.Item.Text
WPJB.GFJ.Map.Item.Date.prototype = Object.create(WPJB.GFJ.Map.Item.Text.prototype);

// Correct the constructor pointer because it points to WPJB.GFJ.Map.Item
WPJB.GFJ.Map.Item.Date.prototype.constructor = WPJB.GFJ.Map.Item.Date;

/**
 * Represents Url Node in JobPosting scheme
 * 
 * @constructor
 * @param {type} item
 * @returns {WPJB.GFJ.Map.Item.Url}
 */
WPJB.GFJ.Map.Item.Url = function(item) {
    this._render = null;
    this._uid = WPJB.GFJ.getUID();
    this._dashicon = "dashicons-admin-links";
    
    this.type = "Url";
    this.template = "wpjb-gfj-map-Text";
    
    this.data = item.data;
    this.value = {
        label: "",
        name: "",
        text: ""
    }; 
    
    if(typeof item.value != "undefined") {
        this.value = item.value;
    }
};

// Inherit from WPJB.GFJ.Map.Item.Text
WPJB.GFJ.Map.Item.Url.prototype = Object.create(WPJB.GFJ.Map.Item.Text.prototype);

// Correct the constructor pointer because it points to WPJB.GFJ.Map.Item
WPJB.GFJ.Map.Item.Url.prototype.constructor = WPJB.GFJ.Map.Item.Url;

/**
 * Represents PropertyValue Node in JobPosting scheme
 * 
 * @constructor
 * @param {type} item
 * @returns {WPJB.GFJ.Map.Item.Url}
 */
WPJB.GFJ.Map.Item.Identifier = function(item) {
    this._render = null;
    this._uid = WPJB.GFJ.getUID();
    this._dashicon = "dashicons-admin-network";
    
    this.type = "Identifier";
    this.template = "wpjb-gfj-map-Identifier";
    
    this.data = item.data;
    this.value = {
        name: {
            label: "",
            name: "",
            text: ""
        },
        value: {
            label: "",
            name: "",
            text: ""
        }
    }; 
    
    if(typeof item.value != "undefined") {
        this.value = item.value;
    }
};

// Inherit from WPJB.GFJ.Map.Item
WPJB.GFJ.Map.Item.Identifier.prototype = Object.create(WPJB.GFJ.Map.Item.prototype);

// Correct the constructor pointer because it points to WPJB.GFJ.Map.Item
WPJB.GFJ.Map.Item.Identifier.prototype.constructor = WPJB.GFJ.Map.Item.Identifier;

/**
 * Returns Form Data
 * 
 * This function will be used to get data from the object befire. The data will
 * be used to either render preview or saved in the database
 * 
 * @returns {object}
 */
WPJB.GFJ.Map.Item.Identifier.prototype.getFormData = function() {
    
    var data = {
        order: this._render.index(),
        key: this.data.name,
        path: {
            name: {
                value: this.value.name.name
            },
            value: {
                value: this.value.value.name
            }
        }
    };
    
    if(data.path.name.value == "text") {
        data.path.name.text = this.value.name.text;
    }
    
    if(data.path.value.value == "text") {
        data.path.value.text = this.value.value.text;
    }
    
    return data;
};

/**
 * Saves Data
 * 
 * This function is being run when "Update" or "Add" button is clicked.
 * 
 * @param {type} item
 * @returns {object}
 */
WPJB.GFJ.Map.Item.Identifier.prototype.save = function(item) {
    if(item.find("option:selected").val().length === 0) {
        throw new Error("Select a field to map!");
    }

    var name = item.find(".wpjb-gfj-item-edit-input[data-name='name']");
    var value = item.find(".wpjb-gfj-item-edit-input[data-name='value']");

    var data = {
        name: {
            label: name.find("option:selected").text(),
            name: name.find("option:selected").val()
        },
        value: {
            label: value.find("option:selected").text(),
            name: value.find("option:selected").val()
        }
    };
    
    if(data.name.name == "text") {
        data.name.text = name.find(".wpjb-gfj-internal-text-value").val();
    }
    
    if(data.value.name == "text") {
        data.value.text = value.find(".wpjb-gfj-internal-text-value").val();
    }

    return data;
};

/**
 * Represents PropertyValue Node in JobPosting scheme
 * 
 * @constructor
 * @param {type} item
 * @returns {WPJB.GFJ.Map.Item.Organization}
 */
WPJB.GFJ.Map.Item.Organization = function(item) {
    this._render = null;
    this._uid = WPJB.GFJ.getUID();
    this._dashicon = "dashicons-groups";
    
    this.type = "Organization";
    this.template = "wpjb-gfj-map-Organization";
    
    this.data = item.data;
    this.value = {
        name: {
            label: "",
            name: "",
            text: ""
        },
        sameAs: {
            label: "",
            name: "",
            text: ""
        },
        logo: {
            label: "",
            name: "",
            text: ""
        }
    }; 
    
    if(typeof item.value != "undefined") {
        this.value = item.value;
    }
};

// Inherit from WPJB.GFJ.Map.Item
WPJB.GFJ.Map.Item.Organization.prototype = Object.create(WPJB.GFJ.Map.Item.prototype);

// Correct the constructor pointer because it points to WPJB.GFJ.Map.Item
WPJB.GFJ.Map.Item.Organization.prototype.constructor = WPJB.GFJ.Map.Item.Organization;

/**
 * Returns Form Data
 * 
 * This function will be used to get data from the object befire. The data will
 * be used to either render preview or saved in the database
 * 
 * @returns {object}
 */
WPJB.GFJ.Map.Item.Organization.prototype.getFormData = function() {
    
    var data = {
        order: this._render.index(),
        key: this.data.name,
        path: {
            name: {
                value: this.value.name.name
            },
            sameAs: {
                value: this.value.sameAs.name
            },
            logo: {
                value: this.value.logo.name
            }
        }
    };
    
    if(data.path.name.value == "text") {
        data.path.name.text = this.value.name.text;
    }
    
    if(data.path.sameAs.value == "text") {
        data.path.sameAs.text = this.value.sameAs.text;
    }
    
    if(data.path.logo.value == "text") {
        data.path.logo.text = this.value.logo.text;
    }
    
    return data;
};

/**
 * Saves Data
 * 
 * This function is being run when "Update" or "Add" button is clicked.
 * 
 * @param {type} item
 * @returns {object}
 */
WPJB.GFJ.Map.Item.Organization.prototype.save = function(item) {
    if(item.find("option:selected").val().length === 0) {
        throw new Error("Select a field to map!");
    }

    var name = item.find(".wpjb-gfj-item-edit-input[data-name='name']");
    var sameAs = item.find(".wpjb-gfj-item-edit-input[data-name='sameAs']");
    var logo = item.find(".wpjb-gfj-item-edit-input[data-name='logo']");

    var data = {
        name: {
            label: name.find("option:selected").text(),
            name: name.find("option:selected").val()
        },
        sameAs: {
            label: sameAs.find("option:selected").text(),
            name: sameAs.find("option:selected").val()
        },
        logo: {
            label: logo.find("option:selected").text(),
            name: logo.find("option:selected").val()
        }
    };
    
    if(data.name.name == "text") {
        data.name.text = name.find(".wpjb-gfj-internal-text-value").val();
    }
    
    if(data.sameAs.name == "text") {
        data.sameAs.text = sameAs.find(".wpjb-gfj-internal-text-value").val();
    }
    
    if(data.logo.name == "text") {
        data.logo.text = logo.find(".wpjb-gfj-internal-text-value").val();
    }

    return data;
};

/**
 * Represents PropertyValue Node in JobPosting scheme
 * 
 * @constructor
 * @param {type} item
 * @returns {WPJB.GFJ.Map.Item.Organization}
 */
WPJB.GFJ.Map.Item.Place = function(item) {
    this._render = null;
    this._uid = WPJB.GFJ.getUID();
    this._dashicon = "dashicons-location-alt";
    
    this.type = "Place";
    this.template = "wpjb-gfj-map-Place";
    
    this.data = item.data;
    this.value = {
        streetAddress: {
            label: "",
            name: "",
            text: ""
        },
        addressLocality: {
            label: "",
            name: "",
            text: ""
        },
        addressRegion: {
            label: "",
            name: "",
            text: ""
        },
        postalCode: {
            label: "",
            name: "",
            text: ""
        },
        addressCountry: {
            label: "",
            name: "",
            text: ""
        }
    }; 
    
    if(typeof item.value != "undefined") {
        this.value = item.value;
    }
};

// Inherit from WPJB.GFJ.Map.Item
WPJB.GFJ.Map.Item.Place.prototype = Object.create(WPJB.GFJ.Map.Item.prototype);

// Correct the constructor pointer because it points to WPJB.GFJ.Map.Item
WPJB.GFJ.Map.Item.Place.prototype.constructor = WPJB.GFJ.Map.Item.Place;

/**
 * Returns Form Data
 * 
 * This function will be used to get data from the object befire. The data will
 * be used to either render preview or saved in the database
 * 
 * @returns {object}
 */
WPJB.GFJ.Map.Item.Place.prototype.getFormData = function() {
    
    var data = {
        order: this._render.index(),
        key: this.data.name,
        path: {
            streetAddress: {
                value: this.value.streetAddress.name
            },
            addressLocality: {
                value: this.value.addressLocality.name
            },
            addressRegion: {
                value: this.value.addressRegion.name
            },
            postalCode: {
                value: this.value.postalCode.name
            },
            addressCountry: {
                value: this.value.addressCountry.name
            }
        }
    };
    
    if(data.path.streetAddress.value == "text") {
        data.path.streetAddress.text = this.value.streetAddress.text;
    }
    
    if(data.path.addressLocality.value == "text") {
        data.path.addressLocality.text = this.value.addressLocality.text;
    }
    
    if(data.path.addressRegion.value == "text") {
        data.path.addressRegion.text = this.value.addressRegion.text;
    }
    
    if(data.path.postalCode.value == "text") {
        data.path.postalCode.text = this.value.postalCode.text;
    }
    
    if(data.path.addressCountry.value == "text") {
        data.path.addressCountry.text = this.value.addressCountry.text;
    }
    
    return data;
};

/**
 * Saves Data
 * 
 * This function is being run when "Update" or "Add" button is clicked.
 * 
 * @param {type} item
 * @returns {object}
 */
WPJB.GFJ.Map.Item.Place.prototype.save = function(item) {

    var streetAddress = item.find(".wpjb-gfj-item-edit-input[data-name='streetAddress']");
    var addressLocality = item.find(".wpjb-gfj-item-edit-input[data-name='addressLocality']");
    var addressRegion = item.find(".wpjb-gfj-item-edit-input[data-name='addressRegion']");
    var postalCode = item.find(".wpjb-gfj-item-edit-input[data-name='postalCode']");
    var addressCountry = item.find(".wpjb-gfj-item-edit-input[data-name='addressCountry']");

    var data = {
        streetAddress: {
            label: streetAddress.find("option:selected").text(),
            name: streetAddress.find("option:selected").val()
        },
        addressLocality: {
            label: addressLocality.find("option:selected").text(),
            name: addressLocality.find("option:selected").val()
        },
        addressRegion: {
            label: addressRegion.find("option:selected").text(),
            name: addressRegion.find("option:selected").val()
        },
        postalCode: {
            label: postalCode.find("option:selected").text(),
            name: postalCode.find("option:selected").val()
        },
        addressCountry: {
            label: addressCountry.find("option:selected").text(),
            name: addressCountry.find("option:selected").val()
        },
    };
    
    if(data.streetAddress.name == "text") {
        data.streetAddress.text = streetAddress.find(".wpjb-gfj-internal-text-value").val();
    }
    
    if(data.addressLocality.name == "text") {
        data.addressLocality.text = addressLocality.find(".wpjb-gfj-internal-text-value").val();
    }
    
    if(data.addressRegion.name == "text") {
        data.addressRegion.text = addressRegion.find(".wpjb-gfj-internal-text-value").val();
    }
    
    if(data.postalCode.name == "text") {
        data.postalCode.text = postalCode.find(".wpjb-gfj-internal-text-value").val();
    }
    
    if(data.addressCountry.name == "text") {
        data.addressCountry.text = addressCountry.find(".wpjb-gfj-internal-text-value").val();
    }

    return data;
};

/**
 * Represents MonetaryAmount Node in JobPosting scheme
 * 
 * @constructor
 * @param {type} item
 * @returns {WPJB.GFJ.Map.Item.Salary}
 */
WPJB.GFJ.Map.Item.MonetaryAmount = function(item) {
    this._render = null;
    this._uid = WPJB.GFJ.getUID();
    this._dashicon = "dashicons-cart";
    
    this.type = "Place";
    this.template = "wpjb-gfj-map-MonetaryAmount";
    
    this.data = item.data;
    this.value = {
        value: {
            label: "",
            name: "",
            text: ""
        },
        currency: {
            label: "",
            name: "",
            text: ""
        }
    }; 
    
    if(typeof item.value != "undefined") {
        this.value = item.value;
    }
};

// Inherit from WPJB.GFJ.Map.Item
WPJB.GFJ.Map.Item.MonetaryAmount.prototype = Object.create(WPJB.GFJ.Map.Item.prototype);

// Correct the constructor pointer because it points to WPJB.GFJ.Map.Item
WPJB.GFJ.Map.Item.MonetaryAmount.prototype.constructor = WPJB.GFJ.Map.Item.MonetaryAmount;

/**
 * Returns Form Data
 * 
 * This function will be used to get data from the object befire. The data will
 * be used to either render preview or saved in the database
 * 
 * @returns {object}
 */
WPJB.GFJ.Map.Item.MonetaryAmount.prototype.getFormData = function() {
    
    var data = {
        order: this._render.index(),
        key: this.data.name,
        path: {
            value: {
                value: this.value.value.name
            },
            currency: {
                value: this.value.currency.name
            }
        }
    };
    
    if(data.path.value.value == "text") {
        data.path.value.text = this.value.value.text;
    }
    
    if(data.path.currency.value == "text") {
        data.path.currency.text = this.value.currency.text;
    }
    
    return data;
};

/**
 * Saves Data
 * 
 * This function is being run when "Update" or "Add" button is clicked.
 * 
 * @param {type} item
 * @returns {object}
 */
WPJB.GFJ.Map.Item.MonetaryAmount.prototype.save = function(item) {

    var value = item.find(".wpjb-gfj-item-edit-input[data-name='value']");
    var currency = item.find(".wpjb-gfj-item-edit-input[data-name='currency']");

    var data = {
        value: {
            label: value.find("option:selected").text(),
            name: value.find("option:selected").val()
        },
        currency: {
            label: currency.find("option:selected").text(),
            name: currency.find("option:selected").val()
        }
    };
    
    if(data.value.name == "text") {
        data.value.text = value.find(".wpjb-gfj-internal-text-value").val();
    }
    
    if(data.currency.name == "text") {
        data.currency.text = currency.find(".wpjb-gfj-internal-text-value").val();
    }

    return data;
};

// Run ...

var WPJB_GFJ = null;
var WPJB_GFJ_MAP = null;
var WPJB_GFJ_DATA = WPJB_GFJ_DATA || [];

jQuery(function($) {
    
    new WPJB.GFJ.Types();
    new WPJB.GFJ.DescriptionTemplate();
    
    WPJB_GFJ = new WPJB.GFJ();
    WPJB_GFJ_MAP = new WPJB.GFJ.Map($("#google_field_name"));

    $.each(WPJB_GFJ_DATA.MAP, function(index, item) {
        
        var struct = WPJB_GFJ_MAP.selector.find("option[value='"+item.key+"']")
        var type = struct.data("type");
        var data = {
            data: {
                name: item.key,
                label: struct.text(),
            },
            value: null
        };
        
        if(jQuery.inArray(type, ["Text", "Date", "Url"]) >= 0) {
            data.value = {
                name: item.path,
                label: WPJB.GFJ.getLabelFor(item.path),
                text: WPJB.GFJ.getTextFrom(item)
            };
        } else if(struct.data("type") == "MonetaryAmount") {
            data.value = {
                value: {
                    name: item.path.value.value,
                    label: WPJB.GFJ.getLabelFor(item.path.value.value),
                    text: WPJB.GFJ.getTextFrom(item.path.value)
                },
                currency: {
                    name: item.path.currency.value,
                    label: WPJB.GFJ.getLabelFor(item.path.currency.value),
                    text: WPJB.GFJ.getTextFrom(item.path.currency)
                }
                
            };
        } else if(struct.data("type") == "Identifier") {
            data.value = {
                name: {
                    name: item.path.name.value,
                    label: WPJB.GFJ.getLabelFor(item.path.name.value),
                    text: WPJB.GFJ.getTextFrom(item.path.name)
                },
                value: {
                    name: item.path.value.value,
                    label: WPJB.GFJ.getLabelFor(item.path.value.value),
                    text: WPJB.GFJ.getTextFrom(item.path.name)
                }
            };
        } else if(struct.data("type") == "Organization") {
            data.value = {
                name: {
                    name: item.path.name.value,
                    label: WPJB.GFJ.getLabelFor(item.path.name.value),
                    text: WPJB.GFJ.getTextFrom(item.path.name)
                },
                sameAs: {
                    name: item.path.sameAs.value,
                    label: WPJB.GFJ.getLabelFor(item.path.sameAs.value),
                    text: WPJB.GFJ.getTextFrom(item.path.sameAs)
                },
                logo: {
                    name: item.path.logo.value,
                    label: WPJB.GFJ.getLabelFor(item.path.logo.value),
                    text: WPJB.GFJ.getTextFrom(item.path.logo)
                }
            };
        } else if(struct.data("type") == "Place") {
            data.value = {
                streetAddress: {
                    name: item.path.streetAddress.value,
                    label: WPJB.GFJ.getLabelFor(item.path.streetAddress.value),
                    text: WPJB.GFJ.getTextFrom(item.path.streetAddress)
                },
                addressLocality: {
                    name: item.path.addressLocality.value,
                    label: WPJB.GFJ.getLabelFor(item.path.addressLocality.value),
                    text: WPJB.GFJ.getTextFrom(item.path.addressLocality)
                },
                addressRegion: {
                    name: item.path.addressRegion.value,
                    label: WPJB.GFJ.getLabelFor(item.path.addressRegion.value),
                    text: WPJB.GFJ.getTextFrom(item.path.addressRegion)
                },
                postalCode: {
                    name: item.path.postalCode.value,
                    label: WPJB.GFJ.getLabelFor(item.path.postalCode.value),
                    text: WPJB.GFJ.getTextFrom(item.path.postalCode)
                },
                addressCountry: {
                    name: item.path.addressCountry.value,
                    label: WPJB.GFJ.getLabelFor(item.path.addressCountry.value),
                    text: WPJB.GFJ.getTextFrom(item.path.addressCountry)
                }
            };
        }
        
        var element = new WPJB.GFJ.Map.Item[struct.data("type")]({
            data: data.data,
            value: data.value
        });

        WPJB_GFJ_MAP.Element.push(element);
        WPJB_GFJ_MAP.area.append(element.view());
    });
    
    WPJB_GFJ.search();
    
});