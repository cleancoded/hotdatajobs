jQuery(function($) {
    $("tr.wpjb-update-error").next().hide();
    
    var elem = $('#update-plugins-table td p:contains("**WPJB-UPGRADE-NOTICE**")');
    
    if(elem.length == 0) {
        return;
    }
    
    var d2 = $("<div></div>").addClass("wpjb-upgrade-error").css("margin-left", "0").html(wpjb_admin_upgrade_lang.message);
    var d1 = $("<div></div>").addClass("plugin-update-tr").append(d2);
    var d0 = $("<div></div>").append(d1);
    
    elem.html(elem.html().replace("**WPJB-UPGRADE-NOTICE**", d0.html()));
});