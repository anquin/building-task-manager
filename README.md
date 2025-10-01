# Building Task Management API

A simple **Laravel-based REST API** for task management, containerized with Docker.

---

## 🚀 Getting Started

### Prerequisites

Make sure you have the following installed on your machine:

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

---

### 🔧 Setup & Run

To build and start the application:

```bash
docker-compose up -d --build
```

This will start the following containers:

- **laravel_app** – Laravel application container (PHP-FPM)
- **laravel_db** – PostgreSQL database
- **laravel_nginx** – Nginx web server

---

### ⚠️ First-Time Setup

On the first run, `php-fpm` will fail to start. This is expected, as the Laravel project needs some initial configuration. The container will stay alive so you can access its shell and run setup commands.

1. Copy the example `.env` file:
   ```bash
   cp src/.env.example src/.env
   ```

2. Install Composer dependencies:
   ```bash
   docker exec -it laravel_app composer install --no-interaction --prefer-dist
   ```

3. Generate the Laravel application key:
   ```bash
   docker exec -it laravel_app php artisan key:generate
   ```

4. Run database migrations and seeders:
   ```bash
   docker exec -it laravel_app php artisan migrate --seed
   ```

5. Generate Swagger documentation:
   ```bash
   docker exec -it laravel_app php artisan l5-swagger:generate
   ```

6. Change runtime directories permissions:

   ```bash
   find src/storage/ -type d -exec chmod 777 {} +
   find src/bootstrap/cache -type d -exec chmod 777 {} +
   find src/database/ -type d -exec chmod 777 {} +
   ```

6. Restart the container after setup:
   ```bash
   docker-compose restart app
   ```

Once everything is up, the API will be available at:

- 📡 **API Base URL:** http://localhost:8000  
- 📘 **Swagger UI:** http://localhost:8000/api/documentation

> ⚠️ These containers are intended for **development use only** and are not optimized for production environments.

---

## 🏗️ Seeded Test Data

When the application is seeded, it includes the following:

### ➤ Building
```json
{
  "id": "6d92e1c3-61c7-4bcc-ba30-532a6193a332"
}
```

### ➤ Users

- **Owner**
  - ID: `518c5881-6e37-4394-937d-554537a50a3e`
  - Email: `admin@example.com`
  - Role: `owner`

- **Employee**
  - ID: `b7d5066b-78fc-48ca-9d0f-4a41f491a287`
  - Email: `employee@example.com`
  - Role: `employee`

---

## 🔐 Authentication

All API endpoints require authentication using a **Bearer Token**.

### ➤ Generate Token

1. Access Laravel Tinker inside the container:

   ```bash
   docker exec -it laravel_app php artisan tinker
   ```

2. Run the following inside Tinker:

   ```php
   $user = App\Models\User::where('email', 'owner@example.com')->first();
   $token = $user->createToken('dev-token');
   echo $token->plainTextToken;
   ```

3. Use the printed token in your requests:

   ```http
   Authorization: Bearer <your_token_here>
   ```

---

## 🧪 API Overview

Currently, the API provides **task-related endpoints**.

> 🛡️ All endpoints require authentication.

Detailed API documentation is available via Swagger:
> 📘 http://localhost:8000/api/documentation

---

## 🧰 Development Notes

- The Laravel source code is mounted from the local `src/` directory.
- Code changes are automatically synced inside the container.
- View logs in real-time using:

```bash
docker-compose logs -f
```

---

## ✅ Running Tests

To run Laravel’s test suite:

```bash
docker exec -it laravel_app php artisan test
```

---

## 📦 Useful Commands

| Task                      | Command                                                                 |
|---------------------------|-------------------------------------------------------------------------|
| Run migrations            | `docker exec -it laravel_app php artisan migrate`                       |
| Run seeders               | `docker exec -it laravel_app php artisan db:seed`                       |
| Access Laravel Tinker     | `docker exec -it laravel_app php artisan tinker`                        |
| Generate Swagger docs     | `docker exec -it laravel_app php artisan l5-swagger:generate`           |
| Rebuild containers        | `docker-compose up -d --build`                                          |
| Stop containers           | `docker-compose down`                                                   |
| Enter app container shell | `docker exec -it laravel_app bash`                                      |

---

