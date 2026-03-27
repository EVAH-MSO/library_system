<?php
session_start();
include "../config/database.php";

/* ---------- ACCESS CONTROL ---------- */
if(!isset($_SESSION['role']) || $_SESSION['role'] != "librarian"){
    die("Access denied");
}

/* ---------- FINE CALCULATION (TIERED) ---------- */
function calculateFine($days) {
    if ($days <= 0) return 0;

    if ($days <= 3) {
        return $days * 10;
    } elseif ($days <= 7) {
        return (3 * 10) + (($days - 3) * 20);
    } else {
        return (3 * 10) + (4 * 20) + (($days - 7) * 50);
    }
}

/* ---------- STATISTICS ---------- */
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM books");
$stmt->execute();
$total_books = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt_borrowed = $conn->prepare("SELECT COUNT(*) as total FROM book_copies WHERE status=?");
$status_b = "borrowed";
$stmt_borrowed->bind_param("s", $status_b);
$stmt_borrowed->execute();
$borrowed = $stmt_borrowed->get_result()->fetch_assoc()['total'];
$stmt_borrowed->close();

$stmt_available = $conn->prepare("SELECT COUNT(*) as total FROM book_copies WHERE status=?");
$status_a = "available";
$stmt_available->bind_param("s", $status_a);
$stmt_available->execute();
$available = $stmt_available->get_result()->fetch_assoc()['total'];
$stmt_available->close();

/* ---------- CALCULATE BORROWED / RETURNED / OVERDUE FOR CHART ---------- */
$totalBorrowed = $borrowed;

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM loans WHERE return_date IS NOT NULL");
$stmt->execute();
$returned = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM loans WHERE return_date IS NULL AND due_date < CURDATE()");
$stmt->execute();
$overdue = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

/* ---------- USER SEARCH (SECURE) ---------- */
$search_results = null;
if(isset($_GET['search_user']) && !empty($_GET['search_user'])){
    $search = "%" . trim($_GET['search_user']) . "%";

    $stmt = $conn->prepare("
        SELECT id, name, email, phone, student_id 
        FROM users 
        WHERE name LIKE ? OR email LIKE ?
    ");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $search_results = $stmt->get_result();
}

/* ---------- ACTIVE LOANS (WITH FINES) ---------- */
$active_loans = $conn->query("
SELECT 
    loans.id AS loan_id,
    users.id AS user_id,
    users.name,
    IFNULL(users.email,'N/A') AS email,
    IFNULL(users.phone,'N/A') AS phone,
    IFNULL(users.student_id,'N/A') AS student_id,
    books.title,
    book_copies.copy_number,
    loans.loan_date,
    loans.due_date,
    GREATEST(DATEDIFF(CURDATE(), loans.due_date), 0) AS days_overdue
FROM loans
JOIN users ON loans.user_id = users.id
JOIN book_copies ON loans.copy_id = book_copies.id
JOIN books ON book_copies.book_id = books.id
WHERE loans.return_date IS NULL
ORDER BY loans.due_date ASC
");

/* ---------- OVERDUE BOOKS ---------- */
$overdue_books = $conn->query("
SELECT 
    users.name,
    books.title,
    book_copies.copy_number,
    loans.due_date,
    DATEDIFF(CURDATE(), loans.due_date) AS days_overdue
FROM loans
JOIN users ON loans.user_id = users.id
JOIN book_copies ON loans.copy_id = book_copies.id
JOIN books ON book_copies.book_id = books.id
WHERE loans.return_date IS NULL
AND loans.due_date < CURDATE()
");

/* ---------- ACTIVE HOLDS ---------- */
$holds = $conn->query("
SELECT 
    holds.id,
    users.id AS user_id,
    users.name,
    IFNULL(users.email,'N/A') AS email,
    books.id AS book_id,
    books.title,
    holds.hold_date
FROM holds
JOIN users ON holds.user_id = users.id
JOIN books ON holds.book_id = books.id
ORDER BY holds.hold_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Librarian Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar" style="display: flex; justify-content: space-between; align-items: center;">
    <div style="display: flex; align-items: center;">
        <h1 style="font-size: 24px; margin-right: 20px;">👨‍💼 Librarian Panel</h1>
        <div style="background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 25px;">
            <span style="color: white; font-weight: bold;">👋 Welcome, Librarian</span>
        </div>
    </div>
    <div>
        <a href="add_book.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: bold;">➕ Add Book</a>
        <a href="manage_books.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: bold;">📋 Manage</a>
        <a href="add_user.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: bold;">👥 Add User</a>
        <a href="edit_user.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: bold;">👥 Manage Users</a>

        <a href="../auth/logout.php" class="logout" style="color: #ff6b6b; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; text-decoration: none; font-weight: bold;" onclick="return confirm('Are you sure you want to logout?')">🚪 Logout</a>
    </div>

</div>

<div class="container">

<!-- DASHBOARD TITLE -->
<h2 class="section-title">Dashboard Overview</h2>

<!-- STAT CARDS -->
<div class="card-grid">
    <div class="card">
        <p>Total Books</p>
        <h2><?= $total_books ?></h2>
    </div>
    <div class="card">
        <p>Total Users</p>
        <h2><?= $total_users ?></h2>
    </div>
    <div class="card">
        <p>Borrowed Copies</p>
        <h2><?= $borrowed ?></h2>
    </div>
    <div class="card">
        <p>Available Copies</p>
        <h2><?= $available ?></h2>
    </div>
</div>

<!-- CHART -->
<div class="chart-container">
    <canvas id="dashboardChart"></canvas>
</div>
<script>
const ctx = document.getElementById('dashboardChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Borrowed', 'Returned', 'Overdue'],
        datasets: [{
            data: [<?= $totalBorrowed ?>, <?= $returned ?>, <?= $overdue ?>],
            backgroundColor: ['#6da8d0','#4CAF50','#f44336']
        }]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{ legend:{ position:'bottom' } }
    }
});
</script>

<!-- SEARCH -->
<div class="quick-search">
    <form method="GET">
        <input type="text" name="search_user" placeholder="🔍 Search user by name or email">
        <button type="submit">Search</button>
    </form>
</div>

<?php if($search_results): ?>
<h2 class="section-title">Search Results</h2>
<table class="table-hover">
<tr>
<th>Name</th><th>Email</th><th>Phone</th><th>Student ID</th>
</tr>
<?php while($u = $search_results->fetch_assoc()): ?>
<tr>
<td><?= $u['name'] ?></td>
<td><?= $u['email'] ?></td>
<td><?= $u['phone'] ?></td>
<td><?= $u['student_id'] ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php endif; ?>


<!-- USERS MANAGEMENT -->
<h2 class="section-title">👥 Users Management</h2>
<table class="table-hover">
<tr>
<th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Student ID</th><th>Role</th><th>Action</th>
</tr>
<?php
$users = $conn->query("SELECT * FROM users ORDER BY name ASC");
while($u = $users->fetch_assoc()): ?>
<tr>
<td><?= $u['id'] ?></td>
<td><?= htmlspecialchars($u['name']) ?></td>
<td><?= htmlspecialchars($u['email']) ?></td>
<td><?= $u['phone'] ?></td>
<td><?= htmlspecialchars($u['student_id']) ?></td>
<td><span class="badge <?= $u['role']=='librarian' ? 'admin' : 'student' ?>"><?= ucfirst($u['role']) ?></span></td>
<td><a class="btn" href="edit_user.php?id=<?= $u['id'] ?>">Edit</a></td>
</tr>
<?php endwhile; ?>
</table>

<!-- TOTAL OVERDUE FINES -->
<?php 
$total_overdue_fine = 0;
// Fetch active loans for fine calc separately to avoid pointer issues
$fine_query = $conn->query("
SELECT GREATEST(DATEDIFF(CURDATE(), due_date), 0) AS days_overdue
FROM loans WHERE return_date IS NULL
");
while($fine_row = $fine_query->fetch_assoc()) {
    $total_overdue_fine += calculateFine($fine_row['days_overdue']);
}
$fine_query->close();
?>
<div class="alert alert-warning">
<strong>Total Overdue Fines: KES <?= number_format($total_overdue_fine) ?></strong>
</div>

<!-- ACTIVE LOANS -->
<h2 class="section-title">Borrowed Books</h2>

<table class="table-hover">
<tr>
<th>User</th>
<th>Email</th>
<th>Book</th>
<th>Copy</th>
<th>Due Date</th>
<th>Status</th>
<th>Fine (KES)</th>
<th>Action</th>
</tr>

<?php while($row = $active_loans->fetch_assoc()): 
$days = $row['days_overdue'];
$fine = calculateFine($days);

$status = $days > 0 ? "Overdue" : "Borrowed";
$class = $days > 0 ? "overdue" : "borrowed";
?>

<tr class="<?= $days > 0 ? 'row-overdue' : '' ?>">
<td><?= $row['name'] ?></td>
<td><?= $row['email'] ?></td>
<td><?= $row['title'] ?></td>
<td><?= $row['copy_number'] ?></td>
<td><?= $row['due_date'] ?></td>
<td><span class="badge <?= $class ?>"><?= $status ?></span></td>
<td><strong><?= $fine ?></strong></td>
    <td>
<a class="btn return-btn" href="return_book.php?loan_id=<?= $row['loan_id'] ?>" onclick="return confirm('Return this book to <?= htmlspecialchars($row['name']) ?>?')">Return</a>
</td>
</tr>

<?php endwhile; ?>
</table>

<!-- OVERDUE -->
<h2 class="section-title">Overdue Books</h2>
<table class="table-hover">
<tr>
<th>User</th><th>Book</th><th>Days Late</th>
</tr>
<?php while($row = $overdue_books->fetch_assoc()): ?>
<tr class="row-overdue">
<td><?= $row['name'] ?></td>
<td><?= $row['title'] ?></td>
<td><?= $row['days_overdue'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<!-- HOLDS -->
<h2 class="section-title">Active Holds</h2>
<table class="table-hover">
<tr>
<th>User</th>
<th>Book</th>
<th>Date</th>
<th>Action</th>
</tr>
<?php while($row = $holds->fetch_assoc()): ?>
<tr>
<td><?= $row['name'] ?></td>
<td><?= $row['title'] ?></td>
<td><?= $row['hold_date'] ?></td>
    <td>
<a class="btn issue-btn" href="issue_book.php?user_id=<?= $row['user_id'] ?>&book_id=<?= $row['book_id'] ?>" onclick="return confirm('Issue <?= htmlspecialchars($row['title']) ?> to <?= htmlspecialchars($row['name']) ?>?')">Issue</a>
</td>
</tr>
<?php endwhile; ?>
</table>

</div>
</body>
</html>