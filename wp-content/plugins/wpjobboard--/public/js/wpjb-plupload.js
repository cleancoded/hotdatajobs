var WPJB = WPJB || {};

WPJB.previewbox = function(selectors) {
    
    this.selectors = jQuery.extend({
        index: ".wpjb-file-pagi-index",
        total: ".wpjb-file-pagi-total",
        prev: ".wpjb-file-pagi-prev",
        next: ".wpjb-file-pagi-next"
    }, selectors);
    
    this.resize = function() {
        var width = jQuery(window).width();
        var height = jQuery(window).height();
        
        var hOuter = Math.floor(height * 0.8);
        var hInner = hOuter - 100;
        
        jQuery("#wpjb-file-upload-overlay > div").css("height", hOuter.toString()+"px");
        jQuery("#wpjb-file-upload-overlay > div #wpjb-file-content").css("height", hInner.toString()+"px");
    };
    
    this.paginate = function(item) {
        
        var $ = jQuery;
        var uploads = item.button.closest(".wpjb-uploads");
        var current = item.file;
        var index = 0;
        var total = 0;

        uploads.find(".wpjb-upload-item").each(function(j, item) {
            total++;
            if($(this).data("file") == current.url) {
                index = total;
            }
        });
        
        var prev = jQuery(this.selectors.prev);
        var next = jQuery(this.selectors.next);
        
        if(index == 1) {
            prev.off("click");
            prev.addClass("wpjb-navi-disabled");
        } else {
            prev.off("click");
            prev.on("click", jQuery.proxy(this.prev, this, item));
            prev.removeClass("wpjb-navi-disabled");
        }
        
        if(index == total) {
            next.off("click");
            next.addClass("wpjb-navi-disabled");
        } else {
            next.off("click");
            next.on("click", jQuery.proxy(this.next, this, item));
            next.removeClass("wpjb-navi-disabled");
        }

        $(this.selectors.index).html(index.toString());
        $(this.selectors.total).html(total.toString());
    },
    
    this.prev = function(item) {
        this.execute(item.button.closest(".wpjb-upload-item").prev());
    },
    
    this.next = function(item) {
        this.execute(item.button.closest(".wpjb-upload-item").next());
    },
            
    this.execute = function(item) {
        
        if(!item.hasClass("wpjb-upload-item")) {
            return;
        }
        
        var preview = item.find(".wpjb-item-preview");
        
        if(preview.length === 0) {
            var download = item.find(".wpjb-item-download")
            var file = null;
            jQuery.each(WPJB.upload.button, function(index, element) {
                if(element.button.is(download)) {
                    file = element;
                }
            });
            this.fallback(file);
        } else {
            preview.click();
        }

        return false;
    },
            
    this.fallback = function(item) {
        var span = jQuery("<span></span>");
        span.addClass("wpjb-glyphs wpjb-icon-eye-off");
        span.css("font-size", "128px");

        var text = jQuery("<div></div>");
        text.css("font-size", "28px");
        text.css("margin", "1em");
        text.html("Sorry, this file cannot be previewed.");

        var download = jQuery("<a></a>");
        download.attr("href", item.file.url);
        download.addClass("wpjb-button");
        download.text(wpjb_plupload_lang.download_file);
        download.css("padding", "0.5em 1em");
        download.css("font-size", "1.6em");

        var div = jQuery("<div></div>");
        div.css("text-align", "center");
        div.append(span);
        div.append(text);
        div.append(download);

        var content = jQuery("#wpjb-file-upload-overlay #wpjb-file-content");
        content.empty();
        content.append(div);
        
        this.paginate(item);
        
        jQuery("#wpjb-file-upload-overlay").show();
        jQuery("#wpjb-file-upload-overlay .wpjb-file-name").html(item.file.name);
    }
    
};

WPJB.upload = {
    
    instance: [],
    
    handles: [],
    
    button: [],
    
    media: [],
    
    once: false,
    
    preview: new WPJB.previewbox({}),
    
    getExt: function(href) {
        return href.split("/").pop().split(".").pop();
    },
    
    todo: function(url) {
        
        var $ = jQuery;
        var ext = WPJB.upload.getExt(url);
        var handle = null;

        $.each(WPJB.upload.handles, function(index, item) {
            handle = item;
            if($.inArray(ext, item.ext) !== -1) {
                return false;
            } 
        });
        
        return handle;
    },
    
    construct: function() {
        var $ = jQuery;
        
        $(".wpjb-upload-list").each(function(index, item) {
            WPJB.upload.init($(item).data());
        });
    },
    
    init: function(options) {
        
        if(WPJB.upload.once === false) {
            jQuery(".wpjb-uploads, .wpjb-upload-ui").removeClass("wpjb-none");
            jQuery(".wpjb-upload-construct").remove();
        
            jQuery(window).resize(WPJB.upload.preview.resize);
        
            WPJB.upload.preview.resize();
            WPJB.upload.once = true;
        }
        
        var uploader = new plupload.Uploader(options);
        uploader.bind('Init', WPJB.upload.bind.init);
        uploader.init();
        
        uploader.bind('FilesAdded', WPJB.upload.bind.filesAdded);
        uploader.bind('UploadProgress', WPJB.upload.bind.uploadProgress);
        uploader.bind('FileUploaded', WPJB.upload.bind.fileUploaded);
        uploader.bind('Error', WPJB.upload.bind.error);
        
        WPJB.upload.instance.push(uploader);
    },
    
    load: function(key, files) {
        var $ = jQuery;
        
        $.each(files, function(index, file) {
            $("#"+key+" .wpjb-uploads").append(WPJB.upload.addFile(file));
            WPJB.upload.refresh();
        });
    },
    
    progress: function(file) {
        
        var $ = jQuery;
        var todo = WPJB.upload.todo(file.name);
        var fico = jQuery("<span></span>").addClass("wpjb-glyphs").addClass("wpjb-file-icon").addClass(todo.icon);
        var item = jQuery("<div></div>").addClass("wpjb-upload-item").attr("id", file.id);
        var fname = jQuery("<span></span>").addClass("wpjb-file-name").text(file.name);
        var prog = jQuery("<span></span>").addClass("wpjb-upload-progress");
        
        var bar = jQuery("<span></span>").addClass("wpjb-upload-progress-bar");
        var bari = jQuery("<span></span>").addClass("wpjb-upload-progress-bar-inner");
        var uico = jQuery("<span></span>").addClass("wpjb-glyphs wpjb-animate-spin wpjb-icon-cw");
        
        bar.append(bari);
        prog.append(bar).append(uico);
        item.append(fico).append(fname).append(prog);

        return item;
    },
    
    add: function(file) {
        
        file = jQuery.extend({url: "#", path: ""}, file);
        
        var $ = jQuery;
        var todo = WPJB.upload.todo(file.name);
        var fico = jQuery("<span></span>").addClass("wpjb-glyphs").addClass("wpjb-file-icon");
        var item = jQuery("<div></div>").addClass("wpjb-upload-item").attr("id", file.id).data("file", file.url);
        var actions = jQuery("<span></span>").addClass("wpjb-item-actions");

        fico.addClass(todo.icon);
        
        $.each(todo.action, function(index, item) {
            
            var action = new WPJB.upload.action[item](file);
            WPJB.upload.button.push(action);
            actions.append(action.button);
        });

        var fname = jQuery("<span></span>").addClass("wpjb-file-name").text(file.name);
        var span = jQuery("<span></span>").addClass("wpjb-file-info").text(plupload.formatSize(file.size));

        item.append(fico).append(fname).append(span).append(actions);

        return item;
    },
    
    addFile: function(file) {
        file.type = "file";
        return WPJB.upload.add(file);
    },
    
    addLink: function(file) {
        file.type = "link";
        return WPJB.upload.add(file);
    },
    
    refresh: function() {
        jQuery.each(WPJB.upload.instance, function(index, uploader) {
            WPJB.upload.instance[index].refresh();
        });
    },
    
    error: function(fade, msg) {

        var attention = jQuery("<span></span>").addClass("wpjb-glyphs wpjb-icon-attention");
        var dispose = jQuery("<span></span>").addClass("wpjb-glyphs wpjb-icon-trash");

        fade.empty();
        fade.attr("class", "wpjb-upload-error");
        fade.append(attention);
        fade.append(wpjb_plupload_lang.error+": "+msg);
        fade.append(dispose);
        fade.css("cursor", "pointer");
        fade.attr("title", wpjb_plupload_lang.dispose_message);
        fade.click(function() {
            jQuery(this).fadeOut("fast", function() {
                jQuery(this).remove();
            }); 
        });
        
    }
}

WPJB.upload.bind = {
    
    init: function(up){
        var $ = jQuery;
        
        var cid = null;
        if(typeof up.settings.container.id === "undefined") {
            cid = up.settings.container;
        } else {
            cid = up.settings.container.id;
        }
        var uploaddiv = $('#'+cid+'.wpjb-upload > .wpjb-upload-ui');

        if(up.features.dragdrop) {
            uploaddiv.addClass('wpjb-drag-drop');
            uploaddiv.find('.wpjb-upload-inner').bind('dragover.wp-uploader', function(){ 
                uploaddiv.addClass('wpjb-drag-over'); 
            });
            uploaddiv.find('.wpjb-drop-zone').bind('dragleave.wp-uploader, drop.wp-uploader', function(){
                uploaddiv.removeClass('wpjb-drag-over'); 
                
            });
        }else{
          uploaddiv.removeClass('wpjb-drag-drop');
          uploaddiv.find(".wpjb-drop-zone").unbind('.wp-uploader');
        }
    },
    
    filesAdded: function(up, files) {
        var $ = jQuery;
        var cid = null;
        if(typeof up.settings.container.id === "undefined") {
            cid = up.settings.container;
        } else {
            cid = up.settings.container.id;
        }
        $.each(files, function(i, file) {
            $("#"+cid+" .wpjb-uploads").append(WPJB.upload.progress(file));
        });

        up.start();
        up.refresh(); // Reposition Flash/Silverlight
    },
    
    uploadProgress: function(up, file) {
        var width = (file.percent * 0.9).toString();
        jQuery('#' + file.id + " span.wpjb-upload-progress-bar-inner").css("width", width + "%");
    },
    
    fileUploaded: function(up, file, response) {
        var result = jQuery.parseJSON(response.response);
        
        if(result.result < 1) {
            WPJB.upload.error(jQuery("#"+file.id), result.msg);
        } else {
            jQuery("#"+file.id).replaceWith(WPJB.upload.addFile(result));
        }

    },
    
    error: function(up, err) {
        var div = jQuery("<div></div>");
        jQuery("#"+up.settings.container).append(div);
        WPJB.upload.error(div, err.message);
        up.refresh(); // Reposition Flash/Silverlight
    }
};

WPJB.upload.action = { 
    
};

/**
 * ACTION IMAGE
 * 
 */
WPJB.upload.action.image = function(file) {
    
    this.file = file;
    this.button = this.construct();
};


WPJB.upload.action.image.prototype.construct = function() {
    var a = jQuery("<a></a>");
    var file = this.file;

    a.attr("href", file.url);
    a.addClass("wpjb-item-preview wpjb-glyphs wpjb-icon-eye");
    a.attr("title", wpjb_plupload_lang.preview);
    a.attr("target", "blank");

    a.click(jQuery.proxy(this.click, this));

    return a;
};
    
WPJB.upload.action.image.prototype.click = function(e) {
        
    e.stopPropagation();
    e.preventDefault();

    WPJB.upload.preview.paginate(this);

    // preload image
    var image = new Image();
    image.onload = jQuery.proxy(this.loaded, this);
    image.src = this.file.url;

    var span = jQuery("<span></span>");
    span.addClass("wpjb-glyphs wpjb-animate-spin wpjb-icon-spinner");
    span.css("font-size", "24px");

    var content = jQuery("#wpjb-file-upload-overlay #wpjb-file-content");
    content.empty();
    content.append(span);
    
    jQuery("#wpjb-file-upload-overlay").show();
};

WPJB.upload.action.image.prototype.loaded = function() {
    
    var content = jQuery("#wpjb-file-upload-overlay #wpjb-file-content");
    var width = jQuery(window).width(); 
    var height = jQuery(window).height();
    var img = jQuery("<img />").attr("src", this.file.url).attr("alt", "");

    img.css("max-width", "100%");
    img.css("max-height", "100%");

    content.empty();
    content.append(img);
    
    jQuery("#wpjb-file-upload-overlay").show();
    jQuery("#wpjb-file-upload-overlay .wpjb-file-name").html(this.file.name);
    
}

/**
 * ACTION DOWNLOAD
 * 
 */
WPJB.upload.action.download = function(file) {
    this.file = file;
    this.button = this.construct();
}

WPJB.upload.action.download.prototype.construct = function() {
    var a = jQuery("<a></a>");
    
    a.attr("href", this.file.url);
    a.addClass("wpjb-item-download wpjb-glyphs wpjb-icon-download")
    a.attr("title", wpjb_plupload_lang.download_file);
    
    return a;
}

/**
 * ACTION AUDIO PLAYBACK
 * 
 */
WPJB.upload.action.audio = function(file) {
    this.file = file;
    this.button = this.construct();
}

WPJB.upload.action.audio.prototype.construct = function() {
    var a = jQuery("<a></a>");
    
    a.attr("href", this.file.url);
    a.addClass("wpjb-item-preview wpjb-glyphs wpjb-icon-play");
    a.attr("title", wpjb_plupload_lang.play_file);
    a.click(jQuery.proxy(this.click, this));
    
    return a;
}

WPJB.upload.action.audio.prototype.click = function(e) {
    e.stopPropagation();
    e.preventDefault();

    WPJB.upload.preview.paginate(this);

    var audio = jQuery("<audio></audio>");
    audio.attr("src", this.file.url);
    audio.attr("controls", "1");
    audio.css("width", "100%");
    audio.css("height", "50%");
    
    var content = jQuery("#wpjb-file-upload-overlay #wpjb-file-content");
    content.empty();
    content.append(audio);
    
    jQuery("#wpjb-file-upload-overlay").show();
    jQuery("#wpjb-file-upload-overlay .wpjb-file-name").html(this.file.name);
};

/**
 * ACTION VIDEO PLAYBACK
 * 
 */
WPJB.upload.action.video = function(file) {
    this.file = file;
    this.button = this.construct();
}

WPJB.upload.action.video.prototype.construct = function() {
    var a = jQuery("<a></a>");
    
    a.attr("href", this.file.url);
    a.addClass("wpjb-item-preview wpjb-glyphs wpjb-icon-play");
    a.attr("title", wpjb_plupload_lang.play_file);
    a.click(jQuery.proxy(this.click, this));
    
    return a;
}

WPJB.upload.action.video.prototype.click = function(e) {
    e.stopPropagation();
    e.preventDefault();

    WPJB.upload.preview.paginate(this);

    var video = jQuery("<video></video>");
    video.attr("src", this.file.url);
    video.attr("controls", "1");
    video.css("max-height", "100%");
    
    var content = jQuery("#wpjb-file-upload-overlay #wpjb-file-content");
    content.empty();
    content.append(video);
    
    jQuery("#wpjb-file-upload-overlay").show();
    jQuery("#wpjb-file-upload-overlay .wpjb-file-name").html(this.file.name);
};
/**
 * ACTION GOOGLE VIEER
 * 
 */
WPJB.upload.action.pdf = function(file) {
    this.file = file;
    this.button = this.construct();
}

WPJB.upload.action.pdf.prototype.construct = function() {
    var a = jQuery("<a></a>");
    
    a.attr("href", this.file.url);
    a.addClass("wpjb-item-preview wpjb-glyphs wpjb-icon-doc-text-inv")
    a.attr("title", wpjb_plupload_lang.preview_file);
    a.click(jQuery.proxy(this.click, this));
    
    return a;
}

WPJB.upload.action.pdf.prototype.click = function(e) {
    e.stopPropagation();
    e.preventDefault();

    WPJB.upload.preview.paginate(this);

    var embed = jQuery("<embed></embed>");
    embed.attr("src", this.file.url);
    embed.css("width", "100%");
    embed.css("height", "100%");
    
    var content = jQuery("#wpjb-file-upload-overlay #wpjb-file-content");
    content.empty();
    content.append(embed);
    
    jQuery("#wpjb-file-upload-overlay").show();
    jQuery("#wpjb-file-upload-overlay .wpjb-file-name").html(this.file.name);
};

/**
 * ACTION DELETE
 * 
 */
WPJB.upload.action.delete = function(file) {
    this.file = file;
    this.button = this.construct();
}

WPJB.upload.action.delete.prototype.construct = function(file) {
    var a = jQuery("<a></a>");
            
    a.attr("href", "#");
    a.addClass("wpjb-item-delete wpjb-glyphs")
    
    
    if(this.file.type == "file") {
        a.addClass("wpjb-icon-trash");
        a.attr("title", wpjb_plupload_lang.delete_file);
    } else {
        a.addClass("wpjb-icon-unlink");
    }
    
    a.click(jQuery.proxy(this.click, this));
    
    return a;
}

WPJB.upload.action.delete.prototype.click = function(e) {
    
    if(typeof e !== 'undefined') {
        e.preventDefault();
        e.stopPropagation();
    }
    
    jQuery("#wpjb-file-delete").show();
    jQuery("#wpjb-file-delete .wpjb-file-delete-error").hide();
    jQuery("#wpjb-file-delete .wpjb-icon-spinner").hide();
    jQuery("#wpjb-file-delete .wpjb-file-delete-name").html(this.file.name);

    //this.overlay.reposition();
    
    var confirm = jQuery("#wpjb-file-delete .wpjb-file-delete-confirm");
    confirm.off("click");
    confirm.on("click", jQuery.proxy(this.confirm, this));
    
    var cancel = jQuery("#wpjb-file-delete .wpjb-file-delete-cancel");
    cancel.off("click");
    cancel.on("click", jQuery.proxy(this.cancel, this));

};

WPJB.upload.action.delete.prototype.confirm = function(e) {
    e.preventDefault();
    
    jQuery("#wpjb-file-delete .wpjb-icon-spinner").show();
    jQuery("#wpjb-file-delete .wpjb-file-delete-confirm").off("click");
    jQuery("#wpjb-file-delete .wpjb-file-delete-cancel").off("click");
    
    var container = this.button.closest(".wpjb-upload");
    var params = {};
    
    jQuery.each(WPJB.upload.instance, function(index, item) {
        if(container.attr("id") === jQuery(item.settings.container).attr("id")) {
            params = item.settings.multipart_params;
        }
    });
    
    if(this.file.type === "file") {
        var data = {
            action: "wpjb_main_delete",
            id: this.file.path
        };
    } else {
        var data = {
            action: "wpjb_main_unlink",
            object: params.object,
            field: params.field,
            id: params.id,
            link_id: this.file.id
        };
    }
    
    jQuery.ajax({
        url: WPJB.upload.ajaxurl,
        context: this,
        type: "post",
        dataType: "json",
        data: data,
        success: this.success
   });
}

WPJB.upload.action.delete.prototype.cancel = function(e) {
    e.preventDefault();
    jQuery("#wpjb-file-delete").hide();
};
 
WPJB.upload.action.delete.prototype.success = function(response) {
    
    jQuery("#wpjb-file-delete .wpjb-icon-spinner").hide();
    
    if(response.result == 1) {
        jQuery("#wpjb-file-delete").hide();
        this.button.closest("div.wpjb-upload-item").fadeOut(function() {
            jQuery(this).remove();
        });
    } else {
        this.click();
        jQuery(".wpjb-file-delete-error").show();
        jQuery(".wpjb-file-delete-error-msg").html(response.msg);
        
    }
};

/**
 * MEDIA LIBRARY
 */

WPJB.mediabox = function(data) {
    this.opener = data.opener;
    this.overlay = data.overlay;
    this.params = data.params;
    this.checked = [];
    this.xhr = null;
    
    var $ = jQuery;
    var uploads = jQuery(this.opener).closest(".wpjb-upload").find(".wpjb-uploads");

    $(this.opener).on("click", $.proxy(this.open, this));
    $(window).on("resize", $.proxy(this.resize, this));
    
    $.each(data.links, function(index, item) {
        uploads.append(WPJB.upload.addLink(item));
    });
    
};

WPJB.mediabox.prototype.resize = function() {

    var width = jQuery(window).width();
    var height = jQuery(window).height();

    var hOuter = Math.floor(height * 0.8);
    var hInner = hOuter - 100;

    jQuery("#wpjb-media-library-overlay > div").css("height", hOuter.toString()+"px");
    jQuery("#wpjb-media-library-overlay > div .wpjb-overlay-content").css("height", hInner.toString()+"px");
    
}

WPJB.mediabox.prototype.open = function(e) {
    e.preventDefault();
    var $ = jQuery;
    
    $(this.overlay).show();
    $(this.overlay).resize();
    
    $("#wpjb-media-library-search").val("");
    
    $(".wpjb-media-library-stat").addClass("wpjb-none");
    $(".wpjb-media-library-count").text("");
    
    this.checked = [];
    
    $(".wpjb-media-library-spinner").removeClass("wpjb-none");
    $(this.overlay).find(".wpjb-attachments").empty();
    
    $("#wpjb-media-library-search").off();
    $("#wpjb-media-library-search").on("change paste keyup", $.proxy(this.change, this));
    $("#wpjb-media-library-search").change();
    
    $(".wpjb-media-library-add").off();
    $(".wpjb-media-library-add").on("click", $.proxy(this.add, this));
    
    $(".wpjb-media-library-cancel").off();
    $(".wpjb-media-library-cancel").click("click", $.proxy(this.cancel, this));
};

WPJB.mediabox.prototype.success = function(response) {
    var $ = jQuery;
    var mediabox = this;
    
    $(".wpjb-media-library-spinner").addClass("wpjb-none");
    $(mediabox.overlay).find(".wpjb-attachments").empty();
    
    $.each(response.data, function(index, item) {
        var li = $("<li></li>");
        var attach = $("<div></div>");
        var thumb = mediabox.thumb(item);
        
        li.attr({
            tabindex: 0,
            role: "checkbox",
            class: "wpjb-attachment"
        });
        
        li.data({
            id: item.id,
            name: item.filename,
            url: thumb.url,
            path: "media#"+item.id,
            size: item.filesizeInBytes
        });
        
        li.on("click", $.proxy(mediabox.selected, mediabox));
        
        $.each(mediabox.checked, function(index, file) {
            if(file.id == item.id) {
                li.data("checked", "1");
                li.addClass("wpjb-media-item-checked");
            }
        });
        
        attach.addClass("wpjb-attachment-preview");
        attach.addClass("type-" + item.type);
        attach.addClass("subtype-" + item.subtype);
        attach.addClass("wpjb-" + thumb.orientation);
        
        attach.append(mediabox.thumbnail(item));
        li.append(attach);
        

        $(mediabox.overlay).find(".wpjb-attachments").append(li);
    });

    
};

WPJB.mediabox.prototype.thumb = function(item) {
    
    var orient = null;
    var icon = null;
    var url = item.url;
    
    if(typeof item.sizes !== 'undefined') {
        
        var allowed = ["full", "large", "medium", "thumbnail"];
        var sizes = item.sizes;
        
        jQuery.each(allowed, function(i, index) {
            if(typeof sizes[index] !== 'undefined') {
                orient = sizes[index].orientation;
                icon = sizes[index].url;
            }
        });
        
        if(orient && icon) {
            return {
                orientation: orient, 
                icon: icon,
                url: url
            };
        }
    }
    
    orient = "landscape";
    
    if(typeof item.thumb !== 'undefined') {
        icon = item.thumb.src;
    } else {
        icon = item.icon;
    }
    
    return {
        orientation: orient, 
        icon: icon,
        url: url
    };
};

WPJB.mediabox.prototype.thumbnail = function(item) {
    var $ = jQuery;
    var thumb = this.thumb(item);
    var thumbnail = $("<div></div>").addClass("wpjb-thumbnail");
    var center = $("<div></di>").addClass("wpjb-centered");
    
    
    if(item.type == "image") {
        center.append($("<img />").attr("src", thumb.icon));
        
        thumbnail.append(center);
    } else {
        var filename = $("<div></div>").text(item.filename);
        center.append($("<img />").attr("src", thumb.icon).addClass("wpjb-icon"));
        
        thumbnail.append(center);
        thumbnail.append($("<div></div>").addClass("wpjb-filename").append(filename));
    }
    
    return thumbnail;
}

WPJB.mediabox.prototype.change = function(e) {
    var $ = jQuery;
    
    $(".wpjb-media-library-spinner").removeClass("wpjb-none");
    
    if(this.xhr !== null) {
        this.xhr.abort();
    }
    
    this.xhr = $.ajax({
        url: WPJB.upload.ajaxurl,
        context: this,
        type: "post",
        dataType: "json",
        data: {
            action: "wpjb_main_attachments",
            query: {
                s: $("#wpjb-media-library-search").val(),
                posts_per_page: 500,
                paged: true
            }
        },
        success: this.success
    });
};

WPJB.mediabox.prototype.selected = function(e) {
    e.preventDefault();
    
    var $ = jQuery;
    var target = $(e.target);
    
    if(!target.hasClass("wpjb-attachment")) {
        target = target.closest(".wpjb-attachment");
    }
    
    var id = target.data("id");
    
    if(target.data("checked") == "1") {
        target.removeClass("wpjb-media-item-checked");
        target.data("checked", null);
        this.checked = jQuery.grep(this.checked, function(value) {
            return value.id != id;
        });
    } else {
        target.addClass("wpjb-media-item-checked");
        target.data("checked", "1");
        if($.inArray(id, this.checked) === -1) {
            this.checked.push(target.data());
        }
    }
    
    target.blur();
    
    if(this.checked.length === 0) {
        $(".wpjb-media-library-stat").addClass("wpjb-none");
    } else {
        $(".wpjb-media-library-stat").removeClass("wpjb-none");
        $(".wpjb-media-library-count").text(this.checked.length);
    }

};

WPJB.mediabox.prototype.add = function(e) {
    e.preventDefault();
    
    var links = [];
    jQuery.each(this.checked, function(index, item) {
       links.push(item);
    });
    
    if(this.xhr !== null) {
        this.xhr.abort();
    }
    
    this.xhr = jQuery.ajax({
        url: WPJB.upload.ajaxurl,
        context: this,
        type: "post",
        dataType: "json",
        data: jQuery.extend(this.params, {links: links}),
        success: this.successAdd
    });
    
    jQuery(this.overlay).hide();
};

WPJB.mediabox.prototype.successAdd = function(response) {
    var uploads = jQuery(this.opener).closest(".wpjb-upload").find(".wpjb-uploads");
    jQuery.each(response.links, function(index, item) {
        if(item.result == "1") {
            uploads.append(WPJB.upload.addLink(item));
        } else {
            var div = jQuery("<div></div>");
            uploads.append(div);
            WPJB.upload.error(div, item.msg);
            
        }
    });
};

WPJB.mediabox.prototype.cancel = function(e) {
    e.preventDefault();
    jQuery(this.overlay).hide();
    
};

/*

    if (typeof wpjb_uploader === 'undefined') {
        wpjb_uploader = [];
    }
    function wpjb_pluploader_add_file(file) { }
    function wpjb_plupload_file_error(fade, msg) { }
    function wpjb_plupload(options) { }
    function wpjb_plupload_refresh() { }

*/