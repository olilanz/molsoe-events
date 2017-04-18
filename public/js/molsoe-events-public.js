(function( $ ) {
	'use strict';

	var formtag = "#" + MyAjax.formname;
	$(formtag).submit(function($) {
		var form = $(formtag);

		// disable submit button
		$.preventDefault();
		form.find('.submit').val('Working...');

		var data = {
			action: 'receive_form',
			securitytoken: MyAjax.security,
			payload: form.serialize()
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(MyAjax.ajaxurl, data, function(response) {
			alert('Got this from the server: ' + response);
		});
	});
})( jQuery );
