<?php 

/**
 * Job list item
 * 
 * This template is responsible for displaying job list item on job list page
 * (template index.php) it is alos used in live search
 * 
 * @author Greg Winiarski
 * @package Templates
 * @subpackage JobBoard
 */

 /* @var $job Wpjb_Model_Job */

?>

    <div class="wpjb-grid-row wpjb-click-area <?php wpjb_job_features($job); ?>">
      <div class="grid-x">
		  
<div class="cell large-9 small-12 column">
	
        <div class="wpjb-grid-col wpjb-col-main wpjb-col-title">
            
            <div class="wpjb-line-major">
                <?php if($job->doScheme("job_title")): else: ?>
                <a href="<?php echo wpjb_link_to("job", $job) ?>" class="wpjb-job_title wpjb-title"><?php echo esc_html($job->job_title) ?></a>
                <?php endif; 
// 				   print_r($job->meta->salary_offered->value());
				?>
				
                
                <?php if($job->isNew()): ?>
                <span class="wpjb-bulb"><?php _e("new", "wpjobboard") ?></span>
                <?php endif; ?>
                
                <?php if(isset($job->getTag()->type[0])): ?>
               <!-- <span class="wpjb-job_type wpjb-sub-title" style="color:#<?php echo $job->getTag()->type[0]->meta->color ?>">
                    <?php echo esc_html($job->getTag()->type[0]->title) ?>
                </span> -->
                <?php endif; ?>
				    <div class="wpjb-logo show-on-mobile">
            <?php if($job->doScheme("company_logo")): ?>
            <?php elseif($job->getLogoUrl()): ?>
            <div class="wpjb-img-100">
                <img src="<?php echo $job->getLogoUrl("50x50") ?>" alt="" class="" />
            </div>
            <?php elseif($job->getCompany(true)->getLogoUrl()): ?>
            <div class="wpjb-img-50">
                <img src="<?php echo $job->getCompany(true)->getLogoUrl("50x50") ?>" alt="" class="" />
            </div>
            <?php else: ?>
            <div class="wpjb-img-50 wpjb-icon-none">
                <span class="wpjb-glyphs wpjb-icon-industry wpjb-icon-50"></span>
            </div>
            <?php endif; ?>
        </div>
				
            </div>
            
            <div class="wpjb-line-minor">
<!-- 				<span> -->
				<?php 
// 					echo $job->meta->salary_offered->value();
				?>
<!-- 				</span> -->
               
                 <span class="wpjb-sub wpjb-sub-opaque wpjb-job_location">
<!--                     <span class="wpjb-glyphs wpjb-icon-location"> -->
					
						<?php echo  $job->job_city .', '. $job->job_state; ?>
					  <?php  if($job->doScheme("company_name")): else: ?> | 
                <span class="wpjb-sub wpjb-company_name"><?php echo esc_html($job->company_name) ?></span>
                <?php endif; ?>

<!-- 				</span> -->
<!--                 </span>

                <span class="wpjb-sub wpjb-sub-opaque wpjb-sub-right wpjb-job_created_at"> -->
                 <?php 
	//echo ' | ' .wpjb_date_display("M, d", $job->job_created_at, false); 
	//?>
                </span>

                <?php do_action( "wpjb_tpl_index_item", $job->id ) ?>
            </div>
			 <p>
				 
			<?php
				 echo substr($job->job_description, 0, 190) . '...'; 
				 ?>
			</p>
			<p>
                <a href="<?php echo wpjb_link_to("job", $job) ?>" class="button button-white button-view-job">View Details</a>
				
			</p>
			
        </div>
		  </div>
		  <div class="cell large-3 small-12 column vertical-centered hidden-on-mobile">
			    <div class="wpjb-grid-col wpjb-col-logo">
            <?php if($job->doScheme("company_logo")): ?>
            <?php elseif($job->getLogoUrl()): ?>
            <div class="wpjb-img-100">
                <img src="<?php echo $job->getLogoUrl("50x50") ?>" alt="" class="" />
            </div>
            <?php elseif($job->getCompany(true)->getLogoUrl()): ?>
            <div class="wpjb-img-50">
                <img src="<?php echo $job->getCompany(true)->getLogoUrl("50x50") ?>" alt="" class="" />
            </div>
            <?php else: ?>
            <div class="wpjb-img-50 wpjb-icon-none">
                <span class="wpjb-glyphs wpjb-icon-industry wpjb-icon-50"></span>
            </div>
            <?php endif; ?>
        </div>
		  </div>
		</div>
		

    </div>