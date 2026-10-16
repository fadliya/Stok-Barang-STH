# Pemesanan supplier dan penerimaan barcode

Jalankan `php artisan migrate` dan `npm run build` setelah memperbarui aplikasi.
Migrasi menambahkan status dan bukti persetujuan; pesanan lama menjadi menunggu
ACC, tanpa mengubah stok atau menganggap tanda tangan lama sudah disetujui.

1. Admin atau petugas gudang membuka **Pemesanan Supplier**, memilih supplier,
   mencentang barang, mengisi jumlah dan menekan **Ajukan ke Pimpinan**.
2. Pimpinan membuka menu yang sama, memeriksa detail, lalu menekan **ACC Pemesanan**.
   Pengajuan dan ACC tidak menambah stok. Pemohon tidak dapat menyetujui sendiri.
3. Setelah ACC, **Cetak Struk / Simpan PDF** tersedia. Struk memuat rincian barang,
   nama pemohon, nama pimpinan, waktu persetujuan dan barcode CODE128.
   Nama dan waktu merupakan bukti persetujuan sistem; tanda tangan manual tidak diperlukan.
4. Setelah SELURUH barang tiba sesuai jumlah pesanan, admin/gudang membuka
   **Scan penerimaan**. Scan barcode struk dengan scanner yang mengirim Enter,
   atau ketik nomor pesanan dan tekan **Terima Seluruh Barang**.
5. Sistem mengambil seluruh rincian pesanan, membuat transaksi Barang Masuk,
   menambah stok barang yang sudah terdaftar, lalu menampilkan struk penerimaan.

Barcode berisi nomor pesanan unik yang mengacu ke rincian tersimpan di server,
sehingga diperlukan koneksi ke aplikasi dan login admin/gudang. Barcode bukan
salinan seluruh data barang yang bisa dibaca secara offline. Scan tidak memakai
kamera pada halaman ini. Penerimaan sebagian belum didukung.

Pesanan belum ACC, nomor tidak dikenal, dan pesanan yang sudah diterima ditolak.
Penerimaan berlangsung dalam satu transaksi database dengan penguncian pesanan
untuk mencegah stok bertambah dua kali. Transaksi penerimaan yang terhubung tidak
dapat dihapus; koreksi stok melalui Stock Opname agar jejak penerimaan tetap ada.
Nama supplier, alamat, pemohon, kode/nama barang dan satuan disalin saat pengajuan
agar cetak ulang tetap stabil. Fitur ini tidak mengirim email dan tidak mencatat harga.
