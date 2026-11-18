document.addEventListener('DOMContentLoaded', () => {
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirmPassword');
    const meter = document.getElementById('pwd-meter-bar');
    const status = document.getElementById('pwd-strength-text');
    function update() {
        confirm.setCustomValidity(confirm.value === password.value ? '' : 'Passwords must match.');
        meter.style.width = Math.min(password.value.length / 12, 1) * 100 + '%';
        status.textContent = password.value.length >= 12 ? 'Minimum met' : password.value ? 'Use at least 12 characters' : 'Empty';
    }
    password.addEventListener('input', update);
    confirm.addEventListener('input', update);
    update();
});
