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