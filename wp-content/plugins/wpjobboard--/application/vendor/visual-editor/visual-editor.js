jQuery(function($) {

    $(".hndle").click(function() {
       var wrap = $(this).parent();
       wrap.find(".inside").slideToggle("fast"); 
    });

    $(".inside li.ui-input-group").draggable({
        connectToSortable: ".wpjb-ve-area",
        helper: "clone",
        revert: "invalid"
    });

    $(".inside li.wpjb-ui-element").draggable({
        connectToSortable: "ul.wpjb-ui-list",
        helper: "clone",
        revert: "invalid"
    });

    $(".inside li.wpjb-ui-builtin").draggable({
        connectToSortable: ".wpjb-ve-area fieldset ul.wpjb-ui-list",
        revert: "invalid"
    });

    $("#ve-save-form").click(function() {

        jQuery(".ve-loader").addClass("ve-show");

        var form = {"field": {}, "group": {}};

        jQuery.each(VisualEditor.Group.getAll(), function(index, item) {
            var order = -1;
            jQuery(".wpjb-ve-area > li.ui-input-group").each(function(i) {
                if(jQuery(this).attr("name") == item.name) {
                    order = i;
                }
            });

            var group = {};

            jQuery.each(item, function(index, cp) {

                switch(jQuery.type(cp)) {
                    case "boolean": group[index] = cp ? 1:0; break;
                    case "null": group[index] = ""; break;
                    default: group[index] = cp;
                }

            });

            group.order = order;
            delete group.field;
            if(order < 0) {
                group.is_trashed = "1";
            } else {
                group.is_trashed = "0";
            }

            form.group[group.name] = group;
        });

        jQuery.each(VisualEditor.Item.getAll(), function(index, item) {
            var order= -1;
            jQuery("ul.wpjb-ui-list > li:not(.wpjb-ui-placeholder)").each(function(i) {
                if(jQuery(this).attr("name") == item.name) {
                    order = i;
                }
            });

            var field = {};

            jQuery.each(item, function(index, cp) {

                switch(jQuery.type(cp)) {
                    case "boolean": field[index] = cp ? 1:0; break;
                    case "null": field[index] = ""; break;
                    default: field[index] = cp;
                }

            });

            field.order = order;
            if(order < 0) {
                field.is_trashed = "1";
                field.group = "";
            } else {
                var locate = jQuery("ul.wpjb-ui-list > li[name='"+field.name+"']");
                field.group = locate.closest(".ui-input-group").attr("name");
                field.is_trashed = "0";
            }

            if(field.type == "ui-input-file") {
                field.renderer = "wpjb_form_field_upload";
            }

            form.field[field.name] = field;
        });


        var data = {
            action: "wpjb_customfields_save",
            form_name: VE_FORM,
            form: form
        };
        
        if(typeof JSON.stringify == "function") {
            data.form = JSON.stringify(data.form);
            data.is_string = 1;
        }

        jQuery.post(ajaxurl, data, function(response) {
            //alert('Got this from the server: ' + response);
            jQuery.each(form.field, function(index, item) {
                VisualEditor.Item.get(item.name).is_saved = true;
            });
            jQuery(".ve-loader").removeClass("ve-show");
            
            jQuery("#ve-save-success").hide();
            jQuery("#ve-save-failed").hide();
            
            if(response.result == "1") {
                jQuery("#ve-save-success").fadeIn("fast");
            } else {
                jQuery("#ve-save-failed").fadeIn("fast");
            }
        }, "json");

        return false; 
    });

    $("---#ve-field-name").blur(function() {
        var $this = jQuery(this);
        var name = $("#TB_window .ve-save").attr("href").replace("#", "") ;
        
        if(name!=$this.val() && VisualEditor.Item.has($this.val())) {
            alert("Name "+$this.val()+" is already being used. Please select different name.");
            $this.focus();
        }
        if(!$this.val().match(/^[a-z0-9\_]+$/)) {
            alert("Name can use only lowercase alphanumeric characters, digits and _!");
            $this.focus();
        }
    });

    $(".wpjb-ve-area").sortable();
    $(".wpjb-ve-area").droppable({
        accept: ".ui-input-group",
        drop: function(event, ui) {

            var e = ui.draggable;

            if(e.find("fieldset").length) {
                return;
            }

            if(VisualEditor.Group.has(e.attr("name"))) {
                var data = VisualEditor.Group.get(e.attr("name"));
            } else {
                var data = {
                    "title": "New Group #"+VisualEditor.NextId("group"),
                    "name": "group_"+VisualEditor.NextId("group"),
                    "type": "ui-input-group",
                    "is_builtin": false,
                    "is_saved": false
                }
            }

            VisualEditor.addGroup(data, e);
            
            var list = jQuery(".ve-trash li.ui-input-group[name='"+data.name+"']");
            jQuery(list[0]).css("display", "none");

        }
    });



});

    
var VisualEditor = {
    
    ImgDir: "",
    
    Input: [],
    
    Group: new VisualEditorRegistry(),
    
    Item: new VisualEditorRegistry(),
    
    NextId: function(type) {
        if(type == "group") {
            var x = this.Group;
            var prefix = "group_";
        } else {
            var x = this.Item;
            var prefix = "field_";
        }
        
        for(var i=1; true; i++) {
            if(!x.has(prefix+i)) {
                return i;
            }
        }
    },
    
    handlePlaceholders: function() {
        jQuery("ul.wpjb-ui-list").each(function(i, item) {
            var x = jQuery(item).find("li.wpjb-toolbox-item").length;
            if(x == 0) {
                jQuery(item).find("li.wpjb-ui-placeholder").show();
            } else {
                jQuery(item).find("li.wpjb-ui-placeholder").hide();
            }
        });
    },
    
    handleHover: function(e) {
        e.hover(function() {
           jQuery(".wpjb-ve-area .ui-input-group").addClass("wpjb-no-border");
           jQuery(".wpjb-ve-area .wpjb-group-actions").addClass("ve-hidden");
        }, function() {
           jQuery(".wpjb-ve-area .ui-input-group").removeClass("wpjb-no-border");
           jQuery(".wpjb-ve-area .wpjb-group-actions").removeClass("ve-hidden");
        });
    },
    
    registerInput: function(input) {
        VisualEditor.Input.push(input);
    },
    
    getInput: function(name) {
        var input = null;
        jQuery.each(this.Input, function(index, item) {
            if(name == item.name) {
                input = item;
            }
        });
        
        return input;
    },
    
    addGroup: function(group, item) {

        VisualEditor.Group.add(group.name, group);

        if(item !== undefined) {
            var drag = item;
            drag.removeClass("wpjb-toolbox-item");
            drag.removeClass("ui-draggable");
            drag.attr("style", "");
            drag.removeClass("ui-draggable-dragging");
        } else {
            var drag = jQuery("<li></li>");
            drag.addClass("ui-input-group");
            drag.attr("style", "");
            jQuery(".wpjb-ve-area").append(drag);
        }
        
        
        var legend = jQuery("<legend></legend>").addClass("wpjb-group-legend").text(group.title);
        
        var imgProp = jQuery("<img />")
            .addClass("wpjb-properties")
            .attr("src", VisualEditor.ImgDir+'/gear.png')
            .click(function() {
                VisualEditorHelper.eventProperties();
                var w = jQuery("#TB_window");
                var r = VisualEditor.Group.get(jQuery(this).closest(".ui-input-group").attr("name"));
                
                w.find("tr").addClass("ve-none");
                w.find("li").addClass("ve-none");
                w.find("tr.field-name").removeClass("ve-none");
                w.find("tr.field-title").removeClass("ve-none");
                w.find("li.general").removeClass("ve-none");
                
                w.find("tr.field-name input").val(r.name);
                w.find("tr.field-title input").val(r.title);
                
                if(r.is_saved) {
                    w.find("tr.field-name input").attr("readonly", "readonly");
                }
                
                w.find("a.ve-save")
                    .attr("href", "#"+r.name)
                    .unbind("click")
                    .click(function() {
                        
                        if(!ve_validate_name()) {
                            return false;
                        }
                        
                        var name = jQuery(this).attr("href").replace("#", "");
                        var r = VisualEditor.Group.get(name);
                        var w = jQuery("#TB_window");
                        
                        r.name =  w.find("tr.field-name input").val();
                        r.title =  w.find("tr.field-title input").val();
                        
                        VisualEditor.Group.update(name, r);
                        
                        if(r.name != name) {
                            VisualEditor.Group.rename(name, r.name);
                            jQuery(".ui-input-group[name='"+name+"']").attr("name", r.name);
                        }
                        
                        var v = jQuery(".ui-input-group[name='"+r.name+"']");
                        v.find(".wpjb-group-legend").text(r.title);
                        
                        tb_remove();
                        return false;
                });
            });
        
        var imgTrash = jQuery("<img />")
            .addClass("wpjb-trash")
            .attr("src", VisualEditor.ImgDir+'/eraser.png')
            .click(VisualEditorHelper.eventTrash);
            
        var div = jQuery("<div></div>").addClass("wpjb-group-actions").append(imgProp).append(imgTrash);
        
        if('editable' in group && !group.editable) {
            var dis = jQuery("<div></div>").addClass("ve-group-disabled").text("This group is not editabled.");
            var fs = jQuery("<fieldset></fieldset>").append(legend).append(div).append(dis);
            
            drag.attr("name", group.name);
            drag.empty();
            drag.html(fs);
        } else {
            var li = jQuery("<li></li>").addClass("wpjb-ui-placeholder").text("Drop elements here")
            var ul = jQuery("<ul></ul>").addClass("wpjb-ui-list").append(li);
            var fieldset = jQuery("<fieldset></fieldset>").append(legend).append(div).append(ul);

            drag.attr("name", group.name);
            drag.empty();
            drag.html(fieldset);
            drag.find(".wpjb-ui-list").sortable({
                connectWith: ".wpjb-ui-list",
                update: VisualEditor.handlePlaceholders,
                cancel: ".wpjb-ui-placeholder" 

            }).droppable({
                drop: VisualEditor.dropItem
            });   
        }
        


    },
    
    addItem: function(group, item) {
        
        var e = jQuery("<li></li>");
        var input = null;
        var wrap = null;
        
        this.Item.add(item.name, item);
        
        e.addClass(item.type);
        e.addClass("wpjb-toolbox-item");
        e.attr("name", item.name);
        
        jQuery(".wpjb-ve-area > li").each(function(i, li) {
            if(jQuery(li).attr("name") == group) {
                wrap = jQuery(li);
            }
        });

        wrap.find(".wpjb-ui-list").append(e);
        
        this.handleHover(e);
        
        jQuery.each(this.Input, function(index, item) {
            if(e.hasClass(item.name)) {
                input = item;
            }
        });
        
        e.empty();
        e.html(input.visualize(item));
        VisualEditorHelper.visualUpdate(e, item);

        this.handlePlaceholders();
        
    },
    
    dropItem: function(event, ui) {

        var e = ui.draggable;
        var input = null;
        var type = null;
        
        jQuery.each(e[0].classList, function(i, item) {
            if(item.substring(0, 9) == "ui-input-") {
                type = item;
            }
        });
        
        if(jQuery(e).find("div.wpjb-element").length > 0) {
            return;
        }
        
        if(e.attr("name")) {
            var data = VisualEditor.Item.get(e.attr("name"));
        } else {
            var data = {
                "name":"field_"+VisualEditor.NextId("item"), 
                "title":"Field #"+VisualEditor.NextId("item"),
                "type": type,
                "is_saved": false,
                "is_builtin": false
            };
            VisualEditor.Item.add(data.name, data);
        }
        
        VisualEditor.handleHover(e);
        
        jQuery.each(VisualEditor.Input, function(index, item) {
            if(e.hasClass(item.name)) {
                input = item;
            }
        });
        
        e.empty();
        e.attr("style", "");
        e.attr("name", data.name);
        e.html(input.visualize(data));
        e.removeClass("ui-draggable");
        e.removeClass("ui-draggable-dragging");
        e.removeClass("ui-sortable-helper");
        VisualEditorHelper.visualUpdate(e, data);
        
        var list = jQuery(".ve-trash li[name='"+data.name+"']");
        jQuery(list[0]).css("display", "none");
        var y = 0;

    }
}

function VisualEditorRegistry(param) {

    if(param === undefined) {
        this._item = {};
    } else {
        this._item = param;
    }
    
    this._validate = function(o) {
        if(o.name === undefined || o.name.length<1) {
            throw "Param name is missing.";
        }
        if(o.type === undefined) {
            throw "Param type is missing."
        }
        if(o.is_builtin == undefined) {
            o.is_builtin = true;
        }
        if(o.is_saved === undefined) {
            o.is_saved = true;
        }
        
        return o;
    }
    
    this.add = function(name, params) {
        this._item[name] = this._validate(params);
    };
    
    this.update = function(name, params) {
        this._item[name] = params;
    };
    
    this.remove = function(name) {
        if(this.get(name).is_builtin) {
            throw "Cannot remove default object.";
        }
        delete this._item[name];
    }
    
    this.get = function(name) {
        return this._item[name];
    }
    
    this.getAll = function() {
        return this._item;
    }
    
    this.has = function(name) {
        if(this._item[name] === undefined) {
            return false;
        } else {
            return true;
        }
    }
    
    this.rename = function(oldname, newname) {
        var o = this.get(oldname);
        var n = this.get(newname);
        
        if(o.is_saved || o.is_builtin) {
            throw "Cannot rename saved object."
        }
        
        if(n !== undefined) {
            throw "Name "+newname+" is already taken.";
        }
        
        o.name = newname;
        
        this.remove(oldname);
        this.add(o.name, o);
    }
    
    this.trash = function(name) {
        this.get(name).is_trashed = true;
    }
    
}

var VisualEditorHelper = {

    editInline: function() {
        $this = jQuery(this);
        if($this.find("input").length==1) {
            return;
        }
        var input = jQuery("<input type='text' size='10' />");
        input.attr("value", $this.text());
        input.addClass("wpjb-inline-input");
        input.blur(function() {
            var parent = jQuery(this).parent();
            parent.empty();
            parent.html(jQuery(this).val());
        });
        $this.empty();
        $this.html(input);
        input.focus()
    },
    
    eventProperties: function() {
        tb_show("Object Properties", "#TB_inline?dummy=0&height=300&width=650&inlineId=ve-property-editor");
        var w = jQuery("#TB_window");
        w.find("tr.ve-none").removeClass("ve-none");
        w.find("li.ve-none").removeClass("ve-none");
        w.find("li.specific").addClass("ve-none");
        
        w.find("input[type=text]").val("").attr("readonly", false);
        w.find("input[type=checkbox]").attr("checked", false);
        
        w.find("li").removeClass("active");
        w.find("li.general").addClass("active");
        
        w.find("div.wpjb-tab").addClass("ve-none");
        w.find("div.wpjb-tab.general").removeClass("ve-none");
        
        w.find("a.ve-save").unbind("click");
    },
    
    eventTrash: function() {
        
        var x = jQuery(this).closest(".wpjb-toolbox-item");
        if(x.length == 0) {
            x = jQuery(this).closest(".ui-input-group");
            if(x.find("li.wpjb-toolbox-item").length>0) {
                alert("Only empty groups can be trashed.");
                return;
            }
            
        }
        
        x.fadeOut("fast",function() {
            var $this = jQuery(this);
            var name = $this.attr("name");
            
            if($this.hasClass("ui-input-group")) {
                var input = VisualEditor.Group.get(name);
                VisualEditor.Group.trash(name);
            } else {
                var input = VisualEditor.Item.get(name);
                VisualEditor.Item.trash(name);
            }
            
            $this.remove();
            
            jQuery(".ve-hidden").removeClass("ve-hidden");
            VisualEditor.handlePlaceholders();
            
            var tpl = jQuery(".ve-field-types ."+input.type).clone();
            tpl.attr("name", name);
            
            if($this.hasClass("ui-input-group")) {
                tpl.draggable({
                    connectToSortable: ".wpjb-ve-area",
                    revert: "invalid",
                    helper: "clone"
                })
            } else {
                tpl.draggable({
                    connectToSortable: ".wpjb-ve-area fieldset ul.wpjb-ui-list",
                    revert: "invalid",
                    helper: "clone",
                    stop: function(event, ui) { 
                        jQuery(ui.helper).attr("style", "");
                    }
                });
            }
            
            var img = jQuery("<img />");
            img.attr("alt", "");
            img.addClass("ve-event-delete");
            
            if(input.is_builtin) {
                img.attr("src", VisualEditor.ImgDir+"/lock.png");
                img.attr("title", "built-in fields cannot be deleted.");
            } else {
                img.attr("src", VisualEditor.ImgDir+"/bin--minus.png");
                img.attr("title", "delete forever ...");
                img.click(function() {
                    if(!confirm("Are you sure you want to delete this field forever?")) {
                        return;
                    }

                    var $this = jQuery(this).parent("li");
                    var name = $this.attr("name");
                    
                    if($this.hasClass("ui-input-group")) {
                        VisualEditor.Group.remove(name);
                    } else {
                        if(VisualEditor.Item.get(name).is_saved) {
                            VisualEditor.Item.get(name).delete_forever = true;
                        } else {
                            VisualEditor.Item.remove(name);
                        }
                    }
                    
                    $this.remove();
                    
                });
            }
            
            var small = jQuery("<small></small>");
            small.text("("+input.title+")");
            
            tpl.append(small);
            tpl.append(img);
            
            
            jQuery(".ve-trash").append(tpl);
        });
    },

    getName: function(item) {
        return jQuery(item).parentsUntil("li.wpjb-toolbox-item").last().parent().attr("name");
    },

    itemTemplate: function(input) {

        var d1 = jQuery("<div></div>")
            .addClass("wpjb-element")
            .addClass("wpjb-element-input-text");
        
        var label = jQuery("<label></label>")
            .addClass("wpjb-label");
            
        var span = jQuery("<span></span>")
            .addClass("wpjb-inline")
            //.click(VisualEditorHelper.editInline)
            .text("");
            
        var req = jQuery("<span></span>")
            .addClass("wpjb-required")
            .addClass("wpjb-none")
            .text("*");
            
        label.append(span).append(" ").append(req);
        
        var imgProp = jQuery("<img />")
            .addClass("wpjb-properties")
            .attr("src", VisualEditor.ImgDir+'/gear.png');
        
        if(input.propertiesShow) {
            imgProp.click(input.propertiesShow);
        } else {
            imgProp.click(VisualEditorHelper.eventProperties);
        }
        
        var imgTrash = jQuery("<img />")
            .addClass("wpjb-trash")
            .attr("src", VisualEditor.ImgDir+'/eraser.png')
            .click(VisualEditorHelper.eventTrash);
        
        var actions = jQuery("<div></div>")
            .addClass("wpjb-element-actions")
            .append(imgProp)
            .append(imgTrash);
            
        var hint = jQuery("<small></small>").addClass("wpjb-hint").addClass("wpjb-none");    
        var field = jQuery("<div></div>").addClass("wpjb-field").append(hint);
        
        
        d1.append(label).append(field).append(actions);
        
        return d1;
    },
    
    propertiesShow: function(r, object) {
        
        var w = jQuery("#TB_window");
        
        w.find("option").attr("selected", false);
        w.find("input[type=text]").val("");
        w.find("textarea").val("");
        
        jQuery.each(r._item, function(k, v) {
            var input = w.find("[name='"+k+"']");
            
            if(input.attr("type") == "checkbox") {
                input.attr("checked", v ? "checked" : false);
            } else {
                input.val(v);
            }
        });
        
        if(r.get("is_saved") || r.get("is_builtin")) {
            w.find("input[name=name]").attr("readonly", "readonly");
        }
        
        w.find(".ve-fill-method").unbind("change").change(function() {
            var $this = jQuery(this);
            if($this.val() == "callback") {
                jQuery(".ve-fill-callback").show();
                jQuery(".ve-fill-options").hide();
            } else if($this.val() == "default") {
                jQuery(".ve-fill-callback").hide();
                jQuery(".ve-fill-options").hide();
            } else {
                jQuery(".ve-fill-callback").hide();
                jQuery(".ve-fill-options").show();
            }
        });
        w.find(".ve-fill-method").change();
        w.find(".ve-save").attr("href", "#"+r.get("name"));
        w.find(".ve-save").click(object.propertiesUpdate);
        

    },
    
    propertiesUpdate: function(name) {
                
        
        var r = VisualEditor.Item.get(name);
        var w = jQuery("#TB_window");
        
        if(!ve_validate_name()) {
            return false;
        }
        
        jQuery.each(w.find("input,select,textarea"), function() {
            var $this = jQuery(this);
            var name = $this.attr("name");
            var type = $this.attr("type");
                        
            var input = $this.closest(".wpjb-tab").attr("class").split(" ")[0];
            
            if(jQuery("#wpjb-tabs li."+input).hasClass("ve-none")) {
                return;
            }
            
            if($this.hasClass("ve-none")) {
                return;
            }
            
            if(type == "checkbox") {
                r[name] = $this.is(":checked");
            } else {
                r[name] = $this.val(); 
            }
        });
        
        VisualEditor.Item.update(name, r);
        
        if(r.name != name) {
            VisualEditor.Item.rename(name, r.name);
            jQuery("li.wpjb-toolbox-item[name='"+name+"']").attr("name", r.name);
        }
        
        var item = jQuery("li.wpjb-toolbox-item[name='"+r.name+"']");
        VisualEditorHelper.visualUpdate(item, r);
        
        tb_remove();
        return false;
    },
    
    visualUpdate: function(input, item) {
        
        item = new VisualEditorRegistry(item);
        
        input.find(".wpjb-label > .wpjb-inline").text(item.get("title"));
        input.attr("name", item.get("name"));
        
        if(item.get("is_required")) {
            input.find(".wpjb-required").removeClass("wpjb-none");
        } else {
            input.find(".wpjb_required").addClass("wpjb-none");
        }
        
        if(item.get("hint") && item.get("hint").length>0) {
            input.find(".wpjb-hint").removeClass("wpjb-none");
            input.find(".wpjb-hint").text(item.get("hint"));
        } else {
            input.find(".wpjb-hint").addClass("wpjb-none");
            input.find(".wpjb-hint").text("");
        }
        
        input.find("input[type=text]").attr("value", item.get("value"));
        input.find("input[type=text]").attr("placeholder", item.get("placeholder"));
        
        if(item.get("description")) {
            input.find(".wpjb-label-description").html(item.get("description"));
        }
        
        var field = VisualEditor.getInput(item.get("type"));
        if(field.visualUpdate) {
            field.visualUpdate(input, item);
        }
    }
}

function VisualEditorInputLabel() {
    var $self = this;
    this.name = "ui-input-label";
    
    this.visualize = function(item) {
        var tpl = VisualEditorHelper.itemTemplate(this);
        var inp = jQuery("<div></div>")
            .attr("class", "wpjb-label-description")
            .attr("name", item.name)
            .html(item.description);

        tpl.find(".wpjb-field").prepend(inp);
        
        return tpl;
    },
    
    this.propertiesShow = function() {
        var name = VisualEditorHelper.getName(this);
        var reg = new VisualEditorRegistry(VisualEditor.Item.get(name));
        VisualEditorHelper.eventProperties();
        VisualEditorHelper.propertiesShow(reg, $self);
        
        var w = jQuery("#TB_window");
        w.find("li.ui-input-label").removeClass("ve-none");
        
    },
    
    this.propertiesUpdate = function(e) {
        e.preventDefault();
        
        var name = jQuery(this).attr("href").replace("#", "");
        VisualEditorHelper.propertiesUpdate(name);
        
        return false;
    }
}

function VisualEditorInputText() {
    var $self = this;
    this.name = "ui-input-text";
    
    this.visualize = function(item) {
        var tpl = VisualEditorHelper.itemTemplate(this);
        var inp = jQuery("<input type=\"text\" />")
            .attr("value", "")
            .attr("class", "regular-text")
            .attr("name", item.name);

        tpl.find(".wpjb-field").prepend(inp);
        
        return tpl;
    },
    
    this.propertiesShow = function() {
        var name = VisualEditorHelper.getName(this);
        var reg = new VisualEditorRegistry(VisualEditor.Item.get(name));
        VisualEditorHelper.eventProperties();
        VisualEditorHelper.propertiesShow(reg, $self);
        
        var w = jQuery("#TB_window");
        w.find("li.ui-input-text").removeClass("ve-none");
        
        if(reg.get("is_builtin")) {
            w.find("select[name=validation_rules]").attr("disabled", "disabled");
        } else {
            w.find("select[name=validation_rules]").attr("disabled", null);
        }
    },
    
    this.propertiesUpdate = function(e) {
        e.preventDefault();
        
        var name = jQuery(this).attr("href").replace("#", "");
        VisualEditorHelper.propertiesUpdate(name);
        
        return false;
    }
}

function VisualEditorInputFile() {
    var $self = this;
    this.name = "ui-input-file";
    
    this.visualize = function(item) {
        var tpl = VisualEditorHelper.itemTemplate(this);
        var inp = jQuery("<input type=\"file\" />")
            .attr("value", "")
            .attr("class", "regular-text")
            .attr("name", item.name);

        tpl.find(".wpjb-field").prepend(inp);
        
        return tpl;
    },
    
    this.propertiesShow = function() {
        var name = VisualEditorHelper.getName(this);
        var reg = new VisualEditorRegistry(VisualEditor.Item.get(name));
        VisualEditorHelper.eventProperties();
        VisualEditorHelper.propertiesShow(reg, $self);
        
        var w = jQuery("#TB_window");
        w.find("li.ui-input-file").removeClass("ve-none");
    },
    
    this.propertiesUpdate = function(e) {
        e.preventDefault();
        
        var name = jQuery(this).attr("href").replace("#", "");
        VisualEditorHelper.propertiesUpdate(name);
        
        return false;
    }
}

function VisualEditorInputSelect() {
    var $self = this;
    this.name = "ui-input-select";
    
    this.visualize = function(item) {
        var tpl = VisualEditorHelper.itemTemplate(this);
        var inp = jQuery("<select></select>")
            .append(jQuery("<option />").attr("value", "").html("Option #1"));


        tpl.find(".wpjb-field").html(inp);
        
        
        return tpl;
    },
    
    this.propertiesShow = function() {
        var name = VisualEditorHelper.getName(this);
        var reg = new VisualEditorRegistry(VisualEditor.Item.get(name));
        VisualEditorHelper.eventProperties();
        VisualEditorHelper.propertiesShow(reg, $self);
        
        var w = jQuery("#TB_window");
        w.find("li.options").removeClass("ve-none");
        w.find("li.ui-input-select").removeClass("ve-none");
        
        if(reg.get("is_builtin") === true) {
            w.find(".ve-fill-method").attr("disabled", "disabled");
            w.find(".ve-fill-method > option[value=default]").attr("selected", "selected");
            w.find(".ve-fill-method").change();
        } else {
            w.find(".ve-fill-method").attr("disabled", null);
        }
    },
    
    this.visualUpdate = function(input, item) {
        var select = input.find("select");
        select.empty();
        
        if(item.get("fill_method") == "callback") {
            select.html("<option><i> -- callback function -- </i></option>");    
        } else if(item.get("fill_method") == "choices") {
            var choices = jQuery.trim(item.get("fill_choices")).split("\n");
            jQuery(choices).each(function(index, item) {
                select.append(jQuery("<option>"+item+"</option>"));
            });
        } else {
            select.html("<option><i> -- default options -- </i></option>"); 
        }
        
        if(parseInt(item.get("select_choices"))>1) {
            input.find(".multi").removeClass("ve-none");
        } else {
            input.find(".multi").addClass("ve-none");
        }

    },
    
    this.propertiesUpdate = function(e) {

        var name = jQuery(this).attr("href").replace("#", "");
        VisualEditorHelper.propertiesUpdate(name);

        return false;
    }
}

function VisualEditorInputTextarea() {
    var $self = this;
    this.name = "ui-input-textarea";
    
    this.visualize = function(item) {
        var tpl = VisualEditorHelper.itemTemplate(this);
        var inp = jQuery("<textarea />")
            .attr("name", item.name)
            .attr("rows", 5)
            .attr("cols", 5);

        tpl.find(".wpjb-field").html(inp);
        
        return tpl;
    },
    
    this.propertiesShow = function() {
        var name = VisualEditorHelper.getName(this);
        var reg = new VisualEditorRegistry(VisualEditor.Item.get(name));
        VisualEditorHelper.eventProperties();
        VisualEditorHelper.propertiesShow(reg, $self);
        
        var w = jQuery("#TB_window");
        w.find("li.ui-input-textarea").removeClass("ve-none");
    },
    
    this.propertiesUpdate = function(e) {
        e.preventDefault();
        
        var name = jQuery(this).attr("href").replace("#", "");
        VisualEditorHelper.propertiesUpdate(name);
        
        return false;
    }
}
    
function VisualEditorInputCheckbox() {
    var $self = this;
    this.name = "ui-input-checkbox";
    
    this.visualize = function(item) {
        var tpl = VisualEditorHelper.itemTemplate(this);
        var wrap = tpl.find(".wpjb-field");
        
        var field = jQuery("<input type=\"checkbox\" />");

        wrap.html("");
        wrap.append(field);
        wrap.append("Option #1");
        wrap.append("<br/>");
        
        return tpl;
    },
    
    this.visualUpdate = function(input, item) {
        var wrap = input.find("div.wpjb-field");
        wrap.empty();
        
        if(item.get("fill_method") == "callback") {
            wrap.html("<input type=\"checkbox\"> -- callback function -- <br />");    
        } else if(item.get("fill_method") == "choices") {
            var choices = jQuery.trim(item.get("fill_choices")).split("\n");
            jQuery(choices).each(function(index, item) {
                wrap.append(jQuery("<input type=\"checkbox\"> "+item+"<br/>"));
            });
        } else {
            wrap.html("<input type=\"checkbox\"> -- default options -- <br />"); 
        }
        
    },
    
    this.propertiesShow = function() {
        var name = VisualEditorHelper.getName(this);
        var reg = new VisualEditorRegistry(VisualEditor.Item.get(name));
        VisualEditorHelper.eventProperties();
        VisualEditorHelper.propertiesShow(reg, $self);
        
        var w = jQuery("#TB_window");
        w.find("li.ui-input-checkbox").removeClass("ve-none");
        w.find("li.options").removeClass("ve-none");
    },
    
    this.propertiesUpdate = function(e) {

        var name = jQuery(this).attr("href").replace("#", "");
        VisualEditorHelper.propertiesUpdate(name);

        return false;
    }
}
    
function VisualEditorInputRadio() {
    var $self = this;
    this.name = "ui-input-radio";
    
    this.visualize = function(item) {
        var tpl = VisualEditorHelper.itemTemplate(this);
        var wrap = tpl.find(".wpjb-field");
        
        var field = jQuery("<input type=\"radio\" />");

        wrap.html("");
        wrap.append(field);
        wrap.append("Option #1");
        wrap.append("<br/>");
        
        return tpl;
    },
    this.visualUpdate = function(input, item) {
        var wrap = input.find("div.wpjb-field");
        wrap.empty();
        
        if(item.get("fill_method") == "callback") {
            wrap.html("<input type=\"radio\"> -- callback function -- <br />");    
        } else if(item.get("fill_method") == "choices") {
            var choices = jQuery.trim(item.get("fill_choices")).split("\n");
            jQuery(choices).each(function(index, item) {
                wrap.append(jQuery("<input type=\"radio\"> "+item+"<br/>"));
            });
        } else {
            wrap.html("<input type=\"radio\"> -- default options -- <br />"); 
        }
        
    },
    
    this.propertiesShow = function() {
        var name = VisualEditorHelper.getName(this);
        var reg = new VisualEditorRegistry(VisualEditor.Item.get(name));
        VisualEditorHelper.eventProperties();
        VisualEditorHelper.propertiesShow(reg, $self);
        
        var w = jQuery("#TB_window");
        w.find("li.options").removeClass("ve-none");
        w.find("li.ui-input-radio").removeClass("ve-none");
    },
    
    this.propertiesUpdate = function(e) {

        var name = jQuery(this).attr("href").replace("#", "");
        VisualEditorHelper.propertiesUpdate(name);

        return false;
    }
}
    
VisualEditor.registerInput(new VisualEditorInputLabel);
VisualEditor.registerInput(new VisualEditorInputText);
VisualEditor.registerInput(new VisualEditorInputSelect);
VisualEditor.registerInput(new VisualEditorInputTextarea);
VisualEditor.registerInput(new VisualEditorInputFile);
VisualEditor.registerInput(new VisualEditorInputCheckbox);
VisualEditor.registerInput(new VisualEditorInputRadio);
    
function ve_switch(tab) {
    var w = jQuery("#TB_window");
    w.find("ul.wpjb-tabs li").removeClass("active");
    w.find("ul.wpjb-tabs li a").removeClass("active");
    w.find("div.wpjb-tab").addClass("ve-none");
    
    w.find("ul.wpjb-tabs li."+tab).addClass("active");
    w.find("ul.wpjb-tabs li a."+tab).addClass("active");
    w.find("div.wpjb-tab."+tab).removeClass("ve-none");
}    
   
jQuery(function() {
    jQuery.each(form, function(i, e) {
        var g = e;
        VisualEditor.addGroup(g);
        if(g.is_trashed !== undefined && g.is_trashed) {
            jQuery("li.ui-input-group[name='"+g.name+"'] img.wpjb-trash").click();
        }
        jQuery.each(g.field, function(j, f) {
            VisualEditor.addItem(g.name, f);
            if(f.is_trashed !== undefined && f.is_trashed) {
                jQuery("li.wpjb-toolbox-item[name='"+f.name+"'] img.wpjb-trash").click();
            }
        });
    });
    
    // remove tmp trash
    if(VisualEditor.Group.has("_trashed")) {
        jQuery(".ui-input-group[name=_trashed]").remove();
        VisualEditor.Group.get("_trashed").is_builtin = false;
        VisualEditor.Group.remove("_trashed");
    }
    
    jQuery("#ve-save-failed").click(function() {
        jQuery(this).hide();
    });
    jQuery("#ve-save-success").click(function() {
        jQuery(this).hide();
    });
});

function ve_validate_name() {

    var $this = jQuery("#ve-field-name");
    var name = jQuery("#TB_window .ve-save").attr("href").replace("#", "") ;
    var disallow = [ "location"];
    
    if($this.attr("readonly") === "readonly") {
        return true;
    }
    
    if(disallow.indexOf($this.val()) >= 0) {
        alert("Name "+$this.val()+" is resverved. Please select different name.");
        return false;
    }

    if(name!=$this.val() && VisualEditor.Item.has($this.val())) {
        alert("Name "+$this.val()+" is already being used. Please select different name.");
        return false;
    }
    
    if(!$this.val().match(/^[a-z0-9\_]+$/)) {
        alert("Name can use only lowercase alphanumeric characters, digits and _!");
        return false;
    }
    
    return true;
}
