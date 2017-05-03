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

		$(mparams.formquery).hide();
	});

	function showFormHandler(formcontainer) {
		$(mparams.formquery).toggle();
	}

	function submitFormHandler(event) {
		event.preventDefault(); // stop form from submitting normally

		var form = $(mparams.formquery);
		form.find('.submit').val('Working...');

		var formdata = getFormData(form);
		var data = {
			action: 'receive_form',
			securitytoken: mparams.securitytoken,
			payload: formdata
		};

		$.post(mparams.ajaxurl, data, function(response) {
			alert('Got this from the server: (' + response.id + '/' + response.message + ')');
		});

		form.find('.submit').val('Submitted :-)');		
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
