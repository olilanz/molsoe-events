(function( $ ) {
	'use strict';

	var formtag = "#" + MyAjax.formname;
	$(formtag).submit(function($) {
		$.preventDefault();

		var data = {
			action: 'receive_form',
			securitytoken : MyAjax.security,
			payload: $(this).serialize()
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(MyAjax.ajaxurl, data, function(response) {
			alert('Got this from the server: ' + response);
		});
	});
})( jQuery );
