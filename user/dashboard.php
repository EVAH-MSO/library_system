<?php
session_start();
include "../config/database.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Messages */
$message = $_SESSION['message'] ?? "";
unset($_SESSION['message']);

/* Dashboard stats */
$totalBorrowed = $conn->query("SELECT COUNT(*) AS total FROM loans WHERE user_id='$user_id' AND return_date IS NULL")->fetch_assoc()['total'];
$overdue = $conn->query("SELECT COUNT(*) AS total FROM loans WHERE user_id='$user_id' AND return_date IS NULL AND due_date < CURDATE()")->fetch_assoc()['total'];
$returned = $conn->query("SELECT COUNT(*) AS total FROM loans WHERE user_id='$user_id' AND return_date IS NOT NULL")->fetch_assoc()['total'];

/* Search */
$searchTerm = $_GET['search_books'] ?? "";
$searchTerm = $conn->real_escape_string($searchTerm);
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="navbar">
    <h1>University Library</h1>
    <div>
        <a href="../index.php">Search Books</a>
        <a href="../auth/logout.php">Logout</a>
    </div>
</div>

<div class="container">

<h2>Student Dashboard</h2>
<p>Manage your borrowed books and discover new ones.</p>

<?php if($message != ""): ?>
<div class="alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="card-grid">
    <div class="card">
        <h3>Currently Borrowed</h3>
        <h2><?php echo $totalBorrowed; ?></h2>
    </div>
    <div class="card">
        <h3>Returned Books</h3>
        <h2><?php echo $returned; ?></h2>
    </div>
    <div class="card">
        <h3>Overdue Books</h3>
        <h2><?php echo $overdue; ?></h2>
    </div>
</div>

<!-- Optional Doughnut Chart -->
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
            data: [<?php echo $totalBorrowed;?>, <?php echo $returned;?>, <?php echo $overdue;?>],
            backgroundColor: ['#1e90ff','#4caf50','#f44336']
        }]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{ legend:{ position:'bottom' } }
    }
});
</script>

<!-- Borrowed Books Table -->
<h2>Your Borrowed Books</h2>
<table class="table-hover">
<tr>
<th>Book</th>
<th>Loan Date</th>
<th>Due Date</th>
<th>Status</th>
<th>Action</th>
</tr>
<?php
$sql = "
SELECT loans.id, books.title, loans.loan_date, loans.due_date, loans.return_date
FROM loans
JOIN book_copies ON loans.copy_id = book_copies.id
JOIN books ON book_copies.book_id = books.id
WHERE loans.user_id='$user_id'
ORDER BY loans.loan_date DESC";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
    $status='Borrowed'; $class='borrowed';
    if($row['return_date']){ $status='Returned'; $class='returned'; }
    if(!$row['return_date'] && strtotime($row['due_date'])<time()){ $status='Overdue'; $class='overdue'; }

    echo "<tr>
    <td>{$row['title']}</td>
    <td>{$row['loan_date']}</td>
    <td>{$row['due_date']}</td>
    <td><span class='badge {$class}'>{$status}</span></td>
    <td>".(!$row['return_date'] ? "<a class='btn' href='return_book.php?loan_id={$row['id']}'>Return</a>" : "-")."</td>
    </tr>";
}
?>
</table>

<!-- Available Books / Holds -->
<h2>Books You Can Borrow or Place Hold</h2>
<form method="GET" class="quick-search">
    <input type="text" name="search_books" placeholder="Search by title, author, ISBN" value="<?php echo htmlspecialchars($searchTerm); ?>">
    <button type="submit">Search</button>
</form>

<div class="book-grid">
<?php
$query = "
SELECT books.*, authors.name AS author,
COUNT(book_copies.id) AS total_copies,
SUM(CASE WHEN book_copies.status='available' THEN 1 ELSE 0 END) AS available_copies
FROM books
JOIN authors ON books.author_id = authors.id
LEFT JOIN book_copies ON book_copies.book_id = books.id
WHERE 1
".($searchTerm!=""?" AND (books.title LIKE '%$searchTerm%' OR authors.name LIKE '%$searchTerm%' OR books.isbn LIKE '%$searchTerm%')":"")."
GROUP BY books.id ORDER BY books.title ASC LIMIT 12";
$availableBooks = $conn->query($query);

while($b = $availableBooks->fetch_assoc()){
    $holdCheck = $conn->query("SELECT * FROM holds WHERE user_id='$user_id' AND book_id='".$b['id']."' AND status='pending'");
    $hasHold = $holdCheck->num_rows>0;

    echo "<div class='book-card'>
        <img src='../images/{$b['image']}' alt='{$b['title']}'>
        <h3>{$b['title']}</h3>
        <p>{$b['author']}</p>
        <p>Available: {$b['available_copies']}</p>";

    if($b['available_copies']>0){
        echo "<a class='btn' href='borrow.php?book_id={$b['id']}'>Borrow</a>";
    } else {
        echo $hasHold ? "<button class='btn btn-hold' disabled>Pending</button>" : "<a class='btn btn-hold' href='hold.php?book_id={$b['id']}'>Place Hold</a>";
    }

    echo "</div>";
}
?>
</div>
</div>
</body>
</html>