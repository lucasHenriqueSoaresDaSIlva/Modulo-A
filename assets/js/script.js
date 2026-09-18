
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-confirmar]').forEach(function (el) {
        el.addEventListener('click', function (ev) {
            if (!confirm(el.dataset.confirmar)) {
                ev.preventDefault();
            }
        });
    });
});