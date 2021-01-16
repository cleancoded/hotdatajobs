<?php

function daq_form_layout(Daq_Form_Abstract $form, $options = array()) {
    
    extract($options);
    if(isset($exclude_fields)) {
        $exclude_fields = explode(",", $exclude_fields);
        $exclude_fields = array_map("trim", $exclude_fields);
    } else {
        $exclude_fields = array();
    }
    
    if(isset($exclude_groups)) {
        $exclude_groups = explode(",", $exclude_groups);
        $exclude_groups = array_map("trim", $exclude_groups);
    } else {
        $exclude_groups = array();
    }
?>

<?php echo $form->renderHidden() ?>
<?php foreach($form->getReordered() as $group): ?>

<?php if(count($group->getReordered()) < 1) continue; ?>
<?php if(in_array($group->getName(), $exclude_groups)) continue; ?>
<?php if(!$group->hasVisibleElements($exclude_fields)) continue; ?>

<div class="postbox wpjb-form-postbox wpjb-namediv <?php echo "wpjb-form-group-".$group->getName() ?>" >
    <h3><?php esc_html_e($group->title) ?></h3>
    <div class="inside">
        <table class="form-table wpjb-form-table">
            <tbody>
            <?php foreach($group->getReordered() as $field): ?>
            <?php /* @var $field Daq_Form_Element */ ?>
            <?php if(in_array($field->getName(), $exclude_fields)) continue; ?>
                <tr valign="top" class="<?php if($field->hasErrors()): ?>error<?php endif; ?>">
                <th class="wpjb-td-first" valign="top">
                    <label for="<?php esc_attr_e($field->getName()) ?>">
                        <?php echo esc_html($field->getLabel()) ?>
                        <?php if($field->isRequired()): ?>
                        <span class="wpjb-red">*</span>
                        <?php endif; ?>
                    </label>
                </th>
                <td>
                    <?php if(!$field->hasRenderer()): ?>
                    <?php echo $field; ?>
                    <?php else: ?>
                    <?php echo call_user_func($field->getRenderer(), $field, $form); ?>
                    <?php endif; ?>

                    <?php if($field->getHint()): ?>
                    <span class="wpjb-description"><?php echo $field->getHint() ?></span>
                    <?php endif ?>
                    
                    <?php if($field->hasErrors()): ?>
                    <ul class="updated wpjb-error-list">
                        <?php foreach($field->getErrors() as $error): ?>
                        <li><?php esc_html_e($error) ?></li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>


<?php
    
}

function daq_form_layout_config(Daq_Form_Abstract $form, $options = array()) {
    
    extract($options);
    if(isset($exclude_fields)) {
        $exclude_fields = explode(",", $exclude_fields);
        $exclude_fields = array_map("trim", $exclude_fields);
    } else {
        $exclude_fields = array();
    }
    if(isset($exclude_groups)) {
        $exclude_groups = explode(",", $exclude_groups);
        $exclude_groups = array_map("trim", $exclude_groups);
    } else {
        $exclude_groups = array();
    }
    
    $a = array(Daq_Form_Element::TYPE_CHECKBOX, Daq_Form_Element::TYPE_RADIO);
    ob_start();
?>
<table class="form-table" style="max-width:900px">
<tbody>
<?php echo $form->renderHidden() ?>
<?php foreach($form->getReordered() as $group): ?>
<?php if(in_array($group->getName(), $exclude_groups)) continue; ?>
<?php if($group->title): ?>    
        
<tr valign="top">
    <th colspan="2" style="padding-bottom:0px">
        <h3 style="border-bottom:1px solid #dfdfdf; line-height:1.4em; font-size:15px"><?php echo $group->title ?></h3>
    </th>
</tr>

<?php endif; ?>
<?php foreach($group->getReordered() as $field): ?>
<?php if($field->getType() == Daq_Form_Element::TYPE_TEXT) $field->addClass("regular-text") ?>
    
<tr valign="top" class="<?php if($field->hasErrors()): ?>error<?php endif ?>">
    <th scope="row">
        <label <?php if(!in_array($field->getType(), $a)): ?>for="<?php esc_html_e($field->getName()) ?>"<?php endif ?>>
            <?php echo $field->getLabel() ?>
            <?php if($field->isRequired()): ?><span class="wpjb-red">&nbsp;*</span><?php endif; ?>
        </label>
    </th>
    <td>
        <?php if(!$field->hasRenderer()): ?>
        <?php echo $field; ?>
        <?php else: ?>
        <?php echo call_user_func($field->getRenderer(), $field, $form); ?>
        <?php endif; ?>
        
        <?php if($field->getHint()): ?>
        <br/><span class="description"><?php echo $field->getHint() ?></span>
        <?php endif ?>
        
        <?php if($field->hasErrors()): ?>
        <ul class="updated wpjb-error-list">
            <li><strong><?php _e("Following errors occured:", "wpjobboard") ?></strong></li>
            <?php foreach($field->getErrors() as $err): ?>
            <li><?php esc_html_e($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </td>
</tr>

<?php endforeach; ?>
<?php endforeach; ?>
</tbody>
</table>
<?php

    return ob_get_clean();

}

function wpjb_form_resume_preview(Daq_Form_Abstract $form, $options = array()) {
    
    extract($options);
    if(isset($exclude_fields)) {
        $exclude_fields = explode(",", $exclude_fields);
        $exclude_fields = array_map("trim", $exclude_fields);
    } else {
        $exclude_fields = array();
    }
    
    if(isset($exclude_groups)) {
        $exclude_groups = explode(",", $exclude_groups);
        $exclude_groups = array_map("trim", $exclude_groups);
    } else {
        $exclude_groups = array();
    }
?>

<?php echo $form->renderHidden() ?>
<?php foreach($form->getReordered() as $group): ?>

<?php if($group->getName() == "experience"): ?>

    <fieldset>
        <div class="postbox wpjb-namediv <?php echo "wpjb-form-group-".$group->getName() ?>" >
            <h3>
                <?php esc_html_e($group->title) ?>
                &nbsp;
                <a class="wpjb-myresume-detail-add" data-detail="<?php echo $group->getName() ?>" data-form="Wpjb_Form_Resumes_Experience" data-before="wpjb-fieldset-null-<?php echo $group->getName() ?>" data-template="wpjb-utpl-experience" href="#">(<?php _e("Add Experience", "wpjobboard") ?>)</a>
            </h3>
            <div class="inside">
                <div id="wpjb-fieldset-null-<?php echo $group->getName() ?>" class="wpjb-fieldset-null">
                    <a class="button wpjb-button wpjb-glyphs wpjb-icon-plus"><?php _e("Add Experience", "wpjobboard") ?></a>
                </div>
            </div>

        </div>
    </fieldset>
    <?php continue; ?>

<?php elseif($group->getName() == "education"): ?>

    <fieldset>
    <div class="postbox wpjb-namediv <?php echo "wpjb-form-group-".$group->getName() ?>" >
        <h3>
            <?php esc_html_e($group->title) ?>
            &nbsp;
            <a  class="wpjb-myresume-detail-add" data-detail="<?php echo $group->getName() ?>" data-form="Wpjb_Form_Resumes_Education" data-before="wpjb-fieldset-null-<?php echo $group->getName() ?>" data-template="wpjb-utpl-education"  href="#">(<?php _e("Add Education", "wpjobboard") ?>)</a>
        </h3>

        <div class="inside">
            <div id="wpjb-fieldset-null-<?php echo $group->getName() ?>" class="wpjb-fieldset-null">
                <a class="button wpjb-button wpjb-glyphs wpjb-icon-plus"><?php _e("Add Education", "wpjobboard") ?></a>
            </div>
        </div>

    </div>
    </fieldset>
    <?php continue; ?>

<?php else: ?>

<?php if(count($group->getReordered()) < 1) continue; ?>
<?php if(in_array($group->getName(), $exclude_groups)) continue; ?>
<?php if(!$group->hasVisibleElements($exclude_fields)) continue; ?>

<div class="postbox wpjb-form-postbox wpjb-namediv <?php echo "wpjb-form-group-".$group->getName() ?>" >
    <h3><?php esc_html_e($group->title) ?></h3>
    <div class="inside">
        <table class="form-table wpjb-form-table">
            <tbody>
            <?php foreach($group->getReordered() as $field): ?>
            <?php /* @var $field Daq_Form_Element */ ?>
            <?php if(in_array($field->getName(), $exclude_fields)) continue; ?>
                <tr valign="top" class="<?php if($field->hasErrors()): ?>error<?php endif; ?>">
                <th class="wpjb-td-first" valign="top">
                    <label for="<?php esc_attr_e($field->getName()) ?>">
                        <?php esc_html_e($field->getLabel()) ?>
                        <?php if($field->isRequired()): ?>
                        <span class="wpjb-red">*</span>
                        <?php endif; ?>
                    </label>
                </th>
                <td>
                    <?php if(!$field->hasRenderer()): ?>
                    <?php echo $field; ?>
                    <?php else: ?>
                    <?php echo call_user_func($field->getRenderer(), $field, $form); ?>
                    <?php endif; ?>

                    <?php if($field->getHint()): ?>
                    <br/><span class="wpjb-description"><?php echo $field->getHint() ?></span>
                    <?php endif ?>
                    
                    <?php if($field->hasErrors()): ?>
                    <ul class="updated wpjb-error-list">
                        <?php foreach($field->getErrors() as $error): ?>
                        <li><?php esc_html_e($error) ?></li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            
            
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php endforeach; ?>


<?php
    

    
}


function wpjb_admin_pricing_render($e) {
    
    $value = $e->getValue();
    $options = $e->getOptions();
    
    if(empty($options)) {
        $pfor = str_replace("items_", "", $e->getName());
        if($pfor == Wpjb_Model_Pricing::PRICE_SINGLE_JOB) {
            $listing = "single-job";
        } else {
            $listing = "single-resume";
        }
        
        Daq_Helper_Html::build("a", array(
            "class" => "button",
            "href" => wpjb_admin_url("pricing", "add", null, array("listing"=>$listing))
        ), sprintf(__("Add New '%s' Option", "wpjobboard"), $e->getLabel()));
        return;
    }
    
    foreach($e->getOptions() as $option) {
        
        $pricing = new Wpjb_Model_Pricing($option["key"]);
        $param = array("ID: ".$pricing->id);
        
        if($pricing->meta->is_featured->value()) {
            $param[] = __("Featured", "wpjobboard");
        }
        if($pricing->meta->visible->value()) {
            $param[] = sprintf(__("Days Visible: %d", "wpjobboard"), $pricing->meta->visible->value());
        }
        
        $opt = array("status"=>"disabled", "usage"=>"");
        if(isset($value[$pricing->id])) {
            $opt = $value[$pricing->id];
        }
        
        ?>
        <select name="<?php esc_attr_e($e->getName()) ?>[<?php echo $pricing->id ?>][status]" class="wpjb-membership-usage">
            <option value="disabled" <?php selected($opt["status"], "disabled") ?>><?php _e("Not Included", "wpjobboard") ?></option>
            <option value="limited" <?php selected($opt["status"], "limited") ?>><?php _e("Limited", "wpjobboard") ?></option>
            <option value="unlimited" <?php selected($opt["status"], "unlimited") ?>><?php _e("Unlimited", "wpjobboard") ?></option>
        </select>
        <input type="text" name="<?php esc_attr_e($e->getName()) ?>[<?php echo $pricing->id ?>][usage]" size="4" value="<?php esc_attr_e($opt["usage"]) ?>" />
        <label class="wpjb-membership-usage-label">
            <strong><?php _e($pricing->title) ?></strong>
            <?php if(!empty($param)): ?>(<?php echo join(", ", $param) ?>)<?php endif; ?>
        </label>
        
        <br/>
        <?php
    }
}

function wpjb_admin_membership_render($e, $form) {
    
    $value = $e->getValue();
    $mlist = $form->getMembership();
    
    foreach($e->getOptions() as $option) {
        
        $pricing = new Wpjb_Model_Pricing($option["key"]);
        $param = array("ID: ".$pricing->id);
        $mdata = null;
        
        if(isset($mlist[$pricing->price_for][$pricing->id])) {
            $mdata = $mlist[$pricing->price_for][$pricing->id];
        }
        
        if($pricing->meta->is_featured->value()) {
            $param[] = __("Featured", "wpjobboard");
        }
        if($pricing->meta->visible->value()) {
            $param[] = sprintf(__("Days Visible: %d", "wpjobboard"), $pricing->meta->visible->value());
        }
        
        $opt = array("status"=>"disabled", "usage"=>"", "used"=>"");
        if(isset($value[$pricing->id])) {
            $opt = $value[$pricing->id];
        }

        ?>
        <select name="<?php esc_attr_e($e->getName()) ?>[<?php echo $pricing->id ?>][status]" class="wpjb-membership-usage" style="width:auto">
            <option value="disabled" <?php selected($opt["status"], "disabled") ?>><?php _e("Not Included", "wpjobboard") ?></option>
            <option value="limited" <?php selected($opt["status"], "limited") ?>><?php _e("Limited", "wpjobboard") ?></option>
            <option value="unlimited" <?php selected($opt["status"], "unlimited") ?>><?php _e("Unlimited", "wpjobboard") ?></option>
        </select>
        <span>
            <input class="wpjb-membership-usage-input" type="text" name="<?php esc_attr_e($e->getName()) ?>[<?php echo $pricing->id ?>][used]" size="4" value="<?php esc_attr_e($opt["used"]) ?>" /> /
            <input class="wpjb-membership-usage-input" type="text" name="<?php esc_attr_e($e->getName()) ?>[<?php echo $pricing->id ?>][usage]" size="4" value="<?php esc_attr_e($opt["usage"]) ?>" />
        </span>
        <label class="wpjb-membership-usage-label">
            <strong><?php _e($pricing->title) ?></strong>
            <?php if(!empty($param)): ?>(<?php echo join(", ", $param) ?>)<?php endif; ?>
        </label>
        
        <br/>
        <?php
    }
}

function wpjb_admin_variable_renderer($field) {
    
    $job = new Wpjb_Model_Job();
    $job = $job->toArray();
    unset($job["read"]);
    $job["tag"] = array("category", "type");
    
    $vars = array();
    $vars[] = array(
        "var" => "job",
        "title" => __("Job Variable", "wpjobboard"),
        "item" => $job
    );
    
    $objects = (array)$field->getValue();
    $customs = null;
    
    ?>
    
    <?php foreach($objects as $o): ?>
        <input type="hidden" name="<?php esc_attr_e($field->getName()) ?>" value="<?php esc_attr_e($o) ?>" />
    <?php endforeach; ?>
        
    <div id="wpjb-mail-var-wrap">

        <?php foreach($vars as $var): ?>
        <?php if(!in_array($var["var"], $objects)) continue; ?>
        <div class="widget wpjb-mail-var-widget">	
             <div class="widget-top">
                <div class="widget-title-action">
                    <a href="#available-widgets" class="widget-action hide-if-no-js"></a>
                </div>
                <div class="widget-title"><h4><?php esc_html_e($var["title"]) ?></h4></div>
            </div>

            <div class="widget-inside">
                <div class="widget-content" style="">
                    <h3><?php _e("Variables", "wpjobboard") ?></h3>
                    <?php foreach($var["item"] as $k => $v): ?>
                    <?php if(is_array($v)) continue; ?>
                    <p>
                        <attr title="<?php echo ucfirst(str_replace("_", " ", $k)) ?>"><img class="wpjb-mail-var-helper" src="<?php esc_attr_e(plugins_url()."/wpjobboard/public/images/question-white.png") ?>" alt="" /></attr>
                        <span class="wpjb-bulb wpjb-mail-var" title="<?php #_e("Click to insert into template", "wpjobboard") ?>">{$<?php echo $var["var"].".".$k ?>}</span>
                    </p>
                    <?php endforeach; ?>

                    <?php if(!empty($var["item"]["meta"])): ?>
                    <h3><?php _e("Custom Fields", "wpjobboard") ?></h3>
                    <?php foreach($var["item"]["meta"] as $k => $v): ?>
                    <?php foreach(array("name", "title", "value", "values") as $m): ?>
                    <p>
                        <attr title="<?php echo ucfirst(str_replace("_", " ", $k." - ".$m)) ?>"><img class="wpjb-mail-var-helper" src="<?php esc_attr_e(plugins_url()."/wpjobboard/public/images/question-white.png") ?>" alt="" /></attr>
                        <span class="wpjb-bulb wpjb-mail-var" title="<?php #_e("Click to insert into template", "wpjobboard") ?>">{$<?php echo $var["var"].".meta.".$k.".".$m ?>}</span>
                    </p>
                    <?php endforeach; ?>
                    <hr style="width:95%" />
                    <?php endforeach; ?>
                    <?php endif; ?>


                    <?php if(isset($var["item"]["tag"])): ?>
                    <h3><?php _e("Tags (categories and job types)", "wpjobboard") ?></h3>
                    <?php foreach($var["item"]["tag"] as $k): ?>
                    <?php foreach(array("id", "type", "slug", "title") as $t): ?>
                    <p>
                        <attr title="<?php echo ucfirst(str_replace("_", " ", $k)) ?>"><img class="wpjb-mail-var-helper" src="<?php esc_attr_e(plugins_url()."/wpjobboard/public/images/question-white.png") ?>" alt="" /></attr>
                        <span class="wpjb-bulb wpjb-mail-var" title="<?php #_e("Click to insert into template", "wpjobboard") ?>">{$<?php echo $var["var"].".tag.".$k.".0.".$t ?>}</span>
                    </p>
                    <?php endforeach; ?>
                    <hr style="width:95%" />
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if($customs): ?>
        <div class="widget wpjb-mail-var-widget">	
             <div class="widget-top">
                <div class="widget-title-action">
                    <a href="#available-widgets" class="widget-action hide-if-no-js"></a>
                </div>
                <div class="widget-title"><h4><?php _e("Custom Variables", "wpjobboard") ?></h4></div>
            </div>

            <div class="widget-inside">
                <div class="widget-content">
                    <h3><?php _e("Variables", "wpjobboard") ?></h3>
                    <?php foreach($customs as $k => $v): ?>
                    <p>
                        <attr title="<?php echo $v ?>"><img class="wpjb-mail-var-helper" src="<?php esc_attr_e(plugins_url()."/wpjobboard/public/images/question-white.png") ?>" alt="" /></attr>
                        <span class="wpjb-bulb wpjb-mail-var" title="<?php #_e("Click to insert into template", "wpjobboard") ?>">{$<?php echo $k ?>}</span>
                    </p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
        
    <script type="text/javascript">
    jQuery(".widget-top").click(function() {
        jQuery(this).closest("div.widget").find(".widget-inside").toggle();
        return false;
    });
    </script>
    <?php
}

function wpjb_export_ui_match_data($callback) {
    $screen = new Wpjb_Utility_ScreenOptions;
    $data = $screen->$callback();
    $form = $data["form"];
    
    $fields = array();
    foreach($data["fields"] as $k => $f) {
        
        if(stripos($k, "__") === 0) {
            continue;
        }
        
        $fields[$k] = array("label"=>$f["label"], "type"=>null);
    }
    
    $form = new $form();
    $groups = $form->getReordered();
    foreach($groups as $group) {
        foreach($group->getReordered() as $field) {

            if(!isset($fields[$field->getName()])) {
                $fields[$field->getName()] = array("label" => $field->getLabel(), "type"=>$field->getType());
            } else {
                $fields[$field->getName()]["type"] = $field->getType();
            }
        }
    }
    
    return $fields;
}

function wpjb_export_ui($action, $export) {
    
    $request = Daq_Request::getInstance();
    $params = array("action" => $action);
    
    foreach(array("filter", "query", "posted", "employer") as $p) {
        if($request->get($p)) {
            $params[$p] = $request->get($p);
        }
    }
    
    ?>

    <script type="text/javascript">
        var WpjbExportParams = <?php echo json_encode($params) ?>;
    </script>
    <style type="text/css">
        .wpjb-export-step {
            display: none;
        }
        
        .wpjb-export-step-1 {
            font-size: 2em;
            text-align: center;
        }
        .wpjb-export-step-1 h4 {
            font-size: 1.5em;
        }
        

        .wpjb-export-step-2.wpjb-export-xml .wpjb-export-xml-hide {
            visibility: hidden !important;
        }
        .wpjb-export-step-2.wpjb-export-xml .wpjb-export-xml-none {
            display: none !important;
            
        }
        
        .wpjb-export-progress {
            display: none;
            line-height: 60px;
            font-size: 15px;
        }
        
        .wpjb-export .ajax-loading {
            line-height: 60px;
            padding-right: 10px;
        }
       
        
    </style>
    
    <div id="" class="wpjb-modal-window wpjb-export" style="display: none">
        <div class="media-modal wp-core-ui">
            <a class="media-modal-close wpjb-modal-window-toggle" href="#" title="<?php _e( "Close", "wpjobboard" ) ?>"><span class="media-modal-icon"></span></a>
            <div class="media-modal-content">
                <div class="media-frame wp-core-ui2 hide-menu">

                    <div class="media-frame-title">
                        <h1><?php _e( "Export Manager", "wpjobboard" ) ?></h1>
                        <!-- 1 of 4 selected -->
                    </div>

                    <div class="media-frame-content"> 

                        <div class="wrap" style="padding:0px 20px">

                            <div class="wpjb-export-step wpjb-export-step-1">
                                <h4><?php _e("Select Export Format", "wpjobboard") ?></h4>
                                <div>
                                    <a href="#" class="wpjb-export-xml"><?php _e("XML", "wpjobboard") ?></a>
                                    —
                                    <a href="#" class="wpjb-export-csv"><?php _e("CSV / Excel", "wpjobboard") ?></a>
                                </div>
                            </div>
                            
                            <div class="wpjb-export-step wpjb-export-step-2">
                                <form method="post" action="" class="wpjb-export-form">
                                    <?php $j = 0; ?>
                                    <?php foreach($export as $eKey => $eData): ?>
                                    
                                    <?php if($j == 0): ?>
                                    <h3><?php _e("Data To Export", "wpjobboard") ?></h3>
                                    <?php elseif($j == 1): ?>
                                    <h3><?php _e("Include Additional Data", "wpjobboard") ?></h3>
                                    <?php endif; ?>
                                        
                                    
                                    <table cellspacing="0" class="widefat post fixed">
                                        <thead>
                                            <tr>
                                                <th style="width: 2.2em; padding: 10px 0 0 3px; vertical-align: top" class="manage-column column-cb check-columnx" scope="col">
                                                    <?php if($j==0): ?>
                                                    <input type="hidden" name="object[]" value="<?php esc_html_e($eKey) ?>" />
                                                    <?php endif; ?>
                                                    <input type="checkbox" class="wpjb-table-toggle" <?php if($j==0): ?>disabled="disabled"<?php endif; ?> name="object[]" <?php checked($eData["checked"]) ?> value="<?php esc_html_e($eKey) ?>" />
                                                </th>
                                                <th style="" class="" scope="col">
                                                    <span style="font-size:1.3em; font-weight: strong"><?php esc_html_e($eData["title"]) ?></span>
                                                </th>
                                                <th style="" class="wpjb-export-xml-hide" scope="col"><?php _e("CSV Column Name", "wpjobboard") ?></th>
                                                <th style="" class="wpjb-export-xml-hide" scope="col"><?php _e("Field Type", "wpjobboard") ?></th>
                                            </tr>
                                            <tr class="wpjb-export-thead-actions wpjb-export-xml-none">
                                                <th></th>
                                                <th colspan="3" >
                                                    <a href="#" class="wpjb-export-check-basic button-secondary"><?php _e("Check Basic", "wpjobboard") ?></a>
                                                    <a href="#" class="wpjb-export-check-all button-secondary"><?php _e("Check All", "wpjobboard") ?></a>
                                                    <span style="line-height: 28px; font-size: 22px">/</span>
                                                    <a href="#" class="wpjb-export-uncheck-all button-secondary"><?php _e("Uncheck All", "wpjobboard") ?></a>
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody class="wpjb-export-xml-none">
                                            <?php $i=0; ?>
                                            <?php foreach(wpjb_export_ui_match_data($eData["callback"]) as $k => $f): ?>
                                            <tr data-type="<?php esc_html_e($f["type"]) ?>" valign="top" class="<?php if($i++%2==0): ?>alternate <?php endif; ?>  author-self status-publish iedit">
                                                <th style="width: 2.2em; padding: 10px 0 0 3px; vertical-align: top" class="check-columnx" scope="row">
                                                    <input type="checkbox" class="wpjb-export-csv-field" value="<?php esc_html_e($eKey) ?>.<?php echo $k ?>" name="item[<?php esc_html_e($eKey) ?>][]"/>
                                                </th>
                                                <td class="post-title column-title">
                                                    <strong><?php esc_html_e($f["label"]) ?></strong>
                                                </td>
                                                <td class="">
                                                    <?php if($eData["prefix"]): ?>
                                                    <?php esc_html_e($eData["prefix"]) ?>.
                                                    <?php endif; ?>
                                                    <?php esc_html_e($k) ?>
                                                </td>
                                                <td>
                                                    <?php if($f["type"]!==null): ?>
                                                    <?php esc_html_e(ucfirst($f["type"])) ?>
                                                    <?php else: ?>
                                                    <em><?php _e("Internal", "wpjobboard") ?></em>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>


                                    </table>

                                    <br class="clear" />
                                    <?php $j++; ?>
                                    <?php endforeach; ?>

                                </form>
                            </div>

                        </div>
                    
                    </div>
                    
                    <div class="media-frame-toolbar">
                        <div class="media-toolbar">
                            <div class="media-toolbar-secondary">
                                <a href="#" class="button media-button button-primary button-large media-button-select wpjb-export-button-xml"><?php _e( "Export To XML", "wpjobboard" ) ?></a>
                                <a href="#" class="button media-button button-primary button-large media-button-select wpjb-export-button-csv"><?php _e( "Export To CSV", "wpjobboard" ) ?></a>
                                
                                <a href="#" class="button media-button button-primary button-large media-button-select wpjb-export-button-download"><?php _e("Download", "wpjobboard") ?></a>
                                
                                <span class="wpjb-export-progress">
                                    Exported <span class="wpjb-export-stat-current">0</span> from estimated <span class="wpjb-export-stat-estimated">0</span>.
                                </span>
                                
                                <span class="spinner" style="margin-top: 20px; margin-left: 10px"></span>
                            </div>
                            <div class="media-toolbar-primary">
                                <span class=""></span>
                                <a href="#" class="button media-button button-secondary button-large media-button-select wpjb-modal-window-toggle"><?php _e( "Cancel", "wpjobboard" ) ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="media-modal-backdrop"></div>
    </div><!-- .adverts-modal -->
  
    <?php 
    
    wp_enqueue_script('wpjb-admin-export');
    wp_enqueue_style('wpjb-media-views', includes_url().'/css/media-views.css');
}

function wpjb_form_helper_suggest($field, $form) {
    ?>
<div class="wpjb-inline-section wpjb-inline-suggest">
    <span style="line-height: 28px; padding-left: 7px"><b class="wpjb-inline-label">&nbsp;</b></span>
    <a class="wpjb-inline-edit hide-if-no-js button-secondary" href="#"><?php _e("Edit") ?></a> 
    <div class="wpjb-inline-field wpjb-inline-select hide-if-js">

        <?php echo $field->render(); ?>
        <a href="#" class="wpjb-inline-cancel button-secondary"><?php _e("Cancel", "wpjobboard") ?></a> <br/>
        <span class="description" ><?php _e("start typing user: name, login or email in the box above, some suggestions will appear.", "wpjobboard") ?></span>
    </div>
</div>
<?php
    
}

function wpjb_admin_url($page, $action = null, $id = null, $param = array()) {
    
    $arr = array(
        "page" => "wpjb-".trim($page, "/"),
        "action" => $action,
        "id" => $id
    );

    foreach($param as $k=>$v) {
        if(strlen($v)>0) {
            $arr[$k] = $v; 
        }
    }
    
    $query = http_build_query($arr);
    
    return admin_url("admin.php?$query");
}

function wpjb_form_val($form, $element, $default = null) {
    if(!$form->hasElement($element)) {
        return null;
    }
    
    $v = $form->getElement($element)->getValue();
    
    if(!$v && $default) {
        return $default;
    } else {
        return $v;
    }
}

function wpjb_form_val_e($form, $element, $default = null) {
    echo wpjb_form_val($form, $element, $default);
}

function wpjb_column_sort($sorted, $order) {
    if($sorted) {
        echo " sorted ";
    } else {
        echo " desc ";
        return;
    }

    if($order == "desc") {
        echo " desc ";
    } else {
        echo " asc ";
    }
}

function wpjb_column_order($sorted, $order) {
    if(!$sorted) {
        return "asc";
    }

    if($order == "asc") {
        return "desc";
    } else {
        return "asc";
    }

}

?>
