<?php
session_start();
include "../config/database.php";

$message = "";

// Handle registration
if(isset($_POST['register'])){

    // Sanitize input
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $student_id = $conn->real_escape_string(trim($_POST['student_id']));
    $password_raw = $_POST['password'];

    // Basic validation
    if(strlen($password_raw) < 8){
        $message = "Password must be at least 8 characters long.";
    } else {

        // Hash password securely
        $password = password_hash($password_raw, PASSWORD_BCRYPT);

        // Check if email already exists
        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if($check->num_rows > 0){
            $message = "Email already exists. Please use another email.";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (name,email,password,phone,student_id,role) VALUES (?,?,?,?,?,?)");
            $role = 'student';
            $stmt->bind_param("ssssss", $name, $email, $password, $phone, $student_id, $role);
            if($stmt->execute()){
                $message = "Registration successful! You can now <a href='login.php'>login</a>.";
            } else {
                $message = "Error registering user. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - University Library</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .register-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 25px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.2);
            background-color: #fff;
        }
        .register-container h2 { text-align:center; margin-bottom:20px; }
        .register-container input[type="text"],
        .register-container input[type="email"],
        .register-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .register-container button {
            width: 100%;
            padding: 12px;
            background-color: #0077cc;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .register-container button:hover {
            background-color: #005fa3;
        }
        .register-container p.message {
            text-align: center;
            font-weight: bold;
            color: green;
        }
        .register-container a { display:block; text-align:center; margin-top:10px; color:#0077cc; text-decoration:none; }
        .register-container a:hover { text-decoration:underline; }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Create Account</h2>

    <?php if($message != ""): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Full Name</label>
        <input type="text" name="name" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Phone Number</label>
        <input type="text" name="phone" required>

        <label>Student ID</label>
        <input type="text" name="student_id" required>

        <label>Password</label>
        <input type="password" name="password" required minlength="8">

        <button type="submit" name="register">Register</button>
    </form>

    <a href="login.php">Already have an account? Login here</a>
</div>

</body>
</html>