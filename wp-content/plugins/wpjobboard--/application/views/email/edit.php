<div class="wrap wpjb">
    
    <?php wp_enqueue_script("wpjb-admin-config-email") ?>
    <?php wp_enqueue_style("wp-jquery-ui-dialog") ?>
    
    <?php wp_enqueue_script("wpjb-multi-level-accordion-menu") ?>
    <?php wp_enqueue_style("wpjb-multi-level-accordion-menu") ?>
    
    <h1>
        <?php _e("Edit Email Template | ID: ", "wpjobboard"); echo $form->getObject()->id; ?> 
        <a class="add-new-h2" href="<?php esc_attr_e(wpjb_admin_url("email")) ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a> 
    </h1>
    
    <?php $this->_include("flash.php"); ?>

<form action="" method="post" class="wpjb-form">

    <?php echo daq_form_layout_config($form) ?>

    <p class="submit">
    <input type="submit" value="<?php _e("Save Changes", "wpjobboard") ?>" class="button-primary button" name="Submit"/>
    </p>

</form>

    
<div id="wpjb-email-html-preview-dialog" style="display:none" title="<?php _e("Preview", "wpjobboard") ?>">
    <div id="wpjb-email-html-preview-preloader" class="wpjb-email-preview-preloader"></div>
    <div class="wpjb-email-frame-tools" style="height:605px">

        <span class="wpjb-email-preview-desktop dashicons dashicons-desktop active"></span>
        <span class="wpjb-email-preview-mobile dashicons dashicons-smartphone"></span>
        <span class="wpjb-email-preview-download dashicons dashicons-download"></span>
        <span class="wpjb-email-preview-close dashicons dashicons-no"></span>
        
        <div id="wpjb-email-frame-wrap" style="height:555px">
            <iframe  id="wpjb-email-html-preview-iframe" class="wpjb-email-preview-iframe" style="width:100%;height:100%"></iframe>
        </div>
    </div>
    
</div>
    
<div id="wpjb-email-advanced-preview-dialog" style="display:none" title="<?php _e("Preview", "wpjobboard") ?>">
    <div id="wpjb-email-html-preview-preloader" class="wpjb-email-preview-preloader"></div>
    <div class="wpjb-email-frame-tools" style="height:605px">

        <span class="wpjb-email-preview-desktop dashicons dashicons-desktop active"></span>
        <span class="wpjb-email-preview-mobile dashicons dashicons-smartphone"></span>
        <span class="wpjb-email-preview-download dashicons dashicons-download"></span>
        <span class="wpjb-email-preview-close dashicons dashicons-no"></span>
        
        <div id="wpjb-email-frame-wrap" style="height:555px">
            <iframe  id="wpjb-email-html-preview-iframe" class="wpjb-email-preview-iframe" style="width:100%;height:100%"></iframe>
        </div>
    </div>
    
</div>
    
<div id="wpjb-email-text-preview-dialog" style="display:none" title="<?php _e("Preview", "wpjobboard") ?>">
    <div id="wpjb-email-html-preview-preloader" class="wpjb-email-preview-preloader"></div>
    <div class="wpjb-email-frame-tools" style="height:605px">

        <span class="wpjb-email-preview-desktop dashicons dashicons-desktop active"></span>
        <span class="wpjb-email-preview-mobile dashicons dashicons-smartphone"></span>
        <span class="wpjb-email-preview-close dashicons dashicons-no"></span>
        
        <div id="wpjb-email-frame-wrap" style="height:555px">
            <iframe  id="wpjb-email-html-preview-iframe" class="wpjb-email-preview-iframe" style="width:100%;height:100%"></iframe>
        </div>
    </div>
    
</div>
    
</div>

<style type="text/css">
    #mail_body_text {
        width: 610px;
        height: 300px;
    }
    
    #mail_body_html_advanced {
        width:610px;
        height:500px;
            border: 1px solid #e5e5e5;
    -webkit-box-shadow: 0 1px 1px rgba(0,0,0,0.04);
    box-shadow: 0 1px 1px rgba(0,0,0,0.04);
    }
    
    #wp-mail_body_html-wrap {
        width: 610px; 
    }
    
    
    .wpjb-preview-dialog .ui-dialog-content {
        padding:0;
    }
    .wpjb-preview-dialog .ui-dialog-titlebar {
        display: none;
    }
    
    .wpjb-variable-dialog .ui-dialog-content {
        background-color: #4d5158;
        padding: 0;
    }
    
    .wpjb-variable-dialog .ui-dialog-titlebar {
        color: white;
        background-color: #4d5158;
        border-bottom: 1px solid #35383d;
    }
    
 
</style>


<div id="wpjb-email-variable-dialog" style="display:none" title="<?php _e("Variables", "wpjobboard") ?>">
<div id="wpjb-mail-var-wrap">
<ul class="cd-accordion-menu animated">
<?php foreach($vars as $i => $var): ?>
<?php if(!in_array($var["var"], $objects)) continue; ?>
    <li class="has-children">
        
        <input type="checkbox" name ="group-<?php echo $i ?>" id="group-<?php echo $i ?>">
        <label for="group-<?php echo $i ?>"><?php esc_html_e($var["title"]) ?></label>

        <?php foreach($var["item"] as $type => $item): ?>
        <?php if(!isset($item["data"]) || empty($item["data"])) continue; ?>
        <ul>
            <li class="has-children">
                <input type="checkbox" name ="sub-group-<?php echo esc_html($type."-".$i) ?>" id="sub-group-<?php echo esc_html($type."-".$i) ?>">
                <label for="sub-group-<?php echo esc_html($type."-".$i) ?>"><?php echo esc_html($item["header"]) ?></label>
                
                <ul>
                <?php foreach($item["data"] as $k => $v): ?>
                    <?php if($type == "meta"): ?>
                    <li>
                        <input type="checkbox" name ="sub-group-meta-<?php echo esc_html($k."-".$i) ?>" id="sub-group-meta-<?php echo esc_html($k."-".$i) ?>">
                        <label for="sub-group-meta-<?php echo esc_html($k."-".$i) ?>"><?php echo esc_html($v["title"]) ?></label>

                        <ul>
                        <?php foreach($meta_inner as $m => $t): ?>
                            <li>
                                <a href="#" class="wpjb-mail-var-insert">
                                    <?php echo esc_html($t) ?>
                                    <span class="wpjb-mail-var">{$<?php echo $var["var"].".meta.".$k.".".$m ?>}</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php elseif($type == "tag"): ?>
                    <li>
                        <input type="checkbox" name ="sub-group-tag-<?php echo esc_html($k."-".$i) ?>" id="sub-group-tag-<?php echo esc_html($k."-".$i) ?>">
                        <label for="sub-group-tag-<?php echo esc_html($k."-".$i) ?>"><?php echo esc_html($v["title"]) ?></label>

                        <ul>

                        <?php foreach($tag_inner as $m => $t): ?>
                        <li>
                            <a href="#" class="wpjb-mail-var-insert">
                                <?php echo esc_html($t) ?>
                                <span class="wpjb-mail-var">{$<?php echo $var["var"].".tag.".$k.".0.".$m ?>}</span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php elseif($type == "file"): ?>
                    <li>
                        <input type="checkbox" name ="sub-group-file-<?php echo esc_html($k."-".$i) ?>" id="sub-group-file-<?php echo esc_html($k."-".$i) ?>">
                        <label for="sub-group-file-<?php echo esc_html($k."-".$i) ?>"><?php echo esc_html($v["title"]) ?></label>

                        <ul>

                        <?php foreach($file_inner as $m => $t): ?>
                        <li>
                            <a href="#" class="wpjb-mail-var-insert">
                                <?php echo esc_html($t) ?>
                                <span class="wpjb-mail-var">{$<?php echo $var["var"].".file.".$k.".0.".$m ?>}</span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php elseif($type == "user"): ?>
                    <li>
                        <?php $tpl = isset($v["var"]) ? $v["var"] : '{$%s.user.%s}' ?>
                        <a href="#" class="wpjb-mail-var-insert">
                            <?php echo esc_html($v["title"]) ?>
                            <span class="wpjb-mail-var"><?php echo sprintf($tpl, $var["var"], $k) ?></span>
                        </a>
                    </li>
                    <?php else: ?>
                        <?php $tpl = isset($v["var"]) ? $v["var"] : '{$%s.%s}' ?>
                        <li>
                            <a href="#" class="wpjb-mail-var-insert">
                                <?php echo esc_html($v["title"]) ?>
                                <span class="wpjb-mail-var"><?php echo sprintf($tpl, $var["var"], $k) ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                </ul>
            </li>
        </ul>
        <?php endforeach; ?>
    </li>
<?php endforeach; ?>
<?php if($customs): ?>
    <li class="has-children">
        
        <input type="checkbox" name ="group-customs" id="group-customs">
        <label for="group-customs"><?php esc_html_e("Other", "wpjobboard") ?></label>
    
        <ul>
        <?php foreach($customs as $k => $v): ?>
        <?php $ctitle = (is_array($v) ? $v["title"] : $v) ?>
        <?php $cname = (is_array($v) ? $v["name"] : $k ) ?>
            <li>
                <a href="#" class="wpjb-mail-var-insert">
                    <?php echo esc_html($ctitle) ?>
                    <span class="wpjb-mail-var"><?php echo sprintf('{$%s}', $cname) ?></span>
                </a>
            </li>
        <?php endforeach; ?>    
        </ul>
    </li>

<?php endif; ?>
</ul>
</div>
</div>

