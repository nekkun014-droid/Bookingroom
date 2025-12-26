# 🏢 Mini Room Booking System

Aplikasi web reservasi ruangan/lab modern dengan antarmuka responsif dan sistem keamanan tingkat enterprise. Dibangun dengan PHP Native + MySQL tanpa framework eksternal.

## 📋 Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Arsitektur Sistem](#arsitektur-sistem)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi & Setup](#instalasi--setup)
- [Cara Menggunakan](#cara-menggunakan)
- [Struktur Project](#struktur-project)
- [Keamanan](#keamanan)
- [Development](#development)

---

## ✨ Fitur Utama

### Untuk User (Pengguna Umum)
- ✅ **Register & Login** — Pendaftaran akun dan autentikasi aman
- ✅ **Lihat Ruangan Tersedia** — Browse daftar ruangan dengan detail
- ✅ **Buat Booking** — Pesan ruangan dengan time slot yang fleksibel
- ✅ **Kelola Booking Saya** — Lihat history, ubah, atau batalkan booking
- ✅ **Remember Me** — Stay logged in dengan persistent login (30 hari)
- ✅ **Reset Password** — Recovery akun via email verification token

### Untuk Admin
- ✅ **Kelola Ruangan** — CRUD (Create, Read, Update, Delete) ruangan
- ✅ **Kelola Time Slot** — Atur jam operasional booking (Morning, Afternoon, dll)
- ✅ **Kelola Booking** — Review dan approve/reject booking dari user
- ✅ **Activity Log** — Catat setiap aktivitas penting untuk audit trail
- ✅ **Export Data** — Download time slots ke CSV untuk laporan

### Fitur Teknis
- 🔐 **Keamanan Tingkat Enterprise**
  - Password hashing dengan bcrypt (`password_hash/password_verify`)
  - CSRF token protection di semua form
  - Session regeneration saat login
  - SQL injection prevention (prepared statements)
  - Secure persistent login dengan token rotation
  
- 📱 **Responsive Design** — Mobile-first, Desktop-optimized
- 🎨 **Modern UI** — Gradient buttons, smooth animations, glassmorphism effects
- ♿ **Accessible** — Semantic HTML5, ARIA labels, keyboard navigation
- 🚀 **Performance** — Minimal dependencies, optimized queries

---

## 🏗️ Arsitektur Sistem

### Model-View-Controller (MVC) Pattern

```
public/                    # Entry point & static assets
├── index.php             # Router utama
├── assets/
│   ├── css/style.css     # Styling global
│   └── js/app.js         # Frontend logic

app/                       # Aplikasi logic
├── config/
│   ├── constants.php     # Konstanta app (APP_NAME, etc)
│   └── db.php            # Koneksi database
├── controllers/          # Business logic
│   ├── AuthController.php
│   ├── BookingController.php
│   ├── RoomController.php
│   └── TimeslotController.php
├── models/               # Database abstraction
│   ├── User.php
│   ├── Room.php
│   ├── Booking.php
│   ├── Timeslot.php
│   ├── ActivityLog.php
│   ├── PasswordReset.php
│   └── PersistentLogin.php
├── views/                # Template HTML
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   ├── home.php
│   └── [feature views...]
├── middleware/
│   └── csrf.php          # CSRF token validation
└── helpers/
    └── logger.php        # Activity logging
```

### Database Schema

**Users** — Penyimpan data user dengan password hashing
- id, email, name, password_hash, role_id, created_at

**Roles** — Admin (1) & User (2)
- id, name

**Rooms** — Daftar ruangan yang bisa dibooking
- id, name, location, capacity, description

**Timeslots** — Jam operasional untuk booking
- id, name, start_time, end_time

**Bookings** — Rekam jaga user
- id, user_id, room_id, timeslot_id, booking_date, status, created_at

**PersistentLogins** — Remember-me token storage
- id, user_id, selector, token_hash, expires_at

**PasswordResets** — Password recovery tokens
- id, user_id, token_hash, expires_at, used_at

**ActivityLogs** — Audit trail
- id, user_id, action, description, created_at

---

## 🖥️ Persyaratan Sistem

- **PHP** ≥ 7.4 (tested on 8.0+)
- **MySQL** ≥ 5.7 atau MariaDB ≥ 10.2
- **Web Server** — Apache (XAMPP), Nginx, atau Laragon
- **Browser** — Modern browser (Chrome, Firefox, Safari, Edge)

---

## 🚀 Instalasi & Setup

### 1️⃣ Clone atau Download Project

```bash
git clone https://github.com/nekkun014-droid/Bookingroom.git
cd Bookingroom
```

### 2️⃣ Setup Database

**Opsi A: Menggunakan phpMyAdmin (GUI)**
1. Buka http://localhost/phpmyadmin
2. Buat database baru: `room_booking`
3. Pilih database → klik tab "Import"
4. Upload file `schema.sql` dari folder project
5. Klik "Go" untuk menjalankan script

**Opsi B: Menggunakan MySQL CLI**

```bash
mysql -u root -p < schema.sql
```

**Opsi C: Otomatis saat pertama kali akses (jika setup script tersedia)**

### 3️⃣ Konfigurasi Database

Edit file `app/config/db.php`:

```php
<?php
define('DB_HOST', 'localhost');      // Host database
define('DB_USER', 'root');           // Username MySQL
define('DB_PASS', '');               // Password MySQL (kosong untuk XAMPP default)
define('DB_NAME', 'room_booking');   // Nama database
```

### 4️⃣ Setup Web Server

**Menggunakan XAMPP:**

1. Letakkan folder project di `C:\xampp\htdocs\Bookingroom` (Windows) atau `/Applications/XAMPP/htdocs/Bookingroom` (macOS)
2. Pastikan Apache running di XAMPP Control Panel
3. Akses: http://localhost/Bookingroom/

**Menggunakan Virtual Host (Opsional untuk production-like setup):**

Edit `httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName booking.local
    DocumentRoot "C:/xampp/htdocs/Bookingroom/public"
    <Directory "C:/xampp/htdocs/Bookingroom/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Edit `hosts` file (`C:\Windows\System32\drivers\etc\hosts` atau `/etc/hosts`):

```
127.0.0.1 booking.local
```

Akses: http://booking.local

### 5️⃣ Verify Installation

- ✅ Buka http://localhost/Bookingroom/ atau http://booking.local
- ✅ Seharusnya tampil halaman login
- ✅ Database connected dan tables created

---

## 🎯 Cara Menggunakan

### Demo Credentials

**Admin Account:**
- Email: `admin@example.com`
- Password: `Admin@123`
- Akses: Kelola ruangan, time slot, booking, activity log

**User Account:**
- Email: `user@example.com`
- Password: `User@123`
- Akses: Lihat ruangan, buat booking, kelola booking saya

### User Journey

#### 1. Register (Pengguna Baru)
```
Home Page → Click "Create account" 
  → Isi Full name, Email, Password
  → Submit & auto-login
```

#### 2. Login (Pengguna Existing)
```
Home Page → Click "Sign in"
  → Isi Email & Password
  → Centang "Remember me" (optional, stay logged 30 hari)
  → Sign in
```

#### 3. Browse Rooms
```
Dashboard → Click "Rooms"
  → Lihat list semua ruangan
  → Click room untuk detail (capacity, lokasi, deskripsi)
```

#### 4. Create Booking
```
Rooms Page → Click "Book Now" pada ruangan pilihan
  → Pilih booking date (harus >= hari ini)
  → Pilih time slot (Morning: 08:00-10:00, dll)
  → Click "Create Booking"
  → Status: Pending (menunggu approval admin)
```

#### 5. Manage My Bookings
```
Dashboard → Click "My Bookings"
  → Lihat status booking (Pending, Approved, Rejected, Cancelled)
  → Actionable: Edit (jika Pending) atau Cancel
```

#### 6. Admin - Manage Rooms
```
Dashboard (Admin) → Click "Rooms"
  → Create Room: Isi name, location, capacity, description
  → Edit / Delete room yang existing
```

#### 7. Admin - Manage Time Slots
```
Dashboard (Admin) → Click "Timeslots"
  → Lihat semua time slots
  → Create / Edit / Delete slots
  → Export CSV untuk laporan
```

#### 8. Admin - Review Bookings
```
Dashboard (Admin) → Click "Bookings"
  → Lihat booking dari semua user
  → Approve / Reject / Cancel booking
```
## 🔗 API

Dokumentasi lengkap untuk penggunaan API tersedia pada file [API_DOCUMENTATION.md](API_DOCUMENTATION.md).

Gunakan dokumentasi tersebut untuk detail endpoint, contoh request `curl`, format token, dan aturan autentikasi.

### Password Recovery

```
Login Page → Click "Forgot password?"
  → Isi email terdaftar
  → Check email untuk recovery link (valid 1 jam)
  → Click link → set password baru
  → Login dengan password baru
```

---

## 📁 Struktur Project

```
Bookingroom/
├── public/                              # Web root
│   ├── index.php                        # Main router
│   ├── clear_session.php               # Session clearer
│   └── assets/
│       ├── css/style.css               # Styling (responsive, modern design)
│       └── js/app.js                   # Frontend logic
│
├── app/
│   ├── config/
│   │   ├── constants.php               # Global constants
│   │   └── db.php                      # Database connection
│   │
│   ├── controllers/                    # Business logic layer
│   │   ├── AuthController.php          # Login, register, logout, password reset
│   │   ├── BookingController.php       # Booking CRUD
│   │   ├── RoomController.php          # Room CRUD
│   │   └── TimeslotController.php      # Timeslot CRUD
│   │
│   ├── models/                         # Database abstraction
│   │   ├── User.php                    # User queries
│   │   ├── Room.php                    # Room queries
│   │   ├── Booking.php                 # Booking queries
│   │   ├── Timeslot.php                # Timeslot queries
│   │   ├── ActivityLog.php             # Logging queries
│   │   ├── PasswordReset.php           # Password reset queries
│   │   └── PersistentLogin.php         # Remember-me queries
│   │
│   ├── views/                          # HTML templates
│   │   ├── login.php                   # Login form
│   │   ├── register.php                # Register form
│   │   ├── dashboard.php               # User/Admin dashboard
│   │   ├── home.php                    # Landing page
│   │   ├── bookings/index.php          # Bookings list
│   │   ├── rooms/index.php             # Rooms management
│   │   ├── timeslots/index.php         # Timeslots management
│   │   ├── auth/
│   │   │   ├── password_request.php    # Forgot password form
│   │   │   └── password_reset.php      # Reset password form
│   │   └── templates/
│   │       ├── header.php              # Header reusable
│   │       ├── footer.php              # Footer reusable
│   │       └── layout.php              # Main layout wrapper
│   │
│   ├── middleware/
│   │   └── csrf.php                    # CSRF token validation
│   │
│   └── helpers/
│       └── logger.php                  # Activity logging helper
│
├── storage/                            # For runtime files (logs, temp)
├── tests/                              # PHPUnit tests
├── scripts/
│   └── seed.php                        # Database seeding (optional)
│
├── composer.json                       # PHP dependencies (minimal)
├── phpunit.xml                         # PHPUnit config
├── schema.sql                          # Database schema
├── dump.sql                            # Database backup (optional)
└── README.md                           # Dokumentasi (file ini)
```

---

## 🔐 Keamanan

### Implementasi Keamanan

| Fitur | Implementasi |
|-------|--------------|
| **Password Hashing** | `password_hash($pass, PASSWORD_BCRYPT)` — bcrypt dengan cost=10 |
| **SQL Injection Prevention** | Prepared statements dengan parameter binding |
| **CSRF Protection** | CSRF token di session, validate di middleware |
| **Session Security** | `session_regenerate_id(true)` after login |
| **Remember-Me Token** | Selector:Token pattern, token rotation, 30-day expiry |
| **Password Reset** | Time-limited token (1 jam), one-time use, email verification |
| **Activity Logging** | Audit trail untuk sensitive actions (login, booking approve, etc) |
| **XSS Prevention** | `htmlspecialchars()` pada output, Content Security Policy ready |
| **Rate Limiting** | Implementable di nginx atau PHP (future) |

### Best Practices Diimplementasikan

✅ Never store plain passwords
✅ Use `password_hash()` & `password_verify()`
✅ Regenerate session ID after login
✅ Validate & sanitize all inputs
✅ Use prepared statements (PDO parameterized queries)
✅ Implement CSRF tokens
✅ Secure cookie flags (HttpOnly, SameSite, Secure for HTTPS)
✅ Proper error handling (no sensitive info in 500 errors)

---

## 👨‍💻 Development

### Local Development Setup

```bash
# 1. Clone repo
git clone https://github.com/nekkun014-droid/Bookingroom.git
cd Bookingroom

# 2. Setup database (lihat section Instalasi)

# 3. Jalankan di XAMPP
# - Start Apache & MySQL
# - Akses http://localhost/Bookingroom/

# 4. Optional: Jalankan tests
composer install
vendor/bin/phpunit
```

### Adding New Features

**Contoh: Add "Email Notification" saat booking approved**

1. **Model** (`app/models/Booking.php`)
   ```php
   public function getById($id) { ... }
   ```

2. **Controller** (`app/controllers/BookingController.php`)
   ```php
   public function approve($id) {
       $booking = Booking->getById($id);
       // send email
       // update booking status
   }
   ```

3. **View** (`app/views/bookings/index.php`)
   ```php
   <form method="post" action="?action=booking_approve">
       <button>Approve</button>
   </form>
   ```

4. **Routing** (`public/index.php`)
   ```php
   case 'booking_approve':
       $controller->approve($_POST['id']);
       break;
   ```

### Testing

```bash
# Jalankan unit tests
vendor/bin/phpunit tests/

# Specific test
vendor/bin/phpunit tests/BookingTest.php
```

---

## 📝 Catatan & To-Do

### Fitur yang Sudah Selesai ✅
- Authentication (Login/Register/Logout)
- Session management dengan remember-me
- CRUD Room, Booking, Timeslot
- Admin dashboard
- Responsive design
- CSRF protection
- Activity logging

### Fitur untuk Enhancement (Optional)
- [ ] Email notifications (booking confirmation, approval)
- [ ] Real-time status updates (WebSocket/SSE)
- [ ] Advanced filtering & search
- [ ] Calendar view untuk bookings
- [ ] Rating & review ruangan
- [ ] Payment integration
- [ ] Two-factor authentication (2FA)
- [ ] Dark mode toggle
- [ ] Internationalization (i18n)

---

## 🐛 Troubleshooting

### "Database connection failed"
- Pastikan MySQL running
- Check credentials di `app/config/db.php`
- Pastikan database `room_booking` exist

### "Table doesn't exist"
- Import `schema.sql` ke database
- Verify semua tables ada: `SHOW TABLES;`

### "Login gagal"
- Clear cookies & session: gunakan "Sign out" atau buka `public/clear_session.php`
- Check database data seeded dengan users
- Verify `password_verify()` working

### "CSRF token mismatch"
- Ensure session started di `public/index.php`
- Check form punya hidden input `name="_csrf"`

---

## 📄 Lisensi

MIT License — Bebas digunakan untuk pembelajaran dan komersial.

---

## 👨‍💼 Author

**nekkun014-droid**  
Minimal student project untuk demonstrasi PHP native + MySQL web application.

---

## 📞 Support

Untuk pertanyaan atau issue:
1. Check dokumentasi di atas
2. Review file `schema.sql` untuk database structure
3. Lihat comments di controller & model
4. Open issue di GitHub repository

---

**Last Updated:** November 2025  
**Version:** 1.0.0 (Stable)
