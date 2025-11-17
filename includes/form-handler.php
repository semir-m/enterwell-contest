<?php
if (!defined('ABSPATH')) exit;

add_action('template_redirect', 'enterwell_handle_form_submission');

function enterwell_handle_form_submission() {
    if (!isset($_POST['contest_nonce'])) return;
    if (!wp_verify_nonce($_POST['contest_nonce'], 'enterwell_form_submit')) return;

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

    $settings = get_option('enterwell_contest_settings', ['success_page' => 0, 'error_page' => 0]);

    // validacija
    if (empty($ime) || empty($prezime) || empty($email) || empty($broj_racuna)) {
        if ($settings['error_page']) {
            wp_redirect(get_permalink($settings['error_page']));
            exit;
        }
        wp_die('Sva polja su obavezna.');
    }

    if (!enterwell_validate_email($email)) {
        if ($settings['error_page']) {
            wp_redirect(get_permalink($settings['error_page']));
            exit;
        }
        wp_die('Email nije validan.');
    }

    if (enterwell_check_duplicate($email, $broj_racuna)) {
        if ($settings['error_page']) {
            wp_redirect(get_permalink($settings['error_page']));
            exit;
        }
        wp_die('Prijava s ovim emailom ili brojem računa već postoji.');
    }

    if (!empty($_FILES['file']['name'])) {
        $uploaded_file = $_FILES['file'];

        if ($uploaded_file['error'] === UPLOAD_ERR_OK) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');

            $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!in_array($uploaded_file['type'], $allowed_types)) {
                enterwell_redirect_or_die($settings, 'Format datoteke nije podržan (dozvoljeni: JPG, PNG, PDF).');
            }

            $upload_overrides = ['test_form' => false];
            $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $uploaded_file_url = $movefile['url'];
                $uploaded_file_name = basename($movefile['file']);
            } else {
                enterwell_redirect_or_die($settings, 'Greška prilikom spremanja datoteke.');
            }
        } else {
            enterwell_redirect_or_die($settings, 'Greška pri uploadu datoteke.');
        }
    }

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

    if ($post_id && !is_wp_error($post_id)) {
        if ($settings['success_page']) {
            wp_redirect(get_permalink($settings['success_page']));
            exit;
        }
        echo '<p>Uspješno poslano!</p>';
    } else {
        if ($settings['error_page']) {
            wp_redirect(get_permalink($settings['error_page']));
            exit;
        }
        wp_die('Došlo je do greške prilikom spremanja prijave.');
    }

    exit;
}