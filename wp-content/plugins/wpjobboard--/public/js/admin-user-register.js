jQuery(document).ready(function($) {
    "use strict";

    var user_type = $("#_user_type").change(function(e) {
        var $this = $(this);
        if($this.val() == "link") {
            $("#_user_link").closest("tr").show();
            $("#user_login").closest("tr").hide();
            $("#user_password").closest("tr").hide();
            $("#user_password2").closest("tr").hide();
            $("#user_email").closest("tr").hide();
        } else {
            $("#_user_link").closest("tr").hide();
            $("#user_login").closest("tr").show();
            $("#user_password").closest("tr").show();
            $("#user_password2").closest("tr").show();
            $("#user_email").closest("tr").show();
        }
    });

    user_type.change();

    var user_link = $('#_user_link').autocomplete({
        source: ajaxurl + "?action=wpjb_main_users&discard="+$('#_user_link').data("discard"),
        minLength: 3,
        select: function(event, ui) {
            $("#_user_id").val(ui.item.id);
        }
    })
            
    user_link.data("ui-autocomplete")._renderItem = function (ul, item) {
        var li = $("<li></li>");
        
        li.data("item.autocomplete", item);
        li.append(item.label);
        li.attr("title", item.hint);
        
        if(item.role != "") {
            li.addClass("ui-state-disabled");
            li.css("cursor", "not-allowed");
            li.css("opacity", "0.5");
        }
            
        return li.appendTo(ul);
    };
    



});