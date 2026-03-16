<?php

/* start session */
session_start();

/* include database */
include "../config/database.php";


/* login logic */
if(isset($_POST['login'])){

$email = $_POST['email'];

$password = md5($_POST['password']);

/* check user */
$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";

$result = $conn->query($sql);

if($result->num_rows == 1){

$user = $result->fetch_assoc();

/* store session */
$_SESSION['user_id'] = $user['id'];

$_SESSION['role'] = $user['role'];


/* redirect depending on role */
if($user['role']=="librarian"){

header("Location: ../admin/dashboard.php");

}else{

header("Location: ../user/dashboard.php");

}

}else{

$error="Invalid login";

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

<input type="email" name="email" required >

<label>Password</label>

<input type="password" name="password" required min-length="8">

<button class="btn" name="login">Login</button>

</form>

</div>

</div>

</body>

</html>