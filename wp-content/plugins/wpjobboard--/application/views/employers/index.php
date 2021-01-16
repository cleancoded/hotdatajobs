<div class="wrap wpjb">
    
<h1>
    <?php _e("Employers", "wpjobboard"); ?>
    <a class="add-new-h2" href="<?php echo wpjb_admin_url("employers", "add") ?>"><?php _e("Add New", "wpjobboard") ?></a> 
</h1>

<?php $this->_include("flash.php"); ?>


<form method="post" action="<?php esc_attr_e(wpjb_admin_url("employers", "redirect", null, array("noheader"=>true))) ?>" id="posts-filter">

<?php if(isset($query) && $query): ?>
<div class="updated fade below-h2" style="background-color: rgb(255, 251, 204);">
    <p>
        <?php printf(__("Your employers list is filtered using following parameters <strong>%s</strong>.", "wpjobboard"), $rquery) ?>&nbsp;
        <?php _e("Click here to", "wpjobboard") ?>&nbsp;<a href="<?php esc_attr_e(wpjb_admin_url("employers", "index")); ?>"><?php _e("browse all employers", "wpjobboard") ?></a>.
    </p>
</div>
<?php endif; ?>    
    
<p class="search-box">
    <label for="post-search-input" class="hidden">&nbsp;</label>
    <input type="text" value="<?php esc_html_e($query) ?>" name="query" id="post-search-input" class="search-input"/>
    <input type="submit" class="button" value="<?php _e("Search Employers", "wpjobboard") ?>" />
</p>
    
<div class="tablenav top">

<div class="alignleft actions">
    <select id="wpjb-action1" name="action">
        <option selected="selected" value=""><?php _e("Bulk Actions", "wpjobboard") ?></option>
        <option value="activate"><?php _e("Activate", "wpjobboard") ?></option>
        <option value="deactivate"><?php _e("Deactivate", "wpjobboard") ?></option>
        <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
        <?php if(Wpjb_Project::getInstance()->conf("cv_access")==2): ?>
        <option value="approve"><?php _e("Approve", "wpjobboard") ?></option>
        <option value="decline"><?php _e("Decline", "wpjobboard") ?></option>
        <?php endif; ?>
        <?php do_action( "wpjb_bulk_actions", "employer" ); ?>
    </select>

    <input type="submit" class="button action" id="wpjb-doaction1" value="<?php _e("Apply", "wpjobboard") ?>" />

</div>
    
</div>

<table cellspacing="0" class="widefat post fixed wp-list-table">
    <?php foreach(array("thead", "tfoot") as $tx): ?>
    <<?php echo $tx; ?>>
        <tr>
            <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
            
            <?php if($screen->show("employer", "company_name")): ?><th style="" class="column-primary" scope="col"><?php _e("Company Name", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "company_logo")): ?><th style="width:50px" class="" scope="col"><?php _e("Logo", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "id")): ?><th style="width:50px" class="column-comments" scope="col"><?php _e("ID", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "__location")): ?><th style="" class="" scope="col"><?php _e("Location", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "company_country")): ?><th style="" class="" scope="col"><?php _e("Company Country", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "company_state")): ?><th style="" class="" scope="col"><?php _e("Company State", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "company_zip_code")): ?><th style="" class="" scope="col"><?php _e("Company Zip-Code", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "company_location")): ?><th style="" class="" scope="col"><?php _e("Company Location", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "user_email")): ?><th style="" class="" scope="col"><?php _e("E-mail", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "user_login")): ?><th style="" class="" scope="col"><?php _e("Representative", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "company_website")): ?><th style="" class="" scope="col"><?php _e("Company Website", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "company_info")): ?><th style="" class="" scope="col"><?php _e("Company Info", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "__jobs_posted")): ?><th style="" class="column-icon" scope="col" title=""><?php _e("Jobs <small>(active / all)</small>", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("employer", "is_public")): ?><th style="" class="fixed column-icon" scope="col"><?php _e("Profile", "wpjobboard") ?></th><?php endif; ?>
            <?php do_action("wpjb_custom_columns_head", "employer") ?>
            <?php if($screen->show("employer", "__status")): ?><th style="" class="fixed column-icon" scope="col"><?php _e("Status", "wpjobboard") ?></th><?php endif; ?>
        </tr>
    </<?php echo $tx; ?>>
    <?php endforeach; ?>

    <tbody id="the-list">
        <?php foreach($data as $i => $item): ?>
        <?php $user = $item->getUser(); ?>
        <tr valign="top" class="<?php if($i%2==0): ?>alternate <?php endif; ?> <?php if($item->is_verified == Wpjb_Model_Company::ACCESS_PENDING): ?>wpjb-unread<?php endif; ?> author-self status-publish iedit">
            <th class="check-column" scope="row">
                <input type="checkbox" value="<?php echo $item->getId() ?>" name="item[]"/>
            </th>

            <?php if($screen->show("employer", "company_name")): ?>
            <td class="post-title column-title column-primary">
                <strong><a title='<?php _e("Edit", "wpjobboard") ?>  "(<?php echo esc_html($item->company_name) ?>)"' href="<?php echo wpjb_admin_url("employers", "edit", $item->getId()); ?>" class="row-title"><?php echo (strlen($item->company_name)<1) ? '<i>'.__("not set", "wpjobboard").'</i>' : esc_html($item->company_name) ?></a></strong>
                <div class="row-actions">
                    <span class="edit"><a title="<?php _e("Edit", "wpjobboard") ?>" href="<?php echo wpjb_admin_url("employers", "edit", $item->getId()); ?>"><?php _e("Edit", "wpjobboard") ?></a> | </span>
                    <span class="view"><a rel="permalink" title="<?php _e("View Profile", "wpjobboard") ?>" href="<?php echo wpjb_link_to("company", $item); ?>"><?php _e("View Profile", "wpjobboard") ?></a> | </span>
                    <span class="view"><a rel="permalink" title="<?php _e("View Jobs", "wpjobboard") ?>" href="<?php echo wpjb_admin_url("job", "index", null, array("employer"=>$item->getId())); ?>"><?php _e("View Jobs", "wpjobboard") ?></a> | </span>
                    <span class="view"><a rel="permalink" class="wpjb-delete wpjb-no-confirm" title="<?php _e("Delete", "wpjobboard") ?>" href="<?php echo wpjb_admin_url("employers", "remove")."&".http_build_query(array("users"=>array($item->id))); ?>"><?php _e("Delete", "wpjobboard") ?></a></span>
                </div>
                <button type="button" class="toggle-row">
                    <span class="screen-reader-text"><?php _e("Show more details") ?></span>
                </button>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "company_logo")): ?>
            <td data-colname="<?php esc_attr_e("Logo", "wpjobboard") ?>">
                <?php if($item->getLogoUrl()): ?>
                <img src="<?php esc_attr_e($item->getLogoUrl("48x48")) ?>" alt="" style="max-width:48px; max-height:48px" />
                <?php endif; ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "id")): ?>
            <td data-colname="<?php esc_attr_e("ID") ?>" class="">
                <?php echo $item->getId() ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "__location")): ?>
            <td data-colname="<?php esc_attr_e("Location", "wpjobboard") ?>" class="author column-author">
                <?php esc_html_e($item->locationToString()) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "company_country")): ?>
            <td data-colname="<?php esc_attr_e("Country", "wpjobboard") ?>">
                <?php 
                    $country = Wpjb_List_Country::getByCode($item->company_country);
                    esc_html_e( $country ? $country["name"] : "—")
                ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "company_state")): ?>
            <td data-colname="<?php esc_attr_e("State", "wpjobboard") ?>">
                <?php esc_html_e($item->company_state) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "company_zip_code")): ?>
            <td data-colname="<?php esc_attr_e("Zip-Code", "wpjobboard") ?>">
                <?php esc_html_e($item->company_zip_code) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "company_location")): ?>
            <td data-colname="<?php esc_attr_e("City", "wpjobboard") ?>">
                <?php esc_html_e($item->company_location) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "user_email")): ?>
            <td data-colname="<?php esc_attr_e("E-mail", "wpjobboard") ?>">
                <?php esc_html_e($user->user_email) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "user_login")): ?>
            <td data-colname="<?php esc_attr_e("Representative", "wpjobboard") ?>" class="author column-author">
                <strong><a href="user-edit.php?user_id=<?php echo $user->ID ?>"><?php echo esc_html($user->display_name." (ID: ".$user->ID.")") ?></a></strong>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "company_website")): ?>
            <td data-colname="<?php esc_attr_e("URL", "wpjobboard") ?>">
                <?php esc_html_e($item->company_website) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "company_info")): ?>
            <td data-colname="<?php esc_attr_e("Company Info", "wpjobboard") ?>">
                <?php echo substr(strip_tags($item->company_info), 0, 120) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "__jobs_posted")): ?>
            <td data-colname="<?php esc_attr_e("Jobs Posted", "wpjobboard") ?>" class="column-icon">
                <?php echo wpjb_find_jobs(array("employer_id"=>$item->id, "count_only"=>true)) ?> 
                <strong>/</strong>
                <?php echo wpjb_find_jobs(array("employer_id"=>$item->id, "count_only"=>true, "filter"=>"all")) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("employer", "is_public")): ?>
            <td data-colname="<?php esc_attr_e("Profile", "wpjobboard") ?>" class="categories column-categories">
                <?php if($item->is_public): ?>
                <?php _e("Public", "wpjobboard") ?>
                <?php else: ?>
                <?php _e("Private", "wpjobboard") ?>
                <?php endif; ?>
            </td>
            <?php endif; ?>
           
            <?php do_action("wpjb_custom_columns_body", "employer", $item) ?>
            
            <?php if($screen->show("employer", "__status")): ?>
            <td data-colname="<?php esc_attr_e("Status", "wpjobboard") ?>" class="date column-date">
                <?php if($item->is_active): ?><span class="wpjb-bulb wpjb-bulb-active"><?php _e("Active", "wpjobboard") ?></span><?php else: ?><span class="wpjb-bulb wpjb-bulb-inactive"><?php _e("Inactive", "wpjobboard") ?></span><?php endif; ?>
                <?php if(isset($opt[$item->is_verified])): ?>
                <?php $bulb = array(Wpjb_Model_Company::ACCESS_DECLINED=>"wpjb-bulb-expired", Wpjb_Model_Company::ACCESS_PENDING=>"wpjb-bulb-pending", Wpjb_Model_Company::ACCESS_GRANTED=>"wpjb-bulb-active") ?>
                <span class="wpjb-bulb <?php echo $bulb[$item->is_verified] ?>"><?php esc_html_e($opt[$item->is_verified]); ?></span>
                <?php endif; ?>
            </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="tablenav">
    <div class="tablenav-pages">
        <?php
            echo paginate_links( array(
                'base' => wpjb_admin_url("employers", "index", null, $param)."%_%",
                'format' => '&p=%#%',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total,
                'current' => $current,
                'add_args' => false
            ));
        ?>
    </div>


    <div class="alignleft actions">
        <select id="wpjb-action2" name="action2">
            <option selected="selected" value=""><?php _e("Bulk Actions", "wpjobboard") ?></option>
            <option value="activate"><?php _e("Activate", "wpjobboard") ?></option>
            <option value="deactivate"><?php _e("Deactivate", "wpjobboard") ?></option>
            <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
            <?php if(wpjb_conf("cv_access")==4): ?>
            <option value="approve"><?php _e("Approve", "wpjobboard") ?></option>
            <option value="decline"><?php _e("Decline", "wpjobboard") ?></option>
            <?php endif; ?>
            <?php do_action( "wpjb_bulk_actions", "employer" ); ?>
        </select>
        <input type="submit" class="button action" id="wpjb-doaction2" value="<?php _e("Apply", "wpjobboard") ?>" />

        <br class="clear"/>
    </div>
    
    <div class="alignleft actions">
        <a href="#" class="button action wpjb-modal-window-toggle wpjb-export-button" style="margin-top:0">
            <span class="dashicons dashicons-archive" style="vertical-align: middle; padding-bottom: 4px;"></span> 
            <?php _e("Export ...", "wpjobboard") ?>
        </a>
    </div>

    <br class="clear"/>
</div>


</form>


</div>
<?php

wpjb_export_ui(
    "wpjb_export_companies",
    array(
        "company" => array(
            "title" => __("Companies", "wpjobboard"),
            "callback" => "dataEmployer",
            "checked" => true,
            "prefix" => "company",
        ),
    )
);

?>
