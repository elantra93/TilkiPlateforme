document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.tbl-row-link').forEach(function (row) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function () {
            window.location.href = this.dataset.href;
        });
    });
});
