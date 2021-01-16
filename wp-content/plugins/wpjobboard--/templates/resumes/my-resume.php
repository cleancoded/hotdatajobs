<?php /* @var $resume Wpjb_Model_Resume */ ?>
<div class="wpjb wpjr-page-my-resume">

    <?php wpjb_flash() ?>
    <?php wpjb_breadcrumbs($breadcrumbs) ?>

    <form action="" method="post" id="wpjb-resume" class="wpjb-form" enctype="multipart/form-data">
        <?php echo $form->renderHidden() ?>
        <fieldset>
            <legend><?php _e("Resume Information", "wpjobboard") ?></legend>
            <?php if(wpjb_conf("cv_approval") == 1): ?>
            <div>
                <label class="wpjb-label"><?php _e("Resume Status", "wpjobboard") ?></label>
                <span><?php echo wpjb_resume_status($resume) ?></span>
            </div>
            <?php endif; ?>
            <div>
                <label class="wpjb-label"><?php _e("Last Updated", "wpjobboard") ?></label>
                <?php if($resume->modified_at == "0000-00-00 00:00:00" || $resume->id < 1): ?>
                <span><?php _e("Never", "wpjobboard") ?></span>
                <?php else: ?>
                <span><?php echo wpjb_date_display(get_option("date_format"), $resume->modified_at) ?></span>
                <?php endif; ?>
            </div>
        </fieldset>

        <?php foreach($form->getReordered() as $group): ?>
        <?php if($group->isTrashed()) continue; ?>
        
        <?php if($group->getName() == "experience"): ?>
        
            <fieldset class="wpjb-resume-detail wpjb-fieldset-<?php echo $group->getName() ?>">
                <legend>
                    <?php esc_html_e($group->title) ?>
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
                    <?php esc_html_e($group->title) ?>
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
                <?php esc_html_e($group->title) ?>
            </legend>
            <?php foreach($group->getReordered() as $name => $field): ?>
            <?php /* @var $field Daq_Form_Element */ ?>
            <div class="<?php wpjb_form_input_features($field) ?>">

                <label class="wpjb-label">
                    <?php esc_html_e($field->getLabel()) ?>
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
            <input type="submit" value="<?php _e("Update", "wpjobboard") ?>" class="wpjb-submit" name="Submit"/>
        </fieldset>
    </form>



</div>

