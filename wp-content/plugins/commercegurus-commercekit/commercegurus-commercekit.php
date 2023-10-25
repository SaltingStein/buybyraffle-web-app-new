<?php

/**
 *
 * @link              https://www.commercegurus.com
 * @since             1.0.0
 * @package           CommerceGurus_Commercekit
 *
 * @wordpress-plugin
 * Plugin Name:       CommerceGurus Commercekit
 * Plugin URI:        https://www.commercegurus.com
 * Description:       CommerceGurus Commercekit
 * Version:           1.0.3
 * Author:            CommerceGurus
 * Author URI:        https://www.commercegurus.com
 * Requires at least: 4.9.7
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       commercegurus-commercekit
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly....
}

/**
 * Required minimums and constants
 */
define( 'CGKIT_MIN_WC_VER', '1.0.0' );

define( 'CGKIT_BASE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CGKIT_BASE_URL', plugin_dir_url( __FILE__ ) );

// require_once BASE_PATH . 'includes/cg-subs-api.php';
/**
 * Main CommerceGurus_Commercekit Class
 *
 * @class CommerceGurus_Commercekit
 * @version 1.0.0
 * @since 1.0.0
 * @package CommerceGurus_Commercekit
 */

if ( ! class_exists( 'CommerceGurus_Commercekit' ) ) {

	class CommerceGurus_Commercekit {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		const VERSION = '1.0.0';

		/**
		 * Notices (array)
		 *
		 * @var array
		 */
		public $notices = array();

		public function __construct() {
			$this->includes();
			add_action( 'extra_theme_headers', array( $this, 'cg_extra_theme_headers' ) );
		}

		/**
		 * Init the plugin after plugins_loaded so environment variables are set.
		 */
		public function includes() {

			if ( is_admin() ) {
				include_once CGKIT_BASE_PATH . 'includes/admin/class-cg-admin.php';
			}
		}

		public function cg_extra_theme_headers( $headers ) {
			$headers[] = 'CGMeta';
			return $headers;
		}
		/**
		 * Init the plugin after plugins_loaded so environment variables are set.
		 */
		public function init() {
			// Don't hook anything else in the plugin if we're in an incompatible environment.
			if ( self::get_environment_warning() ) {
				return;
			}

			/**
			 * Register endpoints.
			 */

		}


		/**
		 * The backup sanity check, in case the plugin is activated in a weird way,
		 * or the environment changes after activation.
		 */
		public function check_environment() {
			$environment_warning = self::get_environment_warning();

			if ( $environment_warning && is_plugin_active( plugin_basename( __FILE__ ) ) ) {
				$this->add_admin_notice( 'bad_environment', 'error', $environment_warning );
			}
		}

		/**
		 * Checks the environment for compatibility problems.  Returns a string with the first incompatibility
		 * found or false if the environment has no problems.
		 */
		static function get_environment_warning() {
			if ( ! defined( 'WC_VERSION' ) ) {
				return __( 'CommerceGurus Subscriptions API requires WooCommerce 3.0+ to be activated to work.', 'commercegurus-subscriptions-api' );
			}

			if ( version_compare( WC_VERSION, CG_SUBSAPI_MIN_WC_VER, '<' ) ) {
				$message = __( 'CommerceGurus Subscriptions API - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'commercegurus-subscriptions-api', 'commercegurus-subscriptions-api' );

				return sprintf( $message, CG_SUBSAPI_MIN_WC_VER, WC_VERSION );
			}

			return false;
		}

		/**
		 * Display any notices we've collected thus far.
		 */
		public function admin_notices() {
			foreach ( (array) $this->notices as $notice_key => $notice ) {
				echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
				echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) );
				echo '</p></div>';
			}
		}

		/**
		 * Allow this class and other classes to add slug keyed notices (to avoid duplication)
		 */
		public function add_admin_notice( $slug, $class, $message ) {
			$this->notices[ $slug ] = array(
				'class'   => $class,
				'message' => $message,
			);
		}

		public function cg_subs_get_search_args() {
			$args      = array();
			$args['d'] = array(
				'description' => esc_html__( 'The domain name to check.', 'commercegurus-subscriptions-api' ),
				'type'        => 'string',
			);

			return $args;
		}

	}

	$CommerceGurus_Commercekit = new CommerceGurus_Commercekit();
}
