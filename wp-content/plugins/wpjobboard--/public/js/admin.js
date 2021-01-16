jQuery(function($) {
    
    if($(".wpjb-date-picker, .daq-date-picker").length == 0) {
        return;
    }
    
    $(".wpjb-date-picker, .daq-date-picker").DatePicker({
        format:wpjb_admin_lang.date_format,
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


var WpjbVE = {
    
    Init: function() {

    }
};

var WpjbDashboard = {
    
    previousPoint: null,
    
    _tmp: null,
    
    Init: function() {
        jQuery(function() {
            if(jQuery("#wpjb_dashboard_currency option").length < 2) {
                jQuery("#wpjb_dashboard_currency").hide();
            }
            jQuery("#wpjb_dashboard_period").change(function() {
                WpjbDashboard.load();
            });
            jQuery("#wpjb_dashboard_currency").change(function() {
                WpjbDashboard.load();
            });
            
            WpjbDashboard.load();
            
            jQuery("#wpjb_dashboard_placeholder").bind("plothover", function (event, pos, item) {
                if (item) {
                    if (WpjbDashboard.previousPoint != item.dataIndex) {
                        WpjbDashboard.previousPoint = item.dataIndex;

                        jQuery("#tooltip").remove();
                        var x = item.datapoint[0];
                        var xValue = "";
                        var y = item.datapoint[1];

                        var i = 0;
                        for(i in WpjbDashboard._tmp.info.tick) {
                            if(WpjbDashboard._tmp.info.tick[i][0] == x) {
                                xValue = WpjbDashboard._tmp.info.tick[i][1];
                            }
                        }
                        
                        if(item.seriesIndex == 1) {
                            y = WpjbDashboard._tmp.info.symbol+y.toFixed(2);
                        } 
                        

                        WpjbDashboard.tooltip(
                            item.pageX, 
                            item.pageY,
                            xValue+"<br/>"+item.series.label+": "+y
                        );
                    }
                }
                else {
                    jQuery("#tooltip").remove();
                    WpjbDashboard.previousPoint = null;            
                }
            });
        });     
    },
    
    load: function() {
        jQuery.ajax({
            type: "POST",
            url: "admin-ajax.php",
            dataType: "json",
            data: {
                action: "wpjb_dashboard_stats",
                currency: jQuery("#wpjb_dashboard_currency").val(),
                stats: jQuery("#wpjb_dashboard_period").val()
            },
            success: WpjbDashboard.loaded
        });
    },
    
    loaded: function(json) {
        jQuery.plot(jQuery("#wpjb_dashboard_placeholder"), json.data, json.options);
        
        var symbol = json.info.symbol;
        var jobP = (json.info.jobs/json.info.orders*100).toFixed(2);
        var resP = (json.info.resumes/json.info.orders*100).toFixed(2);
        
        jQuery("#wpjb_dashboard_info_revenue").text(symbol+json.info.volume.toFixed(2));
        jQuery("#wpjb_dashboard_info_orders").text(json.info.orders);
        jQuery("#wpjb_dashboard_info_job").text(jobP+"%");
        jQuery("#wpjb_dashboard_info_resumes").text(resP+"%");
        
        WpjbDashboard._tmp = json;
    },
    
    tooltip: function (x, y, contents) {
        jQuery('<div id="tooltip">' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y + 5,
            left: x + 5,
            border: '1px solid #9F9FAF',
            padding: '4px',
            'background-color': '#DFDFDF',
            opacity: "0.8",
            'font-size': "12px",
            'font-family': "Arial",
            'color': "#464646",
            'line-height': '1.2em'
        }).appendTo("body").fadeIn(200);
    }
}

var Wpjb = {
    DeleteType: "item",

    Option: null,

    JobState: null,

    Id: 0,

    InsertField: function(object) {

        var option = jQuery("#wpjbOptionText");
        var list = jQuery("#wpjbOptionList");

        var value = object.value;
        if(value.replace(" ", "").length == 0) {
            alert(WpjbAdminLang.addField_empty);
            return false;
        }

        if(value.length >= 120) {
            alert(WpjbAdminLang.addField_120);
            return false;
        }

        var key = "";
        if(object.id) {
            key = "id_"+object.id;
        }

        var input = jQuery('<input type="text" />')
            .attr("name", "option["+key+"]")
            .attr("value", object.value);

        var a = jQuery('<a></a>')
            .attr("href", "#")
            .append(WpjbAdminLang.addField_remove)
            .bind("click", function() {
                var a = jQuery(this);
                a.parent().remove();
                return false;
            });

        var li = jQuery('<li></li>')
            .hide()
            .append(input)
            .append(" ")
            .append(a);

        list.append(li);
        li.show("slow");
        option.val("");

        return false;
    },

    InitSlugUI: function(id) {
        var modifySlug = jQuery('<a class="button button-highlighted" href="#">'+WpjbAdminLang.slug_save+'</a>')
            .attr("id", "wpjb-save-slug")
            .attr("class", "button button-highlighted")
            .css("display", "none")
            .click(function() {
                var save = jQuery(this);
                var cancel = jQuery("#wpjb-cancel-slug");
                var change = jQuery("#wpjb-change-slug");

                jQuery("#"+id).attr("readonly", "readonly");

                cancel.css("display", "none");
                cancel.removeAttr("hold");
                change.css("display", "inline");
                save.css("display", "none");
                return false;
            });

        var cancelSlug = jQuery('<a href="#">'+WpjbAdminLang.slug_cancel+'</a>')
            .attr("id", "wpjb-cancel-slug")
            .attr("class", "button button-highlighted")
            .css("display", "none")
            .click(function() {
                var save = jQuery("#wpjb-save-slug");
                var cancel = jQuery(this);
                var change = jQuery("#wpjb-change-slug");

                jQuery("#"+id)
                    .attr("readonly", "readonly")
                    .attr("value", cancel.attr("hold"));

                cancel.css("display", "none");
                cancel.removeAttr("hold");
                change.css("display", "inline");
                save.css("display", "none");
                return false;
            });

        var changeSlug = jQuery('<a href="#">'+WpjbAdminLang.slug_change+'</a>')
            .attr("class", "button button-highlighted")
            .attr("id", "wpjb-change-slug")
            .click(function() {
                var save = jQuery("#wpjb-save-slug");
                var cancel = jQuery("#wpjb-cancel-slug");
                var change = jQuery(this);

                change.css("display", "none");
                save.css("display", "inline");
                cancel.css("display", "inline");
                cancel.attr("hold", jQuery("#"+id).val());

                jQuery("#"+id).removeAttr("readonly");
                return false;
            });


        var slug = jQuery("#"+id);
        slug
            .attr("readonly", "readonly")
            .after(changeSlug)
            .after(modifySlug)
            .after(cancelSlug);
    },

    Slugify: function(domId, title, object, id) {

        var data = {
            action: 'wpjb_main_slugify',
            object: object,
            title: title,
            id: id
	};

	jQuery.post("admin-ajax.php", data, function(response) {
            jQuery("#"+domId).val(response);
	});

    },

    ChartLoaded: function() {

        jQuery("#stats_draw")
            .bind("click", function() {
                Wpjb.ChartDraw();
                return false;
            })
            .html("draw chart");

    },

    ChartDraw: function() {

        var request = {
            action: "wpjb_stats_index",
            chart: jQuery("#stats_type").val(),
            start: jQuery("#stats_start").val(),
            end: jQuery("#stats_end").val()
        }


	jQuery.post("admin-ajax.php", request, function(response) {
            var data = new google.visualization.DataTable();
            eval("var json = "+response);

            var chart = json.chart;
            var type = "string";
            for(var i in chart.meta) {
                data.addColumn(type, chart.meta[i]);
                type = "number";
            }

            data.addRows(chart.data.length);
            for(i in chart.data) {
                data.setValue(parseInt(i), 0, chart.data[0].date);
                for(var j in chart.data[i].data) {
                    data.setValue(parseInt(i), j+1, parseInt(chart.data[i].data[j]));
                }
            }

            chart = new google.visualization.ImageLineChart(document.getElementById('wpjb-chart'));
            chart.draw(data, {width: 600, height: 240, min: 0});
	});
    }
}

jQuery(document).ready(function() {
    jQuery("a.wpjb-delete").bind("click", function() {
        var id = jQuery(this).attr("href").replace("#", "");
        var t = Wpjb.DeleteType;
        
        if(jQuery(this).hasClass("wpjb-no-confirm")) {
            return;
        }
        
        if(isNaN(id*1)) {
            var name = jQuery(this).closest("td").find("strong > a").text();
            if(!confirm(WpjbAdminLang.remove+" "+t+": "+name+"?")) {
                return false;
            }
        } else {
           
            if(confirm(WpjbAdminLang.remove+" "+t+": "+id+"?")) {
                jQuery("#wpjb-delete-form-id").attr("value", id);
                jQuery("#wpjb-delete-form").submit();
            }
            return false;
        }
    });

    jQuery("#wpjb-doaction1").bind("click", function(){
        var value = jQuery("#wpjb-action1").val();
        if(value == "-1") {
            alert(WpjbAdminLang.selectAction);
            return false;
        }
        jQuery("#wpjb-action-holder").attr("value", value);
    });
    jQuery("#wpjb-doaction2").bind("click", function(){
        var value = jQuery("#wpjb-action2").val();
        if(value == "-1") {
            alert(WpjbAdminLang.selectAction);
            return false;
        }
        jQuery("#wpjb-action-holder").attr("value", value);
    });
});



// wpjb/job
jQuery(function() {
    if(location.href.indexOf("page=wpjb/employer&action=edit") == -1) {
        return;
    }

    jQuery("#wpjb-image-remove").bind("click", function() {
        jQuery("#wpjb-remove-image-form-input").val(1);
        jQuery("#wpjb-remove-image-form").submit();

        return false;
    });
});

// edit category
jQuery(function() {
    if(location.href.indexOf("page=wpjb-category&action=") == -1) {
        return;
    }
    
    Wpjb.InitSlugUI("slug");

    jQuery("#title").bind("blur", function() {
        if(jQuery("#slug").val().length == 0) {
            Wpjb.Slugify("slug", jQuery(this).val(), "category", Wpjb.Id);
        }
    });
});

// edit jobType
jQuery(function() {
    if(location.href.indexOf("page=wpjb-jobType&action=") == -1) {
        return;
    }

    Wpjb.InitSlugUI("slug");

    jQuery("#title").bind("blur", function() {
        if(jQuery("#slug").val().length == 0) {
            Wpjb.Slugify("slug", jQuery(this).val(), "type", Wpjb.Id);
        }
    });
});


var WpjbBubble = {
    update: function(cl, newVal) {
        var $this = jQuery(cl);
        var span = $this.find("span.update-count");
        var pid = span.text();
        
        $this.removeClass("count-"+pid);
        if(newVal>0) {
            $this.addClass("count-"+newVal);
        } else {
            $this.addClass("count-0");
        }
        
        span.text(newVal);
        
    }
}

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

jQuery(function($) {
    var input = $(".wpjb-inline-select > select, .wpjb-inline-select > input");
    var holder = input.closest(".wpjb-inline-section");
   
    holder.find(".wpjb-inline-edit").click(function() {
        var $this = $(this);
        var $holder = $this.closest(".wpjb-inline-section");
        
        $holder.find(".wpjb-inline-field").removeClass("hide-if-js");
        $this.addClass("hide-if-js");
        return false;
    });
    
    holder.find(".wpjb-inline-cancel").click(function(e) {
        e.preventDefault();
        var $this = $(this);
        var $holder = $this.closest(".wpjb-inline-section");

        $holder.find(".wpjb-inline-field").addClass("hide-if-js");
        $holder.find(".wpjb-inline-edit").removeClass("hide-if-js");
    });
    
    holder.find(".wpjb-inline-select > select").change(function() {
        var $this = $(this);
        var $holder = $this.closest(".wpjb-inline-section");
        var text =  $this.find(":selected").text();
        
        $holder.find(".wpjb-inline-label").text(text);
        $holder.find(".wpjb-inline-field").addClass("hide-if-js");
        $holder.find(".wpjb-inline-edit").removeClass("hide-if-js");
        return false;
    });
    
    holder.filter(".wpjb-inline-suggest").find(".wpjb-inline-cancel").click(function(e) {
        e.preventDefault();
        var $this = $(this);
        var $holder = $this.closest(".wpjb-inline-section");
        var val = $holder.find(".wpjb-inline-label").text();
        
        $holder.find("input[type=text]").val(val);
    });
    
    holder.filter(".wpjb-inline-suggest").find("input[type=text]").each(function(i, item) {
        var $this = $(item);
        var suggest = $this.data("suggest");
        
        $this.closest(".wpjb-inline-suggest").find(".wpjb-inline-label").text($this.val());
        
        $this.suggest(ajaxurl + "?action="+suggest, { 
            maxCacheSize: 0, 
            delimiter: '<!-- suggest delimeter -->' ,
            resultsClass: 'wpjb_ac_results',
            onSelect: function(e) {

                var id = $(".wpjb_ac_results .ac_over > span").data("id");
                var field = $(this).closest(".wpjb-inline-suggest");
                var target = $(this).data("target");
                
                field.find(".wpjb-inline-label").text($(this).val());
                field.find(".wpjb-inline-field").addClass("hide-if-js");
                field.find(".wpjb-inline-edit").removeClass("hide-if-js");
                
                $("#"+target).val(id);

                
            }
        });
    } );
    
    holder.find(".wpjb-inline-select > select").change();
    holder.filter(".wpjb-inline-suggest").find(".wpjb-inline-select > .wpjb-inline-cancel").click();
    
});

jQuery(function($) {
    $(".wpjb-linked-job-view").click(function() {
        var href = $(this).attr("href");
        href = href.replace("id=PH", "id="+$("select#job_id").val());
        $(this).attr("href", href);
    });
});

jQuery(function($) {
    $(".wpjb-delete-item-confirm").click(function() {
        if(!confirm(wpjb_admin_lang.confirm_item_delete)) {
            return false;
        }
    });
});

jQuery(function($) {
    if($(".wpjb-form .switch-tmce").length>0) {
        $(".wpjb-form .switch-tmce").closest("form").submit(function() {
            $(this).find(".html-active .switch-tmce").click();
        });
    }
});

function wpjb_slug_ui() {
    jQuery(function($) {

        $(".wpjb-slug-pattern").blur(function() {
            if(jQuery(".wpjb-slug-base").val().length == 0) {
                $("#edit-slug-box").css("visibility", "visible");
                $(".wpjb-slug-buttons .edit-slug").click();
                $("#slug-temp").val($(this).val());
                $(".wpjb-slug-buttons .save").click();
            }
        });
        $(".edit-slug").click(function(e) {
            var span = $("#editable-post-name");
            var text = span.text();
            var input = $("<input type=\"text\" />");
            input.attr("id", "slug-temp");
            input.attr("value", text);

            $(".wpjb-slug-buttons .edit-slug").hide();
            $(".wpjb-slug-buttons .view-slug").hide();

            $(".wpjb-slug-buttons #edit-slug-btn").hide();
            $(".wpjb-slug-buttons #view-slug-btn").hide();
            $(".wpjb-slug-buttons #gets-slug-btn").hide();

            $(".wpjb-slug-buttons .save").show();
            $(".wpjb-slug-buttons .cancel").show();

            span.html(input);
            return false;
        });
        $(".wpjb-slug-buttons .save").click(function() {

            var title = "";
            if($("#slug-temp").val().length == 0) {
                title = $(".wpjb-slug-pattern").val();
            } else {
                title = $("#slug-temp").val();
            }

            var data = {
                action: 'wpjb_main_slugify',
                object: $(".wpjb-slug-type").val(),
                title: title,
                id: $("#wpjb-current-object-id").val()
            };

            jQuery.post(ajaxurl, data, function(response) {
                $(".wpjb-slug-base").val(response);
                $("#slug-temp").val(response);
                $("#editable-post-name").text(response);
                $(".wpjb-slug-buttons .cancel").click();
            });

            return false;
        });
        $(".wpjb-slug-buttons .cancel").click(function() {

            jQuery("#editable-post-name").text($(".wpjb-slug-base").val());
            $(".wpjb-slug-buttons .edit-slug").show();
            $(".wpjb-slug-buttons .view-slug").show();

            $(".wpjb-slug-buttons #edit-slug-btn").show();
            $(".wpjb-slug-buttons #view-slug-btn").show();
            $(".wpjb-slug-buttons #gets-slug-btn").show();


            $(".wpjb-slug-buttons .save").hide();
            $(".wpjb-slug-buttons .cancel").hide();
            return false;
        });
        $(".wpjb-slug-buttons .cancel").click();
    });
}

jQuery(function($) {
    if($("#wpjb-google-api-validate").length == 0) {
        return;
    }
    
    $("#wpjb-google-api-validate").click(function(e) {
        e.preventDefault();
        
        jQuery.ajax({
            type: "POST",
            url: "admin-ajax.php",
            dataType: "json",
            data: {
                action: "wpjb_main_googleapi"
            },
            success: function(response) {
                
                $(".wpjb-google-api-result").remove();
                
                var rdiv = $("<div></div>")
                    .addClass("wpjb-google-api-result")
                    .css("padding", "4px");
                
                if(response.status != "OK") {
                    rdiv.text(response.error_message);
                    rdiv.css("color", "red");
                } else {
                    rdiv.html("<strong><span class='dashicons dashicons-yes'></span> OK</strong>");
                    rdiv.css("color", "green");
                }
                
                $("#wpjb-google-api-validate").after(rdiv);
            }
        });
        
    });
});

jQuery(function($) {
    $(".wpjb-overlay-close").click(function() {
        $(this).closest(".wpjb-overlay").hide();
        return false;
    }); 
    $(".wpjb-overlay").click(function(e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).hide();
    }); 
    $(".wpjb-overlay > div").click(function(e) {
        e.stopPropagation();
    });
});