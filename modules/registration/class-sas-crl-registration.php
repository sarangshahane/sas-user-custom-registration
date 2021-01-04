<?php

/**
 * Registration markup.
 *
 * @package sas-crl
 */

/**
 * Registration Markup
 *
 * @since 1.0.0
 */
class Sas_Crl_Registration_Markup {

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		/* Load Styles */

		add_action( 'wp', array( $this, 'load_shortcode_data' ), 10 );

		/* Embed Shortcode */
		add_shortcode( 'sas-registration', array( $this, 'renter_registration_html' ), 10 );

		add_action( 'wp_ajax_sas_crl_register_user', array( $this, 'sas_crl_register_user' ), 10 );
		add_action( 'wp_ajax_nopriv_sas_crl_register_user', array( $this, 'sas_crl_register_user' ), 10 );
	}

	
	/**
	 * Load shortcode styles/scripts/data.
	 *
	 * @return void
	 */
	public function load_shortcode_data(){
		
		add_action( 'wp_enqueue_scripts', array( $this, 'load_shortcode_scripts' ), 10 );

	}

	/**
	 * Load Registration shortcode scripts.
	 *
	 * @return void
	 */
	public function load_shortcode_scripts() {

		wp_enqueue_style( 
			'sas-registration-css', 
			SAS_CRL_URL . 'assets/css/registration.css', 
			'', 
			SAS_CRL_VER 
		);

		wp_enqueue_script(
			'sas-registration-js',
			SAS_CRL_URL . 'assets/js/registration.js',
			array( 'jquery' ),
			SAS_CRL_VER,
			true
		);

		$localize_vars = array(
			'ajax_url'         => admin_url( 'admin-ajax.php', 'absolute' ),
			'sas_register_user_nonce' => wp_create_nonce( 'sas-register-user' ),
		);

		$localize_vars = apply_filters( 'sas_register_user_js_localize_vars', $localize_vars );

		$localize_script  = '<!-- script to print the frontend localized variables -->';
		$localize_script .= '<script type="text/javascript">';
		$localize_script .= 'var sas_crl = ' . wp_json_encode( $localize_vars ) . ';';
		$localize_script .= '</script>';

		echo $localize_script;

	}

	/**
	 * Render Registration Form.
	 *
	 * @return HTML
	 */
	public function renter_registration_html(){

		ob_start();

			echo '
				<div class="sas-registration-shortcode">
					<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" class="registration-form">


						<div class="field-wrap validate-required">
							<div class="field-label">
								<label for="username">Username <strong>*</strong></label>
							</div>
							
							<div class="input-field">
								<input type="text" name="user_login"  >
							</div>
						</div>

						<div class="field-wrap">
							<div class="field-label">
								<label for="firstname">First Name</label>
							</div>
							
							<div class="input-field">
								<input type="text" name="first_name" >
							</div>
						</div>

						<div class="field-wrap">
							<div class="field-label">
								<label for="website">Last Name</label>
							</div>
							
							<div class="input-field">
								<input type="text" name="last_name" >
							</div>
						</div>

						<div class="field-wrap validate-required">
							<div class="field-label">
								<label for="email">Email <strong>*</strong></label>
							</div>
							
							<div class="input-field">
								<input type="text" name="user_email"  >
							</div>
						</div>	

						<div class="field-wrap">
							<div class="field-label">
								<label for="mobile">Mobile</label>
							</div>
							
							<div class="input-field">
								<input type="text" name="user_mobile" >
							</div>
						</div>	

						<div class="field-wrap validate-required">
							<div class="field-label">
								<label for="user_pass">Password <strong>*</strong></label>
							</div>
							
							<div class="input-field">
								<input type="password" name="user_pass" >
							</div>
						</div>	

						<div class="field-wrap">
							<div class="field-label">
								<label for="state">State</label>
							</div>
							
							<div class="input-field">
								<input type="text" name="user_state">
							</div>
						</div>

						<div class="field-wrap">
							<div class="field-label">
								<label for="city">City</label>
							</div>
							
							<div class="input-field">
								<input type="text" name="user_city">
							</div>
						</div>	

						<div class="field-wrap">
							<div class="field-label">
								<label for="attachement">Upload File</label>
							</div>
							
							<div class="input-field">
								<input type="file" name="attachement">
							</div>
						</div>	
						<div class="sas-message-wrapper"></div>
						<input type="submit" class="sas-register--button" name="submit" value="Register"/>
					</form>
				</div>
		    ';
		return ob_get_clean();
	}

	/**
	 * Register user logic
	 *
	 * @return HTML
	 */
	public function sas_crl_register_user(){

		$response = '';
		$post_data = array();

		check_ajax_referer( 'sas-register-user', 'security' );

		parse_str($_POST['formData'], $post_data);
		
		$validated_array = array();
		
		// Senatize the inputed data.
		foreach( $post_data as $key => $value ){
			if ( isset( $value ) && !empty($value) ){
				$validated_array[$key] = isset( $value ) ? sanitize_text_field( $value ) : '';
			}
		}
		
		$user = wp_insert_user( $validated_array );
	
		if( isset( $user ) ){
			
			$validated_array['password'] = md5( $validated_array['password'] );

			update_user_meta( $user, 'sas_crl_user_data', $validated_array ); 

			$response = array(
				'status' => 'success',
				'msg'    => 'Congrats!! Your account is created successfully...',
				'redirect' => get_site_url(),
			);
		}
		
		echo wp_send_json_success( $response );

		die();

	}

	
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Sas_Crl_Registration_Markup::get_instance();