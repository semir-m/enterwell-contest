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
        'ajax_submit'  => 1,
        'success_page'  => 0,
        'error_page'    => 0,

        // global header
        'header_title'  => 'Prijava na Enterwell nagradnu igru!',
        'header_desc'   => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',

        // success page
        'success_title' => 'Uspješna prijava',
        'success_desc'  => 'Dok čekaš mail potvrde, vrati se i pročitaj zadnji korak na putu do nagrade.',
        'success_btn'   => 'OK',
        'success_link'  => '',

        // error page
        'error_title'   => 'Neuspješna prijava',
        'error_desc'    => 'Došlo je do greške u komunikaciji. Provjeri svoju internetsku vezu i pokušaj ponovo.',
        'error_btn'     => 'Pokušaj ponovo',
        'error_link'  => '',

        // visible fields (default: svi uključeni)
        'visible_fields' => array_fill_keys(array_keys($all_fields), 1),
        'required_fields' => array_merge(
            array_fill_keys(array_keys($all_fields), 1),
            [
                'email' => 1,
                'broj_racuna' => 1,
            ]
        ),
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
            // ajax submit
            'ajax_submit' => intval($_POST['ajax_submit'] ?? 0),

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
            'success_link'   => sanitize_text_field($_POST['success_link'] ?? ''),

            // error textovi
            'error_title' => sanitize_text_field($_POST['error_title'] ?? ''),
            'error_desc'  => sanitize_textarea_field($_POST['error_desc'] ?? ''),
            'error_btn'   => sanitize_text_field($_POST['error_btn'] ?? ''),
            'error_link'   => sanitize_text_field($_POST['error_link'] ?? ''),

            // visible fields
            'visible_fields' => array_map('intval', $_POST['visible_fields'] ?? []),
            'required_fields' => array_map('intval', $_POST['required_fields'] ?? []),
        ];

        $settings['visible_fields']['email'] = 1;
        $settings['visible_fields']['broj_racuna'] = 1;
        $settings['required_fields']['email'] = 1;
        $settings['required_fields']['broj_racuna'] = 1;

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
                <li>Koja polja forme zahtjevaju obavezan unos</li>
                <li>Ajax submit - da li se submit radi putem ajaxa (one page)</li>
                <li>Odabir stranica za uspješnu prijavu i grešku u prijavi</li>
                <li>Tekstove za globalni header, uspješnu prijavu i grešku u prijavi</li>
                <li>Tekstove i linkove gumbova uspješne prijave i greške u prijavi</li>
                <li>Reset postavki na defaultne vrijednosti</li>
            </ul>
            <p style="margin-top:10px; font-weight:600;">Shortcode-i:</p>
            <ul style="margin:5px 0 0 20px; padding:0; list-style:disc;">
                <li><code>[enterwell_contest_form]</code> - prikazuje formu</li>
                <li><code>[enterwell_success_screen]</code> - prikazuje uspješnu prijavu</li>
                <li><code>[enterwell_error_screen]</code> - prikazuje grešku u prijavi</li>
            </ul>
            <p style="margin-top:5px; font-size:12px; color:#777;">
                Napomena: Ako stranice za formu, uspješnu prijavu i grešku u prijavi ne postoje, kreirajte ih ručno i unesite odgovarajući shortcode. Ako koristite ajax submit, stranice za uspješnu prijavu i grešku u prijavi nije potrebno postavljati.
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
                    <div class="form-row" style="display:flex; align-items:center; gap:20px;">

                        <label style="min-width:180px;">
                            <?= esc_html($field['label']); ?>
                        </label>

                        <!-- Visible checkbox -->
                        <label style="display:flex; align-items:center; gap:5px;">
                            <input type="checkbox"
                                name="visible_fields[<?= esc_attr($key); ?>]"
                                value="1"
                                <?= checked(!empty($visible_fields[$key]) || $key === 'email' || $key === 'broj_racuna'); ?>
                                <?= ($key === 'email' || $key === 'broj_racuna') ? 'disabled' : '' ?>>
                            <span>Vidljivo</span>
                        </label>

                        <!-- Required checkbox -->
                        <label style="display:flex; align-items:center; gap:5px;">
                            <input type="checkbox"
                                name="required_fields[<?= esc_attr($key); ?>]"
                                value="1"
                                <?= checked(!empty($settings['required_fields'][$key])); ?>
                                <?= ($key === 'email' || $key === 'broj_racuna') ? 'disabled' : '' ?>>
                            <span>Obavezno</span>
                        </label>

                        <?php if ($key === 'email' || $key === 'broj_racuna'): ?>
                            <span style="color:#777;">(uvijek vidljivo i obavezno)</span>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Page selection -->
            <div class="enterwell-card">
                <h2>Ajax submit i odabir stranica</h2>
                <div class="form-row">
                    <label style="display:flex; align-items:center; gap:5px;">
                        <input id="ajax_submit"
                            name="ajax_submit"
                            type="checkbox"
                            value="1"
                            <?= checked($settings['ajax_submit']); ?>>
                        <span>Koristi ajax submit?</span>
                    </label>
                </div>
                <div class="form-row">
                    <label for="success_page">Upješna prijava</label>
                    <select name="success_page" id="success_page">
                        <option value="0">— Odaberi stranicu —</option>
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
                <div class="form-row">
                    <label for="success_link">Link gumba</label>
                    <input type="text" name="success_link" value="<?= esc_attr($settings['success_link']); ?>">
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
                <div class="form-row">
                    <label for="error_link">Link gumba</label>
                    <input type="text" name="error_link" value="<?= esc_attr($settings['error_link']); ?>">
                </div>
            </div>

            <!-- Buttons -->
            <?php submit_button('Vrati na defaultne postavke', 'secondary', 'enterwell_reset_defaults'); ?>
            <?php submit_button('Spremi postavke', 'primary', 'enterwell_save_settings'); ?>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {


            const ajax_submit = document.getElementById('ajax_submit');
            const successSelect = document.getElementById('success_page');
            const errorSelect = document.getElementById('error_page');
            const rows = document.querySelectorAll('.enterwell-card .form-row');

            function updateAjaxSubmitState() {
                if(ajax_submit.checked){
                    successSelect.value = 0;
                    errorSelect.value = 0;
                    successSelect.disabled = true;
                    errorSelect.disabled = true;
                }
                else{
                    successSelect.disabled = false;
                    errorSelect.disabled = false;
                }
            }

            updateAjaxSubmitState();

            ajax_submit.addEventListener('change', updateAjaxSubmitState);

            rows.forEach(row => {
                const visible = row.querySelector('input[name^="visible_fields"]');
                const required = row.querySelector('input[name^="required_fields"]');

                if (!visible || !required) return;

                if (visible.disabled || required.disabled) return;

                function updateState() {
                    if (!visible.checked) {
                        required.checked = false;
                        required.disabled = true;
                    } else {
                        required.disabled = false;
                    }
                }

                updateState();

                visible.addEventListener('change', updateState);
            });
        });
    </script>

<?php
}