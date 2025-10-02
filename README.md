<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo"></a></p>

<p align="center">
  <a href="https://www.postgresql.org">
  <img src="https://img.shields.io/badge/PostgreSQL-336791?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL">
</a>
 &nbsp;&nbsp;➜&nbsp;&nbsp;
  <a href="https://www.mongodb.com">
    <img src="https://img.shields.io/badge/MongoDB-4EA94B?style=for-the-badge&logo=mongodb&logoColor=white" alt="MongoDB">
  </a>
</p>

# Laravel Postgres to MongoDB Migration

This repo demonstrates how easy it is to migrate a Laravel application from a relational database (Postgres) to MongoDB.

The main branch of this repository contains a sample Laravel application that uses Postgres as its database.
The `mongodb` branch contains the same application, but it has been modified to use MongoDB as its database.

### Prerequisites

- PHP 8.2 or higher
- Composer
- Postgres
- MongoDB
- Laravel 12.x
- Laravel MongoDB package (jenssegers/mongodb)
- Laravel UI package (laravel/ui)
- Node.js and npm (for frontend scaffolding)
- Git
- A code editor of your choice (e.g., VSCode, PHPStorm)
- Basic knowledge of Laravel, Postgres, and MongoDB
- Docker and Docker Compose (for simplified setup)
- (Optional) For manual setup, install [PostgreSQL Server](https://www.postgresql.org/download/) locally.
- MongoDB Server can be installed locally, or you can use the MongoDB cloud service [MongoDB Atlas](https://www.mongodb.com/atlas).

### Installation and Setup

**_(TODO: And the tutorial link here when it's ready)_**

#### Quick Setup with Docker

If you have Docker installed, you can get up and running quickly.

We have included a `docker-compose.yml` file to simplify the setup process. For the main branch, it sets up a Postgres
database, so you can test the app before we migrate to MongoDB.

The `mongodb` branch, has its own `docker-compose.yml` file that sets up a MongoDB database.

1. Clone the repository:
   ```bash
    git clone git@github.com:jaymoh/laravel-postgresql-to-mongodb.git
    cd laravel-postgresql-to-mongodb
    ```
2. Copy the `.env.example` file to `.env`:
   ```bash
   cp .env.example .env
   ```
3. Start the Docker containers:
   ```bash
   docker-compose up -d
   ```
4. Run setup commands inside the container:
    ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate --seed
   ```
5. Install and build frontend assets:
    ```bash
   docker-compose exec app npm install
   docker-compose exec app npm run dev
   ```
6. Access the application:
   Open your browser and navigate to `http://localhost:8000`.

#### Manual Setup

If you prefer to set up without Docker:

1. Clone the repository:
   ```bash
   git clone git@github.com:jaymoh/laravel-postgresql-to-mongodb.git
   cd laravel-postgresql-to-mongodb
    ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Set up environment variables:
    ```bash
   cp .env.example .env
   php artisan key:generate
    ```
4. Configure database connection in .env:
    - For Postgres (main branch):
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=root
   DB_PASSWORD=password
    ```
    - For MongoDB (mongodb branch):
    ```env
    DB_CONNECTION=mongodb
    DB_HOST=
    DB_PORT=27017
    DB_DATABASE=laravel
    DB_USERNAME=
    DB_PASSWORD=
    ```
5. Run migrations and seed the database:
   ```bash
   php artisan migrate --seed
   ```
6. Install and build frontend assets:
   ```bash
   npm install
   npm run dev
   ```
7. Serve the application:
    ```bash
    php artisan serve
     ```
8. Access the application:
    Open your browser and navigate to `http://localhost:8000`.

### Switching Between Postgres and MongoDB Versions

To switch between database versions:

1. Checkout the desired branch:
   - For Postgres:
     ```bash
     git checkout main
     ```
   - For MongoDB:
     ```bash
     git checkout mongodb
     ```
2. Follow the setup instructions for the chosen branch. Remember to:
    - Run composer install after switching branches as dependencies may differ.
    - Update your .env file with appropriate database settings.
    - Clear configuration cache with php artisan config:clear
    - Run migrations again if needed: php artisan migrate:fresh --seed

### Application Features
A simple app ( users, blog posts, comments).
- It allows us to demonstrate CRUD operations and relationships in both Postgres and MongoDB.
