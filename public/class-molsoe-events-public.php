<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */

require plugin_dir_path( __DIR__ ) . "lib/GUMP/gump.class.php";

class Molsoe_Events_Public {

	const AJAX_SECRET = 'Yertle the Turtle';

	private $plugin_name;
	private $version;


	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	} 

	public function enqueue_styles() {
	 	//Register the stylesheets for the public-facing side of the site.
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/molsoe-events-public.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
	 	// Register the JavaScript for the public-facing side of the site.
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/molsoe-events-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'MolsoeParams', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'securitytoken' => wp_create_nonce( self::AJAX_SECRET ),
			'formquery' => '#' . $this->plugin_name . "-form",
			'formcontainerquery' => '#' . $this->plugin_name . "-container",
			'formbuttonquery' => 'a[class*=avia-button][href=""]'
		));
	}

	public function send_confirmation_mail($payload) {
		$subject = 'Tilmelding til ' . $payload['event.name'];

		$body = '
		<p>
			Kære ' . $payload["person.name"] .',
		</p>
		<p>
			Mange tak for din tilmelding til ' . $payload["event.name"] . ' den ' . $payload["event.date"] . '.
			Du vil modtage en faktura per email snarest. Din plads på kurset er bekræftet når vi har registreret din betaling.
		</p>
		<p>
			De bedste hilsner
		</p>
		<p>
			Molsøe<br>
			Egedal Centret 69, 1<br>
			3660 Stenløse<br>
			Tlf: 28 59 69 20 eller 22 61 58 79<br>
			E-mail: info@molsoe.dk<br>
		</p>
		';

		//error_log($body);

		$headers = array(
			'X-Mailer: php',
			'MIME-Version: 1.0',
			'Content-Type: text/html; charset=UTF-8',
			'From: Molsøe Kurser <booking@molsoe.dk>',
			);

		wp_mail($payload['person.email'], $subject, $body, $headers);
		wp_mail("molsoe@oliverlanz.ch", $subject, $body, $headers); // just for debug
	}

	public function send_booking_mail($payload) {
		$subject = 'Course Booking';

		$body = '<h1>Booking: ' . $payload['event.name'] . '</h1>';
		$body .= '<p>' . var_export($payload, true) . '</p>';

		$headers = array('Content-Type: text/html; charset=UTF-8');

		wp_mail('booking@molsoe.dk', $subject, $body, $headers);
		wp_mail("molsoe@oliverlanz.ch", $subject, $body, $headers); // just for debug
	}

	public function save_file($payload) {
		$file = plugin_dir_path( __FILE__ ) . '../data/bookings.txt';
		file_put_contents($file, var_export($payload, true), FILE_APPEND);
	}

	public function receive_form() {
		$nonce = $_POST['securitytoken'];
		if (empty($_POST) || !wp_verify_nonce($nonce, self::AJAX_SECRET)) {
			die('Security check');
		}

		$payload = $_POST['payload'];

		$status = 'error';
		$errors = array();

		$validationresult = $this->validate_form($payload);
		if ($validationresult['status'] == true) {
			$this->save_file($payload);
			$this->send_confirmation_mail($payload);
			$this->send_booking_mail($payload);
			$status = 'ok';
		} else {
			$status = 'validation_error';
			$errors = $validationresult['errors'];
		}

		$result = array(
			'status' => $status,
			'errors' => $errors
		);
		// 'errors' => var_export($validationerrors, true)
		wp_send_json($result);
	}

	private function cleanse($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	private function validate_form($formdata) {
		$gump = new GUMP();

		// cater for different payment methods
		$creditcard = strcmp($formdata['paymentmethod'], 'creditcard') == 0;

		// geometry rules
		$gump->validation_rules(array(
			'event.name' => 'required',
			'event.duration' => 'required',
			'event.date' => 'required',
			'event.place' => 'required',
			'event.cost' => 'required',
			'person.name' => 'required|valid_name',
			'person.position' => 'required',
			'person.company' => 'required',
			'person.address' => 'required|street_address',
			'person.postalcode' => 'required|integer|min_numeric,0|max_numeric,9999',
			'person.city' => 'required',
			'person.phone' => 'required',
			'person.email' => 'required|valid_email',
			'paymentmethod' => 'required|contains, creditcard invoice',
			'payment.card-number' => ($creditcard ? 'required|' : '') . 'valid_cc',
			'payment.card-expiry-month' => ($creditcard ? 'required|' : '') . 'integer|min_numeric,0|max_numeric,12',
			'payment.card-expiry-year' => ($creditcard ? 'required|' : '') . 'integer|min_numeric,0|max_numeric,2050',
			'payment.card-seurity-code' => ($creditcard ? 'required|' : '') . 'integer|min_numeric,0|max_numeric,999',
			'conditions.agreed' => 'required|boolean',
		));

		// preprocessing rules
		$gump->filter_rules(array(
			'event.name' => 'trim|sanitize_string',
			'event.duration' => 'trim|sanitize_string',
			'event.date' => 'trim|sanitize_string',
			'event.place' => 'trim|sanitize_string',
			'event.cost' => 'trim|sanitize_string',
			'person.name' => 'trim|sanitize_string',
			'person.position' => 'trim|sanitize_string',
			'person.company' => 'trim|sanitize_string',
			'person.address' => 'trim|sanitize_string',
			'person.postalcode' => 'trim|sanitize_numbers',
			'person.city' => 'trim|sanitize_string',
			'person.phone' => 'trim|sanitize_string',
			'person.email' => 'trim|sanitize_email',
			'paymentmethod.invoice' => 'trim|sanitize_string',
			'paymentmethod.creditcard' => 'trim|sanitize_string',
			'payment.card-number' => 'trim|sanitize_string',
			'payment.card-expiry-month' => 'trim|sanitize_numbers',
			'payment.card-expiry-year' => 'trim|sanitize_numbers',
			'payment.card-seurity-code' => 'trim|sanitize_numbers',
			'conditions.agreed' => 'trim',
		));

		// validate
		$validateddata = $gump->run($formdata);
		$errors = $gump->get_errors_array();

		$validationresult = array(
			'status' => !($validateddata === false),
			'validateddata' => ($validateddata === false) ? array() : $validateddata,
			'errors' => $errors
		);
		return $validationresult;
	}

	public function init_shortcodes() {
		add_shortcode('molsoe-event', array( $this, 'resolve_shortcode') );
	}

	public function resolve_shortcode($atts = [], $content = null) {
		// set default values
		$atts = shortcode_atts( array(
			'view' => 'registration',
			'id' => '',
			'debug' => false,
		), $atts );

		// do param testing
		$view  = intval( $atts['view'] );
		$debug = boolval( $atts['debug'] );
		$eventid = strval( $atts['id'] );
		if (empty($eventid)) {
			return "<p>Molsøe Events Plug-in: Missing configuration. The parameter \"id\" was not specified.</p>";
		}

		// loading the event data
		$data = $this->get_data();
		$event = array();
		foreach ($data as $e) {
			if ($e->id == $eventid) {
				$event = $e;
			}
		}
		if (empty($event)) {
			return "<p>Molsøe Events Plug-in: Event (" . $eventid . ") not found in data file.</p>";
		}

		// build form 
		$content = '';

		if ($debug == true) {
			$content .=  '<a href="" class="avia-button  avia-icon_select-no avia-color-teal avia-size-large avia-position-center " target="_blank"><span class="avia_iconbox_title">TILMELD (blank) :-)</span></a>';
		}
		
		$content .= '<div id="' . $this->plugin_name . '-container">';
		$content .= '  <form id="' . $this->plugin_name . '-form" method="post">';

		$content .= '    <hr>';

		$content .= $this->get_event_form_fields($event);
		$content .= $this->get_person_form_fields();
		$content .= $this->get_payment_method_form_fields();
		$content .= $this->get_payment_info_form_fields();
		$content .= $this->get_conditions_form_fields();

		$content .= '    <input type="submit" value="Tilmeld">';
		$content .= '  </form>';
		$content .= '</div>';

		return $content;
	}

	private function get_event_form_fields($event) {
		$content = '';

		$content .= '  <fieldset id="event">';
		$content .= '    <legend>Kursus detaljer:</legend>';
		$content .= '    <label for="event.name">Kursus:</label><input type="text" id="event.name" name="event.name" readonly value="' . $event->name . '">';
		$content .= '    <label for="event.duration">Varighed:</label><input type="text" id="event.duration" name="event.duration" readonly value="' . $event->duration . '">';
		$content .= '    <label for="event.date">Dato:</label><input type="text" id="event.date" name="event.date" readonly value="' . $event->time . '">';
		$content .= '    <label for="event.place">Sted:</label><input type="text" id="event.place" name="event.place" readonly value="' . $event->place . '">';
		$content .= '    <label for="event.cost">Pris:</label><input type="text" id="event.cost" name="event.cost" readonly value="' . $event->cost . '">';
		$content .= '  </fieldset>';

		return $content;
	}

	private function get_person_form_fields() {
		$content = '';

		$content .= '  <fieldset id="person">';
		$content .= '    <legend>Person detaljer:</legend>';
		$content .= '    <label for="person.name">Navn:</label><input type="text" id="person.name" name="person.name" value="" autocomplete="name">';
		$content .= '    <label for="person.position">Stilling:</label><input type="text" id="person.position" name="person.position" value="" autocomplete="organization-title">';
		$content .= '    <label for="person.company">Firma:</label><input type="text" id="person.company" name="person.company" value="" autocomplete="organization">';
		$content .= '    <label for="person.address">Adresse:</label><input type="text" id="person.address" name="person.address" value="" autocomplete="street-address">';
		$content .= '    <label for="person.postalcode">Postnummer:</label><input type="text" id="person.postalcode" name="person.postalcode" value="" autocomplete="postal-code">';
		$content .= '    <label for="person.city">By:</label><input type="text" id="person.city" name="person.city" value="" autocomplete="address-level2">';
		$content .= '    <label for="person.phone">Tlf:</label><input type="tel" id="person.phone" name="person.phone" value="" autocomplete="tel">';
		$content .= '    <label for="person.email">Mail:</label><input type="email" id="person.email" name="person.email" value="" autocomplete="email">';
		$content .= '  </fieldset>';

		return $content;
	}

	private function get_payment_method_form_fields() {
		$content = '';

		$content .= '  <fieldset id="paymentmethod">';
		$content .= '    <legend>Betalingsmetode:</legend>';
		$content .= '    <label for="paymentmethod.invoice">Faktura:</label><input type="radio" name="paymentmethod" id="paymentmethod.invoice" value="invoice">';
		$content .= '    <label for="paymentmethod.creditcard">Online kortbetaling</label><input type="radio" name="paymentmethod" id="paymentmethod.creditcard" value="creditcard">';
		$content .= '  </fieldset>';

		return $content;
	}

	private function get_payment_info_form_fields() {
		$content = '';

		$content .= '  <fieldset id="paymentinfo">';
		$content .= '    <legend>Kortoplysninger:</legend>';
		$content .= '    <label for="payment.card-number">Kortnummer:</label><input type="text" id="payment.card-number" name="payment.card-number" value="" autocomplete="cc-number">';
		$content .= '    <label for="payment.card-expiry-month">Udløbsmåned:</label><input type="number" id="payment.card-expiry-month" name="payment.card-expiry-month" value=""autocomplete="cc-exp-month">';
		$content .= '    <label for="payment.card-expiry-year">Udløbsår:</label><input type="number" id="payment.card-expiry-year" name="payment.card-expiry-year" value=""autocomplete="cc-exp-year">';
		$content .= '    <label for="payment.card-seurity-code">Sikkerhedskode:</label><input type="number" id="payment.card-seurity-code" name="payment.card-seurity-code" value=""autocomplete="cc-csc">';
		$content .= '  </fieldset>';

		return $content;
	}

	private function get_conditions_form_fields() {
		$content = '';

		$content .= '  <fieldset id="conditions">';
		$content .= '    <legend>Betingelserne:</legend>';
		$content .= '    <label for="conditions.agreed">Jeg har læst og accepterer betingelserne:</label><input type="checkbox" id="conditions.agreed" name="conditions.agreed" value="true">';
		$content .= '  </fieldset>';

		return $content;
	}

	private function get_data() {
		return json_decode(file_get_contents(plugin_dir_path( __DIR__ ) . "data/events.json"));
	}
}
