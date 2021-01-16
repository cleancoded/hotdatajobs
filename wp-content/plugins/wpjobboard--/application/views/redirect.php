<div class="wrap">

    <div class="updated fade hide-if-js">
        <p>
            <?php _e("Object has been saved.", "wpjobboard") ?>
            <a href="<?php esc_attr_e($url) ?>"><?php _e("Edit") ?></a>
        </p>
    </div>

    <script type="text/javascript">
        window.location.href= "<?php echo esc_url_raw($url) ?>";
    </script>
    
</div>