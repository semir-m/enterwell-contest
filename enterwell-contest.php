<?php
/*
Plugin Name: Enterwell Contest Form
Description: Prijava na nagradnu igru - custom form sa uploadom i CPT.
Version: 1.0
Author: Semir M.
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/cpt.php';
require_once plugin_dir_path(__FILE__) . 'includes/form-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';
require_once plugin_dir_path(__FILE__) . 'includes/contest-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-columns.php';

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'enterwell-contest-style',
        plugin_dir_url(__FILE__) . 'assets/css/style.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/style.css')
    );

    wp_enqueue_script(
        'enterwell-contest-js',
        plugin_dir_url(__FILE__) . 'assets/js/form.js',
        ['jquery'],
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/form.js'),
        true
    );

    wp_localize_script('enterwell-contest-js', 'enterwell_plugin', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('enterwell_nonce'),
        'img_base' => plugin_dir_url(__FILE__) . 'assets/img/'
    ]);
});

// Form
add_shortcode('enterwell_contest_form', function () {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/form.php';
    return ob_get_clean();
});

// Success screen
add_shortcode('enterwell_success_screen', function () {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/success.php';
    return ob_get_clean();
});

// Error screen
add_shortcode('enterwell_error_screen', function () {
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/error.php';
    return ob_get_clean();
});

register_activation_hook(__FILE__, 'enterwell_activate_defaults');
function enterwell_activate_defaults() {
    $defaults = enterwell_get_default_settings();
    if (get_option('enterwell_contest_settings') === false) {
        add_option('enterwell_contest_settings', $defaults);
    }
}