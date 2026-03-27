<?php
session_start();
include "../config/database.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


function calculateFine($days) {
    if ($days <= 0) return 0;

    if ($days <= 3) return $days * 10;
    if ($days <= 7) return (3 * 10) + (($days - 3) * 20);
    return (3 * 10) + (4 * 20) + (($days - 7) * 50);
}

/* Messages */
$message = $_SESSION['message'] ?? "";
unset($_SESSION['message']);

/* Dashboard stats */
$totalBorrowed = $conn->query("SELECT COUNT(*) AS total FROM loans WHERE user_id='$user_id' AND return_date IS NULL")->fetch_assoc()['total'];
$overdue = $conn->query("SELECT COUNT(*) AS total FROM loans WHERE user_id='$user_id' AND return_date IS NULL AND due_date < CURDATE()")->fetch_assoc()['total'];
$returned = $conn->query("SELECT COUNT(*) AS total FROM loans WHERE user_id='$user_id' AND return_date IS NOT NULL")->fetch_assoc()['total'];


$liveOverdueFines = 0;
$overdueLoans = $conn->query("
  SELECT GREATEST(DATEDIFF(CURDATE(), due_date), 0) AS days_overdue
  FROM loans 
  WHERE user_id=$user_id AND return_date IS NULL
");
while($row = $overdueLoans->fetch_assoc()) {
  $liveOverdueFines += calculateFine($row['days_overdue']);
}
$overdueLoans->close();

$storedUnpaidFines = 0;
$unpaidFines = $conn->query(" SELECT SUM(fine_amount) AS total FROM loans WHERE user_id=$user_id AND fine_paid=0 "); 
$storedUnpaidFines = $unpaidFines->fetch_assoc()['total'] ?? 0;
$unpaidFines->close();

$totalFine = $liveOverdueFines + $storedUnpaidFines;
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

<div class="navbar" style="display: flex; justify-content: space-between; align-items: center;">
    <div style="display: flex; align-items: center;">
        <h1 style="font-size: 24px; margin-right: 20px;">👩‍🎓 Student Dashboard</h1>
    </div>
    <div>
        <a href="../index.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: bold;">🏠 Home</a>
        <a href="borrow.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: bold;">📖 Borrow</a>
        <a href="hold.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: bold;">⏳ Holds</a>
        <a href="profile.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: bold;">👤 Profile</a>
        <a href="../auth/logout.php" class="logout" style="color: #ff6b6b; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; text-decoration: none; font-weight: bold;" onclick="return confirm('Are you sure you want to logout?')">🚪 Logout</a>
    </div>
</div>

<div class="container">

<h2>Student Dashboard</h2>
<p>Manage your borrowed books and discover new ones.</p>

<?php if($message != ""): ?>
<div class="alert-success"><?php echo $message; ?></div>
<?php endif; ?>

<div class="fine-card" style="background: linear-gradient(135deg, #ff6b6b, #ee5a52); color: white; padding: 20px; border-radius: 12px; text-align: center; margin-bottom: 30px; box-shadow: 0 8px 25px rgba(255,107,107,0.3);">
    <h3 style="margin: 0 0 10px 0;">⚠️ Total Outstanding Fines</h3>
    <h2 style="font-size: 32px; margin: 0;"><?= number_format($totalFine) ?> KES</h2>
</div>

<!-- Modern Statistics Cards -->
<div class="stats-modern" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card borrowed-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 16px; text-align: center; box-shadow: 0 10px 30px rgba(102,126,234,0.3);">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 10px;">📚 Currently Borrowed</div>
        <h2 style="font-size: 36px; margin: 0;"><?= $totalBorrowed ?></h2>
    </div>
    <div class="stat-card returned-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 25px; border-radius: 16px; text-align: center; box-shadow: 0 10px 30px rgba(240,147,251,0.3);">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 10px;">✅ Returned Books</div>
        <h2 style="font-size: 36px; margin: 0;"><?= $returned ?></h2>
    </div>
    <div class="stat-card overdue-card" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #d35400; padding: 25px; border-radius: 16px; text-align: center; box-shadow: 0 10px 30px rgba(252,182,159,0.3);">
        <div style="font-size: 14px; margin-bottom: 10px;">⚠️ Overdue Books</div>
        <h2 style="font-size: 36px; margin: 0;"><?= $overdue ?></h2>
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
<h2 style="color: #2e7d32; font-size: 24px; margin: 40px 0 20px 0; border-bottom: 3px solid #c8e6c9; padding-bottom: 10px;">📖 Your Borrowed Books</h2>
<table class="table-hover">
<tr>
<th>Book</th>
<th>Loan Date</th>
<th>Due Date</th>
<th>Status</th>
<th>Fine (KES)</th>
<th>Action</th>
</tr>
<?php
$sql = "
SELECT loans.*, books.title,
GREATEST(DATEDIFF(CURDATE(), loans.due_date),0) AS days_overdue
FROM loans
JOIN book_copies ON loans.copy_id = book_copies.id
JOIN books ON book_copies.book_id = books.id
WHERE loans.user_id='$user_id'
ORDER BY loans.loan_date DESC";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
    $status='Borrowed'; $class='borrowed';
    $days = $row['days_overdue'];
    if($row['return_date']){
    $status = "Returned";
    $class = "returned";
    $fine = $row['fine_amount']; // STORED fine
} else {
    if($days > 0){
        $status = "Overdue";
        $class = "overdue";
    } else {
        $status = "Borrowed";
        $class = "borrowed";
    }

    $fine = calculateFine($days); // LIVE fine
}

  echo "<tr>
<td>{$row['title']}</td>
<td>{$row['loan_date']}</td>
<td>{$row['due_date']}</td>
<td><span class='badge {$class}'>{$status}</span></td>
<td>{$fine}</td>
        <td>".(!$row['return_date'] ? "<a class='btn' href='return_book.php?loan_id={$row['id']}' onclick=\"return confirm('Return \\\"{$row['title']}\"? Fines may apply if overdue.');\">Return</a>" : "-")."</td>
</tr>";
}
?>
</table>

<!-- Available Books / Holds -->
<h2>Books You Can Borrow or Place Hold</h2>
<div style="background: white; padding: 25px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); margin-bottom: 40px;">
    <form method="GET" style="display: flex; max-width: 600px; margin: 0 auto;">
        <input type="text" name="search_books" placeholder="🔍 Search books by title, author or ISBN..." value="<?php echo htmlspecialchars($searchTerm); ?>" style="flex: 1; padding: 15px 20px; font-size: 16px; border: none; border-radius: 50px 0 0 50px; outline: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
        <button type="submit" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 15px 30px; border: none; border-radius: 0 50px 50px 0; cursor: pointer; font-size: 16px; font-weight: bold; transition: all 0.3s; box-shadow: 0 5px 15px rgba(102,126,234,0.4);">
            Search
        </button>
    </form>
</div>

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
        echo "<a class='btn' href='borrow.php?book_id={$b['id']}' onclick=\"return confirm('Are you sure you want to borrow this book?');\">Borrow</a>";
    } else {
        echo $hasHold ? "<button class='btn btn-hold' disabled>Pending</button>" : "<a class='btn btn-hold' href='hold.php?book_id={$b['id']}' onclick=\"return confirm('Place a hold on \\\"{$b['title']}\"? It will notify when available.');\">Place Hold</a>";
    }

    echo "</div>";
}
?>
</div>
</div>
</body>
</html>