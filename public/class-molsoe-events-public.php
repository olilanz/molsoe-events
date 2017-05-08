<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
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

	public function send_mail($payload) {
		$subject = 'Course Booking';

		$body = '<h1>Booking: ' . $payload['course'] . '</h1>';
		$body .= '<p>' . var_export($payload, true) . '</p>';

		$headers = array('Content-Type: text/html; charset=UTF-8');

		wp_mail('olilanz@mac.com', $subject, $body, $headers);
		wp_mail('anne@annemollerup.dk', $subject, $body, $headers);
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
		$this->save_file($payload);
		$this->send_mail($payload);

		$result = array(
			'message' => var_export($payload, true)
		);
		wp_send_json($result);
	}

	public function init_shortcodes() {
		add_shortcode('molsoe-event', array( $this, 'resolve_shortcode') );
	}

	public function resolve_shortcode($atts = [], $content = null) {
		// set default values
		$atts = shortcode_atts( array(
			'view'     => 'registration',
			'id'  => '',
			'debug'	   => false,
		), $atts );

		// do param testing
		$view  = intval( $atts['view'] );
		$debug = boolval( $atts['debug'] );
		$eventid = strval( $atts['id'] );

		// prepping the data
		$data = $this->get_data();
		$event = array();
		foreach ($data as $e) {
			if ($e->id == $eventid) {
				$event = $e;
			}
		}

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

		$content .= '    <hr>';

		$content .= '    <input type="checkbox" required name="conditions" value="accepted">Jeg har læst og accepterer betingelserne<br>';
		$content .= '    <input type="submit" value="Submit">';

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
		$content .= '    <label for="person.name">Navn:</label><input type="text" required id="person.name" name="person.name" value="">';
		$content .= '    <label for="person.position">Stilling:</label><input type="text" required id="person.position" name="person.position" value="">';
		$content .= '    <label for="person.company">Firma:</label><input type="text" required id="person.company" name="person.company" value="">';
		$content .= '    <label for="person.address">Adresse:</label><input type="text" required id="person.address" name="person.address" value="">';
		$content .= '    <label for="person.postalcode">Postnummer:</label><input type="text" required id="person.postalcode" name="person.postalcode" value="">';
		$content .= '    <label for="person.city">By:</label><input type="text" required id="person.city" name="person.city" value="">';
		$content .= '    <label for="person.phone">Tlf:</label><input type="tel" required id="person.phone" name="person.phone" value="">';
		$content .= '    <label for="person.mail">Mail:</label><input type="email" required id="person.mail" name="person.mail" value="">';
		$content .= '  </fieldset>';

		return $content;
	}

	private function get_payment_method_form_fields() {
		$content = '';

		$content .= '  <fieldset id="paymentmethod">';
		$content .= '    <legend>Betalingsmetode:</legend>';
		$content .= '    <label for="paymentmethod.invoice">Faktura:</label><input type="radio" required name="paymentmethod" id="paymentmethod.invoice" value="invoice">';
		$content .= '    <label for="paymentmethod.online">Online kortbetaling</label><input type="radio" required name="paymentmethod" id="paymentmethod.online" value="online">';
		$content .= '  </fieldset>';

		return $content;
	}

	private function get_payment_info_form_fields() {
		$content = '';

		$content .= '  <fieldset id="paymentinfo">';
		$content .= '    <legend>Kortoplysninger:</legend>';
		$content .= '    <label for="payment.card-number">Kortnummer:</label><input type="text" required id="payment.card-number" name="payment.card-number" value="">';
		$content .= '    <label for="payment.card-expiry-month">Udløbsmåned:</label><input type="number" required id="payment.card-expiry-month" name="payment.card-expiry-month" value="">';
		$content .= '    <label for="payment.card-expiry-year">Udløbsår:</label><input type="number" required id="payment.card-expiry-year" name="payment.card-expiry-year" value="">';
		$content .= '    <label for="payment.card-seurity-code">Sikkerhedskode:</label><input type="number" required id="payment.card-seurity-code" name="payment.card-seurity-code" value="">';
		$content .= '  </fieldset>';

		return $content;
	}

	private function get_data() {
		return json_decode(file_get_contents(plugin_dir_path( __DIR__ ) . "data/events.json"));
	}
}
