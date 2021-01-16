var wpjb_menu_item_add = function(wrap, d) {
    var $ = jQuery;
    var data = $.extend({
        title: "",
        insert: "",
        attr: "",
        classes: "",
        icon: "",
        visibility: {},
        state: "open"
    }, d);
    var template = $('#wpjb-custom-menu-link-template');
    var id = wrap.data("next-id");
    var tpl = template.html().replace(/48/g, id);
    var iarr = data.insert.split(":");
    var title = "";
    var type = "";
    var url = "";

    switch(iarr[0]) {
        case "frontend": 
            title = "Job Board"; 
            type = "wpjb";
            break;
        case "resumes": 
            title = "Resumes";
            type = "wpjb";
            break;
        case "page": 
            title = "Page";
            type = "page";
            break;
        case "separator":
            title = "";
            type = "separator";
            break;
        case "http": 
        case "https": 
            title = "Custom";
            type = "http";
            url = data.insert;
            break;
    }


    wrap.data("next-id", parseInt(id)+1);

    var $tpl = $(tpl);

    $tpl.find(".menu-item-title").html(data.title);
    $tpl.find(".item-type").html(title);
    $tpl.find(".wpjb-custom-link-type").hide();
    $tpl.find(".wpjb-custom-link-type-"+type).show();
    $tpl.find('.wpjb-icon-picker-toggle').click(wpjb_icon_picker_toggle);
    
    $tpl.find(".edit-menu-item-title").val(data.title);
    $tpl.find(".edit-menu-item-attr-title").val(data.attr);
    $tpl.find(".menu-item-key").val(data.insert);
    $tpl.find(".edit-menu-item-url").val(url);
    $tpl.find(".edit-menu-item-classes").val(data.classes);
    $tpl.find(".edit-menu-item-icon").val(data.icon);
    
    if($.isPlainObject(data.visibility)) {
        $.each(data.visibility, function(index, item) {
            $tpl.find(".edit-menu-item-visibility-"+index).prop("checked", true);
        });
    }

    $tpl.find(".item-cancel").click(function(e) {
        e.preventDefault();
        $(this).closest("li.menu-item").find(".menu-item-settings").slideToggle("fast");
    });
    $tpl.find(".item-delete").click(function(e) {
        e.preventDefault();
        $(this).closest("li.menu-item").remove();
    });
    $tpl.find(".item-edit").click(function(e) {
        e.preventDefault();
        $('.wpjb-custom-menu-icon-picker-wrap').hide();
        $(this).closest("li.menu-item").find(".menu-item-settings").slideToggle("fast");
    });
    $tpl.find(".edit-menu-item-title").keyup(function(e) {
        $(this).closest("li.menu-item").find(".menu-item-title").html($(this).val());
    });
    $tpl.find("input").each(function(index, item) {
        var $this = $(item);
        $this.attr("name", wrap.data("name") + $this.attr("name"));
    });

    if(data.state != "open") {
        $tpl.find(".menu-item-settings").hide();
    }
    if(data.icon) {
        $tpl.find(".wpjb-icon-picker-preview").addClass("wpjb-icon-"+data.icon);
    }
    if(type == "separator") {
        $tpl.find(".field-title").hide();
        $tpl.find(".field-attr-title").hide();
        $tpl.find(".field-css-classes").hide();
        $tpl.find(".field-icon").hide();
        $tpl.find(".field-url").hide();
        
        $tpl.find(".menu-item-title").html('<div class="wpjb-custom-menu-line-bg"><span><em>Separator</em></span></div>');
        $tpl.find(".edit-menu-item-title").val("");
        $tpl.find(".edit-menu-item-attr-title").val("");
    }

    wrap.find(".wpjb-custom-menu-links").append($tpl);
    wrap.find(".wpjb-custom-menu-items").hide();
};

var wpjb_is_url = function(str) {
    var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
        '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
        '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
        '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
        '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
        '(\\#[-a-z\\d_]*)?$','i'); // fragment locator

    if(!pattern.test(str)) {
        return false;
    } else {
        return true;
    }
};

var wpjb_icon_picker_toggle = function(e) {
    e.preventDefault();
    var $ = jQuery;
    var btn = $(this);
    var picker = $('.wpjb-custom-menu-icon-picker-wrap');

    $("#wpjb-category-icon-filter").val("").keyup();

    if(picker.is(":visible")) {
        picker.data("target", "");
        picker.hide();
    } else {
        picker.data("target", $(this).get(0).id);
        picker.css({
            position: 'absolute',
            top: btn.offset().top,
            left: btn.offset().left + btn.width() - picker.width() - 10
        });
        picker.show();
    }
};

jQuery(function($) {
    $("#wpjb-category-icon-filter").keyup(function(e) {
        var val = $.trim($(this).val());

        if(val.length > 0) {
            $(".wpjb-image-icon-picker > li").hide();
            $(".wpjb-image-icon-picker > li[data-name*='"+val+"']").show();
        } else {
            $(".wpjb-image-icon-picker > li").show();
        }
    }); 
    
    $(".wpjb-image-icon-picker > li > a").click(function(e) {
        e.preventDefault(); 
        var picker = $('.wpjb-custom-menu-icon-picker-wrap');
        var target = picker.data("target");
        
        $("#"+target+" .wpjb-icon-picker-preview").addClass("wpjb-icon-"+$(this).data("name"));
        $("#"+target).next().val($(this).data("name"));
        $("#"+target).click();
        
    });
    
});

function wpjb_admin_custom_menu($, id) {
    
    var $menu = $("#"+id);
    
    $menu.find(".wpjb-custom-menu-switch").click(function(e) {
        e.preventDefault();
        var x = $(this).parent();
        $(this).parent().find(".wpjb-custom-menu-items").slideToggle("fast");
    });
    
    $menu.find(".wpjb-custom-menu-insert-url").click(function(e) {
        e.preventDefault();
        
        var wrap = $(this).closest(".wpjb-custom-menu");
        var data = {
            insert: wrap.find(".wpjb-custom-menu-url").val(),
            title: ""
        };
        
        if(!wpjb_is_url(data.insert)) {
            alert("Please provide valid URL (starting with http:// or https:// )");
            return;
        }
        
        wpjb_menu_item_add(wrap, data);
    });
    
    $menu.find(".wpjb-custom-menu-insert-item").click(function(e) {
        e.preventDefault();
        
        var wrap = $(this).closest(".wpjb-custom-menu");
        var data = {
            insert: $(this).data("insert"),
            title: $(this).html(),
            attr: $(this).html()
        };
        wpjb_menu_item_add(wrap, data);
    });
    
    $menu.find(".wpjb-custom-menu-insert-separator").click(function(e) {
        e.preventDefault();
        
        var wrap = $(this).closest(".wpjb-custom-menu");
        var data = {
            insert: $(this).data("insert"),
            title: $(this).html(),
            attr: $(this).html(),
            state: "closed"
        };
        wpjb_menu_item_add(wrap, data);
    });
    
    
    $menu.find( ".wpjb-custom-menu-links" ).sortable();
    $menu.find( ".wpjb-custom-menu-links" ).disableSelection();
};

if(typeof wpjb_custom_menu_struct != 'undefined') {
    jQuery.each(wpjb_custom_menu_struct.menu, function(index, item) {
        wpjb_admin_custom_menu(jQuery, item); 
    });
    jQuery.each(wpjb_custom_menu_struct.item, function(index, item) {
        wpjb_menu_item_add(jQuery(item.id), item.data);
    });
}

jQuery(function($) {
    $( document ).on( 'widget-added', function(event,ui){
        var id = $(ui).find(".wpjb-custom-menu").attr("id"); 
        if(wpjb_custom_menu_struct.menu.indexOf(id) === -1) {
            wpjb_custom_menu_struct.menu.push(id);
            wpjb_admin_custom_menu(jQuery, id)
        }
    }); 
});	