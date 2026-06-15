# Panduan Deployment ke Vercel

Panduan ini menjelaskan langkah-langkah untuk melakukan deployment Aplikasi Warehouse (Laravel 11 + Vue 3 + Inertia) ke platform **Vercel** menggunakan runtime PHP serverless.

## Cara Kerja Serverless Laravel di Vercel

Karena Vercel adalah platform serverless, aplikasi Laravel monolitik perlu disesuaikan agar berjalan sebagai serverless function. Kita menggunakan runtime pihak ketiga `vercel-php` untuk mengeksekusi kode PHP dan mengarahkan seluruh request web melalui entri serverless.

---

## Langkah 1: Buat File Konfigurasi Vercel

Kita perlu menambahkan file konfigurasi `vercel.json` di root direktori proyek dan folder entri serverless `api/`.

### 1. File `vercel.json`
Buat file [vercel.json](vercel.json) di root direktori proyek Anda:
```json
{
  "version": 2,
  "functions": {
    "api/index.php": {
      "runtime": "vercel-php@0.7.4"
    }
  },
  "routes": [
    {
      "src": "/build/(.*)",
      "dest": "/public/build/$1"
    },
    {
      "src": "/(.*)",
      "dest": "/api/index.php"
    }
  ]
}
```

### 2. File `api/index.php`
Buat folder baru bernama `api/` di root proyek, dan buat file [api/index.php](api/index.php) di dalamnya. File ini bertindak sebagai jembatan serverless untuk memuat Laravel:
```php
<?php

// Arahkan request aset statis public secara langsung jika dipanggil
if (file_exists(__DIR__ . '/../public' . $_SERVER['REQUEST_URI'])) {
    return false;
}

// Bootstrap Laravel
require __DIR__ . '/../public/index.php';
```

---

## Langkah 2: Sesuaikan Konfigurasi Laravel

Karena sistem file Vercel bersifat *Read-Only* (kecuali direktori `/tmp`), lakukan penyesuaian berikut:

1. **Session & Cache**: Gunakan database (`database` atau `redis`) atau cookie untuk menyimpan session dan cache, jangan menggunakan `file`. (Konfigurasi `.env` proyek ini sudah menggunakan `database` untuk session, queue, dan cache).
2. **File Upload**: Barang Masuk yang memiliki unggahan berkas fisik tidak dapat disimpan di disk lokal Vercel secara permanen. Sangat disarankan untuk mengubah `FILESYSTEM_DISK` ke layanan penyimpanan eksternal seperti AWS S3 atau Cloudinary pada produksi.

## Langkah 3: Siapkan Database Produksi (TiDB Cloud Serverless)

Vercel tidak menyediakan database MySQL internal secara gratis. Anda dapat menggunakan **TiDB Cloud Serverless** yang sepenuhnya kompatibel dengan protokol MySQL dan memiliki tier gratis.

### 1. Buat Kluster di TiDB Cloud
1. Daftar atau masuk ke [TiDB Cloud](https://pingcap.com/products/tidb-cloud).
2. Buat kluster baru dengan memilih tipe **Serverless** (Gratis).
3. Buat database baru di dalamnya, misalnya `jmc_warehouse`.
4. Pada panel **Connection**, klik **Connect**, lalu pilih **Laravel** atau **General Connection** untuk melihat informasi host, port, username, dan password.

### 2. Informasi Koneksi TiDB Cloud
Secara umum, parameter koneksi TiDB Serverless adalah:
- **DB_CONNECTION**: `mysql`
- **DB_HOST**: `gateway01.xxxx.prod.aws.tidbcloud.com`
- **DB_PORT**: `4000` (TiDB menggunakan port 4000 secara default)
- **DB_DATABASE**: `jmc_warehouse`
- **DB_USERNAME**: `xxxxxx.root`
- **DB_PASSWORD**: `PasswordKlusterAnda`

### 3. Konfigurasi SSL/TLS (Wajib)
TiDB Cloud Serverless mewajibkan koneksi aman menggunakan TLS/SSL. Untuk mengaktifkannya di Laravel pada lingkungan produksi Vercel, tambahkan variabel berikut pada **Environment Variables Vercel** agar PDO menggunakan sertifikat CA bawaan sistem Linux:
- **Key**: `MYSQL_ATTR_SSL_CA`
- **Value**: `/etc/ssl/certs/ca-certificates.crt` (Path standar sertifikat CA pada runtime Vercel Linux)

---

## Langkah 4: Hubungkan & Deploy ke Vercel

Ada dua cara utama untuk mendeploy aplikasi ke Vercel:

### Opsi A: Menggunakan GitHub Integration (Sangat Direkomendasikan)
1. Unggah (push) repositori git proyek Anda ke GitHub / GitLab / Bitbucket.
2. Masuk ke dashboard [Vercel](https://vercel.com).
3. Klik **Add New** -> **Project**.
4. Impor repositori GitHub proyek Anda.
5. Pada bagian **Environment Variables**, tambahkan variabel-variabel penting (lihat daftar di bawah).
6. Klik **Deploy**. Vercel akan otomatis melakukan build frontend (`npm run build`) dan meluncurkan aplikasi PHP Anda.

### Opsi B: Menggunakan Vercel CLI
1. Pasang Vercel CLI secara global:
   ```bash
   npm install -g vercel
   ```
2. Jalankan perintah login dan deploy di root direktori proyek Anda:
   ```bash
   vercel login
   vercel
   ```
3. Ikuti panduan perintah di terminal untuk menghubungkan proyek baru.

---

## Langkah 5: Konfigurasi Environment Variables di Vercel

Pada Dashboard Vercel Proyek Anda -> **Settings** -> **Environment Variables**, masukkan variabel-variabel berikut:

| Key | Value | Keterangan |
| :--- | :--- | :--- |
| `APP_NAME` | `Aplikasi Warehouse` | Nama aplikasi Anda |
| `APP_ENV` | `production` | Set ke produksi |
| `APP_KEY` | `base64:...` | Salin kunci aplikasi dari `.env` lokal Anda |
| `APP_DEBUG` | `false` | Nonaktifkan debug untuk keamanan |
| `APP_URL` | `https://nama-proyek.vercel.app` | URL domain Vercel Anda |
| `DB_CONNECTION` | `mysql` | Driver database |
| `DB_HOST` | `koneksi-database-eksternal.com` | Host MySQL produksi Anda |
| `DB_PORT` | `3306` | Port database |
| `DB_DATABASE` | `nama_database_produksi` | Nama database |
| `DB_USERNAME` | `user_database` | Username database |
| `DB_PASSWORD` | `password_database` | Password database |
| `SESSION_DRIVER` | `database` | Harus menggunakan `database` atau `cookie` |
| `CACHE_STORE` | `database` | Harus menggunakan `database` |

---

## Langkah 6: Jalankan Migrasi di Produksi

Setelah berhasil dideploy, tabel database pada server eksternal harus dimigrasi. Karena Anda tidak memiliki akses SSH langsung ke Vercel:
1. Jalankan perintah migrasi secara lokal dengan mengarahkan `.env` lokal Anda sementara ke database MySQL produksi, lalu jalankan:
   ```bash
   php artisan migrate --seed
   ```
2. Atau tambahkan rute sementara di `routes/web.php` untuk menjalankan migrasi via browser (hapus rute ini setelah berhasil):
   ```php
   Route::get('/run-migration', function () {
       Artisan::call('migrate:fresh', ['--seed' => true]);
       return "Migration success!";
   });
   ```
