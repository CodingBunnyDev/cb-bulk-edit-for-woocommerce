document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.main-product').forEach(function (row) {
        row.addEventListener('click', function () {
            const productId = this.getAttribute('data-product-id');
            const variations = document.querySelectorAll('.variation[data-parent-id="' + productId + '"]');
            variations.forEach(function (variation) {
                variation.style.display = variation.style.display === 'none' ? '' : 'none';
            });
        });
    });

    document.getElementById('select-all').addEventListener('change', function (e) {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
    });
});