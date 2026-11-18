document.addEventListener('DOMContentLoaded', () => {
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
