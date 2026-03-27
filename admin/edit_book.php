<?php

session_start();

include "../config/database.php";

$id = $_GET['id'];

/* fetch book */
$stmt = $conn->prepare("SELECT * FROM books WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();
$stmt->close();


/* update book */
if(isset($_POST['update'])){
    $title = $_POST['title'];
    $isbn = $_POST['isbn'];
    $quantity = (int)$_POST['quantity'];
    
    $stmt = $conn->prepare("UPDATE books SET title=?, isbn=?, Quantity=? WHERE id=?");
    $stmt->bind_param("ssii", $title, $isbn, $quantity, $id);
    if($stmt->execute()){
        echo "Book updated successfully.";
    } else {
        echo "Update failed.";
    }
    $stmt->close();
    exit(header("Location: manage_books.php"));
}
if(isset($_POST['add_copy'])){
    $stmt = $conn->prepare("UPDATE books SET Quantity = Quantity + 1 WHERE id=?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        echo "Copy added successfully.";
    } else {
        echo "Failed to add copy.";
    }
    $stmt->close();
    exit(header("Location: manage_books.php"));
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

<label>Quantity</label>
<input type="number" min="0" name="quantity"> 

<button class="btn" name="update">Update Book</button>


</form>

</div>

</div>

</body>

</html>
