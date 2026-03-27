<?php
session_start();
include "../config/database.php";

/* --- Ensure user is logged in --- */
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* --- Validate book_id --- */
if(!isset($_GET['book_id']) || empty($_GET['book_id'])){
    $_SESSION['message'] = "Invalid book selection.";
    header("Location: dashboard.php");
    exit;
}

$book_id = intval($_GET['book_id']);

/* --- STEP 1: Find an available copy (LOCKED SAFE VERSION) --- */
$sql = "
    SELECT id 
    FROM book_copies 
    WHERE book_id = '$book_id' 
    AND status = 'available' 
    LIMIT 1
    FOR UPDATE
";

$result = $conn->query($sql);

if($result && $result->num_rows > 0){

    $copy = $result->fetch_assoc();
    $copy_id = $copy['id'];

    /* --- STEP 2: Mark copy as borrowed --- */
    $update = $conn->query("
        UPDATE book_copies
        SET status = 'borrowed'
        WHERE id = '$copy_id'
    ");

    if(!$update){
        $_SESSION['message'] = "Failed to update book copy.";
        header("Location: dashboard.php");
        exit;
    }

    /* --- STEP 3: Create loan record (IMPORTANT FIX HERE) --- */
    $loan_date = date("Y-m-d");
    $due_date = date("Y-m-d", strtotime("+14 days"));

    $insert = $conn->query("
        INSERT INTO loans (
            user_id,
            copy_id,
            loan_date,
            due_date,
            return_date,
            fine_amount,
            fine_paid
        )
        VALUES (
            '$user_id',
            '$copy_id',
            '$loan_date',
            '$due_date',
            NULL,
            0.00,
            0
        )
    ");

    if(!$insert){
        $_SESSION['message'] = "Failed to create loan record: " . $conn->error;
        header("Location: dashboard.php");
        exit;
    }

    /* --- SUCCESS --- */
    $_SESSION['message'] = "Book borrowed successfully! Due on $due_date.";
    header("Location: dashboard.php");
    exit;

}else{

    /* --- No available copies --- */
    $_SESSION['message'] = "No copies available for this book at the moment.";
    header("Location: dashboard.php");
    exit;
}
?>