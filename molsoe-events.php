<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Molsø Events
 * Plugin URI:        https://github.com/olilanz/molsoe-events
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           0.0.3
 * Author:            Oliver Lanz
 * Author URI:        http://www.gigglingbits.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       molsoe-events
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 */
function activate_molsoe_events() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-molsoe-events-activator.php';
	Molsoe_Events_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_molsoe_events() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-molsoe-events-deactivator.php';
	Molsoe_Events_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_molsoe_events' );
register_deactivation_hook( __FILE__, 'deactivate_molsoe_events' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-molsoe-events.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function run_molsoe_events() {
	$plugin = new Molsoe_Events();
	$plugin->run();
}

run_molsoe_events();
