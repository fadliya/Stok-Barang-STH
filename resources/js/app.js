import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
Alpine.plugin(focus);
window.Swal = Swal;

window.confirmDelete = async (formId, message = 'Apakah Anda yakin ingin menghapus data ini?') => {
    const result = await Swal.fire({
        title: 'Hapus data?', text: message, icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#be123c', cancelButtonColor: '#475569',
        confirmButtonText: 'Hapus data', cancelButtonText: 'Batal', focusCancel: true,
    });
    if (result.isConfirmed) document.getElementById(formId)?.requestSubmit();
};

// Page-specific dependencies are loaded before DOMContentLoaded handlers run.
if (document.querySelector('canvas[data-chart], #incomingChartCanvas')) {
    const { default: Chart } = await import('chart.js/auto');
    window.Chart = Chart;
}
if (document.querySelector('#barcode-canvas, .barcode-svg')) {
    const { default: JsBarcode } = await import('jsbarcode');
    window.JsBarcode = JsBarcode;
}
window.loadBarcodeCamera = async () => {
    const module = await import('html5-qrcode');
    return module.Html5Qrcode ?? module.default?.Html5Qrcode;
};

// Ensure every form field has a stable accessible name, including dynamic POS rows.
function labelControls(root = document) {
    root.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach((control) => {
        if (control.labels?.length || control.hasAttribute('aria-label')) return;
        const label = control.parentElement?.querySelector('label') ?? control.parentElement?.parentElement?.querySelector('label');
        const name = label?.textContent.trim() || control.getAttribute('placeholder') || control.name || 'Isian formulir';
        control.setAttribute('aria-label', name);
    });
    root.querySelectorAll('button, a').forEach(control => {
        if (!control.textContent.trim() && !control.hasAttribute('aria-label')) {
            const action = control.getAttribute('@click') || '';
            control.setAttribute('aria-label', /remove|delete/i.test(action) ? 'Hapus baris barang' : /close|false|null/i.test(action) ? 'Tutup panel' : 'Buka tindakan');
        }
    });
}
labelControls();
document.getElementById('validation-errors')?.focus();
new MutationObserver(() => labelControls()).observe(document.body, { childList: true, subtree: true });

document.querySelectorAll('form[method="POST"]').forEach(form => {
    form.addEventListener('submit', () => {
        if (!form.checkValidity()) return;
        form.setAttribute('aria-busy', 'true');
        form.querySelectorAll('button[type="submit"]').forEach(button => {
            button.disabled = true;
            button.dataset.originalText = button.textContent;
            button.textContent = 'Menyimpan…';
        });
    });
});
window.addEventListener('pageshow', () => {
    document.querySelectorAll('button[data-original-text]').forEach(button => {
        button.disabled = false;
        button.textContent = button.dataset.originalText;
        delete button.dataset.originalText;
    });
    document.querySelectorAll('form[aria-busy]').forEach(form => form.removeAttribute('aria-busy'));
});
Alpine.start();
const paper = document.getElementById('receipt-paper');
if (paper) {
    const style = document.createElement('style');
    document.head.append(style);
    const updatePaper = () => {
        const width = ['58', '80'].includes(paper.value) ? paper.value : 'a4';
        document.body.dataset.paper = width;
        style.textContent = width === 'a4' ? '' : `@media print { @page { size: auto; margin: 0; } body:has(.receipt-page) { width: ${width}mm; margin: 0; } }`;
    };
    paper.addEventListener('change', updatePaper);
    updatePaper();
    document.getElementById('print-receipt').addEventListener('click', () => window.print());
}
document.dispatchEvent(new Event('app:ready'));
