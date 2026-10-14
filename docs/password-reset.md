# Lupa password

Pengguna memilih **Lupa password?** pada halaman login, memasukkan email akun,
lalu membuka tautan email untuk membuat password baru minimal 8 karakter.
Tautan berlaku 60 menit dan hanya dapat dipakai sekali. Akun yang belum memiliki
email perlu diperbarui melalui menu pengguna oleh admin.

## Pengiriman email

Atur `.env` menggunakan konfigurasi penyedia email:

```dotenv
APP_URL=https://alamat-aplikasi-anda.example
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.penyedia-anda.example
MAIL_PORT=587
MAIL_USERNAME=akun-smtp
MAIL_PASSWORD=password-smtp
MAIL_FROM_ADDRESS=noreply@domain-anda.example
MAIL_FROM_NAME="Sistem Stok Barang"
```

Sesuaikan port dan skema dengan penyedia SMTP (`smtps` untuk TLS implisit,
umumnya port 465). Jalankan `php artisan config:clear` setelah perubahan.
Pastikan migrasi awal sudah dijalankan karena fitur memakai tabel
`password_reset_tokens` yang sudah ada.

Default `MAIL_MAILER=log` tidak mengirim email ke inbox. Untuk pengembangan lokal,
email dan tautannya dapat dilihat di `storage/logs/laravel.log`.
Jangan membagikan log yang berisi tautan reset aktif.

Permintaan email dibatasi 5 kali per menit per IP, pembuatan token dibatasi
sekali per 60 detik per akun, dan percobaan reset dibatasi 10 kali per menit per IP.
Pesan permintaan sama untuk email terdaftar dan tidak terdaftar.
