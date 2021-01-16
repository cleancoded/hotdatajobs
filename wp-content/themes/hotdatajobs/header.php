<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package hotdatajobs
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
	<!-- Facebook Pixel Code -->
	<script>
		jQuery(document).ready(function(){
			jQuery('#top-apply-btn-url').attr('href','?form=apply#wpjb-scroll');
		});
	</script>
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '237059050270410');
  fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
  src="https://www.facebook.com/tr?id=237059050270410&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->
<script type="text/javascript">
_linkedin_partner_id = "408180";
window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
window._linkedin_data_partner_ids.push(_linkedin_partner_id);
</script><script type="text/javascript">
(function(){var s = document.getElementsByTagName("script")[0];
var b = document.createElement("script");
b.type = "text/javascript";b.async = true;
b.src = "https://snap.licdn.com/li.lms-analytics/insight.min.js";
s.parentNode.insertBefore(b, s);})();
</script>
<noscript>
<img height="1" width="1" style="display:none;" alt="" src="https://dc.ads.linkedin.com/collect/?pid=408180&fmt=gif" />
</noscript>	

	
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.1&appId=536923266761124&autoLogAppEvents=1';
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
	
	
	
	
</head>

<body <?php body_class(); ?>>
<div id="page" class="site off-canvas-wrapper">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'hotdatajobs' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="grid-container">
			<div class="top-bar">
				<div class="top-bar-left">
			
					<div class="site-branding"><?php the_custom_logo();
						if ( is_front_page() && is_home() ) :
				?>
				<h1 class="menu-text"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				<?php
			else :
				?>
				<h2 class="m-enu-text"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h2>
				<?php
			endif; 
			?>
		</div><!-- .site-branding -->
		</div>
		<div class="top-bar-right">
			<nav id="site-navigation" class="main-navigation">
			<?php
				if(is_user_logged_in()){
					?>
				<ul id="primary-menu">
			<li class="menu-item nav-btn"><a href="/employer-panel">Employer Dashboard</a></li>
		</ul>
		
				<?php
				}else{
			wp_nav_menu( array(
				'theme_location' => 'menu-1',
				'menu_id'        => 'primary-menu',
			) );
				}
			?>
		</nav><!-- #site-navigation -->
			<button type="button" data-toggle="offcanvas" class="button toggle-menu">
				<i class="fa fa-bars"></i>
			</button>
			</div>
		</div>
		</div>
	</header><!-- #masthead -->
	<div class="off-canvas position-left" id="offcanvas" data-off-canvas>
		<button type="button" class="close-button" aria-label="close menu" data-close>
			<span aria-hidden="true">&times;</span>
		</button> 
		<?php
		if(is_user_logged_in()){
			?>
		<ul class="vertical menu">
			<li><a href="/employer-panel">Employer Dashboard</a></li>
		</ul>
		<?php
		}else{
			wp_nav_menu(array(
				'theme_location' => 'menu-1',
				'menu_class' => 'vertical menu',
			));
		}
		?>
	</div>

	<div id="content" class="site-content off-canvas-content" data-off-canvas-content>