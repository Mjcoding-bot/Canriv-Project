document.addEventListener('DOMContentLoaded', () => {
    const showcase = document.getElementById('product-showcase');
    if (!showcase) return;

    const filters = showcase.querySelectorAll('[data-canriv-equipment-filter]');
    const cards = showcase.querySelectorAll('[data-canriv-equipment]');
    for (const filter of filters) {
        filter.addEventListener('click', () => {
            const kind = filter.dataset.canrivEquipmentFilter;
            for (const tab of filters) {
                tab.setAttribute('aria-pressed', String(tab === filter));
            }
            for (const card of cards) {
                card.hidden = kind !== 'all' && card.dataset.canrivEquipment !== kind;
            }
        });
    }
});
