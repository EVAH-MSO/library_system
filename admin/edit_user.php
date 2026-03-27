<?php
session_start();
include "../config/database.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != "librarian"){
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
if($id == 0){
    header("Location: dashboard.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if(!$user){
    header("Location: dashboard.php");
    exit;
}

/* Update user */
if(isset($_POST['update'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $student_id = trim($_POST['student_id']);
    $role = $_POST['role'];

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, student_id=?, role=? WHERE id=?");
    $stmt->bind_param("sssssi", $name, $email, $phone, $student_id, $role, $id);
    if($stmt->execute()){
        $_SESSION['message'] = "User updated successfully!";
        header("Location: dashboard.php");
    } else {
        $error = "Update failed.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit User - Admin</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="navbar">
    <h1>Edit User: <?= htmlspecialchars($user['name']) ?></h1>
    <a href="dashboard.php" style="color: white; text-decoration: none;">← Back to Dashboard</a>
</div>

<div class="container">
    <?php if(isset($error)): ?>
    <div class="alert" style="background: #ff6b6b; color: white; padding: 10px; border-radius: 5px;"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h2>Update User Details</h2>
        <form method="POST">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">

            <label>Student ID</label>
            <input type="text" name="student_id" value="<?= htmlspecialchars($user['student_id']) ?>">

            <label>Role</label>
            <select name="role" required>
                <option value="student" <?= $user['role']=='student' ? 'selected' : '' ?>>Student</option>
                <option value="librarian" <?= $user['role']=='librarian' ? 'selected' : '' ?>>Librarian</option>
            </select>

            <button class="btn" name="update">Update User</button>
            <a href="dashboard.php" class="btn" style="background: #6c757d;">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>

