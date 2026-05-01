<div id="shop-toast-container" class="shop-toast-container position-fixed top-0 end-0 p-3" aria-live="polite" aria-atomic="true"></div>
<style>
    .shop-toast-container {
        z-index: 11050;
        pointer-events: none;
        max-width: min(420px, calc(100vw - 1.5rem));
    }
    .shop-toast-container .toast {
        pointer-events: auto;
        box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.15);
    }
    .shop-toast-success {
        background-color: #6A1B1B;
        color: #fff;
    }
    .shop-toast-success .btn-close {
        filter: invert(1) grayscale(100%);
    }
    .shop-toast-error {
        background-color: #b02a37;
        color: #fff;
    }
    .shop-toast-error .btn-close {
        filter: invert(1) grayscale(100%);
    }
    .shop-toast-warning {
        background-color: #e0a800;
        color: #1a1a1a;
    }
</style>
<script>
(function () {
    function shopToastEscapeHtml(text) {
        if (text == null) return '';
        const div = document.createElement('div');
        div.textContent = String(text);
        return div.innerHTML;
    }

    window.showShopToast = function (message, type) {
        type = type || 'success';
        const container = document.getElementById('shop-toast-container');
        if (!container || typeof bootstrap === 'undefined' || !bootstrap.Toast) {
            window.alert(message);
            return;
        }
        const variantClass = type === 'error'
            ? 'shop-toast-error'
            : (type === 'warning' ? 'shop-toast-warning' : 'shop-toast-success');
        const el = document.createElement('div');
        el.className = 'toast align-items-center border-0 ' + variantClass;
        el.setAttribute('role', 'alert');
        el.innerHTML =
            '<div class="d-flex w-100">' +
            '<div class="toast-body">' + shopToastEscapeHtml(message) + '</div>' +
            '<button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>' +
            '</div>';
        container.appendChild(el);
        const toast = new bootstrap.Toast(el, { autohide: true, delay: 4500 });
        el.addEventListener('hidden.bs.toast', function () {
            el.remove();
        });
        toast.show();
    };
})();
</script>
