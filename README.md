# Pluto API

A Laravel-based REST API for managing users, authentication, and admin operations.

## Base URL

All API endpoints are prefixed with `/api`.

## Authentication

This API uses **Laravel Sanctum** for authentication. Most endpoints require authentication via Bearer token.

**How to Authenticate:**
1. Login using `/api/auth/login` endpoint to receive an authentication token
2. Include the token in the `Authorization` header for protected routes:
   ```
   Authorization: Bearer {your-token-here}
   ```

**Authentication Middleware:**
- `auth:sanctum` - Requires a valid authentication token
- `adminCheck` - Requires authentication AND admin role (`ROLE_ADMIN`)

---

## API Routes

### Authentication Routes

#### 1. Register User

**Endpoint:** `POST /api/auth/register`

**Authentication:** Not required

**Request Body:**
```json
{
  "phone": "0912345678",
  "first_name": "John",
  "last_name": "Doe",
  "password": "password123",
  "profile_image": "<file>",
  "id_image": "<file>",
  "birth_date": "2000-01-01"
}
```

**Request Parameters:**
- `phone` (string, required): Phone number matching pattern `09[0-9]{8}` (10 digits starting with 09). Must be unique.
- `first_name` (string, required): Maximum 255 characters
- `last_name` (string, required): Maximum 255 characters
- `password` (string, required): Minimum 8 characters
- `profile_image` (file, required): Image file (jpeg, png, jpg), maximum 3072 KB (3 MB)
- `id_image` (file, required): Image file (jpeg, png, jpg), maximum 3072 KB (3 MB)
- `birth_date` (date, required): Date format (Y-m-d). Must be at least 18 years ago from current date.

**Success Response:**
- **Status Code:** `201 Created`
```json
{
  "message": "User registered successfully."
}
```

**Error Responses:**
- **Status Code:** `400 Bad Request` - Validation Error
```json
{
  "message": "Validation Error",
  "errors": {
    "phone": ["The phone field is required."],
    "phone": ["The phone has already been taken."],
    "phone": ["The phone format is invalid."],
    "first_name": ["The first name field is required."],
    "password": ["The password must be at least 8 characters."],
    "profile_image": ["The profile image must be an image."],
    "birth_date": ["The birth date must be a date before or equal to 18 years ago."]
  }
}
```

- **Status Code:** `500 Internal Server Error` - Server/Database Error
```json
{
  "message": "Server Error, try again later."
}
```

---

#### 2. Login

**Endpoint:** `POST /api/auth/login`

**Authentication:** Not required

**Request Body:**
```json
{
  "phone": "0912345678",
  "password": "password123"
}
```

**Request Parameters:**
- `phone` (string, required): Phone number matching pattern `09[0-9]{8}`
- `password` (string, required): Minimum 8 characters

**Success Response:**
- **Status Code:** `200 OK`
```json
{
  "message": "Login successful.",
  "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
}
```

**Error Responses:**
- **Status Code:** `400 Bad Request` - Validation Error
```json
{
  "message": "Validation Error",
  "errors": {
    "phone": ["The phone field is required."],
    "phone": ["The phone format is invalid."],
    "password": ["The password field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

- **Status Code:** `401 Unauthorized` - Invalid Credentials
```json
{
  "message": "Invalid phone or password."
}
```

- **Status Code:** `500 Internal Server Error` - Server Error
```json
{
  "message": "Server Error, try again later."
}
```

---

#### 3. Logout

**Endpoint:** `POST /api/auth/logout`

**Authentication:** Required (`auth:sanctum`)

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response:**
- **Status Code:** `200 OK`
```json
{
  "message": "Logout successful."
}
```

**Error Responses:**
- **Status Code:** `401 Unauthorized` - Missing or Invalid Token
```json
{
  "message": "Unauthenticated."
}
```

- **Status Code:** `500 Internal Server Error` - Server Error
```json
{
  "message": "Server Error, try again later."
}
```

---

### User Routes

#### 4. Get User Profile

**Endpoint:** `GET /api/user/profile`

**Authentication:** Required (`auth:sanctum`)

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response:**
- **Status Code:** `200 OK`
```json
{
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "phone": "0912345678",
    "birth_date": "2000-01-01",
    "profile_image": "http://your-domain.com/storage/profile_images/abc123.jpg",
    "id_image": "http://your-domain.com/storage/id_images/xyz789.jpg"
  }
}
```

**Error Responses:**
- **Status Code:** `401 Unauthorized` - Missing or Invalid Token
```json
{
  "message": "Unauthenticated."
}
```

- **Status Code:** `404 Not Found` - User Not Found
```json
{
  "message": "No query results for model [App\\Models\\User] {id}"
}
```

- **Status Code:** `500 Internal Server Error` - Server Error
```json
{
  "message": "Server Error, try again later."
}
```

---

### Admin Routes

All admin routes require authentication (`auth:sanctum`) and admin role (`adminCheck` middleware).

**Error Response for Non-Admin Users:**
- **Status Code:** `403 Forbidden`
```json
{
  "message": "Forbidden"
}
```

---

#### 5. Get All Registers

**Endpoint:** `GET /api/admin/registers`

**Authentication:** Required (`auth:sanctum`, `adminCheck`)

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response:**
- **Status Code:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "phone": "0912345678",
      "birth_date": "01-01-2000",
      "status": "registered"
    },
    {
      "id": 2,
      "first_name": "Jane",
      "last_name": "Smith",
      "phone": "0987654321",
      "birth_date": "15-05-1995",
      "status": "registered"
    }
  ]
}
```

**Empty Response:**
- **Status Code:** `200 OK`
```json
{
  "data": []
}
```

**Error Responses:**
- **Status Code:** `401 Unauthorized` - Missing or Invalid Token
```json
{
  "message": "Unauthenticated."
}
```

- **Status Code:** `403 Forbidden` - Not Admin
```json
{
  "message": "Forbidden"
}
```

- **Status Code:** `500 Internal Server Error` - Server Error
```json
{
  "message": "Server Error, try again later."
}
```

---

#### 6. Get All Users

**Endpoint:** `GET /api/admin/users`

**Authentication:** Required (`auth:sanctum`, `adminCheck`)

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response:**
- **Status Code:** `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "phone": "0912345678",
      "birth_date": "2000-01-01"
    },
    {
      "id": 3,
      "first_name": "Alice",
      "last_name": "Johnson",
      "phone": "0976543210",
      "birth_date": "1998-03-20"
    }
  ]
}
```

**Empty Response:**
- **Status Code:** `200 OK`
```json
{
  "data": []
}
```

**Error Responses:**
- **Status Code:** `401 Unauthorized` - Missing or Invalid Token
```json
{
  "message": "Unauthenticated."
}
```

- **Status Code:** `403 Forbidden` - Not Admin
```json
{
  "message": "Forbidden"
}
```

- **Status Code:** `500 Internal Server Error` - Server Error
```json
{
  "message": "Server Error, try again later."
}
```

---

#### 7. Get All Owners

**Endpoint:** `GET /api/admin/owners`

**Authentication:** Required (`auth:sanctum`, `adminCheck`)

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response:**
- **Status Code:** `200 OK`
```json
{
  "data": [
    {
      "id": 5,
      "first_name": "Bob",
      "last_name": "Wilson",
      "phone": "0965432109",
      "birth_date": "1990-07-12"
    }
  ]
}
```

**Empty Response:**
- **Status Code:** `200 OK`
```json
{
  "data": []
}
```

**Error Responses:**
- **Status Code:** `401 Unauthorized` - Missing or Invalid Token
```json
{
  "message": "Unauthenticated."
}
```

- **Status Code:** `403 Forbidden` - Not Admin
```json
{
  "message": "Forbidden"
}
```

- **Status Code:** `500 Internal Server Error` - Server Error
```json
{
  "message": "Server Error, try again later."
}
```

---

#### 8. Get Report

**Endpoint:** `GET /api/admin/report`

**Authentication:** Required (`auth:sanctum`, `adminCheck`)

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response:**
- **Status Code:** `200 OK`
```json
{
  "data": {
    "total_users": 150,
    "total_owners": 45,
    "total_registers": 12
  }
}
```

**Error Responses:**
- **Status Code:** `401 Unauthorized` - Missing or Invalid Token
```json
{
  "message": "Unauthenticated."
}
```

- **Status Code:** `403 Forbidden` - Not Admin
```json
{
  "message": "Forbidden"
}
```

- **Status Code:** `500 Internal Server Error` - Server Error
```json
{
  "message": "Server Error, try again later."
}
```

---

#### 9. Delete Register

**Endpoint:** `DELETE /api/admin/registers/{id}`

**Authentication:** Required (`auth:sanctum`, `adminCheck`)

**Headers:**
```
Authorization: Bearer {token}
```

**URL Parameters:**
- `id` (integer, required): The ID of the user register to delete

**Success Response:**
- **Status Code:** `200 OK`
```json
{
  "message": "User register deleted successfully"
}
```

**Error Responses:**
- **Status Code:** `401 Unauthorized` - Missing or Invalid Token
```json
{
  "message": "Unauthenticated."
}
```

- **Status Code:** `403 Forbidden` - Not Admin
```json
{
  "message": "Forbidden"
}
```

- **Status Code:** `404 Not Found` - Register Not Found or Not in Registered Status
```json
{
  "message": "No query results for model [App\\Models\\User] {id}"
}
```

- **Status Code:** `500 Internal Server Error` - Server/Database Error
```json
{
  "message": "Server Error, try again later."
}
```

---

#### 10. Accept Register

**Endpoint:** `PATCH /api/admin/registers/{id}`

**Authentication:** Required (`auth:sanctum`, `adminCheck`)

**Headers:**
```
Authorization: Bearer {token}
```

**URL Parameters:**
- `id` (integer, required): The ID of the user register to accept

**Success Response:**
- **Status Code:** `200 OK`
```json
{
  "message": "User register accepted successfully"
}
```

**Error Responses:**
- **Status Code:** `401 Unauthorized` - Missing or Invalid Token
```json
{
  "message": "Unauthenticated."
}
```

- **Status Code:** `403 Forbidden` - Not Admin
```json
{
  "message": "Forbidden"
}
```

- **Status Code:** `404 Not Found` - Register Not Found or Not in Registered Status
```json
{
  "message": "No query results for model [App\\Models\\User] {id}"
}
```

- **Status Code:** `500 Internal Server Error` - Server/Database Error
```json
{
  "message": "Server Error, try again later."
}
```
