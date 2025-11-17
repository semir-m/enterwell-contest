<?php
if (!defined('ABSPATH')) exit;


add_action('admin_enqueue_scripts', 'enterwell_enqueue_admin_styles');
function enterwell_enqueue_admin_styles($hook_suffix) {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'contest_entry_page_contest_settings') {
        wp_enqueue_style(
            'enterwell-admin-css',
            plugin_dir_url(__DIR__) . 'assets/css/admin.css',
            [],
            '1.0'
        );
    }
}

add_action('admin_menu', 'enterwell_add_settings_submenu');
function enterwell_add_settings_submenu() {
    add_submenu_page(
        'edit.php?post_type=contest_entry',
        'Postavke plugina',
        'Postavke',
        'manage_options',
        'contest_settings',
        'enterwell_render_settings_page'
    );
}

function enterwell_get_default_settings() {
    $all_fields = include plugin_dir_path(__FILE__) . 'fields.php';

    return [
        'success_page'  => 0,
        'error_page'    => 0,

        // global header
        'header_title'  => 'Prijava na Enterwell nagradnu igru!',
        'header_desc'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',

        // success page
        'success_title' => 'Uspješna prijava',
        'success_desc'  => 'Dok čekaš mail potvrde, vrati se i pročitaj zadnji korak na putu do nagrade.',
        'success_btn'   => 'OK',

        // error page
        'error_title'   => 'Neuspješna prijava',
        'error_desc'    => 'Došlo je do greške u komunikaciji. Provjeri svoju internetsku vezu i pokušaj ponovo.',
        'error_btn'     => 'Pokušaj ponovo',

        // visible fields (default: svi uključeni)
        'visible_fields' => array_fill_keys(array_keys($all_fields), 1),
    ];
}

function enterwell_render_settings_page() {
    if (!current_user_can('manage_options')) return;

    $all_fields = include plugin_dir_path(__FILE__) . 'fields.php';

    // Reset na default
    if (isset($_POST['enterwell_reset_defaults'])) {
        check_admin_referer('enterwell_save_settings_nonce');
        $defaults = enterwell_get_default_settings();
        update_option('enterwell_contest_settings', $defaults);
        echo '<div class="updated"><p>Settings reset to defaults.</p></div>';
    }

    // Spremanje postavki
    if (isset($_POST['enterwell_save_settings'])) {
        check_admin_referer('enterwell_save_settings_nonce');
        $settings = [
            // stranice
            'success_page' => intval($_POST['success_page'] ?? 0),
            'error_page'   => intval($_POST['error_page'] ?? 0),

            // globalni header
            'header_title' => sanitize_text_field($_POST['header_title'] ?? ''),
            'header_desc'  => sanitize_textarea_field($_POST['header_desc'] ?? ''),

            // success textovi
            'success_title' => sanitize_text_field($_POST['success_title'] ?? ''),
            'success_desc'  => sanitize_textarea_field($_POST['success_desc'] ?? ''),
            'success_btn'   => sanitize_text_field($_POST['success_btn'] ?? ''),

            // error textovi
            'error_title' => sanitize_text_field($_POST['error_title'] ?? ''),
            'error_desc'  => sanitize_textarea_field($_POST['error_desc'] ?? ''),
            'error_btn'   => sanitize_text_field($_POST['error_btn'] ?? ''),

            // visible fields
            'visible_fields' => array_map('intval', $_POST['visible_fields'] ?? []),
        ];
        update_option('enterwell_contest_settings', $settings);
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    $defaults = enterwell_get_default_settings();
    $settings = wp_parse_args(get_option('enterwell_contest_settings', []), $defaults);
    $visible_fields = $settings['visible_fields'];
    $pages = get_pages();
    ?>

    <div class="wrap">
         <div class="plugin-description" style="margin-bottom:20px; padding:15px; background:#f7f7f7; border-left:4px solid #D9452D; border-radius:4px;">
            <p>
                <strong>Enterwell Contest Plugin</strong> - Custom plugin napravljen za potrebe Enterwell nagradne igre. 
                Omogućava konfiguraciju:
            </p>
            <ul style="margin:5px 0 0 20px; padding:0; list-style:disc;">
                <li>Koja polja se prikazuju na formi (upload računa, ime, prezime, adresa, itd.)</li>
                <li>Odabir stranica za uspješnu prijavu i grešku u prijavi</li>
                <li>Tekstove za globalni header, uspješnu prijavu i grešku u prijavi</li>
                <li>Reset postavki na defaultne vrijednosti</li>
            </ul>
            <p style="margin-top:10px; font-weight:600;">Shortcode-i:</p>
            <ul style="margin:5px 0 0 20px; padding:0; list-style:disc;">
                <li><code>[enterwell_contest_form]</code> - prikazuje formu</li>
                <li><code>[enterwell_success_screen]</code> - prikazuje uspješnu prijavu</li>
                <li><code>[enterwell_error_screen]</code> - prikazuje grešku u prijavi</li>
            </ul>
            <p style="margin-top:5px; font-size:12px; color:#777;">
                Napomena: Ako stranice za formu, uspješnu prijavu i grešku u prijavi ne postoje, kreirajte ih ručno i unesite odgovarajući shortcode.
            </p>
            <p style="margin-top:5px; font-size:12px; color:#777;">Autor: Semir Mašić</p>
        </div>

        <h1>Postavke plugina</h1>

        <form method="post">
            <?php wp_nonce_field('enterwell_save_settings_nonce'); ?>

            <!-- Global header -->
            <div class="enterwell-card">
                <h2>Header tekst</h2>
                <div class="form-row">
                    <label for="header_title">Naslov</label>
                    <input type="text" name="header_title" id="header_title" value="<?= esc_attr($settings['header_title']); ?>">
                </div>
                <div class="form-row">
                    <label for="header_desc">Opis</label>
                    <textarea name="header_desc" id="header_desc" rows="3"><?= esc_textarea($settings['header_desc']); ?></textarea>
                </div>
            </div>

            <!-- Form fields -->
            <div class="enterwell-card">
                <h2>Polja forme</h2>
                <?php foreach ($all_fields as $key => $field): ?>
                    <div class="form-row">
                        <label for="field_<?= esc_attr($key); ?>"><?= esc_html($field['label']); ?></label>
                        <input type="checkbox" id="field_<?= esc_attr($key); ?>"
                            name="visible_fields[<?= esc_attr($key); ?>]" value="1"
                            <?= checked(!empty($visible_fields[$key])); ?>
                            <?= ($key == 'broj_racuna' || $key == 'email') ? 'disabled' : '' ?>>
                        <span>
                            <?= ($key == 'broj_racuna' || $key == 'email') ? 'Obavezan prikaz na formi - validiramo polje' : 'Prikaži na formi' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Page selection -->
            <div class="enterwell-card">
                <h2>Odabir stranica</h2>
                <div class="form-row">
                    <label for="success_page">Upješna prijava</label>
                    <select name="success_page" id="success_page">
                        <option value="0">— Select —</option>
                        <?php foreach ($pages as $page): ?>
                            <option value="<?= esc_attr($page->ID); ?>" <?= selected($settings['success_page'], $page->ID, false); ?>>
                                <?= esc_html($page->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <label for="error_page">Greška u prijavi</label>
                    <select name="error_page" id="error_page">
                        <option value="0">— Odaberi stranicu —</option>
                        <?php foreach ($pages as $page): ?>
                            <option value="<?= esc_attr($page->ID); ?>" <?= selected($settings['error_page'], $page->ID, false); ?>>
                                <?= esc_html($page->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Success page texts -->
            <div class="enterwell-card">
                <h2>Tekstovi za uspješnu prijavu</h2>
                <div class="form-row">
                    <label for="success_title">Naslov</label>
                    <input type="text" name="success_title" id="success_title" value="<?= esc_attr($settings['success_title']); ?>">
                </div>
                <div class="form-row">
                    <label for="success_desc">Opis</label>
                    <textarea name="success_desc" id="success_desc" rows="3"><?= esc_textarea($settings['success_desc']); ?></textarea>
                </div>
                <div class="form-row">
                    <label for="success_btn">Tekst gumba</label>
                    <input type="text" name="success_btn" id="success_btn" value="<?= esc_attr($settings['success_btn']); ?>">
                </div>
            </div>

            <!-- Error page texts -->
            <div class="enterwell-card">
                <h2>Tekstovi za grešku u prijavi</h2>
                <div class="form-row">
                    <label for="error_title">Naslov</label>
                    <input type="text" name="error_title" id="error_title" value="<?= esc_attr($settings['error_title']); ?>">
                </div>
                <div class="form-row">
                    <label for="error_desc">Opis</label>
                    <textarea name="error_desc" id="error_desc" rows="3"><?= esc_textarea($settings['error_desc']); ?></textarea>
                </div>
                <div class="form-row">
                    <label for="error_btn">Tekst gumba</label>
                    <input type="text" name="error_btn" id="error_btn" value="<?= esc_attr($settings['error_btn']); ?>">
                </div>
            </div>

            <!-- Buttons -->
            <?php submit_button('Vrati na defaultne postavke', 'secondary', 'enterwell_reset_defaults'); ?>
            <?php submit_button('Spremi postavke', 'primary', 'enterwell_save_settings'); ?>
        </form>
    </div>

<?php
}