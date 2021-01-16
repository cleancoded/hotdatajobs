<div class="wpjb wpjr-page-register">

    <?php wpjb_flash() ?>

    <form action="" method="post" id="wpjb-resume" class="wpjb-form" enctype="multipart/form-data">
        <?php echo $form->renderHidden() ?>

        <?php foreach($form->getReordered() as $group): ?>
        <?php if($group->isTrashed()) continue; ?>
        
        <?php if($group->getName() == "experience"): ?>
        
            <fieldset class="wpjb-resume-detail wpjb-fieldset-<?php echo $group->getName() ?>">
                <legend>
                    <?php echo esc_html($group->title) ?>
                    &nbsp;
                    <a class="wpjb-myresume-detail-add" data-detail="<?php echo $group->getName() ?>" data-form="Wpjb_Form_Resumes_Experience" data-before="wpjb-fieldset-null-<?php echo $group->getName() ?>" data-template="wpjb-utpl-experience" href="#">(<?php _e("Add Experience", "wpjobboard") ?>)</a>
                </legend>
                
                <div id="wpjb-fieldset-null-<?php echo $group->getName() ?>" class="wpjb-fieldset-null">
                    <a class="wpjb-button wpjb-glyphs wpjb-icon-plus"><?php _e("Add Experience", "wpjobboard") ?></a>
                </div>

            </fieldset>
        
        <?php elseif($group->getName() == "education"): ?>
        
            <fieldset class="wpjb-resume-detail wpjb-fieldset-<?php echo $group->getName() ?>">
                <legend>
                    <?php echo esc_html($group->title) ?>
                    &nbsp;
                    <a  class="wpjb-myresume-detail-add" data-detail="<?php echo $group->getName() ?>" data-form="Wpjb_Form_Resumes_Education" data-before="wpjb-fieldset-null-<?php echo $group->getName() ?>" data-template="wpjb-utpl-education"  href="#">(<?php _e("Add Education", "wpjobboard") ?>)</a>
                </legend>

                
                <div id="wpjb-fieldset-null-<?php echo $group->getName() ?>" class="wpjb-fieldset-null">
                    <a class="wpjb-button wpjb-glyphs wpjb-icon-plus"><?php _e("Add Education", "wpjobboard") ?></a>
                </div>
                
            </fieldset>
        
        <?php else: ?>
        
        <fieldset class="wpjb-fieldset-<?php esc_attr_e($group->getName()) ?>">
            <legend>
                <?php echo esc_html($group->title) ?>
            </legend>
            <?php foreach($group->getReordered() as $name => $field): ?>
            <?php /* @var $field Daq_Form_Element */ ?>
            <div class="<?php wpjb_form_input_features($field) ?>">

                <label class="wpjb-label">
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
            
        <?php endif; ?>
        </fieldset>
        <?php endforeach; ?>
        
        <fieldset style="margin-top: 20px; padding-top: 10px; border-top: 6px double whitesmoke;">
            <legend class="wpjb-empty"></legend>
            <input type="submit" value="<?php _e("Register", "wpjobboard") ?>" class="wpjb-submit" name="Submit"/>
        </fieldset>
    </form>



</div>