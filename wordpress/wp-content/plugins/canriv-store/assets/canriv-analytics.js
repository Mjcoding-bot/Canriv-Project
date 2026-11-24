document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('equipment-table');
    if (!table) return;
    const body = table.tBodies[0];
    const rows = Array.from(body.querySelectorAll('[data-equipment-row]'));
    const search = document.getElementById('canriv-equipment-search');
    const count = document.getElementById('canriv-equipment-count');
    const pageLabel = document.getElementById('canriv-equipment-page');
    const previous = document.getElementById('canriv-equipment-prev');
    const next = document.getElementById('canriv-equipment-next');
    const exportButton = document.getElementById('canriv-equipment-export');
    const pageSize = 6;
    let page = 1;

    function filteredRows() {
        const query = search.value.trim().toLowerCase();
        return rows.filter((row) => `${row.dataset.model} ${row.dataset.sku}`.includes(query));
    }

    function render() {
        const matches = filteredRows();
        const pages = Math.max(1, Math.ceil(matches.length / pageSize));
        page = Math.min(page, pages);
        const start = (page - 1) * pageSize;
        const visible = new Set(matches.slice(start, start + pageSize));
        for (const row of rows) row.hidden = !visible.has(row);
        let empty = body.querySelector('[data-equipment-empty]');
        if (!matches.length && !empty) {
            empty = document.createElement('tr');
            empty.dataset.equipmentEmpty = '';
            empty.innerHTML = '<td colspan="7" class="p-space-md">No shop products match your search.</td>';
            body.append(empty);
        } else if (matches.length && empty) empty.remove();
        count.textContent = `Showing ${matches.length ? start + 1 : 0}–${Math.min(start + pageSize, matches.length)} of ${matches.length} shop products`;
        pageLabel.textContent = `${page} / ${pages}`;
        previous.disabled = page <= 1;
        next.disabled = page >= pages;
    }

    search.addEventListener('input', () => { page = 1; render(); });
    previous.addEventListener('click', () => { page -= 1; render(); });
    next.addEventListener('click', () => { page += 1; render(); });
    exportButton.addEventListener('click', () => {
        const records = filteredRows().map((row) => [
            row.querySelector('.canriv-equipment-name').textContent.trim(), row.dataset.sku,
            row.querySelector('.canriv-equipment-category').textContent.trim(), row.dataset.views,
            row.dataset.cartRate, row.dataset.units, row.dataset.revenue,
        ]);
        const csv = [['Equipment / Model', 'SKU', 'Category', 'Page Views', 'Add to Cart Rate (%)', 'Units Sold', 'Revenue (PHP)'], ...records]
            .map((record) => record.map((value) => `"${String(value).replaceAll('"', '""')}"`).join(',')).join('\r\n');
        const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        const link = document.createElement('a');
        link.href = url;
        link.download = 'canriv-equipment-performance.csv';
        link.click();
        URL.revokeObjectURL(url);
    });
    render();
});
