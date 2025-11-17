<?php
if (!defined('ABSPATH')) exit;

function enterwell_sanitize_field($value) {
    return sanitize_text_field(trim($value));
}

function enterwell_validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function enterwell_check_duplicate($email, $broj_racuna) {
    $args = [
        'post_type' => 'contest_entry',
        'posts_per_page' => 1,
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'email',
                'value' => $email,
                'compare' => '=',
            ],
            [
                'key' => 'broj_racuna',
                'value' => $broj_racuna,
                'compare' => '=',
            ]
        ]
    ];
    $query = new WP_Query($args);
    return $query->have_posts();
}