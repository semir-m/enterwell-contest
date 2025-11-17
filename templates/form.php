<?php
    if (!defined('ABSPATH')) exit;

    include plugin_dir_path(__FILE__) . '/partials/header.php';
    $visible = $settings['visible_fields'] ?? [];
?>

<div class="enterwell-form-wrapper">
    <img id="sticker-image" src="<?= plugin_dir_url(__FILE__) ?>../assets/img/image-sticker.png" alt="Sticker" />
    <form method="post" action="" id="enterwell-form" class="enterwell-contest-form" enctype="multipart/form-data">
        <?php wp_nonce_field('enterwell_form_submit', 'contest_nonce'); ?>

        <!-- Upload i broj racuna -->
        <div class="form-column upload-column">
            <?php if (!empty($visible['file'])): ?>
                <div id="drop-zone" class="upload-box idle">
                    <div class="upload-state">
                        <img id="upload-icon" src="<?= plugin_dir_url(__FILE__) ?>../assets/img/upload-idle.png" alt="Upload" />
                        <div id="file-info" class="hidden"></div>
                        <p id="upload-message">
                            Povuci i ispusti datoteku<br>kako bi započeo prijenos<br><br>
                            ili <label for="file-upload" class="file-label">pretraži računalo.</label>
                        </p>
                    </div>

                    <input type="file" id="file-upload" name="file" accept=".pdf,.png,.jpg,.jpeg" hidden>

                    <p id="upload-description">
                        PODRŽANI FORMATI<br>
                        <span>pdf, png, jpg</span>
                    </p>
                    <p id="invalid-format-feedback" class="hidden">* Format nije podržan</p>
                </div>

            <?php endif; ?>

            <div class="form-group floating mobile d-block">
                <input type="file" id="file-upload" name="file" accept=".pdf,.png,.jpg,.jpeg">
                <label for="file">Fajl</label>
                <span class="feedback d-block">PODRŽANI FORMATI: pdf, png, jpg</span>
            </div>

            <div class="form-group floating mt-26">
                <input type="text" name="broj_racuna" id="broj_racuna" placeholder=" " required>
                <label for="broj_racuna">Broj računa*</label>
                <span class="feedback" aria-hidden="true">*Obavezna ispuna polja</span>
            </div>
        </div>

        <div id="divider"></div>

        <!-- Polja forme -->
        <div class="form-column fields-column">
            <?php
                $fields = include plugin_dir_path(__FILE__) . '../includes/fields.php';
                $visible = $settings['visible_fields'] ?? [];

                foreach ($fields as $key => $field):
                    if (empty($visible[$key]) || $key == 'file' || $key == 'broj_racuna' || $key == 'email') continue;

                    if ($field['type'] === 'select'): ?>
                        <div class="form-group always-active">
                            <label><?= esc_html($field['label']); ?><?= $field['required'] ? '*' : ''; ?></label>
                            <div class="flag-select">
                                <img id="selected-flag" src="https://flagcdn.com/hr.svg" alt="" width="24" height="14">
                                <select name="<?= esc_attr($key); ?>" id="<?= esc_attr($key); ?>" <?= $field['required'] ? 'required' : ''; ?>>
                                    <?php foreach ($field['options'] as $code => $name): ?>
                                        <option value="<?= esc_attr($name); ?>" data-flag="https://flagcdn.com/<?= strtolower($code); ?>.svg">
                                            <?= esc_html($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <span class="feedback" aria-hidden="true">*Obavezna ispuna polja</span>
                        </div>
                    <?php else: ?>
                        <div class="form-group floating">
                            <input
                                type="<?= esc_attr($field['type']); ?>"
                                name="<?= esc_attr($key); ?>"
                                id="<?= esc_attr($key); ?>"
                                placeholder=" "
                                <?= $field['required'] ? 'required' : ''; ?>
                                <?= $field['attrs'] ?? ''; ?>
                            >
                            <label for="<?= esc_attr($key); ?>"><?= esc_html($field['label']); ?><?= $field['required'] ? '*' : ''; ?></label>
                            <span class="feedback" aria-hidden="true">*Obavezna ispuna polja</span>
                        </div>
                    <?php endif;
                endforeach;
            ?>

            <!-- Email (uvijek prisutan) -->
            <div class="form-group floating">
                <input type="email" name="email" id="email" placeholder=" " required>
                <label for="email">E-mail*</label>
                <span class="feedback" aria-hidden="true">*Obavezna ispuna polja</span>
            </div>
        </div>
    </form>
    <div class="form-submit">
        <button type="submit" form="enterwell-form" class="btn-primary">Pošalji</button>
    </div>
</div>