jQuery(function($) {
    
    $("#toplevel_page_wpjb-job").find('a[href$="'+WPJB_CPT.href+'"]').addClass("current").parent().addClass("current");
    
    /* CPT COMMON */
    
    $("#toplevel_page_wpjb-job").removeClass("wp-not-current-submenu");
    $("#toplevel_page_wpjb-job").addClass("wp-menu-open");
    $("#toplevel_page_wpjb-job").addClass("wp-has-current-submenu");
    
    $(".toplevel_page_wpjb-job").removeClass("wp-not-current-submenu");
    $(".toplevel_page_wpjb-job").addClass("wp-menu-open");
    $(".toplevel_page_wpjb-job").addClass("wp-has-current-submenu");
    

    $(".page-title-action").before("'<strong>"+$("#title").val()+"'</strong>");
    $(".wrap > h1").after($("#edit-slug-box").detach());

    $("#editable-post-name").attr("id", "");
    $(".page-title-action").html(WPJB_CPT.go_back).attr("href", WPJB_CPT.url);
    

    
});