(function( $, ajaxhelper ) {
	'use strict';

	$(document).ready(function() { 
		var formtag = "#" + ajaxhelper.formname;
		$(formtag).submit(function(event) {
        	event.preventDefault(); // stop form from submitting normally

			var form = $(formtag);
			form.find('.submit').val('Working...');

			var formdata = getFormData(form);
			var data = {
				action: 'receive_form',
				securitytoken: ajaxhelper.securitytoken,
				payload: formdata
			};

			$.post(ajaxhelper.ajaxurl, data, function(response) {
				alert('Got this from the server: (' + response.id + '/' + response.message + ')');
			});

			form.find('.submit').val('Submitted :-)');
		});
	});

	function getFormData($form){
		var unindexed_array = $form.serializeArray();
		var indexed_array = {};

		$.map(unindexed_array, function(n, i){
			indexed_array[n['name']] = n['value'];
		});

		return indexed_array;
	}
})( jQuery, MyAjax );
