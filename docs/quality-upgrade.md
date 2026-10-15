# Pembaruan kualitas inventaris

## Perilaku utama

- Registrasi cepat hanya untuk admin dan gudang; pimpinan tetap hanya membaca.
- Pencarian inventaris dilakukan di database sebelum pagination, meliputi nama, kode, dan barcode. Filter kategori serta status stok disimpan di URL.
- Pencarian riwayat masuk/keluar meliputi seluruh transaksi, termasuk supplier/tujuan dan nama petugas.
- Transaksi manual dan POS membuka bukti penerimaan/pengeluaran setelah berhasil disimpan. Struk dapat dibuka kembali dari riwayat dan aktivitas dashboard.
- Stok awal saat menambah barang dicatat sebagai penerimaan dan memiliki struk. Registrasi cepat dengan stok awal mengembalikan tautan struk penerimaan awal.
- Form edit barang hanya mengubah master data. Perubahan stok dilakukan melalui transaksi atau stok opname agar tercatat.
- Validasi transaksi menolak tanggal, referensi barang, dan jumlah yang tidak valid tanpa mengubah persediaan.

## Desain dan aksesibilitas

Dashboard memprioritaskan stok kritis dan aktivitas terakhir. Semua halaman yang memakai layout aplikasi dan login menggunakan font lokal, ukuran teks yang lebih terbaca, palet kontras, sudut serta bayangan yang konsisten, dan fokus keyboard. Navigasi mobile serta modal mendukung Escape dan pembatasan fokus. Kesalahan validasi ditampilkan dalam ringkasan yang mendapat fokus.

Struk memuat nomor, tanggal, waktu pencatatan, petugas, supplier/tujuan, rincian barang, jumlah per satuan, catatan, dan area tanda tangan. Pilihan cetak: A4/PDF, thermal 80 mm, dan thermal 58 mm. Total tidak menjumlahkan satuan berbeda menjadi satu angka. Pilih ukuran media yang sesuai pada dialog printer; pengujian printer fisik tetap diperlukan.

## Aset dan pemasangan

CSS, JavaScript, ikon, dan font dibangun lokal melalui Vite. Grafik, barcode, dan kamera dimuat sesuai kebutuhan; tidak menggunakan runtime CDN. Versi dependensi runtime dikunci di package.json dan package-lock.json.

Setelah mengambil perubahan pada lingkungan lain:

```sh
npm ci
npm run build
php artisan view:clear
php artisan test
```

Sertakan hasil `public/build` pada deployment, atau bangun aset di pipeline deployment. Folder tersebut tetap mengikuti aturan .gitignore proyek. Pastikan APP_KEY lingkungan deployment telah dikonfigurasi; jangan mengganti kunci lingkungan yang sudah digunakan. phpunit.xml memiliki kunci khusus pengujian dan database SQLite dalam memori.

## Verifikasi

Hasil pemeriksaan 15 Oktober 2026: 36 tes backend lulus dengan 317 assertion; build produksi dan Pint lulus; npm audit melaporkan 0 kerentanan. Pemeriksaan browser mencakup 29 rute aplikasi dan 3 halaman autentikasi, empat alur transaksi nyata pada database QA, serta pemeriksaan mode gelap. Temuan kontras diperbaiki dan diperiksa kembali pada halaman terkait. Tidak ditemukan error JavaScript atau request aset eksternal pada pemeriksaan halaman aplikasi.

Pengujian fitur mencakup empat jalur transaksi (manual/POS × masuk/keluar), struk, akses peran, pencarian lintas halaman, karakter tanda petik, escaping catatan, referensi tidak valid, jumlah pecahan/negatif/terlalu besar, stok awal, dan larangan mengubah stok lewat master data.

Pemeriksaan browser dilakukan dengan Edge dalam profil terisolasi dan database QA terpisah. Halaman inventaris, transaksi, laporan, master data, barcode, dan struk diperiksa untuk status HTTP, error JavaScript, request eksternal, serta aksesibilitas otomatis. Dashboard, inventaris, POS, dan struk diperiksa pada lebar 320, 768, 1024, dan 1440 px. Hasil cetak A4 dan thermal juga dibuat sebagai PDF. Pemeriksaan otomatis tidak menggantikan pengujian pembaca layar, kamera fisik, printer fisik, beban produksi, dan transaksi serentak pada database produksi.
