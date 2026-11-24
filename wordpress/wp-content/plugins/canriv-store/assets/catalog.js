document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('catalog-product-grid');
    if (!grid) return;
    const cards = Array.from(grid.querySelectorAll('[data-canriv-product]'));
    const search = document.getElementById('canriv-search');
    const sort = document.getElementById('catalog-sort');
    const price = document.getElementById('catalog-price');
    const brandFilters = document.getElementById('catalog-brand-filters');
    const brandCounts = new Map();
    for (const card of cards) {
        const brand = card.dataset.brand;
        if (brand) brandCounts.set(brand, (brandCounts.get(brand) ?? 0) + 1);
    }
    if (brandFilters) {
        for (const [brand, count] of [...brandCounts].sort(([a], [b]) => a.localeCompare(b))) {
            const label = document.createElement('label');
            label.className = 'flex items-center justify-between cursor-pointer group';
            const name = document.createElement('span');
            name.className = 'flex items-center gap-2';
            const input = document.createElement('input');
            input.className = 'accent-primary w-4 h-4 rounded cursor-pointer';
            input.type = 'checkbox';
            input.dataset.filter = 'brand';
            input.value = brand;
            const text = document.createElement('span');
            text.className = 'group-hover:text-primary';
            text.textContent = brand.charAt(0).toUpperCase() + brand.slice(1);
            const total = document.createElement('span');
            total.className = 'font-label-code text-body-sm text-outline';
            total.textContent = count;
            name.append(input, text);
            label.append(name, total);
            brandFilters.append(label);
        }
    }
    const priceValues = cards.map((card) => Number(card.dataset.price)).filter((value) => Number.isFinite(value) && value > 0);
    if (priceValues.length) {
        const minPrice = Math.min(...priceValues);
        const maxPrice = Math.max(...priceValues);
        price.min = minPrice;
        price.max = maxPrice;
        price.value = maxPrice;
        for (const label of document.querySelectorAll('[data-price-min]')) {
            label.textContent = '₱' + minPrice.toLocaleString('en-PH');
        }
    }
    const filters = Array.from(document.querySelectorAll('[data-filter]'));
    const chips = document.getElementById('catalog-filter-chips');
    let sortChanged = false;

    function update() {
        const selected = {};
        for (const input of filters.filter((input) => input.checked)) {
            (selected[input.dataset.filter] ??= []).push(input.value);
        }
        const term = search.value.trim().toLowerCase();
        for (const card of cards) {
            const matches = Object.entries(selected).every(([group, values]) =>
                values.some((value) => (card.dataset[group] ?? '').split(' ').includes(value)));
            const outsideBudget = card.dataset.price === undefined
                ? Number(price.value) < Number(price.max)
                : Number(card.dataset.price) > Number(price.value);
            card.hidden = !matches || outsideBudget ||
                !card.textContent.toLowerCase().includes(term);
        }
        const sorters = {
            'price-asc': (a, b) => Number(a.dataset.price) - Number(b.dataset.price),
            'price-desc': (a, b) => Number(b.dataset.price) - Number(a.dataset.price),
            'cspf-desc': (a, b) => Number(b.dataset.cspf) - Number(a.dataset.cspf),
            popularity: (a, b) => Number(b.dataset.popularity) - Number(a.dataset.popularity),
            'stock-desc': (a, b) => Number(b.dataset.stock) - Number(a.dataset.stock),
        };
        if (sortChanged) {
            const compare = sorters[sort.value];
            cards.sort((a, b) => {
                const quoteOnlyA = a.dataset.price === undefined;
                const quoteOnlyB = b.dataset.price === undefined;
                if (quoteOnlyA || quoteOnlyB) return Number(quoteOnlyA) - Number(quoteOnlyB);
                return compare(a, b);
            });
        }
        for (const card of cards) grid.append(card);
        const count = cards.filter((card) => !card.hidden).length;
        document.getElementById('catalog-results-count').textContent = `${count} of ${cards.length}`;
        document.getElementById('catalog-page-summary').textContent = `Showing ${count ? '1 – ' + count : '0'} of ${cards.length} products`;
        document.getElementById('canriv-empty').hidden = count !== 0;
        for (const label of document.querySelectorAll('[data-price-limit]')) {
            label.textContent = '₱' + Number(price.value).toLocaleString('en-PH');
        }
        chips.replaceChildren();
        for (const input of filters.filter((input) => input.checked)) {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'canriv-filter-chip';
            const label = input.closest('label').textContent.trim().replace(/\s+/g, ' ');
            chip.textContent = label + ' ×';
            chip.setAttribute('aria-label', 'Remove filter ' + label);
            chip.addEventListener('click', () => { input.checked = false; update(); });
            chips.append(chip);
        }
        if (!chips.children.length) chips.textContent = 'All equipment';
    }

    search.addEventListener('input', update);
    sort.addEventListener('change', () => { sortChanged = true; update(); });
    price.addEventListener('input', update);
    for (const filter of filters) filter.addEventListener('change', update);
    for (const reset of document.querySelectorAll('[data-catalog-reset]')) {
        reset.addEventListener('click', () => {
            for (const input of filters) input.checked = false;
            search.value = '';
            price.value = price.max;
            update();
        });
    }
    for (const button of document.querySelectorAll('[data-catalog-view]')) {
        button.addEventListener('click', () => {
            grid.classList.toggle('canriv-catalog-list', button.dataset.catalogView === 'list');
            for (const toggle of document.querySelectorAll('[data-catalog-view]')) {
                toggle.setAttribute('aria-pressed', String(toggle === button));
            }
        });
    }
    const toggle = document.getElementById('catalog-filter-toggle');
    toggle.addEventListener('click', () => {
        const expanded = toggle.getAttribute('aria-expanded') !== 'true';
        toggle.setAttribute('aria-expanded', String(expanded));
        document.getElementById('canriv-catalog-filters').classList.toggle('canriv-filters-open', expanded);
    });
    update();
});
