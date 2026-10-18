const input = document.getElementById('photo');
const status = document.getElementById('photo-status');
const form = document.getElementById('informasi-akun');
const remove = form?.querySelector('[name="remove_photo"]');
const previews = document.querySelectorAll('[data-profile-photo]');
let previewUrl;

function resetPreview() {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = undefined;
    previews.forEach(img => { img.src = img.dataset.profilePhoto; });
}

input?.addEventListener('change', async () => {
    resetPreview();
    input.setCustomValidity('');
    status.textContent = '';
    const file = input.files?.[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        input.setCustomValidity('Pilih foto JPG, PNG, atau WebP.');
    } else if (file.size > 512 * 1024) {
        input.setCustomValidity('Foto terlalu besar. Ukuran maksimal 512 KB.');
    }
    if (!input.validity.valid) {
        status.textContent = input.validationMessage;
        input.reportValidity();
        return;
    }
    previewUrl = URL.createObjectURL(file);
    const image = new Image();
    const candidateUrl = previewUrl;
    image.onload = () => {
        if (candidateUrl !== previewUrl) return;
        if (image.width > 2048 || image.height > 2048) {
            input.setCustomValidity('Dimensi foto maksimal 2048 × 2048 piksel.');
            status.textContent = input.validationMessage;
            resetPreview();
            input.reportValidity();
            return;
        }
        previews.forEach(img => { img.src = candidateUrl; });
        if (remove) remove.checked = false;
        status.textContent = `${file.name} dipilih. Klik Simpan Profil di bawah untuk mengunggah foto.`;
    };
    image.onerror = () => {
        if (candidateUrl !== previewUrl) return;
        input.setCustomValidity('Foto tidak dapat dibaca. Coba file JPG atau PNG lain.');
        status.textContent = input.validationMessage;
        resetPreview();
        input.reportValidity();
    };
    image.src = candidateUrl;
});

remove?.addEventListener('change', () => {
    if (!remove.checked) { status.textContent = ''; return; }
    resetPreview();
    input.value = '';
    input.setCustomValidity('');
    status.textContent = 'Foto akan dihapus setelah Anda klik Simpan Profil.';
});

form?.addEventListener('invalid', event => {
    status.textContent = `Belum dapat disimpan: ${event.target.validationMessage}`;
}, true);
