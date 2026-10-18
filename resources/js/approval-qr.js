import QRCode from 'qrcode';

document.querySelectorAll('.approval-qr').forEach(async element => {
    try {
        await QRCode.toCanvas(element, element.dataset.value, {
            width: 180, margin: 4, errorCorrectionLevel: 'M',
        });
    } catch (error) {
        element.replaceWith(document.createTextNode('QR tidak dapat ditampilkan. Gunakan tautan bukti persetujuan di bawah.'));
        console.error('Gagal membuat QR persetujuan', error);
    }
});
