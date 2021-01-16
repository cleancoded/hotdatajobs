<?php
/**
 * Template Name: Empoloyer Password Reset Page
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
?>
		<div class="grid-container page_content employer_signin">
			<div id="primary" class="content-area grid-container">
			<main id="main" class="site-main">
				<?php the_title('<h1 class="entry-title">', '</h1>'); ?>
				<form name="lostpasswordform" id="lostpasswordform" action="/wp-login.php?action=lostpassword" method="post">
					<div class="wpjb-flash-error login-errors"><span class="wpjb-glyphs wpjb-icon-attention">There are errors in your form.</span></div>
					<p>
						<label for="user_login">Username or Email Address<br/>
						<input type="text" name="user_login" id="user_login" placeholder="Enter Email address or Username" class="input" value="" size="20"></label>
					<ul id="user_login_error"><li>Username or Email Address is required</li></ul>
					</p>
						<input type="hidden" name="redirect_to" value="/employer-sign-in">
					<p class="submit"><button type="submit" name="wp-submit" id="wp-submit" class="button button-darkteal button-large expand check_pwd">Get New Password</button></p>
				</form>
				<p class="right-aligned">
					<a href="/employer-sign-in" class="link">Back to Sign In</a>
			</div>
			</main><!-- #main -->
		</div><!-- #primary -->
<?php
get_footer();
