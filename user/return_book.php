```php
<?php
session_start();
include "../config/database.php";

/* ---------- ACCESS CONTROL ---------- */
if(!isset($_SESSION['role']) || $_SESSION['role'] != "librarian"){
    die("Access denied");
}

/* ---------- VALIDATE INPUT ---------- */
if(!isset($_GET['loan_id']) || empty($_GET['loan_id'])){
    die("Invalid loan ID");
}

$loan_id = intval($_GET['loan_id']);

/* ---------- FINE CALCULATION ---------- */
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

/* ---------- GET LOAN DETAILS ---------- */
$stmt = $conn->prepare("
    SELECT copy_id, due_date 
    FROM loans 
    WHERE id = ? AND return_date IS NULL
");
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Loan not found or already returned");
}

$loan = $result->fetch_assoc();
$copy_id = $loan['copy_id'];
$due_date = $loan['due_date'];

/* ---------- CALCULATE DAYS OVERDUE ---------- */
$today = date("Y-m-d");

$days = (strtotime($today) - strtotime($due_date)) / (60 * 60 * 24);
$days = max(0, floor($days));

/* ---------- CALCULATE FINE ---------- */
$fine = calculateFine($days);

/* ---------- UPDATE LOAN ---------- */
$stmt = $conn->prepare("
    UPDATE loans 
    SET 
        return_date = CURDATE(),
        fine_amount = ?
    WHERE id = ?
");
$stmt->bind_param("di", $fine, $loan_id);
$stmt->execute();

/* ---------- UPDATE BOOK COPY ---------- */
$stmt = $conn->prepare("
    UPDATE book_copies 
    SET status = 'available'
    WHERE id = ?
");
$stmt->bind_param("i", $copy_id);
$stmt->execute();

/* ---------- REDIRECT ---------- */
header("Location: dashboard.php");
exit;
?>
```
