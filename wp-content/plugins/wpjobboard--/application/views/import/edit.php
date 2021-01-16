<div class="wrap wpjb">
    
<h1>
    <?php if($form->getObject()->id): ?>
    <?php _e("Edit Import | ID: ", "wpjobboard"); echo $form->getObject()->id; ?> 
    <?php else: ?>
    <?php _e("Schedule Import", "wpjobboard"); ?>
    <?php endif; ?>
    <a class="add-new-h2" href="<?php echo wpjb_admin_url("import"); ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a> 
</h1>
    
<?php $this->_include("flash.php"); ?>

<form action="" method="post" class="wpjb-form">
    <table class="form-table">
        <tbody>
            <?php echo daq_form_layout_config($form) ?>
        </tbody>
    </table>

    <p class="submit">
    
    <?php if(!$form->getId()): ?>
        <input type="submit" value="<?php _e("Schedule", "wpjobboard") ?>" class="button-primary" name="Schedule" />
        <input type="submit" value="<?php _e("Import Once", "wpjobboard") ?>" class="button-secondary" name="Once" />
    <?php else: ?>
        <input type="submit" value="<?php _e("Update Schedule", "wpjobboard") ?>" class="button-primary" name="Schedule" />
    <?php endif; ?>
    </p>

</form>

</div>