document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('registration-result')?.focus();

    const phone = document.getElementById('mobile');
    if (phone) {
        const validatePhone = () => {
            const number = phone.value.replace(/[\s()-]+/g, '');
            phone.setCustomValidity(number && !/^(?:\+?63|0)?9\d{9}$/.test(number)
                ? 'Enter all 10 digits starting with 9, or use 0917 123 4567 or +63 917 123 4567.'
                : '');
        };
        phone.addEventListener('input', validatePhone);
        phone.addEventListener('blur', validatePhone);
        phone.form.addEventListener('submit', (event) => {
            validatePhone();
            if (!phone.reportValidity()) event.preventDefault();
        });
    }

    const password = document.getElementById('password');
    const confirm = document.getElementById('confirmPassword');
    const meter = document.getElementById('pwd-meter-bar');
    const status = document.getElementById('pwd-strength-text');
    const showPassword = document.getElementById('canriv-show-registration-password');
    if (!password || !confirm || !meter || !status || !showPassword) return;

    showPassword.addEventListener('change', () => {
        password.type = confirm.type = showPassword.checked ? 'text' : 'password';
    });

    function update() {
        confirm.setCustomValidity(confirm.value === password.value ? '' : 'Passwords must match.');
        const length = Array.from(password.value).length;
        meter.style.width = Math.min(length / password.minLength, 1) * 100 + '%';
        status.textContent = length >= password.minLength ? 'Minimum length met' : 'Use at least ' + password.minLength + ' characters';
    }
    password.addEventListener('input', update);
    confirm.addEventListener('input', update);
    update();
});
