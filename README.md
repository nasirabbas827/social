# social-final  

A lightweight PHP‑based social networking prototype that lets users register, create posts (with images), comment, like, and manage categories through a simple dashboard. Ideal for learning PHP fundamentals, CRUD operations, and basic front‑end styling.

---

## Overview  

`social-final` demonstrates a full‑stack web application built with vanilla PHP, MySQL, and plain CSS. It includes user authentication, post creation with image uploads, comment handling, and an admin‑style dashboard for managing categories and posts.

---

## Features  

| ✅ | Feature |
|---|---|
| ✔️ | **User Authentication** – Register, login, logout with session handling. |
| ✔️ | **Post Management** – Create, edit, delete posts; upload images per post. |
| ✔️ | **Comments & Likes** – Add comments, like posts, and view counts in real time. |
| ✔️ | **Category System** – Create, edit, delete categories; assign posts to categories. |
| ✔️ | **Dashboard** – Admin‑style overview of posts, categories, and user activity. |
| ✔️ | **Responsive UI** – Simple, clean layout using a custom CSS stylesheet. |
| ✔️ | **Database Schema** – Ready‑to‑import SQL file (`Database/post_db.sql`). |

---

## Tech Stack  

| Layer | Technology |
|---|---|
| **Backend** | PHP 7.x / 8.x |
| **Database** | MySQL (SQL script provided) |
| **Frontend** | HTML5, CSS3 (custom `style.css`) |
| **Server** | Apache / Nginx (any LAMP/LEMP stack) |
| **Version Control** | Git (GitHub) |

---

## Installation  

1. **Clone the repository**  

   ```bash
   git clone https://github.com/your-username/social-final.git
   cd social-final
   ```

2. **Create a MySQL database**  

   ```sql
   CREATE DATABASE social_final;
   ```

3. **Import the schema**  

   ```bash
   mysql -u your_user -p social_final < Database/post_db.sql
   ```

4. **Configure the application**  

   - Open `config.php` and update the database credentials:  

     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'social_final');
     define('DB_USER', 'YOUR_DB_USERNAME');
     define('DB_PASS', 'YOUR_DB_PASSWORD');
     ```

5. **Set up the web server**  

   - Place the project folder inside your web root (e.g., `htdocs` or `www`).
   - Ensure the `posts_pictures/` directory is writable by the web server for image uploads.

6. **Start the server**  

   - For a quick test, you can use PHP’s built‑in server:  

     ```bash
     php -S localhost:8000
     ```

   - Then navigate to `http://localhost:8000/index.php`.

---

## Usage  

| Action | File(s) |
|---|---|
| **Register a new account** | `register.php` |
| **Log in / log out** | `login.php`, `logout.php` |
| **View home feed** | `home.php` (default entry point) |
| **Create a post** | `create_post.php` |
| **Edit / delete a post** | `edit_post.php` |
| **Comment on a post** | `comments.php` |
| **Like a post** | `like.php` |
| **Manage categories** | `manage_categories.php`, `edit_category.php` |
| **Admin dashboard** | `dashboard.php` |
| **Shared utilities** | `navbar.php`, `footer.php`, `config.php` |
| **Styling** | `css/style.css` |
| **Static assets** | `images/`, `posts_pictures/` |

1