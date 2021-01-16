<?php

/**
 * Add job form
 * 
 * Template displays add job form
 * 
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 * 
 */

 /* @var $form Wpjb_Form_AddJob */
 /* @var $can_post boolean User has job posting priviledges */

?>

<div class="wpjb wpjb-page-add">

    <?php if($can_post): ?>
    
    <?php wpjb_flash() ?>
    <?php include $this->getTemplate("job-board", "step") ?>
    
    <form class="wpjb-form" action="<?php esc_attr_e($urls->preview) ?>" method="post" enctype="multipart/form-data">

        <?php echo $form->renderHidden() ?>
        <?php foreach($form->getReordered() as $group): ?>
        
        <?php /* @var $group stdClass */ ?> 
        <fieldset class="wpjb-fieldset-<?php esc_attr_e($group->getName()) ?>">
            <legend><?php esc_html_e($group->title) ?></legend>
            <?php foreach($group->getReordered() as $name => $field): ?>
            <?php /* @var $field Daq_Form_Element */ ?>
            <div class="<?php wpjb_form_input_features($field) ?>">

                <label class="wpjb-label" for="<?php echo esc_attr($field->getName()) ?>">
                    <?php echo esc_html($field->getLabel()) ?>
                    <?php if($field->isRequired()): ?><span class="wpjb-required">*</span><?php endif; ?>
                </label>
                
                <div class="wpjb-field">
                    <?php wpjb_form_render_input($form, $field) ?>
                    <?php wpjb_form_input_hint($field) ?>
                    <?php wpjb_form_input_errors($field) ?>
                </div>

            </div>
            <?php endforeach; ?>
        </fieldset>
        <?php endforeach; ?>
        
        <fieldset>
            <div>
                <legend></legend>
                
                <div style="margin:1em 0 1em 0">
                    <input type="submit" class="wpjb-submit" name="wpjb_preview" id="wpjb_submit" value="<?php _e("Preview", "wpjobboard") ?>" />
                    <?php _e("or", "wpjobboard") ?>
                    <a href="<?php esc_attr_e($urls->reset) ?>"><?php _e("Reset form", "wpjobboard") ?></a>
                </div>
            </div>
        </fieldset>
        
    </form>
    <?php endif; ?>

</div>
