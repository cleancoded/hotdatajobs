<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
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
<?php if(is_page('12')){
	?>
<div class="grid-x grid-container">
	<div class="cell large-6 small-12 column">
<?php } ?>
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
		<div class="page_section">
			
		<?php
		}
		?>
<?php if(is_page(array('8', '6'))){
	?>
		<div class="grid-container page_content"><div class="sidebar_section"> <div class="sidebar_desktop">
		<div class="sidebar-left">
			<?php
			echo '<div class="filters_sidebar">';
			if(is_active_sidebar('page_left_sidebar')){
				dynamic_sidebar('page_left_sidebar');
			}
			echo '</div>';
			?>
			<div class="common_sidebar">
					<?php if(is_active_sidebar('common_left_sidebar')){
 								dynamic_sidebar('common_left_sidebar');
						} ?>
				</div>
			</div>
		</div>
			</div>
		<div class="job_feed_section">
			<?php
}
		?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', 'page' );

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile; // End of the loop.
		?>

		</main><!-- #main -->
	</div><!-- #primary -->
<?php if(is_page('12')){
	?>
	</div>
	<div class="cell large-6 small-12 column">
		<div class="employer-reg-banner" style="background-image:url(<?php echo get_field('emp_banner')['url']; ?>)">
			<div class="employer-reg-banner-overlay">
				<h3>
					<?php echo get_field('emp_banner_text'); ?>
				</h3>
			</div>
		</div>
		<?php  ?>
		
<?php } ?>
<?php
if(is_page('12')){
	echo '</div>';
	echo '</div>';
}else if(is_page(array('6','8'))){
	?>
			</div>	<div class="sidebar_section sidebar_desktop">
			<div class="sidebar-right">
			<?php			
			if(is_active_sidebar('page_right_sidebar')){
				dynamic_sidebar('page_right_sidebar');
			}
		?>
			</div>
		</div>
		
		<div class="sidebar_section sidebar_mobile">
		
		<div class="sidebar-left">
			<?php
			echo '<div class="filters_sidebar">';
			if(is_active_sidebar('page_left_sidebar')){
				dynamic_sidebar('page_left_sidebar');
			}
			echo '</div>';
			echo '<div class="common_sidebar">';
			if(is_active_sidebar('common_left_sidebar')){
				dynamic_sidebar('common_left_sidebar');			
			}
			echo '</div>';
		
		?>
			</div>
		</div>
		<div class="sidebar_section sidebar_mobile">
			<div class="sidebar-right">
			<?php
			
			if(is_active_sidebar('page_right_sidebar')){
				dynamic_sidebar('page_right_sidebar');
			}
		?>
				
			</div>
		</div></div>
		<?php
}
		?>
		<?php if(get_field('choose_sidebar_maneu')){
	echo '</div></div>';
}
			?>
		<?php if(get_field('banner_code_bottom')){ echo '<div class="ad_banner">';
	echo get_field('banner_code_bottom'); 
				
		echo	'</div>'; 
			} 
get_footer();
