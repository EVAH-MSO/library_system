<?php
session_start();
include "../config/database.php";

/* check librarian access */
if(!isset($_SESSION['role']) || $_SESSION['role'] != "librarian"){
    die("Access denied");
}

/* --------- STATISTICS --------- */
$total_books = $conn->query("SELECT COUNT(*) as total FROM books")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$borrowed = $conn->query("SELECT COUNT(*) as total FROM book_copies WHERE status='borrowed'")->fetch_assoc()['total'];
$available = $conn->query("SELECT COUNT(*) as total FROM book_copies WHERE status='available'")->fetch_assoc()['total'];

/* --------- BORROWED BOOKS --------- */
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
    loans.due_date
FROM loans
JOIN users ON loans.user_id = users.id
JOIN book_copies ON loans.copy_id = book_copies.id
JOIN books ON book_copies.book_id = books.id
WHERE loans.return_date IS NULL
ORDER BY loans.due_date ASC
");

/* --------- OVERDUE BOOKS --------- */
$overdue_books = $conn->query("
SELECT 
    users.name,
    books.title,
    book_copies.copy_number,
    loans.due_date
FROM loans
JOIN users ON loans.user_id = users.id
JOIN book_copies ON loans.copy_id = book_copies.id
JOIN books ON book_copies.book_id = books.id
WHERE loans.return_date IS NULL
AND loans.due_date < CURDATE()
");

/* --------- ACTIVE HOLDS --------- */
$holds = $conn->query("
SELECT 
    users.id AS user_id,
    users.name,
    IFNULL(users.email,'N/A') AS email,
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
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
<h1>Librarian Panel</h1>
<div>
<a href="add_book.php">Add Book</a>
<a href="manage_books.php">Manage Books</a>
<a href="../auth/logout.php">Logout</a>
</div>
</div>

<div class="container">

<!-- STATISTICS CARDS -->
<div class="card-grid">
    <div class="card">
        <h3>Total Books</h3>
        <h2><?php echo $total_books; ?></h2>
    </div>
    <div class="card">
        <h3>Total Users</h3>
        <h2><?php echo $total_users; ?></h2>
    </div>
    <div class="card">
        <h3>Borrowed Copies</h3>
        <h2><?php echo $borrowed; ?></h2>
    </div>
    <div class="card">
        <h3>Available Copies</h3>
        <h2><?php echo $available; ?></h2>
    </div>
</div>

<!-- DOUGHNUT CHART -->
<div class="chart-container">
<canvas id="libraryChart"></canvas>
</div>
<script>
const ctx = document.getElementById('libraryChart').getContext('2d');
const borrowedCount = <?php echo $borrowed; ?>;
const availableCount = <?php echo $available; ?>;
const myChart = new Chart(ctx, {

    type: 'doughnut',
    data: {
        labels: ['Borrowed', 'Available'],
        datasets: [{
            label: 'Books',
            data: [borrowedCount, availableCount],
            backgroundColor: ['#ee90d2', '#a469f3'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,   // scales with container
        maintainAspectRatio: false,  // preserves ratio
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<!-- QUICK SEARCH -->
<div class="quick-search">
<form method="GET" action="">
<input type="text" name="search_user" placeholder="Search user by name or email">
<button type="submit">Search</button>
</form>
</div>

<!-- BORROWED BOOKS -->
<h2>Borrowed Books</h2>
<table class="table-hover">
<tr>
<th>User</th>
<th>Email</th>
<th>Phone</th>
<th>Student ID</th>
<th>Book</th>
<th>Copy</th>
<th>Loan Date</th>
<th>Due Date</th>
<th>Status</th>
<th>Action</th>
</tr>
<?php while($row = $active_loans->fetch_assoc()){ 
$statusClass = 'borrowed';
$statusText = 'Borrowed';
if(strtotime($row['due_date']) < time()) { $statusClass='overdue'; $statusText='Overdue'; }
?>
<tr>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['email']; ?></td>
<td><?php echo $row['phone']; ?></td>
<td><?php echo $row['student_id']; ?></td>
<td><?php echo $row['title']; ?></td>
<td><?php echo $row['copy_number']; ?></td>
<td><?php echo $row['loan_date']; ?></td>
<td><?php echo $row['due_date']; ?></td>
<td><span class="badge <?php echo $statusClass;?>"><?php echo $statusText;?></span></td>
<td><a class="btn" href="return_book.php?loan_id=<?php echo $row['loan_id']; ?>">Return</a></td>
</tr>
<?php } ?>
</table>

<!-- OVERDUE BOOKS -->
<h2>Overdue Books</h2>
<table class="table-hover">
<tr>
<th>User</th>
<th>Book</th>
<th>Copy</th>
<th>Due Date</th>
</tr>
<?php while($row = $overdue_books->fetch_assoc()){ ?>
<tr style="color:red;">
<td><?php echo $row['name'];?></td>
<td><?php echo $row['title'];?></td>
<td><?php echo $row['copy_number'];?></td>
<td><?php echo $row['due_date'];?></td>
</tr>
<?php } ?>
</table>

<!-- ACTIVE HOLDS -->
<h2>Active Holds</h2>
<table class="table-hover">
<tr>
<th>User</th>
<th>Email</th>
<th>Book</th>
<th>Hold Date</th>
</tr>
<?php while($row = $holds->fetch_assoc()){ ?>
<tr>
<td><?php echo $row['name'];?></td>
<td><?php echo $row['email'];?></td>
<td><?php echo $row['title'];?></td>
<td><?php echo $row['hold_date'];?></td>
</tr>
<?php } ?>
</table>

</div>
</body>
</html>