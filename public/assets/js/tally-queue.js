document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#statusFilter .nav-link').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('#statusFilter .nav-link').forEach(function (l) {
                l.classList.remove('active');
            });
            this.classList.add('active');
            var filter = this.dataset.filter;
            document.querySelectorAll('.entry-card').forEach(function (card) {
                card.style.display = (filter === 'all' || card.dataset.status === filter) ? '' : 'none';
            });
        });
    });
});
