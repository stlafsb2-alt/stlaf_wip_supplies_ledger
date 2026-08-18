/**
 * Renders a bounded pagination control (e.g. « 1 … 8 9 [10] 11 12 … 36 »)
 * instead of one button per page, which overflows once totalPages gets large.
 *
 * @param {HTMLElement} containerEl - the <ul class="pagination"> element to fill
 * @param {number} page - current page (1-indexed)
 * @param {number} totalPages
 * @param {function(number)} onPageClick - called with the target page number
 */
function renderPagination(containerEl, page, totalPages, onPageClick) {
    containerEl.innerHTML = '';
    if (totalPages <= 1) return;

    function addItem(label, targetPage, opts = {}) {
        const li = document.createElement('li');
        li.className = 'page-item' + (opts.active ? ' active' : '') + (opts.disabled ? ' disabled' : '');
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = label;
        if (!opts.disabled && !opts.active) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                onPageClick(targetPage);
            });
        }
        li.appendChild(a);
        containerEl.appendChild(li);
    }

    function addEllipsis() {
        const li = document.createElement('li');
        li.className = 'page-item disabled';
        li.innerHTML = '<span class="page-link">&hellip;</span>';
        containerEl.appendChild(li);
    }

    addItem('\u00AB', page - 1, { disabled: page === 1 });

    // Always show first page, last page, current page, and one neighbor on each side.
    const keep = new Set([1, totalPages, page - 1, page, page + 1]);
    const pages = [...keep].filter(p => p >= 1 && p <= totalPages).sort((a, b) => a - b);

    let prev = 0;
    pages.forEach(p => {
        if (p - prev > 1) addEllipsis();
        addItem(p, p, { active: p === page });
        prev = p;
    });

    addItem('\u00BB', page + 1, { disabled: page === totalPages });
}
