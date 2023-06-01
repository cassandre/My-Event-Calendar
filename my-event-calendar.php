<?php
/**
* Plugin Name:     My Event Calendar
* Plugin URI:
* Description:
* Version:         1.0.0
* Author:          Barbara Bothe
* Author URI:      https://barbara-bothe.de
* License:         GNU General Public License v2
* License URI:     http://www.gnu.org/licenses/gpl-2.0.html
* Domain Path:     /languages
* Text Domain:     my-event-calendar
*/

defined('ABSPATH') || exit;

include 'inc/CPT/cpt-event.php';
include 'inc/CPT/cpt-location.php';
include 'inc/CPT/cpt-organizer.php';
include 'inc/shortcode-calendar.php';
include 'inc/shortcode-events.php';

function mec_install() {
    // trigger our function that registers the custom post type
    //jas_setup_post_type();
    // clear the permalinks after the post type has been registered
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'mec_install');

function mec_deactivation() {
    // unregister the post type, so the rules are no longer in memory
    unregister_post_type('event');
    unregister_post_type('location');
    unregister_post_type('organizer');
    // clear the permalinks to remove our post type's rules from the database
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'mec_deactivation');

function mec_admin_scripts() {
    wp_enqueue_script('mec', plugin_dir_url(__FILE__) . '/js/admin.js');
}
add_action('admin_enqueue_scripts', 'mec_admin_scripts');

function mec_scripts() {
    wp_enqueue_style('mec-calendar-style', plugin_dir_url(__FILE__) . 'mec-style.css');
}
add_action('wp_enqueue_scripts', 'mec_scripts');

function mec_plugins_loaded() {
    load_plugin_textdomain( 'my-event-calendar', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'mec_plugins_loaded', 0 );