<?php include plugin_dir_path(__FILE__) . '/partials/header.php'; ?>

<div class="enterwell-form-wrapper centered">
    <img id="sticker-image" src="<?= plugin_dir_url(__FILE__) ?>../assets/img/image-sticker.png" alt="Sticker" />
    <div class="main-area">
        <img class="result-image" src="<?= plugin_dir_url(__FILE__) ?>../assets/img/icon-success.png" alt="Success" />
        <h1><?= esc_html($settings['success_title'] ?? 'Uspješna prijava'); ?></h1>
        <p class="result-description"><?= esc_html($settings['success_desc'] ?? 'Dok čekaš mail potvrde, vrati se i pročitaj zadnji korak na putu do nagrade.'); ?></p>
        <button class="btn-primary"><?= esc_html($settings['success_btn'] ?? 'OK'); ?></button>
    </div>
</div>