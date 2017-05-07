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

		$content .= $this->get_course_form_fields($event->name, $event->cost, $event->occurances);
		$content .= $this->get_person_form_fields();
		$content .= $this->get_payment_form_fields();

		$content .= '    <hr>';

		$content .= '    <input type="checkbox" required name="conditions" value="accepted">Jeg har læst og accepterer betingelserne<br>';
		$content .= '    <input type="submit" value="Submit">';

		$content .= '  </form>';
		$content .= '</div>';

		return $content;
	}

	private function get_course_form_fields($coursename, $cost, $occurances) {
		$content = '';

		$content .= '    <h3>Kursus detaljer:</h3>';
		$content .= '    Kursus: <input type="text" name="course" readonly value="' . $coursename . '"><br>';
		$content .= '    Pris: <input type="text" name="event-date" readonly value="' . $cost . '"><br>';
		$content .= '    Dato: <select name="event-date">';
		foreach ($occurances as $o) {
			$content .= '      <option value="' . $o->time . '">' . $o->time . '</option>';
		}
		$content .= '    </select><br>';
		$content .= '    Kursus sted: <select name="event-place">';
		foreach ($occurances as $o) {
			$content .= '      <option value="' . $o->place . '">' . $o->place . '</option>';
		}
		$content .= '    </select><br>';

		return $content;
	}

	private function get_person_form_fields() {
		$content = '';

		$content .= '    <h3>Person detaljer:</h3>';
		$content .= '    Navn: <input type="text" required name="name" value="a"><br>';
		$content .= '    Stilling: <input type="text" required name="position" value="a"><br>';
		$content .= '    Firma: <input type="text" required name="company" value="a"><br>';
		$content .= '    Adresse: <input type="text" required name="address" value="a"><br>';
		$content .= '    Postnummer: <input type="text" required name="postal-code" value="a"><br>';
		$content .= '    By: <input type="text" required name="city" value="a"><br>';
		$content .= '    Tlf: <input type="tel" required name="phone" value="a"><br>';
		$content .= '    Mail: <input type="email" required name="mail" value="a@a"><br>';

		return $content;
	}

	private function get_payment_form_fields() {
		$content = '';

		$content .= '    <h3>Betalingsmetode:</h3>';
		$content .= '    <input type="radio" required name="paymentmethod" value="invoice">Faktura<br>';
		$content .= '    <input type="radio" required checked name="paymentmethod" value="online">Online kortbetaling<br>';
		$content .= '    Kortnummer: <input type="text" required name="card-number" value="1234-2345-3456-4567"><br>';
		$content .= '    Udløbsmåned: <input type="number" required name="card-expiry-month" value=""><br>';
		$content .= '    Udløbsår: <input type="number" required name="card-expiry-year" value=""><br>';
		$content .= '    Sikkerhedskode: <input type="number" required name="card-seurity-code" value=""><br>';

		return $content;
	}

	private function get_data() {
		return json_decode(file_get_contents(plugin_dir_path( __DIR__ ) . "data/events.json"));
	}
}
