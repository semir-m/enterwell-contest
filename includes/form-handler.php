<?php
if (!defined('ABSPATH')) exit;

add_action('template_redirect', 'enterwell_handle_form_submission');
add_action('wp_ajax_enterwell_form_submit_ajax', 'enterwell_handle_form_submission_ajax');
add_action('wp_ajax_nopriv_enterwell_form_submit_ajax', 'enterwell_handle_form_submission_ajax');

function enterwell_process_form($is_ajax = false) {

    if (!isset($_POST['contest_nonce']) || !wp_verify_nonce($_POST['contest_nonce'], 'enterwell_form_submit')) {
        return [
            'success' => false,
            'message' => 'Sigurnosna provjera nije prošla.'
        ];
    }

    // sanitize
    $ime = enterwell_sanitize_field($_POST['ime'] ?? '');
    $prezime = enterwell_sanitize_field($_POST['prezime'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $broj_racuna = enterwell_sanitize_field($_POST['broj_racuna'] ?? '');
    $adresa = enterwell_sanitize_field($_POST['adresa'] ?? '');
    $kucni_broj = enterwell_sanitize_field($_POST['kucni_broj'] ?? '');
    $mjesto = enterwell_sanitize_field($_POST['mjesto'] ?? '');
    $postanski_broj = enterwell_sanitize_field($_POST['postanski_broj'] ?? '');
    $drzava = enterwell_sanitize_field($_POST['drzava'] ?? '');
    $kontakt_telefon = enterwell_sanitize_field($_POST['kontakt_telefon'] ?? '');

    $settings = get_option('enterwell_contest_settings', []);

    // required validation
    if (empty($ime) || empty($prezime) || empty($email) || empty($broj_racuna)) {
        return [
            'success' => false,
            'message' => 'Sva obavezna polja moraju biti popunjena.'
        ];
    }

    if (!enterwell_validate_email($email)) {
        return [
            'success' => false,
            'message' => 'Email nije validan.'
        ];
    }

    if (enterwell_check_duplicate($email, $broj_racuna)) {
        return [
            'success' => false,
            'message' => 'Prijava s ovim emailom ili brojem računa već postoji.'
        ];
    }

    // file upload
    $uploaded_file_url = '';
    $uploaded_file_name = '';

    if (!empty($_FILES['file']['name'])) {

        $uploaded_file = $_FILES['file'];

        if ($uploaded_file['error'] === UPLOAD_ERR_OK) {

            require_once(ABSPATH . 'wp-admin/includes/file.php');

            $allowed = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!in_array($uploaded_file['type'], $allowed)) {
                return [
                    'success' => false,
                    'message' => 'Format datoteke nije podržan (dozvoljeni: JPG, PNG, PDF).'
                ];
            }

            $movefile = wp_handle_upload($uploaded_file, ['test_form' => false]);

            if ($movefile && !isset($movefile['error'])) {
                $uploaded_file_url = $movefile['url'];
                $uploaded_file_name = basename($movefile['file']);
            } else {
                return [
                    'success' => false,
                    'message' => 'Greška prilikom spremanja datoteke.'
                ];
            }

        } else {
            return [
                'success' => false,
                'message' => 'Greška pri uploadu datoteke.'
            ];
        }
    }

    // create post
    $post_id = wp_insert_post([
        'post_type' => 'contest_entry',
        'post_title' => $ime . ' ' . $prezime,
        'post_status' => 'publish',
        'meta_input' => [
            'email' => $email,
            'broj_racuna' => $broj_racuna,
            'adresa' => $adresa,
            'kucni_broj' => $kucni_broj,
            'mjesto' => $mjesto,
            'postanski_broj' => $postanski_broj,
            'drzava' => $drzava,
            'kontakt_telefon' => $kontakt_telefon,
            'file_url' => $uploaded_file_url,
            'file_name' => $uploaded_file_name,
        ],
    ]);

    if (!$post_id || is_wp_error($post_id)) {
        return [
            'success' => false,
            'message' => 'Greška prilikom spremanja prijave.'
        ];
    }

    return [
        'success' => true,
        'redirect' => get_permalink($settings['success_page'])
    ];
}

function enterwell_handle_form_submission_ajax() {
    $result = enterwell_process_form(true);

    if ($result['success']) {

        // load success template
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/success.php';
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    // load error template
    ob_start();

    $message = $result['message'];
    include plugin_dir_path(__FILE__) . '../templates/error.php';

    $html = ob_get_clean();

    wp_send_json_error(['html' => $html]);
}

function enterwell_handle_form_submission() {
    if (!isset($_POST['contest_nonce'])) return;

    $result = enterwell_process_form(false);
    $settings = get_option('enterwell_contest_settings', []);

    if ($result['success']) {
        wp_redirect($result['redirect']);
        exit;
    }

    // error
    if (!empty($settings['error_page'])) {
        wp_redirect(get_permalink($settings['error_page']));
        exit;
    }

    wp_die($result['message']);
}