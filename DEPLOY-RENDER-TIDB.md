# Hosting Render + TiDB Cloud

Konfigurasi tersedia di `Dockerfile` dan `render.yaml`. Aplikasi menggunakan Apache/PHP 8.4, aset Vite dibangun saat deployment, dan TiDB melalui koneksi MySQL dengan TLS dan verifikasi sertifikat.

## 1. Siapkan TiDB

1. Masuk ke https://tidbcloud.com dan buat cluster Starter, pilih wilayah terdekat dengan Singapore jika tersedia.
2. Buat database kosong bernama `stockbarang` melalui SQL Editor: `CREATE DATABASE stockbarang;`.
3. Buka **Connect**, pilih koneksi MySQL, lalu catat host, port, username lengkap (termasuk prefix), dan password.
4. Jika cluster memakai pembatasan IP, izinkan outbound IP layanan Render dari halaman layanan Render. Ikuti pengaturan jaringan yang tersedia pada jenis cluster Anda.

Gunakan database baru untuk deployment ini. Migrasi akan membuat struktur tabel, bukan menyalin data dari database lokal.

## 2. Siapkan repository

Unggah perubahan aplikasi termasuk file deployment ke repository GitHub `wanazmierpan2006-cloud/stockbarang`. Jangan unggah `.env`, password, vendor, atau node_modules.

## 3. Buat layanan Render

1. Masuk ke https://dashboard.render.com dan hubungkan GitHub yang memiliki akses repository.
2. Pilih **New → Blueprint**, pilih repository dan branch berisi perubahan terbaru. Render membaca `render.yaml`.
3. Isi variabel yang diminta:

| Variabel | Nilai |
| --- | --- |
| `APP_KEY` | Nilai APP_KEY lokal jika memindahkan aplikasi yang sama, atau key Laravel baru untuk instalasi baru. Jangan menggantinya setiap deploy. |
| `APP_URL` | URL HTTPS layanan Render, misalnya `https://stockbarang-xxxx.onrender.com`; sesuaikan dengan URL yang benar-benar diberikan Render. |
| `DB_HOST` | Host dari TiDB Connect, tanpa `https://`. |
| `DB_PORT` | Port TiDB Connect; blueprint memakai 4000. |
| `DB_DATABASE` | `stockbarang` atau nama database yang dibuat. |
| `DB_USERNAME` | Username lengkap dari TiDB Connect. |
| `DB_PASSWORD` | Password TiDB, isi langsung di Environment Render. |
| `INITIAL_ADMIN_EMAIL` | Email pemilik akun admin. |
| `INITIAL_ADMIN_PASSWORD` | Password admin awal, minimal 12 karakter. |

Jika perlu key baru, jalankan `php artisan key:generate --show` secara lokal lalu salin hasilnya ke Render. Jangan menjalankan `key:generate` setiap aplikasi mulai.

Alternatif tanpa Blueprint: **New → Web Service**, pilih repo, runtime **Docker**, Dockerfile `./Dockerfile`, region Singapore, paket Free, health check `/up`, dan salin semua Environment dari `render.yaml`. Build Command dan Start Command tidak perlu diisi karena Dockerfile mengaturnya.

## 4. Setelah deploy

- Periksa Logs: migrasi sukses, lalu Apache mulai. Jika migrasi gagal, perbaiki koneksi/database sebelum deploy ulang. Jangan gunakan `migrate:fresh` pada database berisi data.
- Pastikan `APP_URL` sama dengan URL HTTPS layanan. Setelah mengubah Environment, deploy ulang.
- Login dengan username `admin` dan password awal. Buat akun gudang/pimpinan melalui menu pengguna.
- Hapus `INITIAL_ADMIN_PASSWORD` dari Environment setelah admin berhasil dibuat, lalu deploy ulang. Akun lama tidak diubah saat restart.
- Uji login, pengajuan → ACC → pending → konfirmasi penerimaan, serta scan QR melalui HP. QR harus menggunakan URL publik Render, bukan localhost.
- Data bisnis, session, dan cache disimpan di TiDB. File lokal Render bersifat sementara; aplikasi ini belum dikonfigurasi untuk menyimpan unggahan secara permanen.
- Email default masih `log`, sehingga belum mengirim reset password ke inbox. Untuk pengiriman, atur `MAIL_MAILER=brevo`, `BREVO_API_KEY`, `MAIL_FROM_ADDRESS` yang terverifikasi, dan `MAIL_FROM_NAME` di Render.
- Layanan Free dapat tidur ketika tidak aktif; akses pertama bisa lebih lambat. Blueprint tidak membuat database Render karena database menggunakan TiDB.

Seeder contoh tidak dijalankan otomatis. Jika perlu data lokal lama, rencanakan ekspor/impor terpisah setelah koneksi berhasil; jangan menimpa database hosting tanpa memeriksa isinya.

## Validasi dan keterbatasan

Jalankan `php vendor/phpunit/phpunit/phpunit`, `npm run build`, lalu `docker build -t stockbarang .` jika Docker tersedia. Build Docker dan koneksi TiDB sebenarnya harus diverifikasi di Render bila Docker tidak tersedia secara lokal.

Referensi: https://render.com/docs/docker, https://render.com/docs/blueprint-spec, https://render.com/docs/free, https://docs.pingcap.com/tidbcloud/secure-connections-to-serverless-clusters/.
