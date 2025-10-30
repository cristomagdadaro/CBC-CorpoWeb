document.addEventListener("DOMContentLoaded", function () {
    const slugInput = document.querySelector('input[name="slug"]');
    const generateBtn = document.querySelector("#generate-slug-btn");
    const preview = document.getElementById('slug-preview');

    function generateSlug(length = 9) {
        const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        let result = "";
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    if (slugInput) {
        // Update preview on manual input
        slugInput.addEventListener('input', function () {
            if (preview) {
                preview.textContent = slugInput.value;
            }
        });
    }

    if (slugInput && generateBtn) {
        // Auto-generate slug on button click
        generateBtn.addEventListener("click", (e) => {
            e.preventDefault();
            slugInput.value = generateSlug();
            if (preview) {
                preview.textContent = slugInput.value;
            }
        });
    }
});