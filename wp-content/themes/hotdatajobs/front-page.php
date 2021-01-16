<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package hotdatajobs
 */

get_header();
?>
<div class="home-hero-section" id="homepage_background">
	<h2>
		<?php 
			echo get_field('hero_text'); 
		?>
	</h2>
	<?php if(is_user_logged_in()){ ?>
	<a href="<?php echo get_field('button_1_url'); ?>" class="button button-darkteal"><?php echo get_field('button_1_text'); ?></a>
	<?php } else{ ?>
	<a href="employer-panel/employer-registration/" class="button button-darkteal"><?php echo get_field('button_1_text'); ?></a>
	<?php } ?>
	<a href="#main" class="button button-default"><?php echo get_field('button_2_text'); ?></a>
</div>
	<div id="primary" class="content-area  grid-container grid-x">
		<div class="cell large-12 text-center ad-banner">
			<?php if(get_field('banner_code_top')){
					echo get_field('banner_code_top');
			} ?>
		</div>
				<div class="sidebar_section sidebar_mobile">
		
		<div class="sidebar-left">
			<?php
			echo '<div class="filters_sidebar">';
			if(is_active_sidebar('page_left_sidebar')){
				dynamic_sidebar('page_left_sidebar');
			} ?>
					</div>
		</div>
</div>
			
			 <div class="sidebar_section sidebar_desktop">
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
		<div class="job_feed_section">
	<main id="main" class="site-main">
		<?php
		if ( have_posts() ) :

			if ( is_home() && ! is_front_page() ) :
				?>
				<header>
					<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
				</header>
				<?php
			endif;

			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				/*
				 * Include the Post-Type-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
				 */
				get_template_part( 'template-parts/content', get_post_type() );

			endwhile;

			the_posts_navigation();

		else :

			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>
		
		</main><!-- #main -->
		</div>
		<div class="sidebar_section sidebar_desktop">
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
		</div>
		<div class="cell large-12 column text-center ad-banner">
			<?php if(get_field('banner_code_bottom')){
						echo get_field('banner_code_bottom');
					}
			?>
		</div>
</div>
<?php
get_footer();
