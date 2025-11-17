<?php
    if (!defined('ABSPATH')) exit;

    $settings = wp_parse_args(get_option('enterwell_contest_settings', []), enterwell_get_default_settings());
?>

<div class="enterwell-form-header">
    <h1><?= esc_html($settings['header_title'] ?? 'Prijava na Enterwell nagradnu igru!'); ?></h1>
    <p><?= esc_html($settings['header_desc'] ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'); ?></p>
</div>