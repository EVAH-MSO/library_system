<?php
session_start();
include "../config/database.php";

/* --- Check student login --- */
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* --- Get book ID --- */
if(!isset($_GET['book_id'])){
    $_SESSION['message'] = "Invalid book selection.";
    header("Location: dashboard.php");
    exit;
}

$book_id = intval($_GET['book_id']);

/* --- Check if book already has a pending hold for this user --- */
$check = $conn->query("
    SELECT * 
    FROM holds 
    WHERE user_id='$user_id' 
      AND book_id='$book_id' 
      AND status='pending'
");

if($check->num_rows > 0){
    $_SESSION['message'] = "You already have a pending hold for this book.";
    header("Location: dashboard.php");
    exit;
}

/* --- Insert hold record --- */
$insert = $conn->query("
    INSERT INTO holds (user_id, book_id, hold_date, status)
    VALUES ('$user_id', '$book_id', CURDATE(), 'pending')
");

if($insert){
    $_SESSION['message'] = "Book hold placed successfully!";
} else {
    $_SESSION['message'] = "Failed to place hold. Please try again.";
}

/* --- Redirect back to student dashboard --- */
header("Location: dashboard.php");
exit;
?>