<?php
/**
 * Sas Crl Loader.
 *
 * @package sas-crl
 */

if ( ! class_exists( 'Sas_Crl_Loader' ) ) {

	/**
	 * Class Sas_Crl_Loader.
	 */
	final class Sas_Crl_Loader {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance = null;

		/**
		 * Member Variable
		 *
		 * @var helper
		 */
		public $helper = null;

		
		/**
		 *  Initiator
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {

				self::$instance = new self();

				/**
				 * Plugin loaded.
				 *
				 * Fires when plugin was fully loaded and instantiated.
				 *
				 * @since 1.0.0
				 */
				do_action( 'sas_crl_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->define_constants();

			// Activation hook.
			register_activation_hook( SAS_CRL_FILE, array( $this, 'activation_reset' ) );

			// deActivation hook.
			register_deactivation_hook( SAS_CRL_FILE, array( $this, 'deactivation_reset' ) );

			add_action( 'plugins_loaded', array( $this, 'load_plugin' ), 99 );
			add_action( 'plugins_loaded', array( $this, 'load_sas_crl_textdomain' ) );

		}

		/**
		 * Defines all constants
		 *
		 * @since 1.0.0
		 */
		public function define_constants() {

			define( 'SAS_CRL_BASE', plugin_basename( SAS_CRL_FILE ) );
			define( 'SAS_CRL_DIR', plugin_dir_path( SAS_CRL_FILE ) );
			define( 'SAS_CRL_URL', plugins_url( '/', SAS_CRL_FILE ) );
			define( 'SAS_CRL_VER', '1.0.0' );
			define( 'SAS_CRL_SLUG', 'sas-crl' );
			define( 'SAS_CRL_NAME', 'Custom user registration and login form' );
		}

		/**
		 * Loads plugin files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_plugin() {
			$this->load_core_files();

			/**
			 * Plugin Init.
			 *
			 * Fires when plugin is instantiated.
			 *
			 * @since 1.0.0
			 */
			do_action( 'sas_crl_init' );
		}
		
		/**
		 * Load Core Files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function load_core_files() {
			
			/* Admin File */
			if( is_admin() ){

				include_once SAS_CRL_DIR . 'admin/class-sas-crl-admin.php';
			}

			/* Modules */
			include_once SAS_CRL_DIR . 'modules/registration/class-sas-crl-registration.php';
			include_once SAS_CRL_DIR . 'modules/login/class-sas-crl-login.php';
			
		}


		/**
		 * Load plugin's Text Domain.
		 * This will load the translation textdomain depending on the file priorities.
		 *   1. Global Languages /wp-content/languages/sas-crl/ folder
		 *   2. Local dorectory /wp-content/plugins/sas-crl/languages/ folder
		 *
		 * @since 1.0.3
		 * @return void
		 */
		public function load_sas_crl_textdomain() {

			// Default languages directory plugins.
			$lang_dir = SAS_CRL_DIR . 'languages/';

			/**
			 * Filters the languages directory path to use for other plugins if want.
			 *
			 * @param string $lang_dir The languages directory path.
			 */
			$lang_dir = apply_filters( 'sas_crl_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter.
			global $wp_version;

			$get_locale = get_locale();

			if ( $wp_version >= 4.7 ) {
				$get_locale = get_user_locale();
			}

			$locale = apply_filters( 'plugin_locale', $get_locale, 'sas-crl' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'sas-crl', $locale );

			// Setup paths to current locale file.
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/sas-crl/ folder.
				load_textdomain( 'sas-crl', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/sas-crl/languages/ folder.
				load_textdomain( 'sas-crl', $mofile_local );
			} else {
				// Load the default language files.
				load_plugin_textdomain( 'sas-crl', false, $lang_dir );
			}
		}

		/**
		 * Activation Reset
		 */
		public function activation_reset() {
			
			include_once SAS_CRL_DIR . 'classes/class-sas-crl-helper.php';
			include_once SAS_CRL_DIR . 'classes/class-sas-crl-functions.php';
			include_once SAS_CRL_DIR . 'modules/registration/class-sas-crl-registration.php';
			include_once SAS_CRL_DIR . 'modules/login/class-sas-crl-login.php';

			flush_rewrite_rules();
		}

		/**
		 * Deactivation Reset
		 */
		public function deactivation_reset() {      }
	}

	/**
	 *  Prepare if class 'Sas_Crl_Loader' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Sas_Crl_Loader::get_instance();
}

/**
 * Get global class.
 *
 * @return object
 */
function sas_loader() {
	return Sas_Crl_Loader::get_instance();
}

