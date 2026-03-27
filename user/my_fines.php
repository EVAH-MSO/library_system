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

/* ✅ PAY SINGLE FINE */
if(isset($_GET['pay_id'])){
    $loan_id = intval($_GET['pay_id']);
    $conn->query("UPDATE loans SET fine_paid=1 WHERE id=$loan_id AND user_id=$user_id");
    $_SESSION['message'] = "Fine paid successfully!";
    header("Location: my_fines.php");
    exit;
}

/* ✅ PAY ALL FINES */
if(isset($_GET['pay_all'])){
    $conn->query("UPDATE loans SET fine_paid=1 WHERE user_id=$user_id");
    $_SESSION['message'] = "All fines marked as paid!";
    header("Location: my_fines.php");
    exit;
}

/* ✅ FETCH ONLY UNPAID + ACTIVE FINES */
$loans = $conn->query("
SELECT loans.*, books.title,
GREATEST(DATEDIFF(CURDATE(), loans.due_date),0) AS days_overdue
FROM loans
JOIN book_copies ON loans.copy_id = book_copies.id
JOIN books ON book_copies.book_id = books.id
WHERE loans.user_id=$user_id
AND loans.return_date IS NULL
AND loans.fine_paid = 0
ORDER BY loans.due_date DESC
");

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
<title>My Fines</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="container">
<h2>💰 My Fines</h2>

<?php if(isset($_SESSION['message'])): ?>
<div class="alert-success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
<?php endif; ?>

<table class="table-hover">
<tr>
<th>Book</th>
<th>Days Overdue</th>
<th>Fine (KES)</th>
<th>Action</th>
</tr>

<?php while($l = $loans->fetch_assoc()): 
$days = $l['days_overdue'];
$fine = calculateFine($days);
$total += $fine;
?>
<tr>
<td><?= htmlspecialchars($l['title']) ?></td>
<td><?= $days ?></td>
<td><?= $fine ?></td>
<td>
    <a class="btn"
       href="?pay_id=<?= $l['id'] ?>"
       onclick="return confirm('Pay fine for <?= htmlspecialchars($l['title']) ?>?')">
       💳 Pay
    </a>
</td>
</tr>
<?php endwhile; ?>
</table>

<h3>Total Fine: <?= number_format($total) ?> KES</h3>

<?php if($total > 0): ?>
<br>
<a href="?pay_all=1"
   class="btn"
   onclick="return confirm('Pay ALL fines?')">
   💳 Pay All Fines
</a>
<?php else: ?>
<p>No pending fines 🎉</p>
<?php endif; ?>

</div>

</body>
</html>