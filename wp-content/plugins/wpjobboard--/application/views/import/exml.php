<div class="wrap wpjb">

<h1>
    <?php _e("XML Export", "wpjobboard") ?> 
</h1>
    
    <div class="wpjb-export-all">

        <table class="widefat post fixed" style="width:320px">
            <thead>
                <tr>
                    <td colspan="2">Data To Export</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Meta</td>
                    <td style="text-align: right"><?php echo $count["meta"] ?></td>
                </tr>
                <tr>
                    <td>Jobs</td>
                    <td style="text-align: right"><?php echo $count["job"] ?></td>
                </tr>
                <tr>
                    <td>Applications</td>
                    <td style="text-align: right"><?php echo $count["application"] ?></td>
                </tr>
                <tr>
                    <td>Companies</td>
                    <td style="text-align: right"><?php echo $count["company"] ?></td>
                </tr>
                <tr>
                    <td>Candidates</td>
                    <td style="text-align: right"><?php echo $count["resume"] ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>TOTAL</td>
                    <th style="text-align: right"><strong><?php echo array_sum($count) ?></strong></th>
                </tr>
            </tfoot>
        </table>

        <p>
            <a href="#" class="wpjb-export-all-run button-primary button"><?php _e("Start Export", "wpjobboard") ?></a>
        </p>

        <h2>
            <span class="wpjb-export-progress">
                Exported <span class="wpjb-export-stat-current">0</span> from estimated <span class="wpjb-export-stat-estimated"><?php echo array_sum($count) ?></span>.
            </span>

            <span class="spinner" style="float:left"></span>
        </h2>

        <p>
            <a href="#" class="wpjb-export-button-download button-primary"><?php _e("Download", "wpjobboard") ?></a>
        </p>
    
    </div>
    
</div>

<style type="text/css">

    .wpjb-export-all .wpjb-export-button-download,
    .wpjb-export-progress {
        display: none;
    }

</style>

<script type="text/javascript">

jQuery(function($) {
    $(".wpjb-export-all-run").click(function(e) {
        e.preventDefault();
        
        $(this).hide();
        $(".wpjb-export-all .spinner").css("visibility", "visible");
        $(".wpjb-export-all .wpjb-export-progress").fadeIn("fast");
        
        $.ajax({
            type: "POST",
            data: {action: 'wpjb_export_all'},
            url: ajaxurl,
            dataType: "json",
            success: function(response) {
                $(".wpjb-export-stat-estimated").text(response.count);
               
                wpjb_export_xml_all_run(response.name)
            }
        });
    });
    
    function wpjb_export_xml_all_run(name) {
        var data = { 
            action: 'wpjb_export_xml',
            name: name
        };

        $.ajax({
            type: "POST",
            data: data,
            url: ajaxurl,
            dataType: "json",
            success: function(response) {
                if(response.todo > 0) {
                    $(".wpjb-export-stat-current").text(response.done);
                    $(".wpjb-export-stat-estimated").text(response.count);
                    wpjb_export_xml_all_run(response.name);
                } else if(response.download == "push") {
                    
                    $(".wpjb-export-progress").hide();
                    $(".wpjb-export-all .spinner").css("visibility", "hidden").hide();

                    $(".wpjb-export-stat-current").text(response.done);
                    $(".wpjb-export-stat-estimated").text(response.count);
                    
                    $(".wpjb-export-button-download").css("display", "inline-block");
                    $(".wpjb-export-button-download").attr("href", response.url);
                    
                } else if(response.download == "direct") {
                    $(".wpjb-export-progress").hide();
                    $(".wpjb-export-all .spinner").css("visibility", "hidden").hide();

                    $(".wpjb-export-stat-current").text(response.done);
                    $(".wpjb-export-stat-estimated").text(response.count);
                    
                    $(".wpjb-export-button-download").css("display", "inline-block");
                    $(".wpjb-export-button-download").attr("href", response.url);
                    
                } else {
                    alert("Error occured while exporting. Please try again.");
                }
            }
        });
    }
});

</script>