# 🚀 Panduan Testing REST API di Postman

## 📋 Persiapan

1. **Jalankan Server:**
   ```bash
   cd C:\Users\ASUS\OneDrive\Desktop\Bookingroom
   php -S localhost:8000 -t public
   ```

2. **Import Postman Collection:**
   - Buka Postman
   - Klik **Import** (kiri atas)
   - Pilih file: `Bookingroom_API.postman_collection.json`
   - Klik **Import**

3. **Set Environment (Optional tapi Recommended):**
   - Klik icon ⚙️ (Settings) di kanan atas
   - Klik **Environments** → **Create Environment**
   - Nama: `Booking Room Local`
   - Tambahkan variable:
     - `base_url` = `http://localhost:8000/api.php`
     - `token` = (kosongkan dulu, akan otomatis terisi)
   - Save

---

## 🧪 Test Endpoints Satu per Satu

### ✅ STEP 1: Test Root Endpoint (Pastikan API Hidup)

**Request:**
```
GET http://localhost:8000/api.php
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Booking Room API",
  "version": "1.0.0",
  "endpoints": {
    "POST /auth/register": "Register new user",
    "POST /auth/login": "Login user",
    ...
  }
}
```

---

## 🔐 STEP 2: Authentication

### 1. Register User Baru

**Request:**
```
POST http://localhost:8000/api.php/auth/register
Content-Type: application/json

Body (raw JSON):
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123"
}
```

**Expected Response (201 Created):**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "token": "eyJhbGci...",
    "user": {
      "id": 1,
      "name": "Test User",
      "email": "test@example.com",
      "role_id": 2
    }
  }
}
```

**📝 Copy token dari response!**

---

### 2. Login User

**Request:**
```
POST http://localhost:8000/api.php/auth/login
Content-Type: application/json

Body (raw JSON):
{
  "email": "test@example.com",
  "password": "password123"
}
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJhbGci...",
    "user": { ... }
  }
}
```

**📝 Copy token ini dan gunakan untuk request selanjutnya!**

---

### 3. Get Current User (Test Token)

**Request:**
```
GET http://localhost:8000/api.php/auth/me
Authorization: Bearer YOUR_TOKEN_HERE
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "Test User",
      "email": "test@example.com",
      "role_id": 2
    }
  }
}
```

---

## 🏢 STEP 3: Rooms CRUD

### 1. List All Rooms

**Request:**
```
GET http://localhost:8000/api.php/rooms
```

**Query Parameters (Optional):**
- `page=1`
- `per_page=10`
- `search=meeting`

**Expected Response (200 OK):**
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
      "total": 5,
      "total_pages": 1,
      "has_next": false,
      "has_prev": false
    }
  }
}
```

---

### 2. Get Room Details

**Request:**
```
GET http://localhost:8000/api.php/rooms/1
```

**Expected Response (200 OK):**
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

### 3. Create Room (Admin Only) 🔒

**⚠️ PENTING: Endpoint ini butuh Admin privileges!**

Untuk test endpoint ini, Anda perlu membuat user admin dulu:

**Cara 1: Via Database (Recommended)**
```sql
-- Buat user admin baru
INSERT INTO users (name, email, password, role_id, created_at) 
VALUES ('Admin User', 'admin@example.com', '$2y$10$YourHashedPassword', 1, NOW());

-- ATAU update user yang sudah ada menjadi admin
UPDATE users SET role_id = 1 WHERE email = 'test@example.com';
```

**Cara 2: Register via API lalu update di database**
1. Register user baru via API
2. Update role_id menjadi 1 di database
3. Login ulang untuk dapat token admin

**Request:**
```
POST http://localhost:8000/api.php/rooms
Authorization: Bearer ADMIN_TOKEN_HERE
Content-Type: application/json

Body (raw JSON):
{
  "name": "Conference Room B",
  "location": "Floor 2",
  "capacity": 20
}
```

**Expected Response (201 Created):**
```json
{
  "success": true,
  "message": "Room created successfully",
  "data": []
}
```

**Error jika bukan admin (403):**
```json
{
  "success": false,
  "message": "Admin access required"
}
```

---

## 📅 STEP 4: Bookings CRUD

### 1. List Bookings (Butuh Token)

**Request:**
```
GET http://localhost:8000/api.php/bookings
Authorization: Bearer YOUR_TOKEN_HERE
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "bookings": [
      {
        "id": 1,
        "user_id": 1,
        "user_name": "Test User",
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

### 2. Get Booking Details

**Request:**
```
GET http://localhost:8000/api.php/bookings/1
Authorization: Bearer YOUR_TOKEN_HERE
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "booking": { ... }
  }
}
```

---

### 3. Create Booking

**Request:**
```
POST http://localhost:8000/api.php/bookings
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json

Body (raw JSON):
{
  "room_id": 1,
  "start_time": "2024-12-25 09:00:00",
  "end_time": "2024-12-25 11:00:00"
}
```

**Expected Response (201 Created):**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "booking_id": 15
  }
}
```

**Error jika room sudah dibooking (409 Conflict):**
```json
{
  "success": false,
  "message": "Room already booked for the selected time"
}
```

---

### 4. Update Booking Status (Admin Only) 🔒

**Request:**
```
PUT http://localhost:8000/api.php/bookings/1/status
Authorization: Bearer ADMIN_TOKEN_HERE
Content-Type: application/json

Body (raw JSON):
{
  "status": "approved"
}
```

**Valid status:** `approved`, `rejected`, `pending`

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Booking status updated successfully",
  "data": []
}
```

---

### 5. Delete Booking

**Request:**
```
DELETE http://localhost:8000/api.php/bookings/1
Authorization: Bearer YOUR_TOKEN_HERE
```

**Note:** User bisa hapus bookingnya sendiri, Admin bisa hapus semua booking.

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Booking deleted successfully",
  "data": []
}
```

---

## ⏰ STEP 5: Timeslots CRUD

### 1. List All Timeslots

**Request:**
```
GET http://localhost:8000/api.php/timeslots
```

**Expected Response (200 OK):**
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

### 2. Get Timeslot Details

**Request:**
```
GET http://localhost:8000/api.php/timeslots/1
```

**Expected Response (200 OK):**
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

### 3. Create Timeslot (Admin Only) 🔒

**Request:**
```
POST http://localhost:8000/api.php/timeslots
Authorization: Bearer ADMIN_TOKEN_HERE
Content-Type: application/json

Body (raw JSON):
{
  "name": "Afternoon Slot",
  "start_time": "13:00:00",
  "end_time": "17:00:00"
}
```

**Expected Response (201 Created):**
```json
{
  "success": true,
  "message": "Timeslot created successfully",
  "data": []
}
```

---

### 4. Update Timeslot (Admin Only) 🔒

**Request:**
```
PUT http://localhost:8000/api.php/timeslots/1
Authorization: Bearer ADMIN_TOKEN_HERE
Content-Type: application/json

Body (raw JSON):
{
  "name": "Afternoon Slot Updated",
  "start_time": "13:00:00",
  "end_time": "18:00:00"
}
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Timeslot updated successfully",
  "data": []
}
```

---

### 5. Delete Timeslot (Admin Only) 🔒

**Request:**
```
DELETE http://localhost:8000/api.php/timeslots/1
Authorization: Bearer ADMIN_TOKEN_HERE
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Timeslot deleted successfully",
  "data": []
}
```

---

## 🎯 Tips Testing di Postman

### 1. Menggunakan Environment Variables

Agar tidak perlu copy-paste token manual:

**Setup:**
1. Buka Environment settings
2. Buat variable `token`
3. Di request **Login**, tambahkan **Tests** tab:
   ```javascript
   if (pm.response.code === 200) {
       var jsonData = pm.response.json();
       pm.environment.set("token", jsonData.data.token);
   }
   ```

**Gunakan:**
Di Authorization header:
```
Authorization: Bearer {{token}}
```

---

### 2. Cara Set Authorization di Postman

**Method 1 (Manual):**
1. Pilih tab **Headers**
2. Add key: `Authorization`
3. Value: `Bearer YOUR_TOKEN_HERE`

**Method 2 (Auto):**
1. Pilih tab **Authorization**
2. Type: **Bearer Token**
3. Token: `{{token}}` (jika pakai environment)

---

### 3. Testing Sequence yang Benar

```
1. GET /api.php (test API hidup)
2. POST /auth/register (daftar user)
3. POST /auth/login (dapat token) → SAVE TOKEN!
4. GET /auth/me (test token valid)
5. GET /rooms (list rooms)
6. POST /bookings (buat booking)
7. GET /bookings (lihat bookings)
8. ... dst
```

---

## ⚠️ Common Errors & Solutions

### Error 401 - Unauthorized
```json
{
  "success": false,
  "message": "Invalid or expired token"
}
```
**Solution:** Login ulang untuk dapat token baru.

---

### Error 403 - Forbidden
```json
{
  "success": false,
  "message": "Admin access required"
}
```
**Solution:** Endpoint ini butuh admin. Update `role_id = 1` di database.

---

### Error 404 - Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```
**Solution:** Cek ID yang digunakan, pastikan data ada di database.

---

### Error 409 - Conflict
```json
{
  "success": false,
  "message": "Room already booked for the selected time"
}
```
**Solution:** Ganti waktu booking atau pilih room lain.

---

### Dapat HTML instead of JSON
**Problem:** Response berupa HTML page instead of JSON.

**Solution:**
- Pastikan URL menggunakan `/api.php/...` bukan `/api/...`
- Cek server masih running
- Pastikan URL benar: `http://localhost:8000/api.php/rooms`

---

## 📊 Testing Checklist

Gunakan checklist ini untuk memastikan semua endpoint sudah ditest:

### Authentication
- [ ] POST /auth/register
- [ ] POST /auth/login
- [ ] GET /auth/me

### Rooms
- [ ] GET /rooms (list)
- [ ] GET /rooms/{id} (detail)
- [ ] POST /rooms (create - admin)

### Bookings
- [ ] GET /bookings (list)
- [ ] GET /bookings/{id} (detail)
- [ ] POST /bookings (create)
- [ ] PUT /bookings/{id}/status (update - admin)
- [ ] DELETE /bookings/{id} (delete)

### Timeslots
- [ ] GET /timeslots (list)
- [ ] GET /timeslots/{id} (detail)
- [ ] POST /timeslots (create - admin)
- [ ] PUT /timeslots/{id} (update - admin)
- [ ] DELETE /timeslots/{id} (delete - admin)

---

## 🔥 Quick Test Script (All Endpoints)

Copy-paste URL ini ke Postman satu per satu:

```
# Test API
GET http://localhost:8000/api.php

# Auth
POST http://localhost:8000/api.php/auth/register
POST http://localhost:8000/api.php/auth/login
GET http://localhost:8000/api.php/auth/me

# Rooms
GET http://localhost:8000/api.php/rooms
GET http://localhost:8000/api.php/rooms/1
POST http://localhost:8000/api.php/rooms

# Bookings
GET http://localhost:8000/api.php/bookings
GET http://localhost:8000/api.php/bookings/1
POST http://localhost:8000/api.php/bookings
PUT http://localhost:8000/api.php/bookings/1/status
DELETE http://localhost:8000/api.php/bookings/1

# Timeslots
GET http://localhost:8000/api.php/timeslots
GET http://localhost:8000/api.php/timeslots/1
POST http://localhost:8000/api.php/timeslots
PUT http://localhost:8000/api.php/timeslots/1
DELETE http://localhost:8000/api.php/timeslots/1
```

---

## 🎓 Summary

✅ **16 Endpoints** telah dibuat dan siap ditest
✅ **JWT Authentication** untuk security
✅ **Role-based Access** (User & Admin)
✅ **Validation & Error Handling** lengkap
✅ **CORS enabled** untuk cross-origin requests
✅ **Activity Logging** untuk tracking

Selamat testing! 🚀
