<?php
session_start();
include "../config/database.php";

/* ACCESS CONTROL - Librarian only */
if(!isset($_SESSION['role']) || $_SESSION['role'] != "librarian"){
    die("Access denied");
}

$message = '';
$error = '';

if(isset($_GET['user_id']) && isset($_GET['book_id'])){
    $user_id = (int)$_GET['user_id'];
    $book_id = (int)$_GET['book_id'];

    /* Step 1: Check if active hold exists */
    $stmt = $conn->prepare("SELECT id FROM holds WHERE user_id=? AND book_id=?");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $hold_result = $stmt->get_result();
    
    if($hold_result->num_rows == 0){
        $error = "No active hold found for this user/book.";
    } else {
        $hold_id = $hold_result->fetch_assoc()['id'];
        $stmt->close();

        /* Step 2: Find available copy */
        $stmt = $conn->prepare("SELECT id FROM book_copies WHERE book_id=? AND status='available' LIMIT 1");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $copy_result = $stmt->get_result();
        
        if($copy_result->num_rows == 0){
            $error = "No available copies for this book.";
        } else {
            $copy_id = $copy_result->fetch_assoc()['id'];
            $stmt->close();

            /* Step 3: Create loan */
            $loan_date = date('Y-m-d');
            $due_date = date('Y-m-d', strtotime('+14 days')); // 2 weeks

            $stmt = $conn->prepare("INSERT INTO loans (user_id, copy_id, loan_date, due_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $user_id, $copy_id, $loan_date, $due_date);
            
            if($stmt->execute()){
                /* Step 4: Update copy status */
                $stmt2 = $conn->prepare("UPDATE book_copies SET status='borrowed' WHERE id=?");
                $stmt2->bind_param("i", $copy_id);
                $stmt2->execute();
                $stmt2->close();

                /* Step 5: Delete hold */
                $stmt3 = $conn->prepare("DELETE FROM holds WHERE id=?");
                $stmt3->bind_param("i", $hold_id);
                $stmt3->execute();
                $stmt3->close();

                $message = "Book issued successfully from hold.";
                $stmt->close();
            } else {
                $error = "Failed to create loan.";
            }
        }
    }
} else {
    $error = "Missing user or book ID.";
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Issue Book</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="navbar">
    <h1 style="color: white; font-size: 24px;">📖 Issue Book from Hold</h1>
    <div>
        <a href="dashboard.php" style="color: white; margin-left: 20px;">← Dashboard</a>
        <a href="../auth/logout.php" style="color: #ff4444; margin-left: 20px;">Logout</a>
    </div>
</div>

<div class="container">
<?php if($message): ?>
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;"><?= $message ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;"><?= $error ?></div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="dashboard.php" class="btn" style="background: #0077cc; padding: 12px 24px; font-size: 16px;">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
