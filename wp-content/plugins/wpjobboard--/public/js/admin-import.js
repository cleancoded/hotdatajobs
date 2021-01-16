jQuery(function($) {
    $(".wpjb-old-queue-delete").click(function(e) {
        e.preventDefault();
        $(".wpjb-old-queue .ajax-loading").css("visibility", "visible");
        $(".wpjb-old-queue .wpjb-old-queue-delete").hide();
        
        $.ajax({
            type: "POST",
            url: WpjbImportUrl.clearqueue,
            dataType: "json",
            success: function(response) {
                var success = $("<span></span>").addClass("dashicons dashicons-yes");
                var failed = $("<span></span>").addClass("dashicons dashicons-no");
                $.each(response.queue, function(index, item) {
                    var filename = $(".wpjb-old-queue-list li[data-filename='"+item.filename+"']");
                    if(item.success == 1) {
                        filename.append(success).append(wpjb_admin_import.deleted);
                    } else {
                        filename.append(failed).append(wpjb_admin_import.failed).append(" " + item.error);
                    }
                });
                $(".wpjb-old-queue .wpjb-old-queue-close").show();
                $(".wpjb-old-queue .ajax-loading").css("visibility", "hidden");
            },
            error: function(response) {
                $(".wpjb-old-queue-delete-fail").removeClass("wpjb-none");
                $(".wpjb-old-queue-delete-fail").text(response.responseText);
                $(".wpjb-old-queue .ajax-loading").css("visibility", "hidden");
            }
        }); // end $.ajax
    }); // end $.click
    
    $(".wpjb-old-queue-close").click(function(e) {
        e.preventDefault();
        $(".wpjb-old-queue").hide();
    });
}); // end $

jQuery(function($) {

    var uploader = new plupload.Uploader({
        runtimes : 'gears,html5,flash,silverlight,browserplus',
        browse_button : 'pickfiles',
        container : 'container',
        max_file_size : '1gb',
        chunk_size : '1mb',
        url : WpjbImportUrl.upload,
        flash_swf_url : '<?php echo includes_url() ?>/js/plupload/plupload.flash.swf',
        silverlight_xap_url : '<?php echo includes_url() ?>/js/plupload/plupload.silverlight.xap',
        filters : WpjbImport.filters
    });

    uploader.bind('Init', function(up, params) {

    });

    $('#uploadfiles').click(function(e) {
        
        if($("#wpjb-data-import").length > 0 && $("#wpjb-data-import").val() == "") {
            alert("Select data you wish to import first!");
            return;
        }
        
        $("#uploadfiles").fadeOut("fast");
        $("#ajax-loading-img").removeClass("ajax-loading");
        $('#pickfiles').css("opacity", "0.5");
        $('#pickfiles').css("visibility", "hidden");

        uploader.start();
        e.preventDefault();
    });

    uploader.init();

    uploader.bind('FilesAdded', function(up, files) {
        $.each(files, function(i, file) {
            $('#filelist').html('<div id="' + file.id + '"><span class="filename">' + file.name + '</span> (' + plupload.formatSize(file.size) + ') <b></b></div>');
            //$("#pickfiles").fadeOut("slow");
        });
        $("#uploadfiles").fadeIn("slow");
        up.refresh(); // Reposition Flash/Silverlight
    });

    uploader.bind('UploadProgress', function(up, file) {
        $('#' + file.id + " b").html(file.percent + "%");
    });

    uploader.bind('Error', function(up, err) {
        $('#filelist').append("<div>Error: " + err.code + ", Message: " + err.message + (err.file ? ", File: " + err.file.name : "") + "</div>");
        up.refresh(); // Reposition Flash/Silverlight
    });

    uploader.bind('FileUploaded', function(up, file) {
        $('#' + file.id + " b").html("100%");
    });    
	
    uploader.bind('UploadComplete', function(up, files) {

        jQuery("#importlist").html("Starting import. Please wait ...");
        jQuery.ajax({
            url: WpjbImportUrl.count,
            success: function(response) {
                WpjbImportTotal += parseInt(response);
                WpjbInterval = setInterval(wpjb_start_import, 100);
            }
        });
        
    });
});

var WpjbImportDID = 0;

function wpjb_start_import(filename) {

    if(WpjbImportBusy) {
        return;
    }

    WpjbImportBusy = true;

    var data = {};

    if(jQuery("#wpjb-data-import").length > 0) {
        data.model = jQuery("#wpjb-data-import").val();
        data.did = WpjbImportDID;
    }

    jQuery.ajax({
        type: "POST",
        url: WpjbImportUrl.import,
        data: data,
        dataType: "json",
        success: function(response) {

            WpjbImportDID += parseInt(response.row);
            WpjbImportIteration += parseInt(response.increment);
            jQuery("#importlist").html("Importing ("+WpjbImportIteration+"/"+WpjbImportTotal+") "+response.file+"");
            
            WpjbImportBusy = false;

            jQuery.each(response.imports, wpjb_import_log);

            if(response.hasMore == "0") {
                clearInterval(WpjbInterval);
                jQuery("#ajax-loading-img").addClass("ajax-loading");
                jQuery("#importsuccess").show();
            }
        },
        error: function(response) {
            jQuery("#ajax-loading-img").hide();
            jQuery("#filelist").hide();
            jQuery("#importlist").hide();
            
            jQuery(".wpjb-import-error").removeClass("wpjb-none");
            jQuery(".wpjb-import-error-response").html(response.responseText);
            
            clearInterval(WpjbInterval);
        }
    });
}

function wpjb_import_log(index, item) {
    var $ = jQuery;
    var log = $(".wpjb-import-log");
    var link = null;
    var icon = $("<span></span>");
    
    if(item.admin_url != null) {
        link = $("<a></a>").text(item.title).attr("href", item.admin_url);
    } else {
        link = $("<span></span>").text(item.title);
    }
    
    if(item.action == "insert") {
        icon.addClass("dashicons dashicons-plus").attr("title", "Added");
    } else if(item.action == "update") {
        icon.addClass("dashicons dashicons-update").attr("title", "Updated");
    } else {
        icon.addClass("dashicons dashicons-no").attr("title", wpjb_admin_import.failed);
    }

    var ul = $("<ul></ul>").hide();
    var notices = {
        fatal: 0,
        warning: 0
    };
    
    $.each(item.messages, function(j, err) {
        ul.append($("<li></li>").html(err.text)).addClass("wpjb-import-error-type-"+err.type);
        notices[err.type]++;
    });
    


    var tr = $("<tr></tr>");
    var td1 = $("<td></td>");
    
    
    td1.addClass("import-system row-title");
    td1.append(icon)
    td1.append($("<span></span>").addClass("wpjb-import-item-type").text(item.type));
    td1.append(link);

    if(notices.fatal + notices.warning > 0) {
        
        var title = [];
        
        if(notices.fatal) {
            title.push("Fatal Error!");
            var color = "red";
        } else {
            title.push(("Warnings (%d)").replace("%d", notices.warning));
            var color = null;
        }
        
        var s = $("<span></span>").addClass("dashicons dashicons-arrow-down-alt2").css("vertical-align", "middle");
        var a = $("<a></a>").attr("href", "#").addClass("wpjb-import-log-link").append(title.join(", ")).append(s).css("padding", "0 0 0 0.5em");
        a.click(function(e) {
            e.preventDefault();
            var $this = $(this);
            $this.closest("tr").find("ul").slideToggle("fast");
        });
        
        if(color) {
            a.css("color", color);
        }
        
        td1.append(a);
        td1.append(ul);
    }

    tr.append(td1);

    log.append(tr);
    
}