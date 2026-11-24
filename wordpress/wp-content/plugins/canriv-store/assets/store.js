document.addEventListener('DOMContentLoaded', () => {
    const pwToggle = document.querySelector('[data-canriv-pw-toggle]');
    if (pwToggle) {
        const input = pwToggle.closest('.field-wrap')?.querySelector('input[type="password"], input[type="text"]');
        const glyph = pwToggle.querySelector('.material-symbols-outlined');
        pwToggle.addEventListener('click', () => {
            if (!input) return;
            const reveal = input.type === 'password';
            input.type = reveal ? 'text' : 'password';
            pwToggle.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
            if (glyph) glyph.textContent = reveal ? 'visibility' : 'visibility_off';
        });
    }

    const mobileInputs = document.querySelectorAll(
        '#contactNumber, .canriv-customer-portal input[name="phone"], .canriv-form input[name="phone"]'
    );
    for (const phone of mobileInputs) {
        const localRfqPhone = phone.id === 'contactNumber';
        phone.maxLength = localRfqPhone ? 10 : 16;
        phone.inputMode = localRfqPhone ? 'numeric' : 'tel';
        if (localRfqPhone) phone.pattern = '9[0-9]{9}';
        const validatePhone = () => {
            if (localRfqPhone) {
                const digits = phone.value.replace(/\D/g, '').slice(0, 10);
                if (phone.value !== digits) phone.value = digits;
                phone.setCustomValidity(digits && !/^9\d{9}$/.test(digits)
                    ? 'Enter exactly 10 digits starting with 9, such as 9171234567.'
                    : '');
                return;
            }
            const normalized = phone.value.trim().replace(/[\s().-]+/g, '');
            phone.setCustomValidity(normalized && !/^(?:\+?63|0)?9\d{9}$/.test(normalized)
                ? 'Enter a valid Philippine mobile number, such as 0917 123 4567.'
                : '');
        };
        phone.addEventListener('input', validatePhone);
        phone.addEventListener('blur', validatePhone);
        phone.form?.addEventListener('submit', (event) => {
            validatePhone();
            if (!phone.reportValidity()) event.preventDefault();
        });
    }

    const cartForm = document.querySelector('.canriv-purchase-cart .woocommerce-cart-form');
    if (cartForm) {
        let cartUpdateTimer;
        cartForm.addEventListener('change', event => {
            if (!event.target.matches('.quantity .qty')) return;
            clearTimeout(cartUpdateTimer);
            cartUpdateTimer = setTimeout(() => {
                cartForm.requestSubmit();
            }, 400);
        });
    }

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
