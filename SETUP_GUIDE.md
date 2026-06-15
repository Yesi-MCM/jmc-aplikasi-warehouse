# Panduan Instalasi dan Konfigurasi

Ikuti langkah-langkah di bawah ini untuk memasang dan menjalankan Aplikasi Warehouse secara lokal di sistem Windows (lingkungan Laragon).

## Prasyarat

Pastikan Anda telah memasang perangkat lunak berikut:
- **Laragon** (menyertakan Apache, MySQL, PHP 8.3+)
- **Composer** (v2.x)
- **Node.js** (v20 atau v22) & **NPM**

## Langkah-Langkah Menjalankan Proyek

### 1. Letakkan Proyek
Pindahkan atau ekstrak folder proyek ini ke dalam direktori root `www` Laragon Anda:
```bash
C:\laragon\www\jmc-aplikasi-warehouse
```

### 2. Konfigurasi Environment (.env)
File `.env` sudah dikonfigurasi di dalam proyek. Pastikan pengaturan database MySQL di bawah ini sesuai dengan lingkungan MySQL Laragon Anda (secara default di Laragon menggunakan user `root` dengan password kosong):
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jmc_warehouse
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Buat Database
Buat database baru bernama `jmc_warehouse` melalui Terminal Laragon, phpMyAdmin, atau MySQL Client:
```sql
CREATE DATABASE IF NOT EXISTS jmc_warehouse;
```

### 4. Pasang Dependensi
Buka terminal pada root direktori proyek, lalu jalankan perintah berikut untuk menginstal package Composer dan NPM:
```bash
composer install
npm install
```

### 5. Jalankan Migrasi & Seeding Database
Jalankan migrasi tabel-tabel database beserta pengisian data awal (seeder) yang sesuai dengan contoh kasus pada soal tes teknis:
```bash
php artisan migrate:fresh --seed
```
*Perintah ini akan secara otomatis membuat akun Administrator dan Operator default.*

### 6. Compile Frontend Assets
Lakukan kompilasi file frontend Vue 3:
```bash
# Untuk menjalankan compiler development server secara real-time
npm run dev

# Atau untuk membuild production bundle secara langsung
npm run build
```

### 7. Jalankan Server Lokal
Jalankan server internal PHP Laravel:
```bash
php artisan serve
```
Buka peramban (browser) Anda dan akses alamat `http://127.0.0.1:8000`.

---

## Akun Login Default

Gunakan kredensial berikut untuk masuk ke sistem sesuai dengan peran (role) pengguna:

### 1. Akun Administrator
- **Username**: `admin`
- **Password**: `password`
- **Hak Akses**: Akses penuh ke seluruh modul sistem termasuk **Manajemen Rak**, **Manajemen User**, dan finalisasi **Stock Opname**.

### 2. Akun Operator
- **Username**: `operator`
- **Password**: `password`
- **Hak Akses**: Akses terbatas untuk melihat stok barang, mencatat transaksi **Barang Masuk**, **Barang Keluar**, dan membuat draf checklist **Stock Opname**.

---

## File Rancangan Database SQL
File SQL struktur database beserta sampel datanya sesuai permintaan soal tes dapat ditemukan di root direktori proyek:
* **[rancangan_database.sql](rancangan_database.sql)**
