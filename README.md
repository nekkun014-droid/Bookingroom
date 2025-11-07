# Mini Room Booking App (PHP Native + MySQL)

Deskripsi singkat:
Implementasi ringkas aplikasi reservasi ruangan/lab dengan dua peran: admin & user.

Struktur proyek: lihat template di tugas. Jalankan di XAMPP/Laragon dengan DocumentRoot -> `public/`.

Demo credentials:

- Admin: email: admin@example.com / password: Admin@123
- User: email: user@example.com / password: User@123

Setup singkat:

1. Import `schema.sql` ke MySQL (phpMyAdmin atau mysql CLI).
2. Sesuaikan koneksi DB di `app/config/db.php`.
3. Letakkan project di htdocs (XAMPP) atau set VirtualHost ke folder `public/`.
4. Akses: http://localhost/ (atau sesuai VirtualHost).

Fitur yang disertakan (minimal):

- Semantic HTML5
- RWD (mobile/tablet/desktop)
- Client-side & server-side validation
- Login/logout dengan `session_regenerate_id` dan `password_hash`/`verify`
- CSRF token untuk form penting
- CRUD Rooms & Bookings (prepared statements)
- Flash messages via session

Catatan: Ini skeleton untuk tugas; Anda dapat memperluas controller, model, dan views sesuai rubrik.
