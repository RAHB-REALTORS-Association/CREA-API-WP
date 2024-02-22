<?php
/**
 * Plugin Name: CREA Board Data API
 * Plugin URI: https://github.com/RAHB-REALTORS-Association/CREA-API-WP
 * Description: A WordPress plugin to fetch and display information from the CREA Board Data API.
 * Version: 0.1.3
 * Author: RAHB
 * Author URI: https://lab.rahb.ca
 * License: GPL-2.0
 * Text Domain: crea-api
 */

// Include necessary files
require_once(plugin_dir_path(__FILE__) . 'includes/class-token-manager.php');
require_once(plugin_dir_path(__FILE__) . 'includes/office-functions.php');
require_once(plugin_dir_path(__FILE__) . 'admin/class-settings.php');

// Enqueue styles
function enqueue_crea_styles() {
    wp_enqueue_style('crea-styles', plugin_dir_url(__FILE__) . 'css/styles.css');
}
add_action('wp_enqueue_scripts', 'enqueue_crea_styles');

// Enqueue scripts
function enqueue_crea_scripts() {
    wp_enqueue_script('crea-filter', plugin_dir_url(__FILE__) . 'js/filter.js', array(), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_crea_scripts');


// Initialize settings page
$settings_page = new CREA_Settings_Page();

// Check if credentials are set
$client_id = get_option('crea_client_id');
$client_secret = get_option('crea_client_secret');

if ($client_id && $client_secret) {
    // Include the file for scheduled actions
    require_once(plugin_dir_path(__FILE__) . 'includes/scheduled-actions.php');

    // Initialize token refresh action if not already set
    if (!wp_next_scheduled('crea_refresh_token')) {
        wp_schedule_event(time(), 'hourly', 'crea_refresh_token');
    }

    // Initialize office data refresh action if not already set
    if (!wp_next_scheduled('crea_refresh_office_data')) {
        wp_schedule_event(time(), 'hourly', 'crea_refresh_office_data');
    }
}
