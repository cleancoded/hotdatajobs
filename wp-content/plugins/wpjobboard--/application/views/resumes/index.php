<div class="wrap wpjb">
    
<h1>
    <?php _e("Candidates", "wpjobboard"); ?>
    <a class="add-new-h2" href="<?php echo wpjb_admin_url("resumes", "add") ?>"><?php _e("Add New", "wpjobboard") ?></a> 
</h1>

<?php $this->_include("flash.php"); ?>

<form method="post" action="" id="wpjb-delete-form">
    <input type="hidden" name="delete" value="1" />
    <input type="hidden" name="id" value="" id="wpjb-delete-form-id" />
</form>

<?php if(isset($query) && $query): ?>
<div class="updated fade below-h2" style="background-color: rgb(255, 251, 204);">
    <p>
        <?php printf(__("Your candidates list is filtered using following parameters <strong>%s</strong>.", "wpjobboard"), $rquery) ?>&nbsp;
        <?php _e("Click here to", "wpjobboard") ?>&nbsp;<a href="<?php esc_attr_e(wpjb_admin_url("resumes", "index")); ?>"><?php _e("browse all candidates", "wpjobboard") ?></a>.
    </p>
</div>
<?php endif; ?>
    
<ul class="subsubsub">
    <li><a <?php if($filter == "all"): ?>class="current"<?php endif; ?> href="<?php esc_attr_e(wpjb_admin_url("resumes", "index", null, array_merge($param, array("filter"=>"all")))) ?>"><?php _e("All", "wpjobboard") ?></a><span class="count">(<?php echo (int)$stat->total ?>)</span> | </li>
    <li><a <?php if($filter == "active"): ?>class="current"<?php endif; ?>href="<?php esc_attr_e(wpjb_admin_url("resumes", "index", null, array_merge($param, array("filter"=>"active")))) ?>"><?php _e("Active", "wpjobboard") ?></a><span class="count">(<?php echo (int)$stat->active ?>)</span> | </li>
    <li><a <?php if($filter == "inactive"): ?>class="current"<?php endif; ?>href="<?php esc_attr_e(wpjb_admin_url("resumes", "index", null, array_merge($param, array("filter"=>"inactive")))) ?>"><?php _e("Inactive", "wpjobboard") ?></a><span class="count">(<?php echo (int)$stat->inactive ?>)</span> </li>
</ul>

<form method="post" action="<?php esc_attr_e(wpjb_admin_url("resumes", "redirect", null, array("noheader"=>true))) ?>" id="posts-filter">
    
<p class="search-box">
    <label for="post-search-input" class="hidden"><?php _e("Search Jobs", "wpjobboard") ?>:</label>
    <input type="text" value="<?php esc_html_e($query) ?>" name="query" id="post-search-input" class="search-input"/>
    <input type="submit" class="button" value="<?php _e("Search Candidates", "wpjobboard") ?>" />
</p>
    
<div class="tablenav top">

<div class="alignleft actions">
    <select id="wpjb-action1" name="action">
        <option selected="selected" value=""><?php _e("Bulk Actions", "wpjobboard") ?></option>
        <option value="activate"><?php _e("Activate", "wpjobboard") ?></option>
        <option value="deactivate"><?php _e("Deactivate", "wpjobboard") ?></option>
        <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
        <?php do_action( "wpjb_bulk_actions", "resume" ); ?>
    </select>

    <input type="submit" class="button-secondary action" id="wpjb-doaction1" value="<?php _e("Apply", "wpjobboard") ?>"/>

</div>

</div>
    
<table cellspacing="0" class="widefat post fixed wp-list-table">
    <?php foreach(array("thead", "tfoot") as $tx): ?>
    <<?php echo $tx; ?>>
        <tr>
            <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
            <?php if($screen->show("resume", "__name")): ?><th style="" class="column-primary" scope="col"><?php _e("Name", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "image")): ?><th style="width:50px" class="" scope="col"><?php _e("Photo", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "id")): ?><th style="width:50px" class="" scope="col"><?php _e("ID") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "first_name")): ?><th style="" class="" scope="col"><?php _e("First Name", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "last_name")): ?><th style="" class="" scope="col"><?php _e("Last Name", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "headline")): ?><th style="" class="" scope="col"><?php _e("Headline", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "user_email")): ?><th style="" class="" scope="col"><?php _e("E-mail", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "user_login")): ?><th style="" class="" scope="col"><?php _e("Login", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "phone")): ?><th style="" class="" scope="col"><?php _e("Phone", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "user_url")): ?><th style="" class="" scope="col"><?php _e("Website", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "__location")): ?><th style="" class="" scope="col"><?php _e("Location", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "candidate_country")): ?><th style="" class="" scope="col"><?php _e("Country", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "candidate_state")): ?><th style="" class="" scope="col"><?php _e("State", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "candidate_zip_code")): ?><th style="" class="" scope="col"><?php _e("Zip-Code", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "candidate_location")): ?><th style="" class="" scope="col"><?php _e("City", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "category")): ?><th style="" class="" scope="col"><?php _e("Category", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "created_at")): ?><th style="" class="" scope="col"><?php _e("Created", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "modified_at")): ?><th style="" class="" scope="col"><?php _e("Updated (By Owner)", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "description")): ?><th style="" class="" scope="col"><?php _e("Description", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("resume", "is_public")): ?><th style="" class="" scope="col"><?php _e("Privacy", "wpjobboard") ?></th><?php endif; ?>
            <?php do_action("wpjb_custom_columns_head", "resume") ?>
            <?php if($screen->show("resume", "__status")): ?><th style="" class="" scope="col"><?php _e("Status", "wpjobboard") ?></th><?php endif; ?>
        </tr>
    </<?php echo $tx; ?>>
    <?php endforeach; ?>

    <tbody id="the-list">
        <?php foreach($data as $i => $item): ?>
        <?php $user = new WP_User($item->user_id); ?>
	<tr valign="top" class="<?php if($i%2==0): ?>alternate <?php endif; ?>  author-self status-publish iedit">
            <th class="check-column" scope="row">
                <input type="checkbox" value="<?php echo $item->getId() ?>" name="item[]"/>
            </th>
            
            <?php if($screen->show("resume", "__name")): ?>
            <td class="post-title column-title column-primary">
                <strong><a title='<?php _e("Edit", "wpjobboard") ?>' href="<?php echo wpjb_admin_url("resumes", "edit", $item->getId()); ?>" class="row-title"><?php echo ($user->first_name || $user->last_name) ? esc_html(trim($user->first_name." ".$user->last_name)) : esc_html("ID: ".$item->getId()) ?></a></strong>
                <div class="row-actions">
                    <span class="edit"><a title="<?php _e("Edit", "wpjobboard") ?>" href="<?php echo wpjb_admin_url("resumes", "edit", $item->getId()); ?>"><?php _e("Edit", "wpjobboard") ?></a> | </span>
                    <span class="view"><a rel="permalink" title="<?php _e("View", "wpjobboard") ?>" href="<?php echo wpjr_link_to("resume", $item) ?>"><?php _e("View", "wpjobboard") ?></a> | </span>
                    <span><a href="<?php echo wpjb_admin_url("resumes", "remove")."&".http_build_query(array("users"=>array($item->id))) ?>" class="wpjb-delete wpjb-no-confirm"><?php _e("Delete", "wpjobboard") ?></a> </span>
                </div>
                <button type="button" class="toggle-row">
                    <span class="screen-reader-text"><?php _e("Show more details") ?></span>
                </button>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "image")): ?>
            <td data-colname="<?php esc_attr_e("Photo", "wpjobboard") ?>">
                <?php if($item->getAvatarUrl()): ?>
                <img src="<?php esc_attr_e($item->getAvatarUrl("48x48")) ?>" alt="" />
                <?php endif; ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "id")): ?>
            <td data-colname="<?php esc_attr_e("ID") ?>" class="">
                <?php esc_html_e($item->id) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "first_name")): ?>
            <td data-colname="<?php esc_attr_e("First Name", "wpjobboard") ?>" class="">
                <?php esc_html_e(get_user_meta( $item->user_id, "first_name", true )) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "last_name")): ?>
            <td data-colname="<?php esc_attr_e("Last Name", "wpjobboard") ?>" class="">
                <?php esc_html_e(get_user_meta( $item->user_id, "last_name", true )) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "headline")): ?>
            <td data-colname="<?php esc_attr_e("Headline", "wpjobboard") ?>" class="author column-author">
                <?php esc_html_e($item->headline) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "user_email")): ?>
            <td data-colname="<?php esc_attr_e("E-mail", "wpjobboard") ?>" class="categories column-categories">
                <?php esc_html_e($item->getUser(true)->user_email) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "user_login")): ?>
            <td data-colname="<?php esc_attr_e("Login", "wpjobboard") ?>" class="categories column-categories">
                <a href="<?php esc_attr_e(admin_url("user-edit.php?user_id={$item->user_id}")) ?>">
                    <?php esc_html_e($item->getUser(true)->user_login) ?>
                </a>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "phone")): ?>
            <td data-colname="<?php esc_attr_e("Phone", "wpjobboard") ?>" class="tags column-tags">
                <?php esc_html_e($item->phone) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "user_url")): ?>
            <td data-colname="<?php esc_attr_e("URL", "wpjobboard") ?>" class="">
                <?php esc_html_e($item->getUser(true)->user_url) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "__location")): ?>
            <td data-colname="<?php esc_attr_e("Location", "wpjobboard") ?>">
                <?php esc_html_e($item->locationToString()) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "candidate_country")): ?>
            <td data-colname="<?php esc_attr_e("Country", "wpjobboard") ?>">
                <?php 
                    $country = Wpjb_List_Country::getByCode($code);
                    esc_html_e( $country ? $country["name"] : "—")
                ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "candidate_state")): ?>
            <td data-colname="<?php esc_attr_e("State", "wpjobboard") ?>">
                <?php esc_html_e($item->candidate_state) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "candidate_zip_code")): ?>
            <td data-colname="<?php esc_attr_e("Zip-Code", "wpjobboard") ?>">
                <?php esc_html_e($item->candidate_zip_code) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "candidate_location")): ?>
            <td data-colname="<?php esc_attr_e("Location", "wpjobboard") ?>">
                <?php esc_html_e($item->candidate_location) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "category")): ?>
            <td data-colname="<?php esc_attr_e("Category", "wpjobboard") ?>">
                <?php if(isset($item->tag->category[0])): ?>
                <a href="<?php esc_attr_e($item->tag->category[0]->url()) ?>" title="<?php _e("Edit category", "wpjobboard") ?>">
                    <?php esc_html_e($item->tag->category[0]->title) ?>
                </a>
                <?php else: ?>
                -
                <?php endif; ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "created_at")): ?>
            <td data-colname="<?php esc_attr_e("Created", "wpjobboard") ?>" class="date column-date">
                <?php echo wpjb_date($item->created_at) ?><br/>
                <small>
                    <?php if($item->created_at == date("Y-m-d")): ?>
                    <?php _e("Today", "wpjobboard"); ?>
                    <?php else: ?>
                    <?php esc_html_e(daq_time_ago_in_words(strtotime($item->created_at))." ".__("ago", "wpjobboard")) ?>
                    <?php endif; ?>
                </small>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "modified_at")): ?>
            <td data-colname="<?php esc_attr_e("Updated At", "wpjobboard") ?>" class="date column-date">
                <?php echo wpjb_date($item->modified_at) ?><br/>
                <small>
                    <?php if($item->modified_at == date("Y-m-d")): ?>
                    <?php _e("Today", "wpjobboard"); ?>
                    <?php else: ?>
                    <?php esc_html_e(daq_time_ago_in_words(strtotime($item->modified_at))." ".__("ago", "wpjobboard")) ?>
                    <?php endif; ?>
                </small>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "description")): ?>
            <td data-colname="<?php esc_attr_e("Description", "wpjobboard") ?>">
                <?php echo substr(strip_tags($item->description), 0, 120) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("resume", "is_public")): ?>
            <td data-colname="<?php esc_attr_e("Privacy", "wpjobboard") ?>">
                <?php if($item->is_public): ?>
                <?php _e("Public", "wpjobboard") ?>
                <?php else: ?>
                <?php _e("Private", "wpjobboard") ?>
                <?php endif; ?>
            </td>
            <?php endif; ?>
            
            <?php do_action("wpjb_custom_columns_body", "resume", $item) ?>
            
            <?php if($screen->show("resume", "__status")): ?>
            <td data-colname="<?php esc_attr_e("Status", "wpjobboard") ?>" class="date column-date">
                <?php if($item->is_active): ?>
                <span class="wpjb-bulb wpjb-bulb-active"><?php _e("Active", "wpjobboard") ?></span>
                <?php else: ?>
                <span class="wpjb-bulb wpjb-bulb-inactive"><?php _e("Disabled", "wpjobboard") ?></span>
                <?php endif; ?>
                
                <?php if($item->is_public): ?>
                <span class="wpjb-bulb wpjb-bulb-active"><?php _e("Public", "wpjobboard") ?></span>
                <?php else: ?>
                <span class="wpjb-bulb wpjb-bulb-inactive"><?php _e("Private", "wpjobboard") ?></span>
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
            'base' => wpjb_admin_url("resumes", "index", null, $param)."%_%",
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
            <?php do_action( "wpjb_bulk_actions", "resume" ); ?>
        </select>
        <input type="submit" class="button action" id="wpjb-doaction2" value="<?php _e("Apply", "wpjobboard") ?>"/>

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
    "wpjb_export_candidates",
    array(
        "candidate" => array(
            "title" => __("Candidates", "wpjobboard"),
            "callback" => "dataResume",
            "checked" => true,
            "prefix" => "candidate",
        ),
    )
);

?>

   