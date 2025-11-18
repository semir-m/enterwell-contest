<?php include plugin_dir_path(__FILE__) . '/partials/header.php'; ?>

<div class="enterwell-form-wrapper centered">
    <img id="sticker-image" src="<?= plugin_dir_url(__FILE__) ?>../assets/img/image-sticker.png" alt="Sticker" />
    <div class="main-area">
        <img class="result-image" src="<?= plugin_dir_url(__FILE__) ?>../assets/img/icon-failed.png" alt="Failed" />
        <h1><?= esc_html($settings['error_title'] ?? 'Neuspješna prijava'); ?></h1>
        <p class="result-description"><?= esc_html($settings['error_desc'] ?? 'Došlo je do greške u komunikaciji. Provjeri svoju internetsku vezu i pokušaj ponovo.'); ?></p>
        <a href="<?= esc_url($settings['error_link'] ?? '#'); ?>"  class="btn-primary">
            <?= esc_html($settings['error_btn'] ?? 'Pokušaj ponovo'); ?>
        </a>
    </div>
</div>