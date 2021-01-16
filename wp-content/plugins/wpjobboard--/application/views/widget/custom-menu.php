<?php

    wp_register_script("wpjb-admin-custom-menu", plugins_url()."/wpjobboard/public/js/admin-custom-menu.js", array("jquery"), null, true);
    wp_register_style("wpjb-admin-custom-menu", plugins_url()."/wpjobboard/public/css/admin-custom-menu.css");

    wp_enqueue_style('wpjb-glyphs');
    wp_enqueue_style('wpjb-admin-custom-menu');
    wp_enqueue_script('wpjb-admin-custom-menu');
    wp_enqueue_script('jquery-ui-sortable');


    $links = array(
        "job-board" => array(
            "frontend:step_add" =>  array("title"=>__("Post a Job", "wpjobboard")),
            "frontend:home" =>      array("title"=>__("View Jobs", "wpjobboard")),
            "frontend:search" =>    array("title"=>__("Advanced Search", "wpjobboard")),
            "frontend:employer_new" => array("title"=>__("Employer Registration", "wpjobboard")),
            "frontend:employer_login" => array("title"=>__("Employer Login", "wpjobboard")),
            "frontend:employer_home" => array("title"=>__("Employer Dashboard", "wpjobboard")),
            "frontend:employer_edit" => array("title"=>__("Company Profile", "wpjobboard")),
            "frontend:employer_panel" => array("title"=>__("Company Jobs", "wpjobboard")),
            "frontend:job_applications" => array("title"=>__("Job Applications", "wpjobboard")),
            "frontend:employer_password" => array("title"=>__("Change Password", "wpjobboard")),
            "frontend:employer_delete" => array("title"=>__("Delete Account", "wpjobboard")),
            "frontend:membership" => array("title"=>__("Membership", "wpjobboard")),
            "frontend:employer_logout" => array("title"=>__("Logout", "wpjobboard")),
        ),
        "resumes" => array(
            "resumes:home" => array("title"=>__("Browse Resumes", "wpjobboard")),
            "resumes:advsearch" => array("title"=>__("Search Resumes", "wpjobboard")),
            "resumes:register" => array("title"=>__("Candidate Registration", "wpjobboard")),
            "resumes:login" => array("title"=>__("Candidate Login", "wpjobboard")),
            "resumes:myresume_home" => array("title"=>__("My Dashboard", "wpjobboard")),
            "resumes:myresume" => array("title"=>__("My Resume", "wpjobboard")),
            "resumes:myapplications" => array("title"=>__("My Applications", "wpjobboard")),
            "resumes:mybookmarks" => array("title"=>__("My Bookmarks", "wpjobboard")),
            "resumes:logout" => array("title"=>__("Logout", "wpjobboard")),
        ),
    );

?>

<?php if(!function_exists("wpjb_custom_menu_icon_picker")): ?>
<?php function wpjb_custom_menu_icon_picker() { ?>
<?php 

    $icons = array("");
    $prefix = ".wpjb-icon-";
    $file = file( Wpjb_Project::getInstance()->getBaseDir() . '/public/css/wpjb-glyphs.css');
    
    foreach($file as $line) {
        if(stripos($line, $prefix) === 0) {
            $l = explode(":", $line);
            $icons[] = str_replace($prefix, "", $l[0]);
        }
    }
?>
<div class="wpjb-custom-menu-icon-picker-wrap">
    <input type="text" autocomplete="off" id="wpjb-category-icon-filter" placeholder="<?php _e("Filter Icons ...", "wpjobboard") ?>" />
    <ul class="wpjb-image-icon-picker">
        <?php foreach($icons as $icon): ?>
        <?php $title = ucfirst(str_replace("-", " ", $icon ) ) ?>
        <li data-name="<?php esc_html_e($icon) ?>">
            <a href="#" class="button-secondary" title="<?php esc_html_e( $title ) ?>" data-name="<?php esc_html_e($icon) ?>">
                <span class="wpjb-glyphs <?php esc_html_e(str_replace(".", "", $prefix).$icon) ?>"></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<script id="wpjb-custom-menu-link-template" type="text/x-custom-template">
    <?php if( ! function_exists( "wpjb_custom_menu_template" ) ): ?>
    <?php function wpjb_custom_menu_template( $args = array() ) { 
        
        $defaults = array(
            'menu-item-url' => '',
            'menu-item-key' => '',
            'menu-item-title' => '',
            'menu-item-attr-title' => '',
            'menu-item-classes' => '',
            'menu-item-visibility' => array(),
        );

        $args = wp_parse_args( $args, $defaults );
    ?>
    <li id="" class="menu-item menu-item-depth-0 menu-item-custom menu-item-edit-active">
        <dl class="menu-item-bar">
            <dt class="menu-item-handle ui-sortable-handle">
                <span class="item-title">
                    <span class="wpjb-glyphs wpjb-icon-none"></span>
                    <span class="menu-item-title"></span> 
                    <span class="is-submenu" style="display: none;"></span>
                </span>
                <span class="item-controls">
                        <span class="item-type"></span>
                        <a class="item-edit" id="" title="" href=""><span class="wpjb-glyphs wpjb-icon-down-open"></span></a>
                </span>
            </dt>
        </dl>
        <div class="menu-item-settings" id="menu-item-settings-48" style="display: block; overflow: hidden; width: auto">
            
            <div class="field-url description description-wide" style="width:94%">
                <label for="edit-menu-item-url-48">
                        <p class="wpjb-custom-link-type wpjb-custom-link-type-wpjb link-to-original">
                            <strong>WPJB</strong>
                            
                        </p>
                        <p class="wpjb-custom-link-type wpjb-custom-link-type-page link-to-original">
                            <strong><?php _e("Page") ?></strong>
                        </p>
                        <span class="wpjb-custom-link-type wpjb-custom-link-type-http">
                            <?php _e("URL") ?><br />
                            <input type="text" id="edit-menu-item-url-48" class="widefat code edit-menu-item-url" name="[48][menu-item-url]" value="">
                        </span>
                        
                        <input type="hidden" id="edit-menu-item-key-48" class="menu-item-key" name="[48][menu-item-key]" value="" />
                </label>
            </div>
            
            <p class="field-title description description-thin">
                <label for="edit-menu-item-title-48">
                        <?php _e("Navigation Label") ?>
                        <input type="text" id="edit-menu-item-title-48" class="widefat edit-menu-item-title" name="[48][menu-item-title]" value="">
                </label>
            </p>
            <p class="field-attr-title description description-thin">
                <label for="edit-menu-item-attr-title-48">
                        <?php _e("Title Attribute") ?>
                        <input type="text" id="edit-menu-item-attr-title-48" class="widefat edit-menu-item-attr-title" name="[48][menu-item-attr-title]" value="">
                </label>
            </p>

            <p class="field-css-classes description description-thin">
                <label for="edit-menu-item-classes-48">
                        <?php _e("CSS Classes (optional)") ?>
                        <input type="text" id="edit-menu-item-classes-48" class="widefat code edit-menu-item-classes" name="[48][menu-item-classes]" value="">
                </label>
            </p>
            
            <p class="field-icon description description-thin">
                <label for="edit-menu-item-icon-48">
                        <?php _e("Icon (optional)", "wpjobboard") ?><br/>
                        <a href="#" id="wpjb-icon-picker-48" class="button-secondary wpjb-icon-picker-toggle">
                            <span class="wpjb-glyphs wpjb-icon-picker-preview"></span>
                            <span class="wpjb-glyphs wpjb-icon-down-open wpjb-icon-picker-open"></span>
                        </a>
                        <input type="hidden" id="edit-menu-item-icon-48" class="widefat code edit-menu-item-icon" name="[48][menu-item-icon]" value="">
                </label>
            </p>

            <p class="field-visibility description description-wide">
                <label>
                        <?php _e("Visibility", "wpjobboard") ?><br />
                        <label style="width: 95%; display: inline-block"><input type="checkbox" value="1" class="edit-menu-item-visibility-unregistered" name="[48][menu-item-visibility][unregistered]" /> <?php _e("Unregistered", "wpjobboard") ?></label>
                        <label style="width: 95%; display: inline-block"><input type="checkbox" value="1" class="edit-menu-item-visibility-loggedin" name="[48][menu-item-visibility][loggedin]" /> <?php _e("Logged In", "wpjobboard") ?></label>
                        <label style="width: 95%; display: inline-block"><input type="checkbox" value="1" class="edit-menu-item-visibility-candidate" name="[48][menu-item-visibility][candidate]" /> <?php _e("Candidate", "wpjobboard") ?></label>
                        <label style="width: 95%; display: inline-block"><input type="checkbox" value="1" class="edit-menu-item-visibility-employer" name="[48][menu-item-visibility][employer]" /> <?php _e("Employer", "wpjobboard") ?></label>
                </label>
            </p>
            
            <div class="menu-item-actions description-wide submitbox">
                <a class="item-delete submitdelete deletion" id="delete-48" href="#"><?php _e("Remove") ?></a> 
                <span class="meta-sep hide-if-no-js"> | </span> 
                <a class="item-cancel submitcancel hide-if-no-js" id="cancel-48" href="#"><?php _e("Close") ?></a>
            </div>
    </div>
        <!-- .menu-item-settings-->
        <ul class="menu-item-transport"></ul>
    </li>
    <?php } ?>
    <?php endif; ?>
    <?php wpjb_custom_menu_template() ?>
</script>

<?php } ?>
<?php add_action("admin_footer", "wpjb_custom_menu_icon_picker") ?>
<?php endif; ?>

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

<hr />

<?php

if(empty($instance["structure"])) {
    $max = 0;
} else {
    $max = (int)max((array)array_keys($instance["structure"]));
}

?>

<div class="wpjb-custom-menu" id="<?php esc_attr_e($widget->get_field_id("structure")) ?>" data-name="<?php echo $widget->get_field_name("structure") ?>" data-next-id="<?php echo $max; ?>">
    <div class="wpjb-custom-menu-wrap">
        <a href="#" class="button-secondary wpjb-custom-menu-switch"><?php _e("Add Link", "wpjobboard") ?><span class="wpjb-glyphs wpjb-icon-down-open"></span></a>
        <ul class="wpjb-custom-menu-items">
            <li class="wpjb-custom-menu-item-header"><strong><?php _e("Job Board", "wpjobboard") ?></strong></li>
            <?php foreach($links["job-board"] as $key => $link): ?>
            <li><a href="#" class="wpjb-custom-menu-insert-item" data-insert="<?php esc_attr_e($key) ?>"><?php esc_html_e($link["title"]) ?></a></li>
            <?php endforeach; ?>
            <li class="wpjb-custom-menu-item-header"><strong><?php _e("Resumes", "wpjobboard") ?></strong></li>
            <?php foreach($links["resumes"] as $key => $link): ?>
            <li><a href="#" class="wpjb-custom-menu-insert-item" data-insert="<?php esc_attr_e($key) ?>"><?php esc_html_e($link["title"]) ?></a></li>
            <?php endforeach; ?>
            <li class="wpjb-custom-menu-item-header"><strong><?php _e("Pages") ?></strong></li>
            <?php foreach(get_pages() as $page): ?>
            <li><a href="#" class="wpjb-custom-menu-insert-item" data-insert="<?php esc_attr_e("page:".$page->ID) ?>"><?php esc_html_e($page->post_title) ?></a></li>
            <?php endforeach; ?>

            <li class="wpjb-custom-menu-item-header"><strong><?php _e("Custom URL", "wpjobboard") ?></strong></li>
            <li class="wpjb-custom-menu-item-header">
                <span>
                    <input type="text" value="" class="wpjb-custom-menu-url" placeholder="http://..." />
                    <a href="#" class="button-secondary wpjb-custom-menu-insert-url"><?php _e("Add") ?></a>
                </span>
            </li>
            <li class="wpjb-custom-menu-item-header"><strong><?php _e("Separator", "wpjobboard") ?></strong></li>
            <li>
                <a href="#" class="wpjb-custom-menu-insert-separator" data-insert="separator:x">
                    <div class="wpjb-custom-menu-line-bg"><span>Insert Separator</span></div>
                </a>
            </li>
        </ul>
    </div>

    <ul class="menu ui-sortable wpjb-custom-menu-links" id=""> 	

    </ul>
</div>


<script type="text/javascript">
    <?php if(defined("DOING_AJAX") && DOING_AJAX): ?>
        var wpjb_custom_menu_struct = {
            menu: [],
            item: []
        };
    <?php else: ?>
    if(typeof wpjb_custom_menu_struct == 'undefined') {
        var wpjb_custom_menu_struct = {
            menu: [],
            item: []
        };
    }
    <?php endif; ?>
    
    <?php if($widget->number != "__i__"): ?>
    wpjb_custom_menu_struct.menu.push("<?php esc_attr_e($widget->get_field_id("structure")) ?>");
    <?php endif; ?>
    <?php if(isset($instance["structure"]) && is_array($instance["structure"])) foreach($instance["structure"] as $key => $data): ?>
    wpjb_custom_menu_struct.item.push({
        id: "#<?php esc_attr_e($widget->get_field_id("structure")) ?>",
        data: {
            title: "<?php esc_html_e($data["menu-item-title"]) ?>",
            attr: "<?php esc_html_e($data["menu-item-attr-title"]) ?>",
            insert: "<?php esc_html_e($data["menu-item-key"]) ?>",
            classes: "<?php esc_html_e($data["menu-item-classes"]) ?>",
            icon: "<?php esc_html_e($data["menu-item-icon"]) ?>",
            visibility: <?php echo json_encode( isset($data["menu-item-visibility"]) ? $data["menu-item-visibility"] : array() )  ?>,
            state: "closed"
        }
    });
    <?php endforeach; ?>

    <?php if(defined("DOING_AJAX") && DOING_AJAX): ?>
    jQuery.each(wpjb_custom_menu_struct.menu, function(index, item) {
        wpjb_admin_custom_menu(jQuery, item); 
    });
    jQuery.each(wpjb_custom_menu_struct.item, function(index, item) {
        wpjb_menu_item_add(jQuery(item.id), item.data);
    });
    <?php endif; ?>
</script>
