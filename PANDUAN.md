# 🧮 EQ-Math - Platform Pendaftaran Kelas Matematika

Aplikasi web berbasis PHP dan MySQL untuk pendaftaran kelas bimbingan belajar matematika. Aplikasi ini dilengkapi dengan fitur integrasi *Payment Gateway* menggunakan **Midtrans (Sandbox Mode)** untuk memproses pembayaran secara otomatis.

## 🛠️ Persyaratan Sistem
Pastikan laptop/komputer Anda sudah terinstal:
- **XAMPP** (PHP 7.4 atau lebih baru & MariaDB/MySQL)
- Web Browser (Chrome, Firefox, Edge, dll)

---

## 🚀 Cara Instalasi & Menjalankan di Localhost

1. **Download Kodingan:**
   - Klik tombol hijau **Code** di atas, lalu pilih **Download ZIP**.
   - Ekstrak file ZIP tersebut dan ubah nama foldernya menjadi `eq-math`.
   - Pindahkan folder `eq-math` ke dalam direktori `C:\xampp\htdocs`.

2. **Setup Database:**
   - Buka XAMPP Control Panel dan nyalakan **Apache** & **MySQL**.
   - Buka browser dan akses `http://localhost/phpmyadmin`.
   - Buat database baru dengan nama: **`eq_math_db`**.
   - Pilih tab **Import**, lalu masukkan file `eq_math_db (1).sql` yang ada di dalam folder project ini. *(Catatan: File ini hanya berisi struktur tabel demi keamanan data).*

3. **Jalankan Aplikasi:**
   - Buka tab baru di browser dan ketikkan URL: `http://localhost/eq-math`
   - Aplikasi siap digunakan!

---

## 🧪 Panduan Uji Coba (Testing)

Karena database yang di-import masih kosong (hanya kerangka), ikuti skenario uji coba ini dari awal hingga akhir:

### Skenario 1: Persiapan Data (Oleh Admin)
1. **Register Akun:** Buka halaman pendaftaran dan buat akun baru. (Secara default, akun pertama mungkin terdaftar sebagai siswa, silakan ubah role-nya menjadi `admin` langsung melalui phpMyAdmin di tabel `users`).
2. **Login Admin:** Masuk menggunakan akun admin tersebut.
3. **Tambah Data Master:** Masuk ke menu Master Data, lalu tambahkan minimal 1 Data Pengajar dan 1 Data Kelas beserta jadwalnya agar nanti bisa dipilih oleh siswa.

### Skenario 2: Pendaftaran & Pembayaran (Oleh Siswa)
1. **Buat Akun Siswa:** Buka web dari *Incognito/Private Window*, lalu daftar sebagai siswa baru.
2. **Pilih Kelas:** Masuk ke dashboard siswa, pilih jadwal kelas yang sudah dibuat oleh Admin sebelumnya.
3. **Checkout:** Klik tombol beli/daftar. Anda akan diarahkan ke halaman pembayaran Midtrans.
4. **Simulasi Pembayaran (Midtrans Sandbox):**
   - Saat pop-up Midtrans muncul, pilih metode pembayaran (misal: **BCA Virtual Account** atau **QRIS**).
   - Untuk mensimulasikan pembayaran yang berhasil, gunakan **[Midtrans Simulator](https://simulator.sandbox.midtrans.com/)**.
   - Masukkan nomor Virtual Account ke simulator tersebut dan klik "Pay".
5. **Cek Status:** Kembali ke web EQ-Math, status pembayaran siswa seharusnya sudah otomatis berubah menjadi "Lunas/Settlement".

---

## 🔐 Keamanan & Konfigurasi
Aplikasi ini berjalan menggunakan kunci **Midtrans Sandbox** (Uang Mainan/Simulasi). Server Key telah disesuaikan untuk kebutuhan uji coba lokal dan tidak terhubung dengan transaksi finansial nyata.

*Dibuat untuk keperluan evaluasi dan pembelajaran pemrograman web.*
