### Health Care APIs
Author: [Pratiksingh Malik](https://darksalmon-rook-437684.hostingersite.com/)

<img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white" alt="Laravel">
<img src="https://img.shields.io/badge/PHP-^8.2-777BB4?logo=php&logoColor=white" alt="PHP">
<img src="https://img.shields.io/badge/MySQL-8.x-green?logo=mysql&logoColor=white" alt="MySQL">

--- 

## Prerequisites

Before getting started, make sure you have:

- PHP >= 8.2
- Composer
- MySQL


## 🛠️ Project setup  

Follow these steps to get the project running locally:  

1. **Clone the repository:**
   ```
   git clone <repository-url>
   cd <project-folder>
   ```

2. **Install PHP dependencies**
   ```
   composer install
   ```

3. **Install Node dependencies**
   ```
   npm install
   ```

4. **Copy the .env file**
    ```
    cp .env.example .env
    ```
    Don’t forget to update your DB credentials and other environment settings inside .env.

5. **Generate application key:**
    ```
    php artisan key:generate
    ```

## Database Setup

1. **Run Migrations:**   
    ```
    php artisan migrate
    ```
    This will create all the tables defined in your migration files.

2. **Run seeders to populate initial data:**
    ```
    php artisan db:seed
    ```
    You can also run both migrations and seeders in a single command
    ```
    php artisan migrate --seed
    ```
    **Tip**: If you want to reset the database and start fresh:
    ```
    php artisan migrate:fresh --seed
    ```

## Development

Start the development server:
```
php artisan serve
```


## License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
