jQuery(document).ready(function ($) {

    // Flag select
    const flagSelect = $('#drzava');
    const flagImg = $('#selected-flag');

    flagSelect.on('change', function () {
        const selectedOption = $(this).find('option:selected');
        const flagUrl = selectedOption.data('flag');
        if (flagUrl) flagImg.attr('src', flagUrl);
    });

    // Drag & drop upload
    const dropZone = $('#drop-zone');
    const fileInput = $('#file-upload');
    const uploadIcon = $('#upload-icon');
    const uploadMessage = $('#upload-message');
    const fileInfo = $('#file-info');
    const uploadDescription = $('#upload-description');
    const invalidFormatFeedback = $('#invalid-format-feedback');
    const form = $('#enterwell-form');

    // Slike za različite state-ove
    const icons = {
        idle: enterwell_plugin.img_base + 'upload-idle.svg',
        dragover: enterwell_plugin.img_base + 'upload-dragover.svg',
        success: enterwell_plugin.img_base + 'upload-success.png',
        error: enterwell_plugin.img_base + 'upload-error.png'
    };

    const originalMessageHTML = uploadMessage.html();
    const originalUploaddescriptionHTML = uploadDescription.html();

    // Helper funkcija za promjenu stanja
    function setDropState(state, message = null) {
        dropZone.removeClass().addClass(`upload-box ${state}`);
        uploadIcon.attr('src', icons[state]);
        if (message === null) {
            uploadMessage.html(originalMessageHTML);
        } else {
            uploadMessage.text(message);
        }

        switch (state) {
            case 'error':
                uploadDescription.html(originalMessageHTML);
                invalidFormatFeedback.removeClass('hidden');
                toggleOverlay(false);
                break;
            case 'success':
                uploadDescription.html(originalMessageHTML);
                toggleOverlay(false);
                break;
            case 'dragover':
                uploadDescription.html(originalUploaddescriptionHTML);
                toggleOverlay(true);
                break;
            default:
                invalidFormatFeedback.addClass('hidden');
                toggleOverlay(false);
                break;
        }
    }

     // Helper funkcija za promjenu overlaya
    function toggleOverlay(state){
        if(state == true){
            $('.fields-column').addClass('overlay');
            $('#divider').addClass('overlay');
            $('.mt-14').addClass('overlay');
        }
        else{
            $('.fields-column').removeClass('overlay');
            $('#divider').removeClass('overlay');
            $('.mt-14').removeClass('overlay');
        }
    }

    // Helper funckija za validaciju polja
    function validateField($field) {
        const val = ($field.attr('type') === 'number') ? $field.val() : ($field.val() || '').toString().trim();

        if (!val) {
            $field.addClass('invalid');
            $field.closest('.form-group').find('.feedback').addClass('invalid-feedback').text('*Obavezna ispuna polja').show();
            return false;
        } else {
            $field.removeClass('invalid');
            $field.closest('.form-group').find('.feedback').removeClass('invalid-feedback').text('*Obavezno');
            return true;
        }
    }

    // Obrada fajla
    function handleFile(file) {
        if (!file) return;

        const allowed = ['application/pdf', 'image/png', 'image/jpeg'];
        if (!allowed.includes(file.type)) {
            setDropState('error', '');

            fileInfo.removeClass('hidden').text('Prijenos nije uspio');
            return;
        }

        setDropState('success', '');
        fileInfo.removeClass('hidden').text(file.name);

        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput[0].files = dt.files;
    }

    // Eventi
    dropZone.on('dragover', function (e) {
        e.preventDefault();
        setDropState('dragover', 'Ispusti datoteku');
        fileInfo.addClass('hidden').text('');
    });

    dropZone.on('dragleave', function (e) {
        e.preventDefault();
        setDropState('idle');
        fileInfo.addClass('hidden').text('');
    });

    dropZone.on('drop', function (e) {
        e.preventDefault();
        const file = e.originalEvent.dataTransfer.files[0];
        handleFile(file);
    });

    fileInput.on('change', function () {
        handleFile(this.files[0]);
    });

    // Validacija
    const $form = $('#enterwell-form');
    const $requiredFields = $form.find('input[required], select[required]');

    $requiredFields.each(function () {
        const $field = $(this);
        $field.removeClass('invalid');
        $field.closest('.form-group').find('.feedback').hide();
    });

    // Validacija na change
    $requiredFields.on('input change', function () {
        validateField($(this));
    });

    // Prikaži *Obavezno na focus
    $requiredFields.on('focusin', function () {
        $(this).closest('.form-group').find('.feedback').removeClass('invalid-feedback').text('*Obavezno').show();
    });

    // Validacija na focosout
    $requiredFields.on('focusout', function () {
        const validated = validateField($(this));
        if(validated){
            $(this).closest('.form-group').find('.feedback').hide();
        }
    });

    // Validacija na submit
    $form.on('submit', function (e) {
        let valid = true;

        $requiredFields.each(function () {
            if (!validateField($(this))) {
                if (valid) {
                    $('html, body').animate({ scrollTop: $(this).closest('.form-group').offset().top - 60 }, 300);
                }
                valid = false;
            }
        });

        if (!valid) e.preventDefault();
    });

    // Inicijalno stanje
    setDropState('idle');
});