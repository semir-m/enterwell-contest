<?php
if (!defined('ABSPATH')) exit;

add_filter('manage_contest_entry_posts_columns', function ($columns) {
    return [
        'title'          => __('Ime i prezime', 'enterwell'),
        'email'          => __('Email', 'enterwell'),
        'broj_racuna'    => __('Broj računa', 'enterwell'),
        'file'           => __('Fajl', 'enterwell'),
        'adresa'         => __('Adresa', 'enterwell'),
        'kucni_broj'     => __('Kućni broj', 'enterwell'),
        'mjesto'         => __('Mjesto', 'enterwell'),
        'postanski_broj' => __('Poštanski broj', 'enterwell'),
        'drzava'         => __('Država', 'enterwell'),
        'kontakt_telefon'=> __('Kontakt telefon', 'enterwell'),
        'date'           => __('Datum', 'enterwell'),
    ];
});

add_action('manage_contest_entry_posts_custom_column', function ($column, $post_id) {
    switch ($column) {
        case 'email':
        case 'broj_racuna':
        case 'adresa':
        case 'kucni_broj':
        case 'mjesto':
        case 'postanski_broj':
        case 'drzava':
        case 'kontakt_telefon':
            echo esc_html(get_post_meta($post_id, $column, true));
            break;
        case 'file':
            $file_url = get_post_meta($post_id, 'file_url', true);
            $file_name = get_post_meta($post_id, 'file_name', true);

            if ($file_url && $file_name) {
                echo '<a href="' . esc_url($file_url) . '" target="_blank" download>' . esc_html($file_name) . '</a>';
            } elseif ($file_name) {
                echo esc_html($file_name);
            } else {
                echo '<em>Nema fajla</em>';
            }
            break;
    }
}, 10, 2);

add_filter('post_row_actions', function ($actions, $post) {
    if ($post->post_type === 'contest_entry') {
        unset($actions['edit'], $actions['inline'], $actions['trash'], $actions['view']);
    }
    return $actions;
}, 10, 2);

add_filter('the_title', function ($title, $post_id) {
    $post = get_post($post_id);
    if ($post && $post->post_type === 'contest_entry') {
        remove_filter('the_title', __FUNCTION__, 10, 2);
        return esc_html($title);
    }
    return $title;
}, 10, 2);

add_filter('post_title', function ($title, $post_id) {
    global $pagenow, $typenow;
    if ($typenow === 'contest_entry' && $pagenow === 'edit.php') {
        return esc_html($title);
    }
    return $title;
}, 10, 2);

add_action('admin_head', function () {
    global $typenow;
    if ($typenow === 'contest_entry') {
        echo '<style>
            .page-title-action,
            .editinline,
            .row-actions .edit,
            .row-actions .inline,
            .row-actions .trash,
            .row-actions .view {
                display: none !important;
            }
            .wp-list-table .column-title a.row-title {
                pointer-events: none;
                color: inherit;
                text-decoration: none;
            }
        </style>';
    }
});

add_action('admin_menu', function () {
    global $submenu;
    if (isset($submenu['edit.php?post_type=contest_entry'])) {
        foreach ($submenu['edit.php?post_type=contest_entry'] as $index => $item) {
            if (in_array('post-new.php?post_type=contest_entry', $item, true)) {
                unset($submenu['edit.php?post_type=contest_entry'][$index]);
            }
        }
    }
});