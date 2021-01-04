( function ( $ ) {
	
	var validate_form = function () {

		var $registration_fields = [],
			sas_errors = [];

		$registration_fields = $( ".sas-registration-shortcode" ).find(
			'input[type="text"], input[type="tel"], input[type="email"], input[type="password"]'
		);

		//Add focus class on clicked on input types
		var access = "true",
			field_focus = "";

		Array.from($registration_fields).forEach(function ($this) {
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

	var register_user = function (){
		
		$('.sas-registration-shortcode').on( 'click', '.sas-register--button', function(e) {

			$('.sas-registration-shortcode').addClass('is-processing');
			
			$('.sas-register--button').val('Registering');

			e.preventDefault();
			
			if( 'true' === validate_form() ){
				var data = {
					action: 'sas_crl_register_user',
					formData: $('.sas-registration-shortcode form').serialize().toString(),
					security: sas_crl.sas_register_user_nonce,
				};

				$.ajax( {
					type: 'POST',
					url: sas_crl.ajax_url,
					data: data,

					success: function ( response ) {
						
						var response_data = response.data;

						if( response_data.status === 'success' ){
							
							$('.sas-message-wrapper').html("<div class='wrap--section wrap-success'> <ul class='sas-messages'> <li>" + response_data.msg + " </li> </ul> </div>");	

							$('.sas-register--button').val('Done! Redirecting...');
						}
						
						setTimeout(
							function(){ 
								window.location.href = response_data.redirect; 
							}, 
						3000 );

						$('.sas-registration-shortcode').removeClass('is-processing');
					},
				} );

				
			}else{
				$('.sas-registration-shortcode').removeClass('is-processing');
				return;
			}

		});
			
	}
	

	$( document ).ready( function ( $ ) {
		
		register_user();
		
	} );

} )( jQuery );
