<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    register_post_type('contest_entry', [
        'labels' => [
            'name' => 'Prijave natječaja',
            'singular_name' => 'Prijava natječaja',
            'menu_name' => 'Prijave natječaja',
            'add_new_item' => 'Dodaj novu prijavu',
            'edit_item' => 'Uredi prijavu',
            'view_item' => 'Prikaz prijave',
            'search_items' => 'Pretraži prijave',
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-awards',
        'supports' => [],
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'has_archive' => false,
    ]);
});

add_action('admin_head', function() {
    global $submenu;
    if (isset($submenu['edit.php?post_type=contest_entry'])) {
        unset($submenu['edit.php?post_type=contest_entry'][10]);
    }
    
    if (get_current_screen()->post_type === 'contest_entry') {
        echo '<style>
            .page-title-action { display: none !important; }
        </style>';
    }
});

add_filter('post_row_actions', function($actions, $post) {
    if ($post->post_type === 'contest_entry') {
        unset($actions['edit']);
        unset($actions['inline hide']);
        unset($actions['trash']);
    }
    return $actions;
}, 10, 2);

add_filter('bulk_actions-edit-contest_entry', '__return_empty_array');

add_action('admin_enqueue_scripts', function($hook) {
    global $post_type;
    if ($post_type === 'contest_entry') {
        wp_dequeue_script('inline-edit-post');
    }
});

add_filter('manage_contest_entry_posts_columns', function ($columns) {
    return [
        'title' => 'Full Name',
        'email' => 'Email',
        'receipt_number' => 'Receipt Number',
        'file' => 'File',
        'date' => 'Date',
    ];
});

add_action('manage_contest_entry_posts_custom_column', function ($column, $post_id) {
    switch ($column) {
        case 'email':
            echo esc_html(get_post_meta($post_id, 'email', true));
            break;
        case 'receipt_number':
            echo esc_html(get_post_meta($post_id, 'broj_racuna', true));
            break;
        case 'file':
            $file_url = get_post_meta($post_id, 'file_url', true);
            $file_name = get_post_meta($post_id, 'file_name', true);

            if ($file_url && $file_name) {
                echo '<a href="' . esc_url($file_url) . '" download>' . esc_html($file_name) . '</a>';
            } elseif ($file_name) {
                echo esc_html($file_name);
            } else {
                echo '<em>Nema fajla</em>';
            }
            break;
    }
}, 10, 2);

add_filter('manage_edit-contest_entry_sortable_columns', function ($columns) {
    $columns['email'] = 'email';
    $columns['receipt_number'] = 'receipt_number';
    return $columns;
});

add_filter('the_title', function($title, $post_id) {
    $post = get_post($post_id);
    if ($post->post_type === 'contest_entry' && is_admin()) {
        return esc_html($title);
    }
    return $title;
}, 10, 2);