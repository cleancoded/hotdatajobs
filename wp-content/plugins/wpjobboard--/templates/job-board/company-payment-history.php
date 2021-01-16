<?php
/**
 * Payment list
 * 
 * This template file is responsible for displaying list of jobs on job board
 * home page, category page, job types page and search results page.
 * 
 * 
 * @author Mark Winiarski
 * @package Templates
 * @subpackage JobBoard

 */

?>

<div class="wpjb wpjb-page-index">

    <?php wpjb_flash(); ?>
    <?php wpjb_breadcrumbs($breadcrumbs) ?>

    <ul class="wpjb-tabs">
        <li class="wpjb-tab-link <?php if($browse == "all"):?>current<?php endif; ?>">
            <a href="<?php echo wpjb_link_to("payment_history", null, array("filter"=>"all")) ?>"><?php _e("All", "wpjobboard"); ?></a> (<?php echo $total->all ?>)
        </li>
        <li class="wpjb-tab-link <?php if($browse == "completed"):?>current<?php endif; ?>">
            <a href="<?php echo wpjb_link_to("payment_history", null, array("filter"=>"completed")) ?>"><?php _e("Completed", "wpjobboard"); ?></a> (<?php echo $total->completed ?>)
        </li>
        <li class="wpjb-tab-link <?php if($browse == "pending"):?>current<?php endif; ?>">
            <a href="<?php echo wpjb_link_to("payment_history", null, array("filter"=>"pending")) ?>"><?php _e("Pending", "wpjobboard"); ?></a> (<?php echo $total->pending ?>)
        </li>
        <li class="wpjb-tab-link <?php if($browse == "failed"):?>current<?php endif; ?>">
            <a href="<?php echo wpjb_link_to("payment_history", null, array("filter"=>"failed")) ?>"><?php _e("Failed", "wpjobboard"); ?></a> (<?php echo $total->failed ?>)
        </li>
        <li class="wpjb-tab-link <?php if($browse == "refunded"):?>current<?php endif; ?>">
            <a href="<?php echo wpjb_link_to("payment_history", null, array("filter"=>"refunded")) ?>"><?php _e("Refunded", "wpjobboard"); ?></a> (<?php echo $total->refunded ?>)
        </li>
    </ul>
  
    <div class="wpjb-grid wpjb-tab-content">
        <?php if ( $result->count ) : foreach($result->payments as $item): ?>
        <?php /* @var $job Wpjb_Model_Payment */ ?>
        <?php include $this->getTemplate("job-board", "payment-history-item") ?>
        <?php endforeach; else :?>
        <div class="wpjb-grid-row">
            <?php _e("No payment history found.", "wpjobboard"); ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if($pagination): ?>
    <div class="wpjb-paginate-links">
        <?php wpjb_paginate_links($url, $result->pages, $result->page) ?>
    </div>
    <?php endif; ?>
 
</div>