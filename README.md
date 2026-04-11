# EQ - Math: Web Pendaftaran Kelas Matematika

## 📌 Deskripsi Proyek
**EQ - Math** adalah platform pendaftaran kelas matematika berbasis web. Proyek ini dikembangkan untuk memenuhi tugas Evaluasi Tengah Semester (ETS) mata kuliah Pemrograman Web Program Studi Sistem Informasi UPN Veteran Jatim. Web ini memfasilitasi siswa untuk mendaftar kelas dan melakukan simulasi pembayaran, sekaligus memungkinkan admin untuk mengelola data jadwal dan pengajar.

## 🛠️ Teknologi yang Digunakan
Sesuai dengan ketentuan ETS, proyek ini dibangun secara *native* tanpa *framework* utama untuk *backend*:
* **Frontend:** HTML5, Tailwind CSS, JavaScript (untuk DOM/Midtrans).
* **Backend:** PHP Native, Session, Cookies.
* **Database:** MySQL (phpMyAdmin).
* **Integrasi Pihak Ketiga (Fitur Lanjutan):** * Midtrans Payment Gateway (Mode Sandbox) untuk simulasi pembayaran.
    * Fonnte API (WhatsApp Gateway) untuk notifikasi pembayaran sukses via WA.

---

## 🏗️ Struktur Website & Fitur

### 1. Halaman Publik (Landing Page)
* Beranda utama yang menampilkan informasi Tentang Kami (*Company Profile*).
* Daftar harga/paket kelas (*Pricing*).
* Halaman Daftar / Login (Autentikasi dengan *Password Hashing*).

### 2. Dashboard Siswa (User Role)
* **Pilih Kelas:** Memilih kelas berdasarkan jenjang (SD/SMP/SMA).
* **Pembayaran:** Melakukan pembayaran kelas sesuai pricing (terintegrasi *pop-up* simulasi Midtrans).
* **Riwayat:** Melihat riwayat pembayaran dan statusnya.
* **Kelas Saya:** Melihat kelas aktif atau *upcoming*.
* **Bantuan:** Fitur *Customer Service* via WhatsApp.

### 3. Dashboard Admin (Admin Role)
* **Mengelola Pengajar:** Fitur CRUD data guru.
* **Mengelola Kelas:** Fitur CRUD master data kelas.
* **Mengelola Jadwal:** Mengelola jadwal kelas.
* **Mengelola Pembayaran:** Memantau transaksi pembayaran.
* **Mengelola Siswa:** Melihat daftar siswa yang terdaftar.

---

## 🗄️ Struktur Database (Tabel & Relasi)
Terdapat 5 tabel utama yang akan digunakan untuk memenuhi syarat minimal 2 tabel relasi (1 tabel user + 1 tabel master):
1.  **`users`**: Menyimpan data autentikasi dengan pembagian minimal 2 role (admin & siswa).
2.  **`master_kelas`**: Menyimpan referensi nama kelas, jenjang, dan harga. (Tabel Master Utama).
3.  **`master_pengajar`**: Menyimpan nama-nama guru matematika.
4.  **`jadwal_kelas`**: Tabel relasi antara kelas dan pengajar.
5.  **`transaksi_pembayaran`**: Menyimpan histori *checkout* siswa, *Order ID*, dan status Midtrans.

---

## 🚀 Alur Pengerjaan (Workflow Tim)
Pengerjaan proyek ini **tidak harus dilakukan secara berurutan**. Anggota tim dapat bekerja secara paralel sesuai dengan peran dan area fokus masing-masing untuk efisiensi waktu. 

### 🎨 Area Frontend (Bisa dikerjakan paralel)
Fokus pada tampilan antarmuka (Slicing UI) dan validasi tanpa perlu menunggu *database* atau *backend* selesai.
* **Slicing HTML & Styling:** Membuat struktur halaman web dalam file `.html` dan mendesainnya menggunakan **Tailwind CSS**.
* **Data Dummy:** Menggunakan teks atau gambar *dummy* sementara agar desain bisa langsung terlihat menggunakan ekstensi *Live Server* di VS Code.
* **Halaman Target:** Landing Page, form Daftar/Login, UI Dashboard Admin, dan UI Dashboard Siswa.

### ⚙️ Area Backend & Database (Bisa dikerjakan paralel)
Fokus pada rancangan penyimpanan data dan logika sistem.
* **Setup Database:** Mengeksekusi *script* SQL untuk membuat tabel dan relasi.
* **Koneksi & Logic:** Membuat file `koneksi.php` dan file-file *Action* menggunakan **PHP Native** (seperti `proses_login.php`, `proses_tambah.php`).
* **Eksperimen API:** Melakukan uji coba *request* ke Midtrans (Snap Token) dan Fonnte (Kirim pesan WA).

### 🔗 Area Integrasi (Dikerjakan bersama)
Fokus menggabungkan hasil kerja tim Frontend dan Backend.
* **Convert ke PHP:** Mengubah ekstensi file UI dari `.html` menjadi `.php`.
* **Modularisasi UI:** Memotong elemen berulang seperti *Navbar* dan *Sidebar* untuk digabungkan dengan fungsi `include`.
* **Data Dinamis:** Mengganti teks *dummy* dengan *tag* dinamis PHP yang mengambil data dari *database*.
* **Session & Cookies:** Menerapkan `$_SESSION` dan `$_COOKIE` untuk autentikasi *login*.
* **Implementasi CRUD:** Menghubungkan antarmuka UI dengan logika *Create, Read, Update,* dan *Delete* pada tabel master data.

### 📝 Area Finishing & Dokumentasi (Mendekati Deadline)
* **Pemberkasan:** Menyusun dokumen laporan yang berisi latar belakang (ringkasan studi kasus), desain database/ERD, list fitur, link github, pembagian tugas, serta screenshot hasil web.
* **Export DB:** Mengumpulkan file database-nya dalam format `.sql`.
* **Recording:** Buat video walkthrough web dengan durasi maks 10 menit (Penjelasan fitur, code, validasi, cookies dan session).
* **Pengumpulan:** Mengumpulkan link repository github dengan pengaturan *public*.
