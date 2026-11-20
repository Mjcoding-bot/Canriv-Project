document.addEventListener('DOMContentLoaded', () => {
    const timeline = document.getElementById('timeline-history');
    if (!timeline) return;

    const buttons = timeline.querySelectorAll('.timeline-nav-btn');
    const cards = timeline.querySelectorAll('.timeline-item');
    const activeClasses = ['bg-primary', 'text-on-primary', 'font-semibold', 'shadow-sm'];
    const inactiveClasses = ['text-on-surface-variant', 'hover:text-on-surface'];

    for (const button of buttons) {
        button.addEventListener('click', () => {
            for (const tab of buttons) {
                const active = tab === button;
                tab.setAttribute('aria-pressed', String(active));
                activeClasses.forEach(name => tab.classList.toggle(name, active));
                inactiveClasses.forEach(name => tab.classList.toggle(name, !active));
            }
            for (const card of cards) {
                card.hidden = button.dataset.target !== 'all' && !card.classList.contains(button.dataset.target);
            }
        });
    }
});
