<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package hotdatajobs
 */

?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<div class="grid-container">
			<div class="footer-widgets">
				<div class="grid-x">
					<div class="cell large-3 medium-6 small-12 column">
						<?php if(is_active_sidebar('footer_sidebar_1')){
								dynamic_sidebar('footer_sidebar_1');
						} ?>
					</div>
					<div class="cell large-3 medium-6 small-12 column">
						<?php if(is_active_sidebar('footer_sidebar_2')){
								dynamic_sidebar('footer_sidebar_2');
						} ?>
					</div>
					<div class="cell large-3 medium-6 small-12 column">
						<?php if(is_active_sidebar('footer_sidebar_3')){
								dynamic_sidebar('footer_sidebar_3');
							} ?>
					</div>
					<div class="cell large-3 medium-6 small-12 column">
						<?php if(is_active_sidebar('footer_sidebar_4')){
								dynamic_sidebar('footer_sidebar_4');
						} ?>
					</div>
				</div>
			</div>
			
		<div class="site-info">
		<?php echo 'Copyright '.date('Y').' &copy; <a href="'.get_bloginfo('url').'">' . get_bloginfo('name') .' </a> | All rights reserved.'; ?>	
		</div><!-- .site-info -->
			</div></footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('.toggle_filters').on('click', function(event){
		event.preventDefault();
		jQuery(this).next('.filter').toggle('slow');		
	});
	//add envelope icon to newsletter widget title of right sidebar
	jQuery('.widget_mc4wp_form_widget').each(function(i, v){
		jQuery(v).find('.widget-title').html('<i class="fa fa-envelope-open"></i> '+jQuery(v).find('.widget-title').text());
	});
	
	
	/*jQuery('#apply-btn-top').on('click', function(){
		jQuery('#wpjb-form-job-apply').toggle();
		jQuery('html, body').animate({
			scrollTop: jQuery('#wpjb-form-job-apply').offset().top
		});
	});*/
	if(jQuery('.wpjb-element-name-company_logo')){
		jQuery('.wpjb-element-name-company_logo').find('label').append('<small>(200x2000 Pixels)</small>');
	}
	//homepage bg
	  function hp_bg(){
            jQuery.getScript('https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js',function(){
                particlesJS("homepage_background", {
                    "particles": {
                        "number": {
                            "value": 100,
                            "density": {
                                "enable": true,
                                "value_area": 800
                            }
                        },
                        "color": {
                            "value": "#ffffff"
                        },
                        "shape": {
                            "type": "circle",
                            "stroke": {
                                "width": 0,
                                "color": "#000000"
                            },
                            "polygon": {
                                "nb_sides": 5
                            },
                            "image": {
                                "src": "img/github.svg",
                                "width": 100,
                                "height": 100
                            }
                        },
                        "opacity": {
                            "value": 0.5,
                            "random": false,
                            "anim": {
                                "enable": false,
                                "speed": 1,
                                "opacity_min": 0.1,
                                "sync": false
                            }
                        },
                        "size": {
                            "value": 3,
                            "random": true,
                            "anim": {
                                "enable": false,
                                "speed": 40,
                                "size_min": 0.1,
                                "sync": false
                            }
                        },
                        "line_linked": {
                            "enable": true,
                            "distance": 150,
                            "color": "#ffffff",
                            "opacity": 0.4,
                            "width": 1
                        },
                        "move": {
                            "enable": true,
                            "speed": 6,
                            "direction": "none",
                            "random": false,
                            "straight": false,
                            "out_mode": "out",
                            "bounce": false,
                            "attract": {
                                "enable": false,
                                "rotateX": 600,
                                "rotateY": 1200
                            }
                        }
                    },
                    "interactivity": {
                        "detect_on": "canvas",
                        "events": {
                            "onhover": {
                                "enable": true,
                                "mode": "grab"
                            },
                            "onclick": {
                                "enable": true,
                                "mode": "push"
                            },
                            "resize": true
                        },
                        "modes": {
                            "grab": {
                                "distance": 140,
                                "line_linked": {
                                    "opacity": 1
                                }
                            },
                            "bubble": {
                                "distance": 400,
                                "size": 40,
                                "duration": 2,
                                "opacity": 8,
                                "speed": 3
                            },
                            "repulse": {
                                "distance": 200,
                                "duration": 0.4
                            },
                            "push": {
                                "particles_nb": 4
                            },
                            "remove": {
                                "particles_nb": 2
                            }
                        }
                    },
                    "retina_detect": true
                });
            });
        }
	<?php if(is_front_page()){ ?> hp_bg(); <?php } ?>
});
	//init foundation
	jQuery(document).ready(function(){
	jQuery(document).foundation();
	<?php 
		if(is_page('12')){ ?>
		jQuery('.wpjb-form-actions').append('<p class="text-center bottom-text">Already have an account? <a class="button button-white button-small" href="/employer-sign-in">Sign In</a></p>');	
		jQuery('<p class="text-center"><small>By signing up you agree to Hot Data Jobs <a href="/terms" target="_blank">Terms of Service</a> and <a href="/privacy-policy" target="_blank"> Privacy Policy</a>.</small></p>').insertAfter('#wpjb_submit');
		jQuery('input[name="full_name"]').attr("placeholder", "First and last name");
		jQuery('input[name="user_email"]').attr("placeholder", "Your work email");
		jQuery('input[name="user_login"]').attr("placeholder", "user name");
		jQuery('input[name="user_password"]').attr("placeholder", "Create password");
		jQuery('input[name="company_website"]').attr("placeholder", "www.yourcompany.com");
		jQuery('input[name="company_phone"]').attr("placeholder", "xxx-xxx-xxxx");
		jQuery('input[name="company_name"]').attr("placeholder", "Company name");
		jQuery('input[name="job_title"]').attr("placeholder", "Your role or position");
		<?php } ?>
		<?php if(is_page('11')){ ?>
			jQuery('.wpjb-box-membership').find('.wpjb-box-title').text('Purchase Package');
		<?php } ?>
		<?php if(is_page('119')){ ?>
			jQuery('#male_cl').circleProgress({
				'value': 0.74,
				'size': 150,
				'fill':'#439a8a',
				'emptyFill': '#EEE',
				'thickness': 15
			});
			jQuery('#age_cl').circleProgress({
				'value': 0.68,
				'size': 150,
				'fill':'#439a8a',
				'emptyFill': '#EEE',
				'thickness': 15				
			});
			jQuery('#college_cl').circleProgress({
				'value': 0.78,
				'size': 150,
				'fill':'#439a8a',
				'emptyFill': '#EEE',
				'thickness': 15				
				
			});
			jQuery('#graduate_cl').circleProgress({
				'value': 0.33,
				'size': 150,
				'fill':'#439a8a',
				'emptyFill': '#EEE',		
				'thickness': 15
			});
		<?php } ?>
		if(jQuery('.check_pwd')[0]){
			jQuery('.check_pwd').on('click', function(event){
				event.preventDefault();
				if(!jQuery('#user_login').val()){
					jQuery('#user_login').addClass('input-error');	
					jQuery('#user_login_error').show();
					jQuery('.login-errors').show();
					jQuery('#user_login').prev('.input-group-label').addClass('input-error');					
					
				}else{
					jQuery('#user_login').removeClass('input-error');	
					jQuery('#user_login_error').hide();
					jQuery('#user_login').prev('.input-group-label').removeClass('input-error');				
					jQuery('#lostpasswordform').submit();
				}
			});			
		}
		if(jQuery('.check_login')[0]){
			jQuery('.check_login').on('click', function(event){
				event.preventDefault();
				if(!jQuery('#user_login').val()){
					jQuery('#user_login').addClass('input-error');
					jQuery('#user_login_error').show();
					jQuery('.login-errors').show();
					jQuery('#user_login').prev('.input-group-label').addClass('input-error');
					
				}else{
					jQuery('#user_login').removeClass('input-error');	
					jQuery('#user_login_error').hide();
					jQuery('#user_login').prev('.input-group-label').removeClass('input-error');
				}
				if(!jQuery('#user_password').val()){
					jQuery('#user_password').addClass('input-error');
					jQuery('#user_password').prev('.input-group-label').addClass('input-error');
					jQuery('#user_password_error').show();
					jQuery('.login-errors').show();					
				}else{
					jQuery('#user_password').removeClass('input-error');					
					jQuery('#user_password_error').hide();		
					jQuery('#user_password').prev('.input-group-label').removeClass('input-error');
				}
				if(jQuery('#user_login').val() && jQuery('#user_password').val()){
					jQuery('.login-errors').hide();					
					jQuery('#emp_login').submit();
				}
			});
		}
		setTimeout(function(){
		jQuery('.wpjb .wpjb-job-apply a.wpjb-button').text('Apply to this Job');
		jQuery('.wpjb .wpjb-job-apply a.wpjb-button').css('color','#FFF');
		//handle apply now button
		if(jQuery('#top-apply-btn-url')[0]){
			jQuery('#top-apply-btn-url').attr('href', jQuery('.wpjb-job-apply').find('a').attr('href'));
			jQuery('#top-apply-btn-url').attr('target', '_blank');
		}}, 250);
		<?php if(is_page('12')){ ?>
			jQuery('#user_password').attr('type', 'password');
			jQuery('#user_password2').attr('type', 'password');

			jQuery('.wpjb-form').on('submit', function (event) {
				event.preventDefault;
			if(jQuery('#user_password').val() !== jQuery('#user_password2').val()){
				jQuery('.wpjb-flash-error').show();
				jQuery('.wpjb-flash-error').append('<p>password not matched, please check and try again</p>');
				return false;
			}else{
				// jQuery('.wpjb-flash-error').hide();
				return true;
			}


			});
			<?php } ?>
	});
</script>

</body>
</html>
