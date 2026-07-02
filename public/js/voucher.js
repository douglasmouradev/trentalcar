document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('voucher-print');
    if (btn) {
        btn.addEventListener('click', function () {
            window.print();
        });
    }
});
