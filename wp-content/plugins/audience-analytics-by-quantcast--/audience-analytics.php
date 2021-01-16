<?php
/*
Plugin Name: Audience Analytics – by Quantcast
Plugin URI:  https://www.quantcast.com/
Description: Quantcast Measure provides free, powerful audience measurement for your site. Please go to Settings - Quantcast to get started.
Version:     1.0.1
Author:      Quantcast
Author URI:  https://www.quantcast.com/
Requires at least: 4.0
Tested up to: 4.9.8
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Quantcast' ) ) :

class WP_Quantcast {

	protected static $_instance = null;
	protected $plugin_name;
	protected $version;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

		$this->plugin_name = 'wp-quantcast';
		$this->version = '1.0.1';

		add_action( 'init' , array( $this, 'init' ) );
	}

	public function init() {

		add_action( 'admin_menu', array( $this, 'qc_add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'qc_admin_menu_init' ) );
		add_action( 'wp_footer',  array( $this, 'qc_footer_scripts' ) );
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'qc_settings_link' ) );
		add_action( 'admin_notices', array( $this, 'qc_admin_notice_pcode' ) );
	}

	/**
	 * Adds links to settings page
	 *
	 * @since  1.0.0
	 *
	 * @param  array $links Original links
	 * @return array $links Updated links
	 */
	public function qc_settings_link( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=wp-quantcast' ),  __( 'Settings', 'wp-quantcast' ) );

		return $links;
	}

	public function qc_admin_menu_init() {
		register_setting( 'wp-quantcast', 'wp-quantcast_settings' );

		add_settings_section(
			'qc_section',
			'',
			array( $this, 'qc_settings_section_callback' ),
			'wp-quantcast'
		);

		add_settings_field(
			'qc-pcode',
			__( 'Your Tracking ID (P-code)', 'wp-quantcast' ),
			array( $this, 'qc_p_code_callback' ),
			'wp-quantcast',
			'qc_section'
		);

	}

	public function qc_admin_notice_pcode() {

		$setting = get_option( 'wp-quantcast_settings' );

		if( ! $setting['qc-pcode'] ) {
			$error = sprintf( '%s<a href="%s">%s</a>', __( 'Audience Analytics – by Quantcast requires Your Tracking ID (P-code) for data collection. ', 'wp-quantcast' ) , admin_url( 'options-general.php?page=wp-quantcast' ),  __( 'Settings', 'wp-quantcast' ) );
		?>
			<div class="notice notice-warning">
				<p><?php echo $error; ?></p>
			</div>
		<?php
		}
	}

	public function qc_settings_section_callback() {
		?>
		<div class="quantcast-plugin-logo" style="position: relative; top: 25px; right: 45px;">
			<a href="https://quantcast.com"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/logo-black.png'; ?>" width="220" height="220"></a>
		</div>
		<?php
	}

	public function qc_p_code_callback() {

		$setting = get_option( 'wp-quantcast_settings' );
		?>
		<input type='text' name='wp-quantcast_settings[qc-pcode]' value='<?php echo $setting['qc-pcode']; ?>'> <small><?php echo esc_html_e( 'Profile will begin showing data once sufficient data has been collected, typically within a day', 'wp-quantcast' ); ?></small>
		<?php
	}

	public function qc_add_admin_menu() {
		add_options_page( __( 'Quantcast', 'wp-quantcast' ), __( 'Quantcast', 'wp-quantcast' ), 'manage_options', 'wp-quantcast', array( $this, 'quantcast_admin_page' ) );
	}

	public function quantcast_admin_page() {
		$setting = get_option( 'wp-quantcast_settings' );
    ?>
		<div class="wrap">
			<form action="options.php" method="POST">
				<?php settings_fields( 'wp-quantcast' ); ?>
				<?php do_settings_sections( 'wp-quantcast' ); ?>
				<?php
				// Display account link
				if( $setting['qc-pcode'] ) {
					?>
					<p><strong><a href="https://quantcast.com/<?php echo $setting['qc-pcode']; ?>" target="_blank"><?php esc_html_e( 'Go to your Quantcast website profile', 'wp-quantcast' ); ?></a></strong></p>
					<p><strong><a href="https://www.quantcast.com/measure/home" target="_blank"><?php esc_html_e( 'Go to your Quantcast dashboard', 'wp-quantcast' ); ?></a></strong></p>
					<?php
				}
				?>
				<?php submit_button(); ?>
			</form>
		</div>
    <?php
	}

	public function qc_footer_scripts() {
		$setting = get_option( 'wp-quantcast_settings' );
		if( ! $setting['qc-pcode'] ) {
			return;
		}
		?>
		<!-- Quantcast Tag -->
		<script type="text/javascript">
			var _qevents = _qevents || [];

			(function() {
				var elem = document.createElement('script');
				elem.src = (document.location.protocol == "https:" ? "https://secure" : "http://edge") + ".quantserve.com/quant.js";
				elem.async = true;
				elem.type = "text/javascript";
				var scpt = document.getElementsByTagName('script')[0];
				scpt.parentNode.insertBefore(elem, scpt);
			})();

			_qevents.push({
				qacct:"<?php echo $setting['qc-pcode']; ?>",
				source:"wp"
			});
		</script>

		<noscript>
		<div style="display:none;">
			<img src="//pixel.quantserve.com/pixel/<?php echo $setting['qc-pcode']; ?>.gif" border="0" height="1" width="1" alt="Quantcast"/>
		</div>
		</noscript>
		<!-- End Quantcast tag -->
		<?php
	}
}

endif;

function QC() {
	return WP_Quantcast::instance();
}
QC();
?>
