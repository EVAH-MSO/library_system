# University Library System - Technical Report

## Project Overview
A PHP/MySQL web application for university library management. Features student borrowing, admin book management, fine calculation, holds system.

**Tech Stack:**
- Backend: PHP 8+, MySQL
- Frontend: HTML5, CSS3, Chart.js
- Environment: XAMPP (Apache + MySQL)

**Current Working Directory:** `c:/xampp/htdocs/library_system`

## File Structure
```
library_system/
├── admin/           # Admin panels
│   ├── dashboard.php
│   ├── edit_book.php
│   ├── add_book.php
│   ├── manage_books.php
│   └── return_book.php
├── auth/            # Authentication
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── user/            # Student portal
│   ├── dashboard.php
│   ├── borrow.php
│   ├── hold.php
│   ├── profile.php
│   └── return_book.php
├── config/          # Database config
│   └── database.php
├── css/             # Styles
│   └── style.css
├── js/              # Scripts
│   └── main.js
├── images/          # Book covers
├── classes/         # OOP Classes (Book, User)
└── index.php        # Landing/search page
```

## Key Features

### 1. **Authentication**
- Role-based login (student/librarian)
- Password hashing (bcrypt/MD5 migration)
- Session management

### 2. **Student Dashboard**
- Borrowed books table with status/fines
- Live overdue count & fine calculation
- Book search & borrow/hold
- Fine card: `live_overdue_fines + stored_unpaid_fines`
- Statistics cards & doughnut chart

### 3. **Admin Dashboard**
- Book/copy management
- User search
- Active loans/holds/overdues
- Statistics & charts
- Issue/return books

### 4. **Fine System**
```php
function calculateFine($days) {
    if ($days <= 3) return $days * 10;
    elseif ($days <= 7) return 30 + ($days-3)*20;
    else return 110 + ($days-7)*50;
}
```
- Tiered: Day 1-3: KES10, 4-7: KES20, 8+: KES50 per day
- Live calculation for current loans
- Stored & unpaid fines tracked

### 5. **Database Schema**
```
books(id, title, isbn, image, Quantity, author_id)
book_copies(id, book_id, copy_number, status)
loans(id, user_id, copy_id, loan_date, due_date, return_date, fine_amount, fine_paid)
holds(id, user_id, book_id, hold_date, status)
users(id, name, email, password, role, phone, student_id)
authors(id, name)
```

## Recent Improvements
1. **Removed login greetings** from navbars (user/dashboard.php, admin/dashboard.php)
2. **Fixed $user_name undefined** - removed variable
3. **admin/edit_book.php** - Fixed malformed input tag
4. **Student fines card** - Added live overdue fines calculation:
   ```
   $liveOverdueFines = SUM(calculateFine(days_overdue)) for active loans
   $storedUnpaidFines = SUM(fine_amount WHERE fine_paid=0)
   $totalFine = live + stored
   ```
5. **Resource management** - Close MySQLi results

## Code Quality
- **Security:** Prepared statements, input sanitization, SQL injection prevention
- **Performance:** Efficient queries, indexed likely (id, user_id, dates)
- **UI/UX:** Responsive, modern gradients, charts, emojis
- **Lint:** No PHP syntax errors (php -l passed)

## Deployment
1. Start XAMPP (Apache + MySQL)
2. Import DB schema to phpMyAdmin
3. Access `http://localhost/library_system`
4. Register/login as student/librarian

## Future Enhancements
- Fine payment system
- Email notifications for holds/overdues
- Book categories/genres
- User roles (faculty/student separation)
- PDF reports/export
- REST API for mobile app

**Report generated:** $(date)
