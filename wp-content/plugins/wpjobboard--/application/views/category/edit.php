<div class="wrap wpjb">
    

    <h1>
        <?php if($form->getObject()->id): ?>
        <?php _e("Edit Job Category | ID: ", "wpjobboard"); echo $form->getObject()->id; ?> 
        <?php else: ?>
        <?php _e("Add Job Category", "wpjobboard"); ?>
        <?php endif; ?>
        <a class="add-new-h2" href="<?php echo wpjb_admin_url("category"); ?>"><?php _e("Go back &raquo;", "wpjobboard") ?></a> 
    </h1>
<?php $this->_include("flash.php"); ?>

<script type="text/javascript">
    Wpjb.Id = <?php echo $form->getObject()->getId() ?>;
</script>

<form action="" method="post" class="wpjb-form">
    <table class="form-table">
        <tbody>
            <?php echo daq_form_layout_config($form) ?>
        </tbody>
    </table>

    <p class="submit">
    <input type="submit" value="<?php _e("Save Changes", "wpjobboard") ?>" class="button-primary button" name="Submit"/>
    </p>

</form>

</div>