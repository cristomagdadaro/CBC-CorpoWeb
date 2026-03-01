jQuery(document).ready(function($) {
    // --- Slug Generation and Preview ---
    function generateRandomSlug(length = 9) {
        const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        let result = "";
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    $('#generate-slug-btn').on('click', function(e) {
        e.preventDefault();
        const newSlug = generateRandomSlug();
        $('#slug').val(newSlug);
        $('#slug-preview').text(newSlug);
    });

    $('#slug').on('input', function() {
        $('#slug-preview').text($(this).val());
    });

    // --- AJAX Form Submission ---
    $('#brm-link-form').on('submit', function(e) {
        e.preventDefault();

        const $form = $(this);
        const $submitButton = $form.find('.brm-submit-button');
        const $feedback = $('#brm-form-feedback');
        const originalButtonText = $submitButton.val();

        $submitButton.val('Saving...').prop('disabled', true);
        $feedback.removeClass('brm-alert-success brm-alert-error').empty().hide();

        const formData = $form.serializeArray();
        const data = {};
        $.each(formData, function() {
            data[this.name] = this.value;
        });

        data.nonce = brm_ajax.nonce;

        $.post(brm_ajax.ajax_url, data, function(response) {
            if (response.success) {
                let successHtml = '<strong>Success!</strong> ' + response.data.message + '<br>Your link is: <code>' + response.data.full_url + '</code>';

                if (response.data.qr_code) {
                    successHtml += '<div class="brm-public-qr-wrap">';
                    successHtml += '<div><strong>QR Code</strong></div>';
                    successHtml += '<img src="' + response.data.qr_code + '" alt="GoLink QR Code" class="brm-qr-image">';
                    successHtml += '<a href="' + response.data.qr_code + '" download class="brm-download-qr">Download QR</a>';
                    successHtml += '</div>';
                }

                $feedback.addClass('brm-alert brm-alert-success').html(successHtml).show();
                $form.hide();

                // Optional: Redirect after a delay
                setTimeout(function() {
                    if (response.data.redirect && response.data.redirect.startsWith('http')) {
                         // window.location.href = response.data.redirect;
                    }
                }, 3000);

            } else {
                $feedback.addClass('brm-alert brm-alert-error').html('<strong>Error:</strong> ' + response.data.message).show();
                $submitButton.val(originalButtonText).prop('disabled', false);
            }
        }).fail(function() {
            $feedback.addClass('brm-alert brm-alert-error').html('<strong>Error:</strong> A network or server error occurred.').show();
            $submitButton.val(originalButtonText).prop('disabled', false);
        });
    });
});

