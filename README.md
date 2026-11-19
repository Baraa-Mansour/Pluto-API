# Pluto API

Pluto API is a Laravel REST API for apartment discovery and booking. It connects apartment seekers with owners, with account approval, role-based administration, favourites, ratings, wallet-style balances, booking requests, date changes, and Firebase Cloud Messaging notifications.

## Project Concept

The platform is designed around two main user journeys:

- **Tenants** register, wait for administrator approval, search apartments, save favourites, rate apartments, and create or manage booking requests.
- **Apartment owners** publish apartments, receive booking requests, accept or decline them, and receive updates after bookings are completed or canceled.

A booking moves through a controlled lifecycle:

```text
registered -> accepted -> completed
     |
     +------> declined

registered/accepted -> canceled
```

Creating a booking reserves the calculated stay cost from the tenant's balance. The owner receives the amount when the booking completes, while declining or canceling a request applies the appropriate refund. This makes balance transactions, availability checks, and booking status changes part of the core domain logic.

## Main Features

- Phone-and-password registration and login using Laravel Sanctum
- Administrator approval workflow for new accounts
- Admin and owner authorization middleware
- Apartment creation and soft deletion
- Governorate, city, area, room, floor, and price filters
- Tenant favourites
- One-rating-per-apartment logic
- Booking availability validation against existing bookings
- Wallet-style balance deduction, refund, and transfer
- Booking accept, decline, cancel, and complete operations
- Booking date-update requests
- Scheduled cleanup and status-transition jobs
- Firebase push notifications for booking events
- RESTful JSON resources for consistent API responses

## Technology Stack

| Area                  | Technology                                                            |
| --------------------- | --------------------------------------------------------------------- |
| Language              | PHP 8.2+                                                              |
| Framework             | Laravel 12                                                            |
| Authentication        | Laravel Sanctum 4                                                     |
| Database              | SQLite by default; MySQL/PostgreSQL can be configured through Laravel |
| ORM                   | Eloquent                                                              |
| API architecture      | REST JSON                                                             |
| Validation            | Laravel Form Request classes                                          |
| Push notifications    | Firebase Cloud Messaging through `kreait/laravel-firebase`            |
| Queues and scheduling | Laravel jobs and scheduler                                            |
| Frontend tooling      | Vite 7, Tailwind CSS 4, Axios                                         |
| Testing               | PHPUnit 11                                                            |

## Architecture

The application follows a layered Laravel structure:

```text
Routes
  -> Controllers
  -> Form Requests (validation)
  -> Services (business rules)
  -> Repositories (database queries)
  -> Eloquent Models
  -> API Resources (response formatting)
```

Key concepts include:

- **Controllers** handle HTTP input and responses.
- **Services** contain domain workflows such as registration, balances, apartments, bookings, ratings, and update requests.
- **Repositories** encapsulate common data access and filtering operations.
- **Models** define Eloquent relationships and lifecycle constants.
- **Resources** shape JSON returned to API clients.
- **Middleware** protects routes and verifies admin, apartment-owner, or booking-owner permissions.
- **Jobs** run periodic booking and update-request maintenance.

## Core Domain Models

- `User`: registered accounts, role, approval status, balance, and FCM token
- `Apartment`: property details, owner, city, price, photo, and ratings
- `Governorate`: regional grouping
- `City`: city linked to an apartment and governorate
- `Booking`: tenant, apartment, date range, total price, and status
- `UpdateRequest`: a proposed change to a booking date range
- `FavouriteApartment`: tenant/apartment favourite relationship
- `Rate`: tenant rating for an apartment
- `PaymentMethod`: payment-method model/migration scaffold (its API route is currently commented out)

## API Overview

All API routes use the `/api` prefix. Most routes require a Sanctum bearer token:

```http
Authorization: Bearer <token>
```

### Authentication

| Method | Endpoint             | Access        | Purpose                            |
| ------ | -------------------- | ------------- | ---------------------------------- |
| `POST` | `/api/auth/register` | Public        | Register a pending account         |
| `POST` | `/api/auth/login`    | Public        | Log in and receive a Sanctum token |
| `POST` | `/api/auth/logout`   | Authenticated | Delete the current access token    |

### User

| Method   | Endpoint                             | Access        | Purpose                             |
| -------- | ------------------------------------ | ------------- | ----------------------------------- |
| `GET`    | `/api/user/profile`                  | Authenticated | Retrieve the current profile        |
| `GET`    | `/api/user/favourites`               | Authenticated | List favourite apartments           |
| `POST`   | `/api/user/favourites/{apartmentId}` | Authenticated | Add an apartment to favourites      |
| `DELETE` | `/api/user/favourites/{apartmentId}` | Authenticated | Remove an apartment from favourites |

### Apartments and Locations

| Method | Endpoint                                   | Access        | Purpose                        |
| ------ | ------------------------------------------ | ------------- | ------------------------------ |
| `GET`  | `/api/apartments`                          | Authenticated | Search apartments with filters |
| `GET`  | `/api/apartments/{apartmentId}`            | Authenticated | Retrieve apartment details     |
| `POST` | `/api/apartments`                          | Authenticated | Create an apartment listing    |
| `POST` | `/api/apartments/{apartmentId}/ratings`    | Authenticated | Rate an apartment              |
| `GET`  | `/api/governorates`                        | Authenticated | List governorates              |
| `GET`  | `/api/governorates/{governorateId}/cities` | Authenticated | List cities in a governorate   |
| `GET`  | `/api/cities`                              | Authenticated | List all cities                |

### Bookings and Updates

| Method   | Endpoint                                         | Access          | Purpose                                          |
| -------- | ------------------------------------------------ | --------------- | ------------------------------------------------ |
| `GET`    | `/api/bookings`                                  | Authenticated   | List the current user's bookings                 |
| `POST`   | `/api/bookings`                                  | Authenticated   | Create a booking request                         |
| `GET`    | `/api/bookings/registrations`                    | Authenticated   | List requests for apartments owned by the user   |
| `PATCH`  | `/api/bookings/{bookingId}/accept`               | Apartment owner | Accept a booking request                         |
| `PATCH`  | `/api/bookings/{bookingId}/decline`              | Apartment owner | Decline a booking request and refund the tenant  |
| `PATCH`  | `/api/bookings/{bookingId}/cancel`               | Booking tenant  | Cancel an active booking or request              |
| `PUT`    | `/api/bookings/{bookingId}/update`               | Booking tenant  | Request a date-range update                      |
| `GET`    | `/api/bookings/updateRequests`                   | Authenticated   | List update requests visible to the current user |
| `PUT`    | `/api/bookings/updateRequests/{updateRequestId}` | Authenticated   | Accept a booking update                          |
| `DELETE` | `/api/bookings/updateRequests/{updateRequestId}` | Authenticated   | Delete a booking update request                  |

### Administration

| Method   | Endpoint                        | Access | Purpose                        |
| -------- | ------------------------------- | ------ | ------------------------------ |
| `GET`    | `/api/admin/registers`          | Admin  | List pending registrations     |
| `GET`    | `/api/admin/owners`             | Admin  | List apartment owners          |
| `GET`    | `/api/admin/users`              | Admin  | List active users              |
| `GET`    | `/api/admin/report`             | Admin  | Retrieve platform totals       |
| `PATCH`  | `/api/admin/registers/{id}`     | Admin  | Approve a registration         |
| `DELETE` | `/api/admin/registers/{id}`     | Admin  | Remove a pending registration  |
| `POST`   | `/api/admin/wallet/add-balance` | Admin  | Add balance to a user's wallet |
| `DELETE` | `/api/admin/users/{id}`         | Admin  | Mark or remove a user          |

## Scheduled Maintenance

The Laravel scheduler is configured to run three maintenance jobs every minute for development/testing:

- Complete accepted bookings whose stay period has ended
- Decline expired booking registrations
- Delete expired booking-update requests

In production, these frequencies can be changed to daily or more appropriate intervals in `bootstrap/app.php`.

Run the Laravel scheduler with:

```powershell
php artisan schedule:work
```

## Installation

### Requirements

- PHP 8.2 or newer
- Composer
- Node.js and npm
- A database supported by Laravel (SQLite works out of the box)
- Optional Firebase credentials for push notifications

### Setup

```powershell
git clone <repository-url>
cd "Pluto API"
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

For SQLite, create the configured database file if it does not exist:

```powershell
New-Item -ItemType File database/database.sqlite -Force
php artisan migrate
```

The default `.env.example` uses SQLite and database-backed sessions, cache, and queues. Update `DB_*`, mail, storage, and queue settings when deploying to another environment.

### Run Development Services

The included Composer `dev` script runs the application, queue listener, Pail, and Vite concurrently:

```powershell
composer run dev
```

The API is normally available at:

```text
http://127.0.0.1:8000
```

Alternatively, start Laravel and the frontend assets separately:

```powershell
php artisan serve
npm run dev
```

## Firebase Configuration

Booking notifications use Firebase Cloud Messaging. Configure Firebase credentials through the package normally used by `kreait/laravel-firebase`, and provide a `FIREBASE_CREDENTIALS` value when your deployment requires an explicit credentials path or JSON configuration.

A login request can store an optional `fcm_token`. Booking events send notifications to users who have a registered FCM token.

## Queue Processing

Although the default environment uses the database queue connection, scheduled jobs can be run through the Laravel scheduler. For queued job deployment, run:

```powershell
php artisan queue:work
```

## Storage

Profile images, ID images, and apartment photos are stored through Laravel's public storage system. After configuring the disk, expose stored files with:

```powershell
php artisan storage:link
```

## Testing

Run the PHPUnit suite with:

```powershell
php artisan test
```

```powershell
composer test
```

The repository contains the Laravel example test structure; feature and unit test coverage should be expanded for authentication, booking rules, balances, middleware, and update workflows.

## Security Notes

- Protect the `.env` file and never commit credentials or private Firebase keys.
- Always use HTTPS in production.
- Run migrations and scheduled tasks under an appropriate database account and OS user.
- Rotate Sanctum tokens and review access policies regularly.
- Validate file uploads and storage paths with Laravel validation and server configuration.

## License

This project is built on Laravel. Add the repository's license terms here if a specific license is intended.
