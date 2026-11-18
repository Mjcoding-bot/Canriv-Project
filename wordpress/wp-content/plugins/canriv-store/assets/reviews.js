document.addEventListener('DOMContentLoaded', () => {
    for (const group of document.querySelectorAll('.rating-group')) {
        const buttons = Array.from(group.querySelectorAll('button'));
        for (const button of buttons) {
            button.textContent = '★';
            button.addEventListener('click', () => {
                const rating = Number(button.dataset.ratingValue);
                group.querySelector('input').value = rating;
                for (const [index, star] of buttons.entries()) {
                    star.textContent = index < rating ? '★' : '☆';
                    star.style.fontVariationSettings = index < rating ? "'FILL' 1" : "'FILL' 0";
                    star.setAttribute('aria-pressed', String(index < rating));
                    star.classList.toggle('text-primary', index < rating);
                    star.classList.toggle('text-outline-variant', index >= rating);
                }
            });
        }
    }
    const input = document.getElementById('photo-upload');
    const dropzone = document.getElementById('dropzone');
    const preview = document.getElementById('file-preview-list');
    const error = document.getElementById('review-upload-error');
    const limit = Number(document.getElementById('review-photo-limit').value);
    function showFiles() {
        const files = Array.from(input.files);
        const valid = files.length <= 3 && files.every((file) => ['image/png', 'image/jpeg'].includes(file.type)) &&
            files.reduce((total, file) => total + file.size, 0) <= limit;
        error.textContent = valid ? '' : `Choose up to 3 JPG or PNG photos, no more than ${Math.floor(limit / 1024 / 1024)} MB in total.`;
        input.setCustomValidity(valid ? '' : error.textContent);
        preview.replaceChildren();
        for (const file of files) {
            const label = document.createElement('span');
            label.className = 'canriv-upload-chip';
            label.textContent = `${file.name} (${Math.ceil(file.size / 1024)} KB)`;
            preview.append(label);
        }
    }
    dropzone.addEventListener('click', (event) => {
        if (event.target !== input) input.click();
    });
    dropzone.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            input.click();
        }
    });
    dropzone.addEventListener('dragover', (event) => event.preventDefault());
    dropzone.addEventListener('drop', (event) => {
        event.preventDefault();
        input.files = event.dataTransfer.files;
        showFiles();
    });
    input.addEventListener('change', showFiles);
});
