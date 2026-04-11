# Dokumentasi Proyek EQ - Math

## 📋 Struktur Folder Baru

Proyek telah direorganisir dengan struktur yang lebih rapi dan professional:

```
EQ-Math/
├── config/
│   └── database.php           # Konfigurasi koneksi database
├── includes/
│   ├── auth.php              # Fungsi autentikasi (login, logout, register)
│   ├── functions.php         # Fungsi-fungsi helper
│   ├── header.php            # Komponen header (HTML head, mobile menu)
│   ├── footer.php            # Komponen footer (scripts, closing tags)
│   ├── sidebar-admin.php     # Sidebar untuk dashboard admin
│   └── sidebar-student.php   # Sidebar untuk dashboard siswa
├── dashboard/
│   ├── admin/
│   │   ├── index.php         # Dashboard admin (overview)
│   │   ├── pengajar.php      # CRUD data pengajar
│   │   ├── kelas.php         # CRUD master data kelas
│   │   ├── jadwal.php        # CRUD jadwal kelas
│   │   ├── pembayaran.php    # Monitoring pembayaran
│   │   └── siswa.php         # Data siswa terdaftar
│   └── student/
│       ├── index.php         # Dashboard siswa (overview)
│       ├── pilih-kelas.php   # Memilih kelas berdasarkan jenjang
│       ├── pembayaran.php    # Proses pembayaran kelas
│       ├── riwayat.php       # Riwayat transaksi
│       ├── kelas-saya.php    # Kelas aktif siswa
│       └── bantuan.php       # Customer Service via WhatsApp
├── actions/
│   ├── proses_pembayaran.php # Proses pembayaran & integrasi Midtrans
│   └── logout.php            # Proses logout
├── assets/
│   ├── css/                  # File CSS kustom
│   ├── js/                   # File JavaScript kustom
│   └── images/               # Aset gambar
├── style/                    # Tailwind CSS output
├── index.html                # Landing page
├── login.php                 # Halaman login
├── register.php              # Halaman registrasi
├── lupa-password.php         # Lupa password
├── koneksi.php               # Koneksi database (legacy)
└── eq_math_db.sql           # Database schema
```

## 🗄️ Struktur Database

### Tabel Utama:

1. **users** - Data pengguna (admin & siswa)
   - id, nama, email, password, no_hp, role, status, last_login, created_at

2. **master_kelas** - Master data kelas
   - id, nama_kelas, jenjang, deskripsi, harga, durasi, kapasitas, status

3. **master_pengajar** - Data pengajar
   - id, nama, email, spesialisasi, pendidikan, no_hp, status

4. **jadwal_kelas** - Jadwal pelaksanaan kelas
   - id, kelas_id, pengajar_id, hari, jam, created_at

5. **transaksi_pembayaran** - Riwayat pembayaran
   - id, user_id, kelas_id, jadwal_id, invoice, jumlah, status, metode_pembayaran

## 🔐 Autentikasi & Authorization

### Role-Based Access Control:
- **Admin**: Akses penuh ke dashboard admin
- **Siswa**: Akses ke dashboard siswa

### Fungsi Autentikasi:
```php
// Login
login($email, $password, $remember = false)

// Register
register($nama, $email, $password, $no_hp)

// Logout
logout()

// Check authentication
isLoggedIn()
hasRole($role)
requireLogin()
requireRole($role)
```

## 🎨 Desain & UI

### Tailwind CSS Configuration:
- **Primary Color**: Blue (#2563eb, #1e40af)
- **Font**: Inter (Google Fonts)
- **Responsive**: Mobile-first approach
- **Components**: Cards, Modals, Tables, Forms

### Komponen Reusable:
- Header dengan mobile menu toggle
- Sidebar (admin & student)
- Footer dengan JavaScript utilities
- Modal pop-up untuk form input
- Status badges (success, warning, danger, info)

## 💳 Sistem Pembayaran

### Integrasi Midtrans (Simulasi):
File `actions/proses_pembayaran.php` menangani:
1. Pembuatan invoice
2. Simulasi proses pembayaran Midtrans
3. Update status pembayaran
4. Assign jadwal kelas otomatis

### Notifikasi WhatsApp:
- Menggunakan Fonnte API (disiapkan)
- Kirim konfirmasi pembayaran sukses
- Update ke siswa

## 📱 Dashboard Features

### Admin Dashboard:
- **Overview**: Statistik total siswa, pengajar, kelas, pendapatan
- **Data Pengajar**: CRUD dengan search & filter
- **Data Kelas**: CRUD dengan grouping by jenjang
- **Jadwal Kelas**: Manage jadwal per hari/jam
- **Pembayaran**: Monitoring semua transaksi
- **Data Siswa**: View & manage siswa terdaftar

### Student Dashboard:
- **Overview**: Welcome banner, quick stats, next class
- **Pilih Kelas**: Filter by jenjang (SD/SMP/SMA)
- **Pembayaran**: Tagihan pending, metode pembayaran
- **Riwayat**: History transaksi dengan status
- **Kelas Saya**: Kelas aktif & jadwal upcoming
- **Bantuan**: CS WhatsApp & FAQ

## 🔧 Helper Functions

### Format Functions:
```php
formatDateIndo($date)      // Format tanggal Indonesia
formatRupiah($angka)       // Format mata uang IDR
formatTime($time)          // Format waktu AM/PM
getInitials($name)         // Generate inisial nama
```

### Utility Functions:
```php
cleanInput($data)          // Sanitasi input
uploadFile()               // Upload file validation
redirectWithMessage()      // Redirect dengan flash message
showMessage()              // Tampilkan flash message
paginate()                 // Logic pagination
```

### Status Functions:
```php
getStatusBadge($status)    // Return CSS class untuk badge
```

## 🚀 Cara Penggunaan

### 1. Setup Database:
```bash
# Import database schema
mysql -u root -p eq_math_db < eq_math_db.sql
```

### 2. Konfigurasi Database:
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eq_math_db');
```

### 3. Create Admin Account:
```sql
INSERT INTO users (nama, email, password, role, status)
VALUES ('Administrator', 'admin@eqmath.com',
        '$2y$10$hashedpassword', 'admin', 'aktif');
```

### 4. Akses Dashboard:
- **Admin**: http://localhost/dashboard/admin/
- **Siswa**: http://localhost/dashboard/student/

## 🔒 Security Features

1. **Password Hashing**: Menggunakan `password_hash()` (bcrypt)
2. **SQL Injection Prevention**: Menggunakan PDO Prepared Statements
3. **XSS Prevention**: Fungsi `cleanInput()` & `htmlspecialchars()`
4. **CSRF Protection**: Token validation (ready to implement)
5. **Session Management**: Secure session configuration
6. **Remember Me**: Secure token with expiration

## 📝 Catatan Pengembangan

### Yang Perlu Ditambahkan:
1. CSRF token implementation
2. Rate limiting untuk login attempts
3. Email verification untuk registrasi
4. Password reset via email
5. Export data to Excel/PDF
6. Real-time notifications
7. Chat system untuk siswa-pengajar
8. Video conference integration (Zoom API)
9. Payment gateway integration (Midtrans production)
10. WhatsApp notification (Fonnte API)

### Best Practices Applied:
- ✅ Modularisasi kode
- ✅ DRY (Don't Repeat Yourself)
- ✅ Separation of Concerns
- ✅ Clean Code principles
- ✅ Responsive Design
- ✅ User-friendly UX
- ✅ Error handling
- ✅ Input validation
- ✅ Database security

## 🐛 Troubleshooting

### Common Issues:

**1. Session not working:**
- Pastikan `session_start()` dipanggil di awal setiap file
- Check PHP session configuration

**2. Database connection failed:**
- Verify XAMPP/WAMP services running
- Check database credentials in `config/database.php`
- Ensure database `eq_math_db` exists

**3. Redirect not working:**
- Check for whitespace before `<?php` tag
- Verify `header()` is called before any HTML output

**4. Styles not loading:**
- Verify Tailwind CSS CDN is accessible
- Check browser console for errors

## 📞 Support

Untuk pertanyaan atau bantuan, hubungi:
- Email: support@eqmath.com
- WhatsApp: +62 812-3456-7890

---

**Dibuat untuk ETS Pemrograman Web - Program Studi Sistem Informasi UPN Veteran Jatim**

© 2024 EQ - Math. All rights reserved.
