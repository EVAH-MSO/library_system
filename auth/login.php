<?php
session_start();
include "../config/database.php";

if(isset($_POST['login'])){

    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    /* fetch user */
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        $storedPassword = $user['password'];

        /* check password */
        if(str_starts_with($storedPassword, '$2y$')) {
            // bcrypt password
            if(password_verify($password, $storedPassword)){
                $loginSuccess = true;
            } else {
                $loginSuccess = false;
            }
        } else {
            // old MD5 password
            if(md5($password) === $storedPassword){
                // upgrade to bcrypt
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $conn->query("UPDATE users SET password='$newHash' WHERE id=".$user['id']);
                $loginSuccess = true;
            } else {
                $loginSuccess = false;
            }
        }

        if($loginSuccess){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // redirect based on role
            if($user['role'] == "librarian"){
                header("Location: ../admin/dashboard.php");
                exit;
            } else {
                header("Location: ../user/dashboard.php");
                exit;
            }
        } else {
            $error = "Invalid login";
        }

    } else {
        $error = "Invalid login";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="navbar">
    <h1>University Library</h1>
</div>

<div class="container">
    <div class="form-box">
        <h2>Login</h2>
        <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required minlength="8">

            <button class="btn" name="login">Login</button>
        </form>
    </div>
</div>

</body>
</html>