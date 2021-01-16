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
	<?php if(is_home()):
				?>
<div class="home-hero-section">
	<h2>
		Laeading Job site for data, analytics and business analytics.
		</h2>
		<a href="#" class="button button-darkteal">POST A JOB</a> <a href="#" class="button button-default">FIND A JOB</a>
</div>
<?php
			endif;
?>
	<div id="primary" class="content-area  grid-container grid-x">
		<div class="cell large-3">
			<?php if(is_active_sidebar('page_left_sidebar')) {
			dynamic_sidebar('page_left_sidebar');
				}?>
		</div>
			 
		<div class="cell large-6 small-12 medium-6">
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
		<div class="cell large-3">
		
		<?php
			get_sidebar();
		?>
		</div>
</div>
<?php
get_footer();
