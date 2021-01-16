<div class="wrap wpjb">

<h1><?php _e("Add New Candidate", "wpjobboard"); ?> <a class="add-new-h2" href="<?php echo wpjb_admin_url("resumes", "add") ?>"><?php _e("Add New", "wpjobboard") ?></a> </h1>

<?php $this->_include("flash.php"); ?>


<form action="" method="post" enctype="multipart/form-data" class="wpjb-form">
    <div class="metabox-holder has-right-sidebar" id="poststuff" >
            <div id="post-body">
            <div id="post-body-content">


            <?php wpjb_form_resume_preview($form) ?>

            </div>
            <p class="submit">
                <input type="submit" value="<?php _e("Create Candidate", "wpjobboard") ?>" class="button-primary" name="Submit"/>
            </p>
        </div>
    </div>
</form>    
               

</div>