<?php

/**
 * Login markup.
 *
 * @package sas-crl
 */

/**
 * Login Markup
 *
 * @since 1.0.0
 */
class Sas_Crl_login_Markup {

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

		add_action( 'wp', array( $this, 'load_login_shortcode_data' ), 10 );

		/* Embed Shortcode */
		add_shortcode( 'sas-login', array( $this, 'renter_login_html' ), 10 );

		add_action( 'wp_ajax_sas_crl_login_user', array( $this, 'sas_crl_login_user' ), 10 );
		add_action( 'wp_ajax_nopriv_sas_crl_login_user', array( $this, 'sas_crl_login_user' ), 10 );

	}

	/**
	 * Load shortcode styles/scripts/data.
	 *
	 * @return void
	 */
	public function load_login_shortcode_data(){
		
		add_action( 'wp_enqueue_scripts', array( $this, 'load_login_shortcode_scripts' ), 10 );

	}

	/**
	 * Load Login shortcode scripts.
	 *
	 * @return void
	 */
	public function load_login_shortcode_scripts() {

		wp_enqueue_style( 
			'sas-login-css', 
			SAS_CRL_URL . 'assets/css/sas-login.css', 
			'', 
			SAS_CRL_VER 
		);

		wp_enqueue_script(
			'sas-login-js',
			SAS_CRL_URL . 'assets/js/sas-login.js',
			array( 'jquery' ),
			SAS_CRL_VER,
			true
		);

		$localize_vars = array(
			'ajax_url'         => admin_url( 'admin-ajax.php', 'absolute' ),
			'sas_login_user_nonce' => wp_create_nonce( 'sas-login-user' ),
		);

		$localize_vars = apply_filters( 'sas_login_user_js_localize_vars', $localize_vars );

		$localize_script  = '<!-- script to print the frontend localized variables -->';
		$localize_script .= '<script type="text/javascript">';
		$localize_script .= 'var sas_crl_login_vars = ' . wp_json_encode( $localize_vars ) . ';';
		$localize_script .= '</script>';

		echo $localize_script;

	}

	/**
	 * Render Login form HTML.
	 *
	 * @return void
	 */
	public function renter_login_html( ) {
		if( is_admin() ){
			return;
		}

		if( ! is_user_logged_in() ) {
			?>
			<div class="sas-login-shortcode">
				<form method="post" class="custom-login-form">
					<div class="field-wrap validate-required">
						<div class="field-label">
							<label for="custom-user"><?php _e('Username', 'sas-crl'); ?></label>
						</div>
						<div class="input-field">
							<input type="text" name="custom_user" id="custom-user" placeholder="Type Username or Email" />
						</div>
					</div>
					<div class="field-wrap validate-required">
						<div class="field-label">
							<label for="custom-pass"><?php _e('Password', 'sas-crl'); ?></label>
						</div>
						<div class="input-field">
							<input type="password" name="custom_pass" id="custom-pass" placeholder="Type the password" />
						</div>
					</div>

					<div class="field-wrap field-wrap--full-width">
						<div class="field-label">
							<label for="remember-me">
								<input type="checkbox" name="remember_me" id="remember-me"/>
								<?php _e('Remember Me', 'sas-crl'); ?>
							</label>
						</div>
						<div class="input-field align-right">
							<a href="<?php echo wp_lostpassword_url(); ?>"><?php _e('Lost Password', 'sas-crl'); ?></a>
						</div>
					</div>
					
					<div class="sas-message-wrapper"></div>
					<div>
						<input type="submit" class="sas-login--button" name="submit_custom_login" value="<?php _e('Login', 'sas-crl'); ?>" />    
					</div>
				</form>
			</div>
			<?php
		}
	}

	/**
	 * Validate login details.
	 *
	 * @return void
	 */
	public function sas_crl_login_user(){

		$response = '';
		$post_data = array();

		check_ajax_referer( 'sas-login-user', 'security' );

		parse_str($_POST['formData'], $post_data);
		
		$validated_array = array();
		
		// Senatize the inputed data.
		foreach( $post_data as $key => $value ){
			if ( isset( $value ) && !empty($value) ){
				$validated_array[$key] = isset( $value ) ? sanitize_text_field( $value ) : '';
			}
		}

		$custom_user = isset( $validated_array['custom_user'] ) ? $validated_array['custom_user'] : '';
		$custom_pass = isset( $validated_array['custom_pass'] ) ? $validated_array['custom_pass'] : '';
		$remember_me = isset( $validated_array['remember_me'] ) ? $validated_array['remember_me'] : '';
		
		// Logic to login the user.

		$user_info = get_user_by( 'login', $custom_user );
        
        if( ! $user_info ) {
            $user_info = get_user_by( 'email', $custom_user );
        }

        if( $user_info ) {
		    $user_id = $user_info->ID;
		    if( wp_check_password( $custom_pass, $user_info->user_pass, $user_id ) ) {

		        if( isset($remember_me) ) {
		            $remember_me = true;
		        } else {
		            $remember_me = false;
		        }
				
				$status = 'fail';
		        $is_login = $this->custom_log_userin($user_id, $custom_user, $custom_pass, $remember_me);
				
				if( $is_login ){
					$status = 'success';
				}
		        $response = array(
					'status' => $status,
					'msg'    => 'Congrats!! Your account is created successfully...',
					'redirect' => get_dashboard_url( $user_id ),
				);

		    } else {
		        
		        $response = array(
					'status' => 'false',
					'msg'    => 'The password you entered is incorrect.',
					'redirect' => get_page_link(),
				);
		    }

		} else {

			$response = array(
				'status' => 'false',
				'msg'    => 'The username you entered is incorrect.',
				'redirect' => get_page_link(),
			);
		    
		}

		echo wp_send_json_success( $response );
		die();
	}

	/**
	 * Log-in the User
	 *
	 * This function accepts various parameters that are used for checking whether the user exists or not.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The ID of the user.
	 * @param string $user_login The users username/email.
	 * @param string $user_pass Password of the user.
	 * @param boolean $user_remember Whether to remember the user credentials or not.
	 *
	 * @return void Return early if the user doesn't exist.
	 **/
	public function custom_log_userin($user_id, $user_login, $user_pass, $user_remember = false ) {
	    if( ! absint( $user_id ) || $user_id < 1 ) {
	        return;
	    }
	    
	    wp_set_auth_cookie( $user_id, $user_remember );
	    wp_set_current_user( $user_id, $user_login );
		do_action( 'wp_login', $user_login, get_userdata( $user_id ) );
		
		return true;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Sas_Crl_login_Markup::get_instance();