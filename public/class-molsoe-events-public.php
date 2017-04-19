<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Molsoe_Events_Public {

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

		$content =  '<div class="' . $this->plugin_name . '">';
		$content .= '  <form action="/action_page.php">';

		$content .= '    <hr>';
		$content .= '    <h3>Kursus detaljer:</h3>';
		$content .= '    Kursus: <input type="text" name="course" readonly value="' . $event->name . '"><br>';
		$content .= '    Pris: <input type="text" name="event-date" readonly value="' . $event->cost . '"><br>';
		$content .= '    Dato: <select name="event-date">';
		foreach ($event->occurances as $o) {
			$content .= '      <option value="' . $o->time . '">' . $o->time . '</option>';
		}
		$content .= '    </select><br>';
		$content .= '    Kursus sted: <select name="event-place">';
		foreach ($event->occurances as $o) {
			$content .= '      <option value="' . $o->place . '">' . $o->place . '</option>';
		}
		$content .= '    </select><br>';

		$content .= '    <hr>';
		$content .= '    <h3>Person detaljer:</h3>';
		$content .= '    Navn: <input type="text" required name="name"><br>';
		$content .= '    Stilling: <input type="text" required name="position"><br>';
		$content .= '    Firma: <input type="text" required name="company"><br>';
		$content .= '    Adresse: <input type="text" required name="address"><br>';
		$content .= '    Tlf: <input type="tel" required name="phone"><br>';
		$content .= '    Mail: <input type="email" required name="mail"><br>';
		$content .= '    <hr>';
		$content .= '    <input type="submit" value="Submit">';
		$content .= '  </form>';
		$content .= '</div>';

		return $content;
	}

	private function get_data() {
		return json_decode(file_get_contents(plugin_dir_path( __DIR__ ) . "data/events.json"));
	}
}
