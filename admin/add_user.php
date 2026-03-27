<?php
session_start();
include "../config/database.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "librarian"){
    header("Location: ../auth/login.php");
    exit;
}

/* Add new user */
if(isset($_POST['add'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone = trim($_POST['phone']);
    $student_id = trim($_POST['student_id']);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, student_id, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $password, $phone, $student_id, $role);
    
    if($stmt->execute()){
        $_SESSION['message'] = "User added successfully!";
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Failed to add user. Email may exist.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Add User - Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="navbar">
    <h1>Add New User</h1>
    <a href="dashboard.php" style="color: white; text-decoration: none;">← Back to Dashboard</a>
</div>

<div class="container">
    <?php if(isset($error)): ?>
    <div class="alert" style="background: #ff6b6b; color: white; padding: 10px; border-radius: 5px;"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h2>Create New User Account</h2>
        <form method="POST">
            <label>Name *</label>
            <input type="text" name="name" required>

            <label>Email *</label>
            <input type="email" name="email" required>

            <label>Password *</label>
            <input type="password" name="password" required minlength="8">

            <label>Phone</label>
            <input type="text" name="phone">

            <label>Student ID</label>
            <input type="text" name="student_id">

            <label>Role *</label>
            <select name="role" required>
                <option value="student">Student</option>
                <option value="librarian">Librarian</option>
            </select>

            <button class="btn" name="add">➕ Add User</button>
            <a href="dashboard.php" class="btn" style="background: #6c757d;">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>

