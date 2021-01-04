( function ( $ ) {
	
	var validate_login = function () {

		var $login_fields = [],
			sas_errors = [];

		$login_fields = $( ".sas-login-shortcode" ).find( 'input[type="text"], input[type="email"], input[type="password"]' );

		//Add focus class on clicked on input types
		var access = "true",
			field_focus = "";

		Array.from($login_fields).forEach(function ($this) {
			var type = $this.type,
				name = $this.name,
				field_row = $this.closest(".field-wrap"),
				has_class = field_row.classList.contains("validate-required"),
				field_value = $.trim($this.value);
				
			if (has_class && "" == field_value) {
				$this.classList.add("field-required");
				access = "false";
				sas_errors.push( '<li> Field ' + name + ' is required field </li>' );

				if ("" == field_focus) {
					field_focus = $this;
				}
			} else {
				if (
					"email" == type &&
					false ==
						/^([a-zA-Z0-9_\+\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,14})$/.test(
							field_value
						)
				) {
					$this.classList.add("field-required");
					access = "false";

					if ("" == field_focus) {
						field_focus = $this;
					}
				}

				$this.classList.remove("field-required");
			}
		});

		// Focus the errored field
		if ("" != field_focus) {
			field_focus.focus();
		}
		
		// Display Errors if any
		if( sas_errors.length > 0 ){
			$('.sas-message-wrapper').html("<div class='wrap--section wrap-errors'> <ul class='sas-errors'>" + sas_errors.join(' ') + " </ul> </div>");
		}
	
		return access;
	};

	var custom_login_user = function (){
		
		$('.sas-login-shortcode').on( 'click', '.sas-login--button', function(e) {
			
			$('.sas-login-shortcode').addClass('is-processing');
			
			$('.sas-login--button').val('Login in...');

			e.preventDefault();
			
			if( 'true' === validate_login() ){
				var data = {
					action: 'sas_crl_login_user',
					formData: $('.sas-login-shortcode form').serialize().toString(),
					security: sas_crl_login_vars.sas_login_user_nonce,
				};

				$.ajax( {
					type: 'POST',
					url: sas_crl_login_vars.ajax_url,
					data: data,

					success: function ( response ) {
						
						var response_data = response.data;

						if( response_data.status === 'success' ){
							
							$('.sas-message-wrapper').html("<div class='wrap--section wrap-success'> <ul class='sas-messages'> <li>" + response_data.msg + " </li> </ul> </div>");	

							$('.sas-login--button').val('Done! Redirecting...');

							setTimeout(
								function(){ 
									window.location.href = response_data.redirect; 
								}, 
							3000 );

						}else{
							$('.sas-message-wrapper').html("<div class='wrap--section wrap-errors'> <ul class='sas-errors'> <li>" + response_data.msg + " </li> </ul> </div>");	

							$('.sas-login-shortcode').removeClass('is-processing');

							$('.sas-login--button').val('Please try again...');

							return false;
						}

						$('.sas-login-shortcode').removeClass('is-processing');
					},
				} );
				
			}else{
				$('.sas-login-shortcode').removeClass('is-processing');
				return;
			}

		});
			
	}
	

	$( document ).ready( function ( $ ) {
		
		custom_login_user();
		
	} );

} )( jQuery );
