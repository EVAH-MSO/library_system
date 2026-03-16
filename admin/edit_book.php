<?php

session_start();

include "../config/database.php";

$id = $_GET['id'];

/* fetch book */
$result = $conn->query("SELECT * FROM books WHERE id='$id'");

$book = $result->fetch_assoc();


/* update book */
if(isset($_POST['update'])){

$title = $_POST['title'];

$isbn = $_POST['isbn'];

$conn->query("UPDATE books
SET title='$title', isbn='$isbn'
WHERE id='$id'");

header("Location: manage_books.php");

}

?>

<!DOCTYPE html>
<html>

<head>

<link rel="stylesheet" href="../css/style.css">

<title>Edit Book</title>

</head>

<body>

<div class="navbar">

<h1>Edit Book</h1>

</div>


<div class="container">

<div class="form-box">

<form method="POST">

<label>Title</label>
<input type="text" name="title" value="<?php echo $book['title']; ?>">

<label>ISBN</label>
<input type="text" name="isbn" value="<?php echo $book['isbn']; ?>">

<button class="btn" name="update">Update</button>

</form>

</div>

</div>

</body>

</html>