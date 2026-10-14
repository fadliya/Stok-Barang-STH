# Surat pemesanan supplier

Jalankan `php artisan migrate` untuk membuat tabel `goods_requests` dan
`goods_request_details` sebelum membuka menu baru. Migrasi tidak mengubah stok.

1. Buka **Pemesanan Supplier** di sidebar atau **Barang Masuk → Permintaan / Pemesanan**.
2. Pilih supplier dan klik **Tampilkan Barang**. Semua barang milik supplier itu
   tampil, termasuk barang yang stoknya masih cukup. Tidak ada pilihan otomatis.
3. Ceklis barang yang diperlukan dan isi jumlah pesanan dalam satuan barang.
   Gunakan pencarian nama atau kode bila perlu. Pilihan yang tersembunyi oleh
   pencarian tetap tersimpan; periksa rincian surat sebelum mencetak.
4. Isi tanggal serta catatan opsional, lalu klik **Simpan & Lihat Surat**.
5. Klik **Cetak Surat / Simpan PDF**. Surat ukuran A4 berisi nomor unik,
   supplier, alamat, rincian barang, jumlah, catatan, dan tempat tanda tangan.
   Surat dapat dicetak ulang melalui riwayat.

Fitur tersedia untuk admin dan petugas gudang. Satu surat hanya untuk satu
supplier. Supplier barang mengikuti pilihan pada data master barang; admin
dapat memperbaikinya jika salah.

Nama supplier, alamat, pemohon, kode/nama barang, dan satuan disalin saat surat
dibuat supaya cetak ulang tidak berubah mengikuti pembaruan data master.
Surat belum mengirim email, belum mencatat harga, dan tidak menambah stok.
Saat barang diterima, catat transaksi **Barang Masuk** seperti biasa serta
cantumkan nomor surat pada catatan. Penerimaan belum ditautkan otomatis ke surat.
