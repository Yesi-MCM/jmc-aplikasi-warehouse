# Aplikasi Warehouse (Gudang) - Laravel Vue

Sistem manajemen pergudangan berbasis web yang dibangun menggunakan **Laravel 11**, **Vue 3**, dan **Inertia.js** dengan styling **TailwindCSS**. Aplikasi ini merupakan solusi untuk soal tes teknis Programmer Mid (Database).

## Fitur Utama

1. **Dashboard**: Panel ringkasan inventaris real-time yang menampilkan total jenis barang, jumlah total stok fisik, total valuasi barang (Rupiah), serta log transaksi barang masuk dan keluar terbaru.
2. **Data Barang**: Menampilkan daftar master barang beserta kategorinya. Memiliki baris detail yang dapat diekspansi untuk melihat penempatan lokasi penyimpanan (rak dan tingkat), harga stok (mendukung multi-price), dan tanggal kadaluarsa dari masing-masing stok barang.
3. **Barang Masuk**: Mengelola pencatatan barang masuk dari vendor. Dilengkapi dengan form dinamis baris barang dan modal alokasi penyimpanan (**Lokasi Barang**) yang interaktif (Unit -> Ruangan -> Rak -> Tingkat Rak). Mendukung unggahan lampiran dokumen (PDF, Word, Gambar, maks 8MB).
4. **Barang Keluar**: Mengelola pencatatan pengeluaran barang untuk penerima. Sistem secara otomatis memotong stok barang di gudang asal menggunakan strategi **FIFO (First In First Out)** berdasarkan rak penyimpanan.
5. **Stock Opname**: Penjadwalan agenda audit stok fisik berkala. Lembar SO menampilkan daftar checklist barang, kuantitas sistem vs kuantitas fisik, selisih perhitungan otomatis, dan catatan. Dilengkapi dengan tombol **[🔍] Simulasi Scan** barcode scanner. Finalisasi audit otomatis menyesuaikan saldo stok di sistem.
6. **Manajemen Rak**: Halaman CRUD admin untuk mengelola unit gudang, ruangan, dan rak penyimpanan beserta pengaturan jumlah tingkat (tier) masing-masing rak.
7. **Manajemen User**: Halaman CRUD admin untuk mengelola pengguna (Admin dan Operator) beserta pembatasan akses berbasis peran (role-based access control).

## Desain Database

Skema database dirancang secara ternormalisasi untuk menangani:
- Pelacakan multi-price untuk barang yang sama yang dibeli di waktu/vendor berbeda.
- Pemetaan dinamis struktur tata letak penyimpanan fisik gudang.
- Log pencatatan transaksi barang & otomatisasi penyesuaian kartu stok.
- Snapshot kuantitas sistem saat audit opname dimulai.

Untuk panduan instalasi lengkap, silakan merujuk ke [SETUP_GUIDE.md](SETUP_GUIDE.md).
