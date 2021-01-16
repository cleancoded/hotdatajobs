<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package hotdatajobs
 */

get_header();
?>

	<div id="primary" class="content-area grid-container">
			<div class="page_section">
				
		<main id="main" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', get_post_type() );

			the_post_navigation();

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile; // End of the loop.
		?>

		</main><!-- #main -->
			</div>
		
	
<div class="sidebar_section">
	<div class="sidebar-right">
	
	<?php
		if(is_active_sidebar('job_sidebar')){
			dynamic_sidebar('job_sidebar');
		}
	?>
		</div>
		</div>
		<?php
get_footer();