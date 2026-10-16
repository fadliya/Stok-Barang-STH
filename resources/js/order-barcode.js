import JsBarcode from 'jsbarcode';

document.querySelectorAll('.barcode-svg').forEach(element => {
    JsBarcode(element, element.dataset.value, { format: 'CODE128', width: 1.5, height: 70, displayValue: true });
});
