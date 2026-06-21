document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('tr.tbl-row-link[data-href]').forEach(function (row) {
        row.style.cursor = 'pointer';
        row.setAttribute('tabindex', '0');

        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button, input, select, textarea, label, [data-bs-toggle], form')) return;
            window.location.href = this.dataset.href;
        });

        row.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                window.location.href = this.dataset.href;
            }
        });
    });
});
