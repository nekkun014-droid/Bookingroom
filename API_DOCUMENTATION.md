# Booking Room REST API Documentation

Base URL: `http://localhost/Bookingroom/public/api`

## Authentication//

Most endpoints require authentication using Bearer token in the Authorization header:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

Admin-only endpoints are marked with 🔒 **Admin Only**

---

## Authentication Endpoints

### 1. Register User
**POST** `/auth/register`

Create a new user account.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "token": "YOUR_JWT_TOKEN_HERE",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role_id": 2
    }
  }
}
```

---

### 2. Login
**POST** `/auth/login`

Login with existing credentials.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "YOUR_JWT_TOKEN_HERE",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role_id": 2
    }
  }
}
```

---

### 3. Get Current User
**GET** `/auth/me`

Get currently authenticated user information.

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Response (200):**
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role_id": 2
    }
  }
}
```

---

## Room Endpoints

### 4. List All Rooms
**GET** `/rooms`

Get list of all rooms with pagination.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 10, max: 100)
- `search` (optional): Search by name or location

**Example:**
```
GET /rooms?page=1&per_page=10&search=meeting
```

**Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "rooms": [
      {
        "id": 1,
        "name": "Meeting Room A",
        "location": "Floor 1",
        "capacity": 10,
        "created_at": "2024-01-01 10:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 25,
      "total_pages": 3,
      "has_next": true,
      "has_prev": false
    }
  }
}
```

---

### 5. Get Room Details
**GET** `/rooms/{id}`

Get details of a specific room.

**Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "room": {
      "id": 1,
      "name": "Meeting Room A",
      "location": "Floor 1",
      "capacity": 10,
      "created_at": "2024-01-01 10:00:00"
    }
  }
}
```

---

### 6. Create Room 🔒 **Admin Only**
**POST** `/rooms`

Create a new room.

**Headers:**
```
Authorization: Bearer ADMIN_TOKEN
```

**Request Body:**
```json
{
  "name": "Conference Room B",
  "location": "Floor 2",
  "capacity": 20
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Room created successfully",
  "data": []
}
```

---

## Booking Endpoints

### 7. List Bookings
**GET** `/bookings`

Get list of bookings. Admin sees all bookings, regular users see only their own.

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "bookings": [
      {
        "id": 1,
        "user_id": 1,
        "user_name": "John Doe",
        "room_id": 1,
        "room_name": "Meeting Room A",
        "start_time": "2024-12-25 09:00:00",
        "end_time": "2024-12-25 11:00:00",
        "status": "pending",
        "created_at": "2024-12-20 10:00:00"
      }
    ]
  }
}
```

---

### 8. Get Booking Details
**GET** `/bookings/{id}`

Get details of a specific booking.

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "booking": {
      "id": 1,
      "user_id": 1,
      "user_name": "John Doe",
      "room_id": 1,
      "room_name": "Meeting Room A",
      "start_time": "2024-12-25 09:00:00",
      "end_time": "2024-12-25 11:00:00",
      "status": "pending",
      "created_at": "2024-12-20 10:00:00"
    }
  }
}
```

---

### 9. Create Booking
**POST** `/bookings`

Create a new booking.

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Request Body:**
```json
{
  "room_id": 1,
  "start_time": "2024-12-25 09:00:00",
  "end_time": "2024-12-25 11:00:00"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "booking_id": 15
  }
}
```

**Error Response (409 - Conflict):**
```json
{
  "success": false,
  "message": "Room already booked for the selected time"
}
```

---

### 10. Update Booking Status 🔒 **Admin Only**
**PUT** `/bookings/{id}/status`

Update the status of a booking (approve/reject).

**Headers:**
```
Authorization: Bearer ADMIN_TOKEN
```

**Request Body:**
```json
{
  "status": "approved"
}
```

Valid statuses: `approved`, `rejected`, `pending`

**Response (200):**
```json
{
  "success": true,
  "message": "Booking status updated successfully",
  "data": []
}
```

---

### 11. Delete Booking
**DELETE** `/bookings/{id}`

Delete a booking. Users can delete their own bookings, admins can delete any.

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

**Response (200):**
```json
{
  "success": true,
  "message": "Booking deleted successfully",
  "data": []
}
```

---

## Timeslot Endpoints

### 12. List All Timeslots
**GET** `/timeslots`

Get list of all timeslots.

**Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "timeslots": [
      {
        "id": 1,
        "name": "Morning Slot",
        "start_time": "09:00:00",
        "end_time": "12:00:00",
        "created_at": "2024-01-01 10:00:00"
      }
    ]
  }
}
```

---

### 13. Get Timeslot Details
**GET** `/timeslots/{id}`

Get details of a specific timeslot.

**Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "timeslot": {
      "id": 1,
      "name": "Morning Slot",
      "start_time": "09:00:00",
      "end_time": "12:00:00",
      "created_at": "2024-01-01 10:00:00"
    }
  }
}
```

---

### 14. Create Timeslot 🔒 **Admin Only**
**POST** `/timeslots`

Create a new timeslot.

**Headers:**
```
Authorization: Bearer ADMIN_TOKEN
```

**Request Body:**
```json
{
  "name": "Afternoon Slot",
  "start_time": "13:00:00",
  "end_time": "17:00:00"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Timeslot created successfully",
  "data": []
}
```

---

### 15. Update Timeslot 🔒 **Admin Only**
**PUT** `/timeslots/{id}`

Update an existing timeslot.

**Headers:**
```
Authorization: Bearer ADMIN_TOKEN
```

**Request Body:**
```json
{
  "name": "Afternoon Slot Updated",
  "start_time": "13:00:00",
  "end_time": "18:00:00"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Timeslot updated successfully",
  "data": []
}
```

---

### 16. Delete Timeslot 🔒 **Admin Only**
**DELETE** `/timeslots/{id}`

Delete a timeslot.

**Headers:**
```
Authorization: Bearer ADMIN_TOKEN
```

**Response (200):**
```json
{
  "success": true,
  "message": "Timeslot deleted successfully",
  "data": []
}
```

---

## Error Responses

All endpoints may return the following error responses:

### 400 - Bad Request
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": "Email is required",
    "password": "Password is required"
  }
}
```

### 401 - Unauthorized
```json
{
  "success": false,
  "message": "Invalid or expired token"
}
```

### 403 - Forbidden
```json
{
  "success": false,
  "message": "Admin access required"
}
```

### 404 - Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 409 - Conflict
```json
{
  "success": false,
  "message": "Room already booked for the selected time"
}
```

### 500 - Internal Server Error
```json
{
  "success": false,
  "message": "Server error: [error details]"
}
```

---

## Notes

- All datetime fields use format: `YYYY-MM-DD HH:MM:SS`
- Time-only fields use format: `HH:MM:SS`
- Tokens expire after 7 days
- `role_id`: 1 = Admin, 2 = Regular User
- Booking status: `pending`, `approved`, `rejected`

---

## Testing with Postman

1. **Import** this documentation or create requests manually
2. **Register** a user or **Login** to get a token
3. **Save** the token in Postman environment variable
4. **Use** the token in Authorization header for protected endpoints:
   ```
   Authorization: Bearer {{token}}
   ```

### Creating an Admin User

To test admin endpoints, you need to manually set `role_id = 1` in the database:

```sql
UPDATE users SET role_id = 1 WHERE email = 'your-admin@example.com';
```
# Bookingroom API Documentation

Panduan singkat ini menjelaskan cara menggunakan API pada proyek Bookingroom.

Base URL
- API diakses melalui entrypoint `public/api.php` dengan prefix `/api`. Contoh: `https://example.com/api/`

Autentikasi
- Registrasi: `POST /auth/register` dengan JSON body: `{ "name": "Nama", "email": "email@example.com", "password": "secret" }`.
- Login: `POST /auth/login` dengan JSON body: `{ "email": "email@example.com", "password": "secret" }`.
- Semua endpoint yang membutuhkan autentikasi mengharuskan header:
  - `Authorization: Bearer <token>`
- Token: aplikasi menggunakan token mirip JWT dengan header.payload.signature. Token kadaluarsa 7 hari.

Format response
- Semua response API dikembalikan sebagai JSON dengan struktur umum:

  {
    "success": true|false,
    "message": "...",
    "data": { ... }
  }

Endpoint utama

- Auth
  - `POST /auth/register` — Registrasi user baru.
  - `POST /auth/login` — Login dan menerima `token`.
  - `GET /auth/me` — Mengambil data user saat ini (memerlukan token).

- Rooms
  - `GET /rooms` — Daftar ruangan. Mendukung query params: `page`, `per_page`, `search`.
  - `GET /rooms/{id}` — Detail ruangan.
  - `POST /rooms` — Buat ruangan baru (admin saja). Body JSON: `{ "name": "...", "location": "...", "capacity": 10 }`

- Bookings
  - `GET /bookings` — Daftar booking (memerlukan token). Admin melihat semua; user biasa hanya melihat miliknya.
  - `GET /bookings/{id}` — Detail booking (memerlukan token).
  - `POST /bookings` — Buat booking (memerlukan token). Body JSON: `{ "room_id": 1, "start_time": "YYYY-MM-DD HH:MM:SS", "end_time": "YYYY-MM-DD HH:MM:SS" }`
  - `PUT /bookings/{id}/status` — Update status booking (admin saja). Body JSON: `{ "status": "approved"|"rejected"|"pending" }`
  - `DELETE /bookings/{id}` — Hapus booking (admin atau pemilik booking).

- Timeslots
  - `GET /timeslots` — Daftar timeslot.
  - `GET /timeslots/{id}` — Detail timeslot.
  - `POST /timeslots` — Buat timeslot (admin). Body JSON: `{ "name":"..", "start_time":"HH:MM:SS", "end_time":"HH:MM:SS" }`
  - `PUT /timeslots/{id}` — Update timeslot (admin).
  - `DELETE /timeslots/{id}` — Hapus timeslot (admin).

Contoh penggunaan (curl)

- Register

```bash
curl -X POST https://example.com/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Budi","email":"budi@example.com","password":"secret"}'
```

- Login

```bash
curl -X POST https://example.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"budi@example.com","password":"secret"}'
```

- Mengambil daftar rooms

```bash
curl https://example.com/api/rooms
```

- Membuat booking (setelah login)

```bash
curl -X POST https://example.com/api/bookings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"room_id":1,"start_time":"2025-12-30 09:00:00","end_time":"2025-12-30 10:00:00"}'
```

Catatan penting
- Beberapa endpoint memerlukan hak admin (role_id = 1). Pastikan user yang melakukan request memiliki peran tersebut.
- Format tanggal/waktu untuk booking: `YYYY-MM-DD HH:MM:SS`.
- Format waktu untuk timeslot: `HH:MM:SS`.
- Validasi dan error: server mengembalikan status code yang sesuai (400, 401, 403, 404, 409, 500) dan pesan di field `message`.

Konfigurasi lokal
- Sesuaikan `app/config/db.php` untuk koneksi database dan impor `schema.sql` atau `dump.sql` untuk membuat tabel.

Jika butuh dokumentasi lebih rinci (contoh response, skema database, contoh request lengkap), beri tahu saya agar saya lengkapi.
