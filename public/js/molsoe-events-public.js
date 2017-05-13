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
			if (this.checked && this.value == 'creditcard') {
				$(mparams.formquery).find('fieldset#paymentinfo').fadeIn("fast");		
			}
			if (this.checked && this.value != 'creditcard') {
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
		var formdata = getFormData(form);
		var data = {
			action: 'receive_form',
			securitytoken: mparams.securitytoken,
			payload: formdata
		};

		$.post(mparams.ajaxurl, data, function(response) {
			if (response.status === 'ok') {
				clearFormErrors(form);
				alert('All good: (' + JSON.stringify(response) + ')');
			} else if (response.status == 'validation_error') {
				displayFormErrors(form, response['errors'])
			} else {
				alert('Server reported error: (' + JSON.stringify(response) + ')');				
			}
		});
	}

	function clearFormErrors(form) {
		displayFormErrors(form, {});
	}

	function displayFormErrors(form, errors) {
		form.find('label').each(function(ix, label){
			if (!label.title) {
				// use title to store original label text 
				label.title = label.textContent;
			}
			
			if (!(typeof errors[label.htmlFor] === 'undefined')) {
				$(label).text(label.title + ' (' + errors[label.htmlFor] + ')');
				$(label).addClass('molsoe-events-invalid');
				$(label).focus();
			} else {
				$(label).text(label.title);
				$(label).removeClass('molsoe-events-invalid');
			}
		});
	}

	function getFormData(form){
		var unindexed_array = form.serializeArray();
		var indexed_array = {};

		$.map(unindexed_array, function(n, i){
			indexed_array[n['name']] = n['value'];
		});

		return indexed_array;
	}
})( jQuery, MolsoeParams );
