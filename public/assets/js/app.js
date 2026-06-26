document.addEventListener('DOMContentLoaded', function () {
    // Clickable table rows
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

    // Clipboard copy buttons ([data-copy-target="id"])
    document.querySelectorAll('[data-copy-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(this.dataset.copyTarget);
            if (!target) return;
            navigator.clipboard.writeText(target.value).then(function () {
                var icon = btn.querySelector('i');
                if (icon) { icon.className = 'bi bi-check'; setTimeout(function () { icon.className = 'bi bi-clipboard'; }, 1500); }
            });
        });
    });

    // Confirm before form submit ([data-confirm="message"])
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm(this.dataset.confirm)) e.preventDefault();
        });
    });

    // Particulier / Entreprise toggle ([data-account-type-toggle] radios → [data-enterprise-section])
    (function () {
        var radios  = document.querySelectorAll('[data-account-type-toggle]');
        var section = document.querySelector('[data-enterprise-section]');
        if (!radios.length || !section) return;

        function applyToggle(value) {
            section.classList.toggle('d-none', value !== 'entreprise');
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () { applyToggle(this.value); });
        });

        var checked = document.querySelector('[data-account-type-toggle]:checked');
        if (checked) applyToggle(checked.value);
    }());

    // Admin sidebar toggle
    var toggle  = document.getElementById('tkToggle');
    var sidebar = document.getElementById('tkSidebar');
    var overlay = document.getElementById('tkOverlay');

    if (toggle && sidebar && overlay) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('is-open');
            overlay.classList.toggle('is-open');
        });
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('is-open');
            overlay.classList.remove('is-open');
        });
    }
});
