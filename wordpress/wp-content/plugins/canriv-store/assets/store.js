document.addEventListener('DOMContentLoaded', () => {
    const checkout = document.querySelector('.canriv-purchase form.checkout');
    const submit = checkout?.querySelector('.canriv-summary-submit');
    const nativeSubmit = checkout?.querySelector('#place_order');
    if (submit && nativeSubmit) {
        submit.hidden = false;
        checkout.classList.add('canriv-summary-action-active');
        submit.addEventListener('click', () => {
            const button = checkout.querySelector('#place_order');
            if (button && !checkout.classList.contains('processing')) button.click();
        });
    }
    document.querySelector('.canriv-summary-edit')?.addEventListener('click', () => {
        const editor = document.getElementById('canriv-edit-cart');
        if (editor) editor.open = true;
    });

    const aircon = document.getElementById('heroCardAircon');
    const elevator = document.getElementById('heroCardElevator');
    const toggles = document.querySelectorAll('[data-canriv-toggle]');
    if (aircon && elevator && toggles.length) {
        for (const toggle of toggles) {
            toggle.addEventListener('click', () => {
                const showAircon = toggle.id === 'heroTabAircon';
                aircon.classList.toggle('hidden', !showAircon);
                elevator.classList.toggle('hidden', showAircon);
                for (const button of toggles) {
                    const active = button === toggle;
                    button.setAttribute('aria-pressed', String(active));
                    button.classList.toggle('bg-surface-container-lowest', active);
                    button.classList.toggle('text-primary', active);
                }
            });
        }
    }
});
