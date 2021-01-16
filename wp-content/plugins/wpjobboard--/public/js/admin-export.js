jQuery(function($) {
    $(".wpjb-modal-window-toggle").click(function(e) {
        e.preventDefault();
        $(".wpjb-modal-window").toggle();
        $(".wpjb-export-step").hide();
        $(".wpjb-export-step-1").show();
        $(".wpjb-export-button-xml").hide();
        $(".wpjb-export-button-csv").hide();
        $(".wpjb-export-button-download").hide();
        $(".wpjb-export .ajax-loading").css("visibility", "hidden");
        $(".wpjb-export .wpjb-export-progress").hide();
        $(".wpjb-export-stat-current").text("0");
        $(".wpjb-export-stat-estimated").text("0");
    });
    
    $(".wpjb-export-xml").click(function(e) {
        e.preventDefault();
        
        $(".wpjb-export-step-2").addClass("wpjb-export-xml");
        $(".wpjb-export-step-2").removeClass("wpjb-export-csv");

        $(".wpjb-export-step-1").hide();
        $(".wpjb-export-step-2").show();
        
        $(".wpjb-export-button-xml").fadeIn("fast");
    });
    
    $(".wpjb-export-csv").click(function(e) {
        e.preventDefault();
        
        $(".wpjb-export-step-2").addClass("wpjb-export-csv");
        $(".wpjb-export-step-2").removeClass("wpjb-export-xml");

        $(".wpjb-export-step-1").hide();
        $(".wpjb-export-step-2").show();
        
        $(".wpjb-export-button-csv").fadeIn("fast");
    });
    
    $(".wpjb-table-toggle").change(function(e) {
        var checked = $(this).is(":checked");
        var tbody = $(this).closest("table").find("tbody");
        var thead = $(this).closest("table").find(".wpjb-export-thead-actions");
        
        if(checked) {
            tbody.show();
            thead.show();
            
            thead.find(".wpjb-export-check-basic").click();
        } else {
            tbody.hide();
            thead.hide();
        }
        
    });
    
    $(".wpjb-export-check-basic").click(function(e) {
        e.preventDefault();
        var tbody = $(this).closest("table").find("tbody");
        tbody.find("tr input[type=checkbox]").prop("checked", false);
        tbody.find("tr").filter(function() {
            switch($(this).data("type")) {
                case "":
                case "text":
                case "select":
                    return true;
            };
        }).find('input[type=checkbox]').prop("checked", true);
    });
    
    $(".wpjb-export-check-all").click(function(e) {
        e.preventDefault();
        $(this).closest("table").find("tbody tr input[type=checkbox]").prop("checked", true);
    });
    
    $(".wpjb-export-uncheck-all").click(function(e) {
        e.preventDefault();
        $(this).closest("table").find("tbody tr input[type=checkbox]").prop("checked", false);
    });
   
    $(".wpjb-export-button-xml").click(function(e) {
        e.preventDefault();

        $(this).hide();
        $(".wpjb-export .spinner").css("visibility", "visible");

        var objects = $(".wpjb-export-xml .wpjb-table-toggle:checkbox:checked").map(function(){
              return $(this).val();
        }).get();
        
        var data = {};
        $.each(WpjbExportParams, function(index, item) {
            data[index] = item;
        });
        data.format = 'xml';
        data.objects = objects;

        $(".wpjb-export-xml .wpjb-table-toggle:checkbox").attr("disabled", "disabled");

        $.ajax({
            type: "POST",
            data: data,
            url: ajaxurl,
            dataType: "json",
            success: function(response) {
                $(".wpjb-export .wpjb-export-progress").fadeIn("fast");
                $(".wpjb-export-stat-estimated").text(response.count);
               
                wpjb_export_xml_run(response.name)
            }
        });
    });
    
    $(".wpjb-export-button-csv").click(function(e) {
        e.preventDefault();

        $(this).hide();
        $(".wpjb-export .spinner").css("visibility", "visible");

        var objects = $(".wpjb-export-csv .wpjb-table-toggle:checkbox:checked").map(function(){
              return $(this).val();
        }).get();
        
        var fields = $(".wpjb-export-csv .wpjb-export-csv-field:checkbox:checked").map(function(){
              return $(this).val();
        }).get();
        
        var data = {};
        $.each(WpjbExportParams, function(index, item) {
            data[index] = item;
        });
        data.format = 'csv';
        data.objects = objects;
        data.fields = fields;

        $(".wpjb-export-xml .wpjb-table-toggle:checkbox").attr("disabled", "disabled");

        $.ajax({
            type: "POST",
            data: data,
            url: ajaxurl,
            dataType: "json",
            success: function(response) {
                $(".wpjb-export .wpjb-export-progress").fadeIn("fast");
                $(".wpjb-export-stat-estimated").text(response.count);
               
                wpjb_export_csv_run(response.name)
            }
        });
    });
    
    function wpjb_export_xml_run(name) {
        var data = { 
            action: 'wpjb_export_xml',
            name: name
        };
        
        wpjb_export_run(data);
    }
    
    function wpjb_export_csv_run(name) {
        var data = { 
            action: 'wpjb_export_csv',
            name: name
        };
        
        wpjb_export_run(data);
    }
    
    function wpjb_export_run(data) {
        $.ajax({
            type: "POST",
            data: data,
            url: ajaxurl,
            dataType: "json",
            success: function(response) {
                if(response.todo > 0) {
                    $(".wpjb-export-stat-current").text(response.done);
                    $(".wpjb-export-stat-estimated").text(response.count);
                    if(this.data.indexOf("csv") > 0) {
                        wpjb_export_csv_run(response.name);
                    } else {
                        wpjb_export_xml_run(response.name);
                    }
                } else if(response.download == "push") {
                    
                    var url = ajaxurl + "?action=wpjb_export_download&name=" + response.name;
                    
                    $(".wpjb-export-stat-current").text(response.done);
                    $(".wpjb-export-stat-estimated").text(response.count);
                    
                    $(".wpjb-export-button-download").show();
                    $(".wpjb-export-button-download").attr("href", url);
                    
                    $(".wpjb-export .spinner").css("visibility", "hidden");
                    
                } else if(response.download == "direct") {
                    
                } else {
                    alert("Error occured while exporting. Please try again.");
                }
            }
        });
    }
   
    $(".wpjb-table-toggle").change();
});
