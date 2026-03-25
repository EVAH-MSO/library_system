# University Library System

## Overview
A PHP-based web application for managing a university library. Users can register/login to search and view books. Librarians have admin access for managing books.

## Features
- **User Features**:
  - Register/Login/Logout
  - Search books by title, author, ISBN
  - View book details
  - Borrow/Hold/Return books (user dashboard)
- **Admin/Librarian Features**:
  - Add/Edit/Delete books
  - Manage book inventory (dashboard)
  - Process returns
- **Authentication**: Session-based login required to view main content (index.php protected).
- Responsive design with CSS grid for book display.

## Project Structure
```
library_system/
├── index.php          # Protected home/search page
├── book_details.php   # Book details view
├── auth/              # Authentication (login, register, logout)
├── admin/             # Admin dashboard & book management
├── user/              # User dashboard (borrow, holds, returns)
├── classes/           # PHP classes (Book.php, User.php)
├── config/            # Database config
├── css/               # Styles (style.css)
├── js/                # Scripts (main.js)
├── images/            # Book covers
```

## Setup
1. **Requirements**:
   - XAMPP (Apache + MySQL)
   - PHP 7+
   - MySQL database

2. **Installation**:
   ```
   # Place in htdocs (e.g., c:/xampp/htdocs/library_system)
   cd c:/xampp/htdocs/library_system
   ```

3. **Database**:
   - Import schema to MySQL (tables: `books`, `authors`, `users` expected from code).
   - Update `config/database.php` with DB credentials:
     ```
     $host = 'localhost';
     $user = 'root';
     $pass = '';
     $dbname = 'library_db';
     ```

4. **Run**:
   ```
   # Start XAMPP Apache & MySQL
   # Open http://localhost/library_system/index.php
   ```
   - Register/login to access books.

## Recent Changes
- Protected `index.php` content: Requires login (`$_SESSION['user_id']`) to view search/books. Guests redirect to `auth/login.php`.

## Testing Authentication
- No session: → auth/login.php
- Logged in: See search & book grid
- Logout: → login

## Usage
- **Guest**: Sees navbar → Login/Register
- **User**: Home search, dashboard for actions
- **Librarian**: Admin dashboard (role-based access)

## Troubleshooting
- Session issues: Check `session_start()` and cookie settings.
- DB errors: Verify `config/database.php` & tables.
- Images missing: Add to `images/`.

Built/Modified with BLACKBOXAI assistance.

