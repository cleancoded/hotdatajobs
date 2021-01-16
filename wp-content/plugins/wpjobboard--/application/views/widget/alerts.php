<p>
    <label for="<?php echo $widget->get_field_id("title") ?>">
    <?php _e("Title", "wpjobboard") ?>
    <?php Daq_Helper_Html::build("input", array(
        "id" => $widget->get_field_id("title"),
        "name" => $widget->get_field_name("title"),
        "value" => $instance["title"],
        "type" => "text",
        "class"=> "widefat",
        "maxlength" => 100
    )); 
    ?>
   </label>
</p>

<p>
   <label for="<?php echo $widget->get_field_id("hide") ?>">
   <?php _e("Show on job board only", "wpjobboard") ?>
   <?php Daq_Helper_Html::build("input", array(
       "id" => $widget->get_field_id("hide"),
       "name" => $widget->get_field_name("hide"),
       "checked" => (int)$instance["hide"],
       "value" => 1,
       "type" => "checkbox",
       "class"=> ""
   )); 
   ?>
   </label>
</p>

<p>
   <label for="<?php echo $widget->get_field_id("smart") ?>">
   <?php _e("Enable smart alerts", "wpjobboard") ?>
   <?php Daq_Helper_Html::build("input", array(
       "id" => $widget->get_field_id("smart"),
       "name" => $widget->get_field_name("smart"),
       "checked" => (int)$instance["smart"],
       "value" => 1,
       "type" => "checkbox",
       "class"=> ""
   )); 
   ?>
   </label>
</p>
