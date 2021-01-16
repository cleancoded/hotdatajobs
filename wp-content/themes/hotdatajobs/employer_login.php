<?php
/**
 * Template Name: Empoloyer Login page
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
if(is_user_logged_in()){
	wp_redirect('/employer-panel');
}
?>
		<div class="grid-container page_content employer_signin">
			<div id="primary" class="content-area grid-container">
			<main id="main" class="site-main">
				<?php the_title('<h1 class="entry-title">', '</h1>'); ?>
    <form action="" method="post" enctype="multipart/form-data" id="emp_login">
		<?php if(isset($_GET['loginerror']) && $_GET['loginerror'] == "invalid"){ echo '<p class="login-error">Invalid username or password entered, please check and try again.</p>';} ?>
		<div class="wpjb-flash-error login-errors"> <span class="wpjb-glyphs wpjb-icon-attention">There are errors in your form.</span></div>
        <input id="_wpjb_action" name="_wpjb_action" value="login" type="hidden">
        <input id="redirect_to" name="redirect_to" value="http://hotdatajobs.flywheelsites.com/employer-panel/" type="hidden">
			<label class="wpjb-label"> Email Address <span class="wpjb-required">*</span> </label>
       		<div class="input-group">
				<span class="input-group-label"><i class="fa fa-user"></i></span>
         	   <input id="user_login" class="input-group-field" name="user_login" placeholder="Your email Address" type="text">				
				</div>
				<ul id="user_login_error"><li>Username or Email Address is required</li></ul>
				
        	
			
            <label class="wpjb-label"> Password <span class="wpjb-required">*</span> </label>
				<div class="input-group">
					<span class="input-group-label"><i class="fa fa-lock"></i></span>
		            <input id="user_password" class="input-group-field" name="user_password" placeholder="Your password" type="password">
					</div>
					<ul id="user_password_error"><li>Password is required</li></ul>

		
		<p>
			<a href="/reset-password" class="right-aligned link">Forgot Password?</a>
		</p>
       	<p>
            <input type="button" class="wpjb-submit button button-darkteal check_login" name="wpjb_submit" id="wpjb_submit" value="Sign In"> 
		</p>
		<p>
			
           Don't have an account?<br />
            <a class="button button-white button-small" href="http://hotdatajobs.flywheelsites.com/employer-panel/employer-registration/">Sign Up</a>
        
		</p>
    </form>
			</main><!-- #main -->
	</div><!-- #primary -->
</div>
<?php
get_footer();
