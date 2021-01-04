<?php

/**
 * Admin Setting.
 *
 * @package sas-crl
 */

/**
 * Admin Setting
 *
 * @since 1.0.0
 */
class Sas_Crl_Admin_Setting {

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

		add_action( 'show_user_profile', array( $this, 'add_extra_profile_fields' ), 10, 1 );
        add_action( 'edit_user_profile', array( $this, 'add_extra_profile_fields' ), 10, 1 );
        
        add_action( 'personal_options_update', array ( $this, 'save_extra_profile_fields' ), 10, 1 );
        add_action( 'edit_user_profile_update', array ( $this, 'save_extra_profile_fields' ), 10, 1 );
	}

    public function add_extra_profile_fields( $user ) {

            $user_data = get_user_meta( $user->ID, 'sas_crl_user_data', true );
             
            
        ?>
            <h3>Extra Information</h3>

            <table class="form-table">
                <input type="hidden" name="username" value="<?php if ( isset( $user_data['user_login'] ) ){  echo $user_data['user_login']; } ?>">
                <input type="hidden" name="userpass" value="<?php if ( isset( $user_data['password'] ) ){  echo $user_data['password']; } ?>">
                <tr>
                    <th><label for="contact">Contact Number</label></th>
                    <td>
                        <input type="text" name="user_mobile" id="contact" value="<?php if ( isset( $user_data['user_mobile'] ) ){  echo $user_data['user_mobile']; } ?>" class="regular-text" placeholder="Enter contact number"/><br />
                    </td>
                </tr>

                <tr>
                    <th><label for="state">State</label></th>
                    <td>
                        <input type="text" name="user_state" id="state" value="<?php if ( isset( $user_data['user_state'] ) ){  echo $user_data['user_state']; } ?>" class="regular-text" placeholder="Enter your state" /><br />
                    </td>
                </tr>

                <tr>
                    <th><label for="city">City</label></th>
                    <td>
                        <input type="text" name="user_city" id="city" value="<?php if ( isset( $user_data['user_city'] ) ){  echo $user_data['user_city']; } ?>" class="regular-text" placeholder="Enter your city" /><br />
                    </td>
                </tr>

            </table>
        <?php
    }

    public function save_extra_profile_fields( $user_id ) {

        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }
        
        $validated_array = array(

            "user_login"  => isset( $_POST['username'] ) ? sanitize_text_field( $_POST['username'] ) : '',
            "first_name"  => isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '',
            "last_name"   => isset( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '',
            "user_email"  => isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '',
            "password"   => isset( $_POST['userpass'] ) ? sanitize_text_field( $_POST['userpass'] ) : '',
            "user_mobile" => isset( $_POST['user_mobile'] ) ? sanitize_text_field( $_POST['user_mobile'] ) : '',
            "user_state"  => isset( $_POST['user_state'] ) ? sanitize_text_field( $_POST['user_state'] ) : '',
            "user_city"   => isset( $_POST['user_city'] ) ? sanitize_text_field( $_POST['user_city'] ) : '',           
        );

        update_user_meta( $user_id, 'sas_crl_user_data', $validated_array ); 
    }

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Sas_Crl_Admin_Setting::get_instance();