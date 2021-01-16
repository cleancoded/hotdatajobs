<div class="wrap wpjb">
    
<h1><?php _e("Applications", "wpjobboard") ?> <a class="add-new-h2" href="<?php esc_html_e(wpjb_admin_url("application", "add")) ?>"><?php _e("Add New", "wpjobboard") ?></a> </h1>
<?php $this->_include("flash.php"); ?>


<?php 
    global $_wp_admin_css_colors;

    if(isset($_wp_admin_css_colors[get_user_option('admin_color')]->colors)) {
        $star_color = $_wp_admin_css_colors[get_user_option('admin_color')]->colors[2];
    } else {
        $star_color = "#ff0000";
    }

?>
<style type="text/css">
    .wpjb-star-color:before {
        color: <?php echo $star_color; ?>;
    }
</style>


<script type="text/javascript">
    Wpjb.DeleteType = "application";
</script>

<?php $ids = get_transient( 'wpjb_bulkprint' ); ?>
<?php if( $ids !== false ): ?>
<?php delete_transient( 'wpjb_bulkprint' ); ?>
<script type="text/javascript">
    var url = "<?php echo wpjb_api_url("print/multiple"); ?>?id=<?php echo base64_encode( json_encode( $ids ) ); ?>";
    jQuery(document).ready(function(){
        window.open(url, "_blank"); 
    });
</script>
<?php endif; ?>

<form method="post" action="<?php esc_attr_e(wpjb_admin_url("application", "redirect", null, array("noheader"=>1))) ?>" id="posts-filter">
    
<?php if($param["job"]>0): ?>
<input type="hidden" name="job" value="<?php esc_attr_e($param["job"]) ?>" />
<?php endif ?>

<?php if(isset($query) && $query): ?>
<div class="updated fade below-h2" style="background-color: rgb(255, 251, 204);">
    <p>
        <?php printf(__("Your applications list is filtered using following parameters <strong>%s</strong>.", "wpjobboard"), $rquery) ?>&nbsp;
        <?php _e("Click here to", "wpjobboard") ?>&nbsp;<a href="<?php esc_attr_e(wpjb_admin_url("application", "index")); ?>"><?php _e("browse all applications", "wpjobboard") ?></a>.
    </p>
</div>
<?php endif; ?>

<?php if(isset($jobObject) && $jobObject): ?>
<div class="updated fade below-h2" style="background-color: rgb(255, 251, 204);">
    <p>
        <?php _e("You are browsing applications for job", "wpjobboard") ?>&nbsp;
        <strong><?php esc_html_e($jobObject->job_title) ?> (ID: <?php echo esc_html($jobObject->getId()) ?>)</strong>.
        <?php _e("Click here to", "wpjobboard") ?>&nbsp;<a href="<?php esc_attr_e(wpjb_admin_url("application")); ?>"><?php _e("browse all applications", "wpjobboard") ?></a>.</p>
</div>
<?php endif; ?>

<ul class="subsubsub">
    <?php $status_list = array_merge(array("all"=>array("key"=>"all", "label"=>__("All"))), wpjb_get_application_status()); ?>
    <?php foreach($status_list as $st): ?>  
    <li>
        <a <?php if($filter == $st["key"]): ?>class="current"<?php endif; ?> href="<?php esc_attr_e(wpjb_admin_url("application", "index", null, array_merge($param, array("filter"=>$st["key"])))) ?>"><?php esc_html_e($st["label"]) ?></a>
        <span class="count">(<?php echo (int)Wpjb_Model_Application::search(array_merge($search, array("filter"=>$st["key"], "count_only"=>1))); ?>)</span> |
    </li>
    <?php endforeach; ?>
</ul>

<p class="search-box">
    <label for="post-search-input" class="hidden">&nbsp;</label>
    <input type="text" value="<?php esc_html_e($query) ?>" name="query" id="post-search-input" class="search-input"/>
    <input type="submit" class="button" value="<?php _e("Search Applications", "wpjobboard") ?>" />
</p>
    
<div class="tablenav top">

    <div class="alignleft actions">
        <select name="action" id="wpjb-action1">
            <option selected="selected" value=""><?php _e("Bulk Actions") ?></option>
            <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
            <option value="print"><?php _e("Print", "wpjobboard") ?></option>
            
            <optgroup label="<?php _e("Change Status To", "wpjobboard") ?>">
                <?php foreach(wpjb_get_application_status() as $k => $status_list): ?>
                <option value="<?php esc_attr_e($status_list["key"]) ?>"><?php esc_html_e($status_list["label"]) ?></option>
                <?php endforeach; ?>
            </optgroup>
            
             <?php do_action( "wpjb_bulk_actions", "application" ); ?>
        </select>
    
        <input type="submit" class="button action" id="wpjb-doaction1" value="Apply"/>
    </div>
    
    <div class="alignleft actions">
        <select name="posted">
            <option value=""><?php _e("View all dates", "wpjobboard") ?></option>
            <?php foreach($months as $k => $v): ?>
            <option value="<?php esc_attr_e($k) ?>" <?php if($posted==$k): ?>selected="selected"<?php endif; ?>><?php esc_html_e($v) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="submit" class="button action" value="<?php _e("Filter", "wpjobboard") ?>" id="post-query-submit"/>
    </div>
    
</div>

<table cellspacing="0" class="widefat post fixed wp-list-table wpjb-list-applications">
    <?php foreach(array("thead", "tfoot") as $tx): ?>
    <<?php echo $tx; ?>>
        <tr>
            <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
            <?php if($screen->show("application", "__gravatar")): ?><th style="width:36px" class="wpjb-list-application-gravatar wpjb-no-mobile" scope="col"><span class="dashicons dashicons-camera" title="<?php _e("Avatar", "wpjobboard") ?>"></span></th><?php endif; ?>
            <?php if($screen->show("application", "applicant_name")): ?><th style="max-width:250px" class="column-primary" scope="col"><?php _e("Applicant Name", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("application", "id")): ?><th style="width:25px" class="" scope="col"><?php _e("ID") ?></th><?php endif; ?>
            <?php if($screen->show("application", "email")): ?><th style="" class="" scope="col"><?php _e("Applicant Email", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("application", "__job")): ?><th style="" class="" scope="col"><?php _e("Job", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("application", "file")): ?><th style="width:15px" class="" scope="col"><span class="dashicons dashicons-paperclip" title="<?php _e("Files", "wpjobboard") ?>"></span></th><?php endif; ?>
            <?php if($screen->show("application", "applied_at")): ?>
                <th style="" class="sortable <?php wpjb_column_sort($sort=="applied_at", $order) ?>" scope="col">
                    <a href="<?php echo esc_attr(add_query_arg(array("sort"=>"applied_at", "order"=>wpjb_column_order($sort=="applied_at", $order)))) ?>">
                       <span><?php _e("Posted", "wpjobboard") ?></span>
                       <span class="sorting-indicator"></span>
                   </a>
                </th> 
            <?php endif; ?>
            <?php if($screen->show("application", "message")): ?><th style="" class="" scope="col"><?php _e("Message", "wpjobboard") ?></th><?php endif; ?>
            <?php if($screen->show("application", "__rating")): ?>
                <th style="" class="sortable <?php wpjb_column_sort($sort=="__rating", $order) ?>" scope="col">
                    <a href="<?php echo esc_attr(add_query_arg(array("sort"=>"__rating", "order"=>wpjb_column_order($sort=="__rating", $order)))) ?>">
                       <span><?php _e("Rating", "wpjobboard") ?></span>
                       <span class="sorting-indicator"></span>
                   </a>
                </th> 
            <?php endif; ?>
            <?php do_action("wpjb_custom_columns_head", "application") ?>
            <?php if($screen->show("application", "__status")): ?><th style="" class="" scope="col"><?php _e("Status", "wpjobboard") ?></th><?php endif; ?>
        </tr>
    </<?php echo $tx; ?>>
    <?php endforeach; ?>

    <tbody id="the-list">
        <?php foreach($data as $i => $item): ?>
	<tr valign="top" class="<?php if($i%2==0): ?>alternate <?php endif; ?>  author-self status-publish iedit <?php if($item->status == 1): ?>wpjb-unread<?php endif; ?>">
            <th class="check-column" scope="row">
                <input type="checkbox" value="<?php esc_attr_e($item->getId()) ?>" name="item[]"/>
            </th>
            
            <?php if($screen->show("application", "__gravatar")): ?>
            <td class="wpjb-list-application-gravatar wpjb-no-mobile">
                <?php echo get_avatar($item->email, "36") ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("application", "applicant_name")): ?>
            <td class="post-title column-title column-primary">
                <strong><a title='<?php _e("Edit", "wpjobboard") ?>  "<?php esc_attr_e($item->applicant_name) ?>"' href="<?php echo esc_attr(wpjb_admin_url("application", "edit", $item->getId(), $navi)); ?>" class="row-title"><?php esc_html_e($item->applicant_name) ?></a></strong>
                <div class="row-actions">
                    <span class="edit"><a title="<?php _e("Edit", "wpjobboard") ?>" href="<?php esc_attr_e(wpjb_admin_url("application", "edit", $item->getId(), $navi)); ?>"><?php _e("Edit", "wpjobboard") ?></a> | </span>
                    <span class=""><a href="<?php echo wpjb_api_url("print/index"); ?>?id=<?php echo $item->getId() ?>" target="_blank" title="<?php _e("Print", "wpjobboard") ?>"><?php _e("Print", "wpjobboard") ?></a> | </span>
                    <span class=""><a href="<?php esc_attr_e(wpjb_admin_url("application", "delete", $item->getId(), array("noheader"=>1))) ?>" title="<?php _e("Delete", "wpjobboard") ?>" class="wpjb-delete"><?php _e("Delete", "wpjobboard") ?></a> </span>
                </div>
                <button type="button" class="toggle-row">
                    <span class="screen-reader-text"><?php _e("Show more details") ?></span>
                </button>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("application", "id")): ?>
            <td data-colname="<?php esc_attr_e("ID", "wpjobboard") ?>">
                <?php esc_html_e($item->id) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("application", "email")): ?>
            <td data-colname="<?php esc_attr_e("E-mail", "wpjobboard") ?>" class="date column-date">
                <a href="mailto:<?php esc_attr_e($item->email) ?>"><?php esc_attr_e($item->email) ?></a>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("application", "__job")): ?>
            <td data-colname="<?php esc_attr_e("Job", "wpjobboard") ?>" class="date column-date">
                <?php if($item->getJob(true)->id): ?>
                <a href="<?php echo esc_attr(add_query_arg("query", "job:".$item->getJob()->getId())) ?>" title="<?php printf(__("ID: %d", "wpjobboard"), $item->getJob()->getId()) ?>"><?php esc_attr_e($item->getJob()->job_title) ?></a>
                <?php else: ?>
                —
                <?php endif; ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("application", "file")): ?>
            <td data-colname="<?php esc_attr_e("Files", "wpjobboard") ?>" class="date column-date wpjb-column-right">
                <?php echo esc_html(count($item->getFiles())) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("application", "applied_at")): ?>
            <td data-colname="<?php esc_attr_e("Posted", "wpjobboard") ?>" class="date column-date">
                <?php esc_html_e(wpjb_date($item->applied_at)) ?><br/>
                <?php echo daq_time_ago_in_words(strtotime($item->applied_at))." ".__("ago.") ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("application", "message")): ?>
            <td data-colname="<?php esc_attr_e("Message", "wpjobboard") ?>">
                <?php echo substr(strip_tags($item->message), 0, 120) ?>
            </td>
            <?php endif; ?>
            
            <?php if($screen->show("application", "__rating")): ?>
            <td data-colname="<?php esc_attr_e("Rating", "wpjobboard") ?>">
                <?php $rated = absint($item->meta->rating->value()) ?>
                <span class="wpjb-star-ratings" data-id="<?php echo esc_html($item->id) ?>">
                    
                    <span class="wpjb-star-rating-bar">
                        <?php for($i=0; $i<5; $i++): ?><span class="wpjb-star-rating wpjb-star-color dashicons dashicons-star-empty <?php echo ($rated>$i) ? "wpjb-star-checked" : "" ?>" data-value="<?php echo $i+1 ?>" ></span><?php endfor ?>
                    </span>
                    <span class="wpjb-star-rating-loader" style="display:none"><img src="<?php echo esc_attr(includes_url() . "images/spinner-2x.gif") ?>" alt="" /></span>
                </span>
            </td>
            <?php endif; ?>
            
            <?php do_action("wpjb_custom_columns_body", "application", $item) ?>
            
            <?php if($screen->show("application", "__status")): ?>
            <td data-colname="<?php esc_attr_e("Status", "wpjobboard") ?>">
                <?php echo (wpjb_application_status($item->status, true)) ?>
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
            'base' => wpjb_admin_url("application", "index", null, $param)."%_%",
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
        <select name="action2" id="wpjb-action2">
            <option selected="selected" value=""><?php _e("Bulk Actions", "wpjobboard") ?></option>
            <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
            <option value="print"><?php _e("Print", "wpjobboard") ?></option>
            
            <optgroup label="<?php _e("Change Status To", "wpjobboard") ?>">
                <?php foreach(wpjb_get_application_status() as $k => $status_list): ?>
                <option value="<?php esc_attr_e($status_list["key"]) ?>"><?php esc_html_e($status_list["label"]) ?></option>
                <?php endforeach; ?>
            </optgroup>
             <?php do_action( "wpjb_bulk_actions", "application" ); ?>
        </select>
        
        <input type="submit" class="button action" id="wpjb-doaction2" value="<?php _e("Apply", "wpjobboard") ?>" />
        

    </div>
    
    <div class="alignleft actions">
        <a href="#" class="button wpjb-modal-window-toggle wpjb-export-button" style="margin:0 1px 1px 1px">
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
    "wpjb_export_applications",
    array(
        "application" => array(
            "title" => __("Applications", "wpjobboard"),
            "callback" => "dataApplication",
            "checked" => true,
            "prefix" => "application",
        ),
        "job" => array(
            "title" => __("Jobs", "wpjobboard"),
            "callback" => "dataJob",
            "checked" => false,
            "prefix" => "job",
        )
    )
);

?>

