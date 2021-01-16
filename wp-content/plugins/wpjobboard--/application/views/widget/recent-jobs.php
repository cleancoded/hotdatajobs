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
    <label for="<?php echo $widget->get_field_id("count") ?>">
    <?php _e("Number of jobs", "wpjobboard") ?>
    <?php Daq_Helper_Html::build("input", array(
        "id" => $widget->get_field_id("count"),
        "name" => $widget->get_field_name("count"),
        "value" => $instance["count"],
        "type" => "text",
        "class"=> "widefat",
        "maxlength" => 100
    )); 
    ?>
   </label>
</p>

<p>
    <label for="<?php echo $widget->get_field_id("query") ?>">
    <?php _e("Keyword", "wpjobboard") ?>
    <?php Daq_Helper_Html::build("input", array(
        "id" => $widget->get_field_id("query"),
        "name" => $widget->get_field_name("query"),
        "value" => isset($instance["query"]) ? $instance["query"] : "",
        "type" => "text",
        "class"=> "widefat",
        "maxlength" => 100
    )); 
    ?>
   </label>
</p>

<p>
    <label for="<?php echo $widget->get_field_id("location") ?>">
    <?php _e("Location", "wpjobboard") ?>
    <?php Daq_Helper_Html::build("input", array(
        "id" => $widget->get_field_id("location"),
        "name" => $widget->get_field_name("location"),
        "value" => isset($instance["location"]) ? $instance["location"] : "",
        "type" => "text",
        "class"=> "widefat",
        "maxlength" => 100
    )); 
    ?>
   </label>
</p>

<p>
   <label for="<?php echo $widget->get_field_id("category") ?>">
   <?php _e("Category", "wpjobboard") ?>
   <?php 
        $field = new Daq_Form_Element_Select($widget->get_field_name("category"));
        $field->addOptions(wpjb_form_get_categories());
        $field->setMaxChoices(100);
        $field->setValue(isset($instance["category"]) ? $instance["category"] : array());
        $field->addClass("widefat");
        echo $field->render(); 
    ?>
   </label>
    <small>(Use Ctrl + click to select more than one category)</small>
</p>

<p>
   <label for="<?php echo $widget->get_field_id("category") ?>">
   <?php _e("Job Type", "wpjobboard") ?>
   <?php 
        $field = new Daq_Form_Element_Select($widget->get_field_name("type"));
        $field->addOptions(wpjb_form_get_jobtypes());
        $field->setMaxChoices(100);
        $field->setValue(isset($instance["type"]) ? $instance["type"] : array());
        $field->addClass("widefat");
        echo $field->render(); 
    ?>
   </label>
    <small>(Use Ctrl + click to select more than one category)</small>
</p>

<p>
   <label for="<?php echo $widget->get_field_id("is_featured") ?>">
   <?php Daq_Helper_Html::build("input", array(
       "id" => $widget->get_field_id("hide"),
       "name" => $widget->get_field_name("is_featured"),
       "checked" => isset($instance["is_featured"]) ? (int)$instance["is_featured"] : null,
       "value" => 1,
       "type" => "checkbox",
       "class"=> ""
   )); 
   ?>
   <?php _e("Show featured jobs only", "wpjobboard") ?>
   </label>
</p>

<p>
   <label for="<?php echo $widget->get_field_id("hide") ?>">
   <?php Daq_Helper_Html::build("input", array(
       "id" => $widget->get_field_id("hide"),
       "name" => $widget->get_field_name("hide"),
       "checked" => (int)$instance["hide"],
       "value" => 1,
       "type" => "checkbox",
       "class"=> ""
   )); 
   ?>
   <?php _e("Show on job board only", "wpjobboard") ?>
   </label>
</p>
