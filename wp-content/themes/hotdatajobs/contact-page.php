<?php
/**
 * Template Name: Contact Page
 *
 * @package hotdatajobs
 */

get_header();
if(get_field('banner_code_top')) {
echo '<div class="ad_banner">';
echo get_field('banner_code_top');
echo '</div>';
}
 
?>
		<?php if(get_field('choose_sidebar_maneu')){
	?>
		<div class="standard-page grid-container">
			
		<div class="sidebar_section">
			<div class="sidebar-left">
				<ul class="menu vertical">
			<?php
	$menu_items = wp_get_nav_menu_items(get_field('choose_sidebar_maneu'));
	foreach($menu_items as $menu_item){
		$active_class = (get_queried_object_id() == $menu_item->object_id) ? "active" : "";
		echo '<li><a href="'.$menu_item->url.'" class="'.$active_class.'">'.$menu_item->title.'</a></li>';
	}
	?>
	</ul>
			</div>
		</div>
			
		<?php
		}
		?>
		<div class="job_feed_section" style="float:left;">
			<div class="contact_form">
		<?php 
		echo do_shortcode(get_field('form_shortcode'));
		?>
		</div>
	</div>
		<div class="sidebar_section">
		<div class="contact_form_info">

		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile; // End of the loop.
		?>
	</div>

		</div>
	</div>		
		<?php if(get_field('banner_code_bottom')){ echo '<div class="ad_banner">';
	echo get_field('banner_code_bottom'); 
				
		echo	'</div>'; 
			} 
get_footer();
