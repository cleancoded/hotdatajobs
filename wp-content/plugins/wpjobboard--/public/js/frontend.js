var WPJB = WPJB || {};

WPJB.execute = function(functionName, context /*, args */) {
    var args = [].slice.call(arguments).splice(2);
    var namespaces = functionName.split(".");
    var func = namespaces.pop();
    for(var i = 0; i < namespaces.length; i++) {
        context = context[namespaces[i]];
    }
    return context[func].apply(context, args);
}

WPJB.form = function( selector ) {
    
    this.form = selector;

    this.addError = function(message) {
        var $ = jQuery;
        var error = $("<div></div>");
        error.addClass("wpjb-flash-error");
        error.append($("<span></span>").addClass("wpjb-glyphs wpjb-icon-attention"));
        error.append($("<span></span>").text(message));

        $(this.form).prepend(error);
    };

    this.addFieldError = function(field, message) {
        var $ = jQuery;
        var row = $(this.form + " " + field);
        
        row.addClass("wpjb-error");
        
        if(row.find(".wpjb-errors").length == 0) {
            row.find(".wpjb-field").append($("<ul></ul>").addClass("wpjb-errors"));
        }
        
        row.find(".wpjb-errors").append($("<li></li>").text(message));
    };
    
    this.clearErrors = function() {
        var $ = jQuery;
        $(this.form).find(".wpjb-flash-error").remove();
        $(this.form).find(".wpjb-errors").remove();
        $(this.form).find(".wpjb-error").removeClass("wpjb-error");
    };
}

jQuery(function($) {
    
    if($(".wpjb-date-picker, .daq-date-picker").length == 0) {
        return;
    }
    
    $(".wpjb-date-picker, .daq-date-picker").DatePicker({
        format:WpjbData.date_format,
        date: "",
        current: "",
        starts: 1,
        position: 'l',
        onBeforeShow: function(param){
            $(this).addClass(param.id);
            var v = $(this).val();
            if(v.length > 0) {
                $(this).DatePickerSetDate(v, true);
            }
        },
        onChange: function(formated, dates){
            if($("#"+this.id+" tbody.datepickerDays").is(":visible")) {
                $("."+this.id).attr("value", formated).DatePickerHide();
            } 
            
            
        }
    });
});

jQuery(function($) {
    
    if(! $.isFunction($.fn.selectList)) {
        return;
    }
    
    $(".daq-multiselect").selectList({
        sort: false,
        template: '<li class="wpjb-upload-item">%text%</li>',
        onAdd: function (select, value, text) {

            if(value.length == 0) {
                $(select).parent().find(".selectlist-item:last-child")
                    .css("display", "none")
                    .click();
            }
            
            $(select).next().val("");
        }
    });
    $(".daq-multiselect").each(function(index, item) {
        if($(item).find("option[selected=selected]").length == 0) {
            $(item).prepend('<option value="" selected="selected"> </option>');
        }
    });
});

(function($) {
    $.fn.wpjb_menu = function(options) {

        // merge default options with user options
        var settings = $.extend({
            position: "left",
            classes: "wpjb-dropdown wpjb-dropdown-shadow",
            postfix: "-menu"
        }, options);

        return this.each(function() {

            var menu = $(this);
            var img = menu.find("img");
            var ul = menu.find("ul");

            //var id = $(this).attr("id");
            var menuId = ul.attr("id");

            $("html").click(function() {
                $("#"+menuId).hide();
                $("#"+menuId+"-img").removeClass("wpjb-dropdown-open");
            });
            
            ul.find("li a").hover(function() {
                $(this).addClass("wpjb-hover");
            }, function() {
                $(this).removeClass("wpjb-hover");
            });

            ul.hide();
            $(this).after(ul);
            $(this).click(function(e) {
                var dd = $("#"+menuId);
                var visible = dd.is(":visible");
                dd.css("position", "absolute");
                dd.css("margin", "0");
                dd.css("padding", "0");

                $("html").click();
                
                img.addClass("wpjb-dropdown-open");

                var parent = $(this).position();
                var parent_width = $(this).width();

                //dd.css("top", parent.top+$(this).height());

                if(settings.position == "left") {
                    dd.css("left", parent.left);
                } else {
                    dd.show();
                    dd.css("left", parent.left+parent_width-dd.width());
                }

                if(visible) {
                    dd.hide();
                } else {
                    dd.show();
                }

                e.stopPropagation();
                e.preventDefault();
            });
        });


    }
})(jQuery);

jQuery(function() {

    if(jQuery("input#protection").length > 0) {
        jQuery("input#protection").attr("value", WpjbData.Protection);
    }

    if(jQuery(".wpjb_apply_form").length > 0) {
        var hd = jQuery('<input type="hidden" />');
        hd.attr("name", "protection");
        hd.attr("value", WpjbData.Protection);
        jQuery(".wpjb_apply_form").append(hd);
    }
});

jQuery(function() {
    
    var autoClear = jQuery("input.wpjb-auto-clear");
    
    autoClear.each(function(index, item) {
        var input = jQuery(item);
        input.attr("autocomplete", "off");
        input.attr("wpjbdef", input.val());
        input.addClass("wpjb-ac-enabled");
    });
    
    autoClear.keydown(function() {
        jQuery(this).removeClass("wpjb-ac-enabled");
    });
    
    autoClear.focus(function() {
        var input = jQuery(this);
        
        if(input.val() == input.attr("wpjbdef")) {
            input.val("");
            input.addClass("wpjb-ac-enabled");
        }
        
    });
    
    autoClear.blur(function() {
        var input = jQuery(this);
        input.removeClass("wpjb-ac-enabled");
        
        if(input.val() == "") {
            input.val(input.attr("wpjbdef"));
            input.addClass("wpjb-ac-enabled");
        }
    });
    
    autoClear.closest("form").submit(function() {
        autoClear.unbind("click");
        if(autoClear.val() == autoClear.attr("wpjbdef")) {
            autoClear.val("");
        }
    });

});

jQuery(function($) {
    $(".wpjb-form-toggle").click(function(event) {
        var id = $(this).data("wpjb-form");
        
        if(!id) {
            id = $(this).attr("data-wpjb-form");
        }        
        
        $(this).find(".wpjb-slide-icon").toggleClass("wpjb-slide-up");
        
        $("#"+id).slideToggle("fast", function() {
            if(typeof WPJB.upload == 'object') WPJB.upload.refresh();
        });
        return false;
    });
    $(".wpjb-slide-icon").removeClass("wpjb-none");
});

jQuery(function($) {
    $(".wpjb-subscribe").click(function() {

        $("#wpjb-overlay").show();
        
        $("#wpjb-overlay").css("height", $(document).height());
        $("#wpjb-overlay").css("width", $(document).width());
        

        return false;
    });
    
    $(".wpjb-overlay-close").click(function() {
        $(this).closest(".wpjb-overlay").hide();
        return false;
    });
    $(".wpjb-overlay").click(function() {
        $(this).hide();
        return false;
    });
    $(".wpjb-overlay > div").click(function(e) {
        e.stopPropagation();
    });
    $(".wpjb-subscribe-save").click(function(e) {
        
        e.preventDefault();
        
        if(typeof WPJB_SEARCH_CRITERIA === 'undefined' || WPJB_SEARCH_CRITERIA == "") {
            
            WPJB_SEARCH_CRITERIA = {
                'filter' : 'active',
                'query' : $(".wpjb-ls-query").val(),
                'category' : $(".wpjb-ls-category").val(),
                'type' : $(".wpjb-ls-type").val(),
            }
        }
        
        var data = {
            action: "wpjb_main_subscribe",
            email: $("#wpjb-subscribe-email").val(),
            frequency: $(".wpjb-subscribe-frequency:checked").val(),
            criteria: WPJB_SEARCH_CRITERIA
        };
        
        $(".wpjb-subscribe-save").hide();
        $(".wpjb-subscribe-load").show();
        
	$.post(ajaxurl, data, function(response) {
            
            $(".wpjb-subscribe-load").hide();
            
            var span = $(".wpjb-subscribe-result");
            
            span.css("display", "block");
            span.text(response.msg);
            span.removeClass("wpjb-subscribe-success");
            span.removeClass("wpjb-subscribe-fail");
            span.removeClass("wpjb-flash-info");
            span.removeClass("wpjb-flash-error");
            
            if(response.result == "1") {
                span.addClass("wpjb-subscribe-success");
                span.addClass("wpjb-flash-info");
                $("#wpjb-subscribe-email").hide();
            } else {
                span.addClass("wpjb-subscribe-fail"); 
                span.addClass("wpjb-flash-error"); 
                $(".wpjb-subscribe-save").show();
                
            }
	}, "json");
        
        return false;
    });
});

jQuery(function($) {
    $(".wpjb-tooltip").click(function() {
        if($(".wpjb-map-slider iframe").length==0) {
            return false;
        }
        if($(".wpjb-map-slider iframe").attr("src").length == 0) {
            $(".wpjb-map-slider iframe").attr("src", $(this).attr("href"));
            $(".wpjb-map-slider iframe").fadeIn();
        }
        $(".wpjb-map-slider").toggle();
        return false;
    });
});

jQuery(function($) {
    if($(".wpjb-form .switch-tmce").length>0) {
        $(".wpjb-form .switch-tmce").closest("form").submit(function() {
            $(this).find(".html-active .switch-tmce").click();
        });
    }
});

jQuery(function($) {
    $(".wpjb-refine-button").click(function(e) {
        e.preventDefault();
        $(".wpjb-form-to-refine").slideToggle("fast");
    });
    
    $(".wpjb-refine-cancel").click(function(e) {
        e.preventDefault();
        
        var field = $(this).data("wpjb-field-remove");
        var element = $(".wpjb-element-name-"+field);
        
        if(element.find("input[type=text]").length > 0) {
            element.find("input[type=text]").val("");
        }
        if(element.find(".daq-multiselect-holder").length > 0) {
            var v = $(this).data("wpjb-field-value");
            element.find("input[type=checkbox][value='"+v+"']").attr("checked", null).change();
        }
        if(element.find("select").length > 0) {
            element.find("select").val("");
        }
        if(element.find("input[type=checkbox]").length > 0) {
            var v = $(this).data("wpjb-field-value");
            element.find("input[type=checkbox][value='"+v+"']").attr("checked", null).change();
        }
        if(element.find("input[type=radio]").length > 0) {
            var v = $(this).data("wpjb-field-value");
            element.find("input[type=radio][value='"+v+"']").attr("checked", null).change();
        }
        
        $(".wpjb-form-to-refine").submit();
    });
    
    $(".wpjb-form[method=get]").submit(function(e) {
        var all_empty = true;
        $(this).find('input, textarea, select').each(function(_, inp) {
          if ($(inp).val() === '' || $(inp).val() === null) {
            $(inp).attr("name", "");
          } else {
              all_empty = true;
          }
        });
        if(all_empty) {
            $(this).append('<input type="hidden" name="results" value="1" />');
        }
    });
});

jQuery(function($) {
    $(".wpjb-button-submit").on("click", function(e) {
        e.preventDefault();
        jQuery(this).closest("form").submit();
    });
});

if(window.location.hash == "#wpjb-sent" && history.pushState) {
    history.pushState('', document.title, window.location.pathname);
}

function wpjb_hide_scroll_hash() {
    if(window.location.hash == "#wpjb-scroll" && history.pushState) {
        history.pushState('', document.title, window.location.pathname);
    } 
}



// live search livesearch

var WPJB_SEARCH_CRITERIA = WPJB_SEARCH_CRITERIA || {};
var WpjbXHR = null;

function wpjb_ls_jobs_init() {
                
    var $ = jQuery;
    
    $("#wpjb-top-search ul").css("width", "100%");
    $(".wpjb-top-search-submit").hide();
    
        
    $(".wpjb-ls-query, .wpjb-ls-location").keyup(function() {
        wpjb_ls_jobs();
    });
    $(".wpjb-ls-type").change(function() {
        wpjb_ls_jobs();
        return false;
    });
                
    $(".wpjb-paginate-links").hide();
                
    //wpjb_ls_jobs();
}        
     
function wpjb_ls_jobs(e) {
        
    var $ = jQuery;
        
    if(WpjbXHR) {
        WpjbXHR.abort();
    }
                
    var page = null;
                
    if(typeof e == 'undefined') {
        page = 1;
    } else {
        page = parseInt($(".wpjb-ls-load-more a.btn").data("wpjb-page"));
    }
      
    var data = $.extend({}, WPJB_SEARCH_CRITERIA);
    data.action = "wpjb_jobs_search";
    data.page = page;
    data.type = [];
    
    WPJB_SEARCH_CRITERIA.filter = "active";
                
    if($(".wpjb-ls-query").val().length > 0) {
        data.query = $(".wpjb-ls-query").val();
        WPJB_SEARCH_CRITERIA.query = data.query;
    }                
    if($(".wpjb-ls-location").length && $(".wpjb-ls-location").val().length >= 0) {
        data.location = $(".wpjb-ls-location").val();
        WPJB_SEARCH_CRITERIA.location = data.location;
    }                
              
    $(".wpjb-ls-type:checked").each(function() {
        data.type.push($(this).val());
    });
    WPJB_SEARCH_CRITERIA.type = data.type;
                
    $(".wpjb-job-list").css("opacity", "0.4");
                
    WpjbXHR = $.ajax({
        type: "POST",
        data: data,
        url: ajaxurl,
        dataType: "json",
        success: function(response) {
                        
            var total = parseInt(response.total);
            var nextPage = parseInt(response.page)+1;
            var perPage = parseInt(response.perPage);
            var loaded = 0;
                                
            $(".wpjb-subscribe-rss input[name=feed]").val(response.url.feed);
            $(".wpjb-subscribe-rss a.wpjb-button.btn").attr("href", response.url.feed);

            if(total == 0) {
                $(".wpjb-job-list").css("opacity", "1");
                $(".wpjb-job-list").html('<div>'+WpjbData.no_jobs_found+'</div>');
                return;
            }
                                
            var more = perPage;
                                
            if(nextPage == 2) {
                $(".wpjb-job-list").empty();
            }
                        
            $(".wpjb-job-list .wpjb-ls-load-more").remove();
            $(".wpjb-job-list").css("opacity", "1");
            $(".wpjb-job-list").append(response.html);
                                
            loaded = $(".wpjb-job-list").children().length;
                                
            var delta = total-loaded;
                                
            if(delta > perPage) {
                more = perPage;
            } else if(delta > 0) {
                more = delta;
            } else {
                more = 0;
            }
                                
            if(more) {
                var txt = WpjbData.load_x_more.replace("%d", more);
                var loadMoreHtml = "";
                
                if($(".wpjb-job-list").prop("tagName") == "TBODY") {
                    loadMoreHtml = '<tr class="wpjb-ls-load-more"><td colspan="3"><a href="#" data-wpjb-page="'+(nextPage)+'" class="btn">'+txt+'</a></td></tr>';
                } else {
                    loadMoreHtml = '<div class="wpjb-ls-load-more"><a href="#" data-wpjb-page="'+(nextPage)+'" class="wpjb-button btn">'+txt+'</a></div>';
                }
                
                $(".wpjb-job-list").append(loadMoreHtml);
                $(".wpjb-job-list .wpjb-ls-load-more a").click(wpjb_ls_jobs);
            }
                                
        }
    });
                
    return false;
}

/**
 * Repositions $path
 * 
 * @deprecated              No longer needed since version 4.4.4
 * @param string $path      jQuery selector
 * @returns                 Null
 */
function wpjb_overlay_reposition(path) {
    // do nothing
}

