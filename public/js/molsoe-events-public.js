(function( $, ajaxhelper ) {
	'use strict';

	$(document).ready(function() { 
		var formtag = "#" + ajaxhelper.formname;
		$(formtag).submit(function(event) {
        	event.preventDefault(); // stop form from submitting normally

			var form = $(formtag);
			form.find('.submit').val('Working...');

			var data = {
				action: 'receive_form',
				securitytoken: ajaxhelper.securitytoken,
				payload: form.serializeArray()
			};

			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post(ajaxhelper.ajaxurl, data, function(response) {
				alert('Got this from the server: (' + response.id + '/' + response.message + ')');
			});

			form.find('.submit').val('Submitted :-)');
		});
	});
})( jQuery, MyAjax );
