<?php wp_enqueue_style('wpjb-glyphs') ?>
<div class="wrap wpjb">
    
<h1>
    <?php _e("Payments", "wpjobboard"); ?>
</h1>

<?php $this->_include("flash.php"); ?>
    
<div class="clear">&nbsp;</div>

<form action="<?php esc_attr_e(wpjb_admin_url("payment", "redirect", null, array("noheader"=>1))) ?>" method="post">
    
<p class="search-box">
    <label for="post-search-input" class=""><?php _e("Find Payment by ID", "wpjobboard") ?>:</label>
    <input type="text" value="<?php esc_html_e($payment_id) ?>" name="payment_id" id="post-search-input" class="search-input"/>
    <input type="submit" class="button" value="<?php _e("Search", "wpjobboard") ?>"/>
</p>
    
<div class="tablenav top">

<div class="alignleft actions">
    <select id="wpjb-action1" name="action">
        <option selected="selected" value=""><?php _e("Bulk Actions", "wpjobboard") ?></option>
        <option value="markpaid"><?php _e("Mark as Paid", "wpjobboard") ?></option>
        <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
        <?php do_action( "wpjb_bulk_actions", "payment" ); ?>
    </select>

    <input type="submit" class="button action" id="wpjb-doaction1" value="<?php _e("Apply", "wpjobboard") ?>" />
</div>
    
</div>

<table cellspacing="0" class="widefat post fixed wp-list-table">
    <?php foreach(array("thead", "tfoot") as $tx): ?>
    <<?php echo $tx; ?>>
        <tr>
            <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox"/></th>
            <th style="min-width:13%" class="column-primary" scope="col"><?php _e("ID", "wpjobboard") ?></th>
            <th style="width:20%" class="" scope="col"><?php _e("Payment For", "wpjobboard") ?></th>
            <th style="width:75px" class="" scope="col"><?php _e("Created At", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("User", "wpjobboard") ?></th>
            <th style="" class="" scope="col"><?php _e("Payment Gateway", "wpjobboard") ?></th>
            <th style="width:75px" class="column-icon" scope="col"><?php _e("To Pay", "wpjobboard") ?></th>
            <th style="width:75px" class="fixed column-icon" scope="col"><?php _e("Paid", "wpjobboard") ?></th>
            <th style="width:75px"><?php _e("Status", "wpjobboard") ?></th>
        </tr>
    </<?php echo $tx; ?>>
    <?php endforeach; ?>

    <tbody id="the-list">
        <?php foreach($data as $i => $item): ?>
	<tr valign="top" class="<?php if($i%2==0): ?>alternate <?php endif; ?>  author-self status-publish iedit">
            <th class="check-column" scope="row">
                <input type="checkbox" value="<?php echo $item->getId() ?>" name="item[]"/>
            </th>
            <td class="post-title column-title column-primary">
                <strong><a href="<?php echo esc_html( wpjb_admin_url( "payment", "edit", $item->id ) ); ?>" ><?php esc_html_e($item->id()) ?></a></strong>
                
                <div class="row-actions">
                    <span><a href="<?php echo esc_html(wpjb_admin_url( "payment", "edit", $item->id ) ); ?>" ><?php _e("Edit", "wpjobboard") ?></a> | </span>
                    <span><a href="<?php echo esc_html(wpjb_admin_url( "payment", "markpaid", $item->getId(), array("noheader"=>1))) ?>" class="wpjb-payment-mark-paid"><?php _e("Mark as Paid", "wpjobboard") ?></a> | </span>
                    <span><a href="<?php echo esc_html(wpjb_admin_url( "payment", "delete", $item->getId(), array("noheader"=>1))) ?>" class="wpjb-delete"><?php _e("Delete", "wpjobboard") ?></a> </span>
                </div>
                
                <button type="button" class="toggle-row">
                    <span class="screen-reader-text"><?php _e("Show more details") ?></span>
                </button>
                
            </td>
            <td data-colname="<?php esc_attr_e("Payment For", "wpjobboard") ?>">
                <?php
                    switch($item->object_type) {
                        case Wpjb_Model_Payment::JOB: $object = new Wpjb_Model_Job($item->object_id); break;
                        case Wpjb_Model_Payment::RESUME: $object = new Wpjb_Model_Resume($item->object_id); break;
                        case Wpjb_Model_Payment::MEMBERSHIP: $object = new Wpjb_Model_Membership($item->object_id); break;
                        case Wpjb_Model_Payment::CAND_MEMBERSHIP: $object = new Wpjb_Model_Membership($item->object_id); break;
                        default: $object = null;
                    }
                ?>
                
                <?php if(!in_array($item->object_type, array(1,2,3,4))): ?>
                <?php do_action("wpjb_payment_for", $item) ?>
                <?php elseif(!$object || !$object->exists()): ?>
                <span class="wpjb-glyhs wpjb-icon-attention"><strong><?php _e("Deleted", "wpjobboard") ?></strong></span>
                <?php elseif($item->object_type == Wpjb_Model_Payment::JOB): ?>
                <span class="wpjb-glyphs wpjb-icon-briefcase" style="vertical-align: top" title="<?php esc_attr_e(__("Job", "wpjobboard") . " (ID: ".$object->id.")") ?>"></span>
                <strong><a href="<?php echo esc_url(wpjb_admin_url("job", "edit", $object->id)) ?>"><?php esc_html_e($object->job_title) ?></a></strong>
                <?php elseif($item->object_type == Wpjb_Model_Payment::RESUME): ?>
                <span class="wpjb-glyphs wpjb-icon-lock-open" style="vertical-align: top" title="<?php esc_attr_e(__("Resume Access", "wpjobboard") . " (ID: ".$object->id.")") ?>"></span>
                <strong><a href="<?php echo esc_url(wpjb_admin_url("resumes", "edit", $object->id)) ?>"><?php esc_html_e($object->getSearch(true)->fullname) ?></a></strong>
                <?php elseif($item->object_type == Wpjb_Model_Payment::MEMBERSHIP): ?>
                <span class="wpjb-glyphs wpjb-icon-users" style="vertical-align: top" title="<?php esc_attr_e(__("Membership", "wpjobboard") . " (ID: ".$object->id.")") ?>"></span>
                <strong><a href="<?php esc_url(wpjb_admin_url("memberships", "edit", $object->id)) ?>"><?php esc_html_e($object->getPricing(true)->title) ?></a></strong>
                <?php elseif($item->object_type == Wpjb_Model_Payment::CAND_MEMBERSHIP): ?>
                <span class="wpjb-glyphs wpjb-icon-users" style="vertical-align: top" title="<?php esc_attr_e(__("Candidate Membership", "wpjobboard") . " (ID: ".$object->id.")") ?>"></span>
                <strong><a href="<?php esc_url(wpjb_admin_url("memberships", "edit", $object->id)) ?>"><?php esc_html_e($object->getPricing(true)->title) ?></a></strong>
                <?php endif; ?>
                

                
            </td>
            <td data-colname="<?php esc_attr_e("Created At", "wpjobboard") ?>" class="">
                <?php echo wpjb_date($item->created_at) ?>
            </td>
            <td data-colname="<?php esc_attr_e("User", "wpjobboard") ?>" class="">
                <?php if($item->user_id < 1): ?>
                <?php _e("Anonymous", "wpjobboard") ?>
                <?php else: ?>
                <a href="user-edit.php?user_id=<?php echo $item->user_id ?>"><?php echo esc_html($item->getUser()->display_name." (ID: ".$item->getUser()->getId().")") ?></a>
                <?php endif; ?>
            </td>
            <td data-colname="<?php esc_attr_e("Payment", "wpjobboard") ?>" class="">
                <?php if($item->engine): ?>
                <?php esc_html_e($item->engine) ?>
                <?php else: ?>
                —
                <?php endif; ?>
            </td>
            <td data-colname="<?php esc_attr_e("To Pay", "wpjobboard") ?>" class="">
                <?php echo wpjb_price($item->payment_sum, $item->payment_currency) ?>
            </td>
            <td data-colname="<?php esc_attr_e("Paid", "wpjobboard") ?>" class="" style="color:<?php if($item->payment_sum==$item->payment_paid): ?>green<?php else: ?>red<?php endif; ?>">
                <?php echo wpjb_price($item->payment_paid, $item->payment_currency) ?>
                <?php if($item->payment_sum==$item->payment_paid): ?>
                <small><?php echo wpjb_date($item->paid_at) ?></small>
                <?php endif; ?>
            </td>
            <td data-colname="<?php esc_attr_e("Status", "wpjobboard") ?>">
                <?php $status = wpjb_get_payment_status($item->status) ?>
                <span class="wpjb-bulb">
                    <?php esc_html_e($status["label"]) ?>
                </span>
            </td>

        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

    
<div class="tablenav">
    <div class="tablenav-pages">
        <?php
        echo paginate_links( array(
            'base' => wpjb_admin_url("payment", "index", null)."%_%",
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
            <option value="markpaid"><?php _e("Mark as Paid", "wpjobboard") ?></option>
            <option value="delete"><?php _e("Delete", "wpjobboard") ?></option>
            <?php do_action( "wpjb_bulk_actions", "payment" ); ?>
        </select>

        <input type="submit" class="button action" id="wpjb-doaction1" value="<?php _e("Apply", "wpjobboard") ?>" />

    </div>
    
    <br class="clear"/>
</div>
</form>

    
</div>
