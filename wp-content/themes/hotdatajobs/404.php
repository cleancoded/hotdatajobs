<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package hotdatajobs
 */

get_header();
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">

			<section class="error-404 not-found">
			<div class="grid-container">
				<div class="page-content">
			<div class="grid-x">

				<div class="cell large-6 small-12 column 404-img">
					<img src="http://hotdatajobs.flywheelsites.com/wp-content/uploads/2018/09/Screenshot-from-2018-09-05-00-51-57.png" alt="not found"/>
				</div>
				<div class="cell large-6 small-12 column">
					<div class="text404">
						<h3>
							We couldn't find what you are looking for. 
						</h3>
						<p>
							Unfortunately the page were looking for could not be found. It may be temporarily unavailable, moved or no longer exist.
						</p>
						<p>
							Please check the URL you entered for any mistakes and try again. Alternatively, please <a href="<?php echo get_bloginfo('url'); ?>">click here</a> to visit our home page.
						</p>
					<div>
				</div>

				</div>
			</div>
		</div>
			</section><!-- .error-404 -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
