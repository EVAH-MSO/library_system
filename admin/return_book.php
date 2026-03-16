<?php

session_start();

include "../config/database.php";

/* get loan id */
$loan_id = $_GET['loan_id'];


/* find copy id */
$sql = "SELECT copy_id FROM loans WHERE id='$loan_id'";

$result = $conn->query($sql);

$row = $result->fetch_assoc();

$copy_id = $row['copy_id'];


/* mark loan returned */
$conn->query("
UPDATE loans
SET return_date = CURDATE()
WHERE id='$loan_id'
");


/* make copy available again */
$conn->query("
UPDATE book_copies
SET status='available'
WHERE id='$copy_id'
");


header("Location: dashboard.php");

?>