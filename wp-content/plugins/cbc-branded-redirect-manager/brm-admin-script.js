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
});