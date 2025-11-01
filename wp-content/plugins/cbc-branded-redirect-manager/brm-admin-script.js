document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.brm-copy-url').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const urlToCopy = this.getAttribute('data-url');

            // Copy logic (Clipboard API with fallback)
            let copied = false;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(urlToCopy).then(() => copied = true).catch(() => copied = false);
            } else {
                const tempInput = document.createElement('textarea');
                tempInput.value = urlToCopy;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                copied = true;
            }

            if (copied) {
                const originalText = this.textContent;
                this.textContent = 'Copied!';
                this.classList.add('copied'); // CSS class for feedback
                setTimeout(() => {
                    this.textContent = originalText;
                    this.classList.remove('copied');
                }, 1500);
            }
        });
    });

    // Regenerate QR Code via AJAX
    document.querySelectorAll('.brm-action-regenerate-qr').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const linkId = this.getAttribute('data-id');
            const originalText = this.textContent;
            const row = this.closest('tr');
            const qrCell = row ? row.querySelector('.brm-qr-cell') : null;

            this.textContent = 'Generating...';
            this.disabled = true;

            const formData = new FormData();
            formData.append('action', 'brm_regenerate_qr');
            formData.append('nonce', brm_admin_ajax.nonce);
            formData.append('id', linkId);

            fetch(brm_admin_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    this.textContent = 'Done!';
                    if (qrCell && result.data.qr_code) {
                        // Clear previous content and add new QR
                        qrCell.innerHTML = `
                            <img src="${result.data.qr_code}" alt="QR Code" class="brm-qr-image">
                            <a href="${result.data.qr_code}" download class="button button-small brm-download-qr">Download</a>
                        `;
                    }
                } else {
                    this.textContent = 'Failed!';
                    alert(result.data.message || 'An unknown error occurred.');
                }

                setTimeout(() => {
                    this.textContent = originalText;
                    this.disabled = false;
                }, 2000);
            })
            .catch(error => {
                console.error('Error:', error);
                this.textContent = 'Error!';
                 setTimeout(() => {
                    this.textContent = originalText;
                    this.disabled = false;
                }, 2000);
            });
        });
    });
});