(function( $, mparams ) {
	'use strict';

	/**
	 * Install handlers for form interactions
	 */
	$(document).ready(function() {
		// form submit button
		$(mparams.formquery).submit(submitFormHandler);

		// show form button
		$(mparams.formbuttonquery).click(function() {
			$(mparams.formbuttonquery).fadeOut("fast");
			$(mparams.formquery).fadeIn("fast");
			return false;
		});

		// handle switching between payment methods
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

		// hide paymentmethod for as long as credit card payment is not supported
		$(mparams.formquery).find('fieldset#paymentmethod').hide();
	});

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
				//alert('All good: (' + JSON.stringify(response) + ')');
				alert('Mange tak for din tilmelding. Du vil modtage en mail angående din tilmelding indenfor de næste par minutter.');
				$(mparams.formquery).fadeOut("fast");
				$(mparams.formbuttonquery).fadeIn("fast");
			} else if (response.status == 'validation_error') {
				displayFormErrors(form, response['errors'])
			} else {
				alert('Der er sket en fejl. Prøv igen senere, eller send en mail til booking@molsoe.dk\n\nDetaljer om fejlen: ' + JSON.stringify(response));
			}
		}).fail(function(response) {
			alert('Der er sket en fejl. Prøv igen senere, eller send en mail til booking@molsoe.dk\n\nDetaljer om fejlen: ' + JSON.stringify(response));
		});;
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
