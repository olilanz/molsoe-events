(function( $, mparams ) {
	'use strict';

	/**
	 * Install handlers for form interactions
	 */
	$(document).ready(function() {
		// form submit button
		$(mparams.formquery).submit(submitFormHandler);

		// show form button
		var btn = $(mparams.formbuttonquery);
		var container = $(mparams.formcontainerquery);
		btn.click(function() {
			showFormHandler(container);
			return false;
		});

		$(mparams.formquery).find('fieldset#paymentmethod').find('input:radio[name="paymentmethod"]').change(
			function () {
			if (this.checked && this.value == 'online') {
				$(mparams.formquery).find('fieldset#paymentinfo').fadeIn("fast");		
			}
			if (this.checked && this.value != 'online') {
				$(mparams.formquery).find('fieldset#paymentinfo').fadeOut("fast");		
			}
		});

		// initialize the form 
		$(mparams.formquery).hide();
		$(mparams.formquery).find('fieldset#paymentinfo').hide();
		$(mparams.formquery).find('fieldset#paymentmethod').find('input#paymentmethod\\.invoice').attr("checked", true);
	});

	function showFormHandler(formcontainer) {
		$(mparams.formquery).fadeToggle("fast");
	}

	function submitFormHandler(event) {
		event.preventDefault(); // stop form from submitting normally

		var form = $(mparams.formquery);
		//form.find('.submit').val('Working...');
		//form[0].checkValidity();

		var formdata = getFormData(form);
		var data = {
			action: 'receive_form',
			securitytoken: mparams.securitytoken,
			payload: formdata
		};

		$.post(mparams.ajaxurl, data, function(response) {
			if (response.status == 'ok') {
				alert('All good: (' + JSON.stringify(response) + ')');
			} else {
				alert('Error: (' + JSON.stringify(response) + ')');
			}
		});
	}

	function getFormData($form){
		var unindexed_array = $form.serializeArray();
		var indexed_array = {};

		$.map(unindexed_array, function(n, i){
			indexed_array[n['name']] = n['value'];
		});

		return indexed_array;
	}
})( jQuery, MolsoeParams );
