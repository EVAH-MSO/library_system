<?php

session_start();
include "../config/database.php";

/* only librarians allowed */
if(!isset($_SESSION['role']) || $_SESSION['role']!="librarian"){
    die("Access denied");
}

$message = "";

if(isset($_POST['add_book'])){

$title = mysqli_real_escape_string($conn,$_POST['title']);
$author = mysqli_real_escape_string($conn,$_POST['author']);
$isbn = mysqli_real_escape_string($conn,$_POST['isbn']);
$copies = intval($_POST['copies']);

/* -------- IMAGE UPLOAD -------- */

$image_name = $_FILES['image']['name'];
$tmp_name = $_FILES['image']['tmp_name'];

$upload_path = "../images/books/".$image_name;

move_uploaded_file($tmp_name,$upload_path);

/* -------- AUTHOR CHECK -------- */

$check_author = $conn->query("SELECT id FROM authors WHERE name='$author'");

if($check_author->num_rows > 0){
    $author_id = $check_author->fetch_assoc()['id'];
}
else{
    $conn->query("INSERT INTO authors(name) VALUES('$author')");
    $author_id = $conn->insert_id;
}

/* -------- INSERT BOOK -------- */

$conn->query("
INSERT INTO books(title,author_id,isbn,image)
VALUES('$title','$author_id','$isbn','$image_name')
");

$book_id = $conn->insert_id;

/* -------- INSERT COPIES -------- */

for($i=1;$i<=$copies;$i++){

$conn->query("
INSERT INTO book_copies(book_id,copy_number,status)
VALUES('$book_id','$i','available')
");

}

$message="Book added successfully!";

}

?>

<!DOCTYPE html>
<html>

<head>

<title>Add Book</title>
<link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="navbar">

<h1>Librarian Panel</h1>

<div>
<a href="dashboard.php">Dashboard</a>
<a href="manage_books.php">Manage Books</a>
<a href="../auth/logout.php">Logout</a>
</div>

</div>


<div class="container">

<h2>Add New Book</h2>

<?php if($message!=""){ ?>
<p style="color:green;"><?php echo $message; ?></p>
<?php } ?>

<form method="POST" enctype="multipart/form-data">

<label>Book Title</label><br>
<input type="text" name="title" required><br><br>

<label>Author Name</label><br>
<input type="text" name="author" required><br><br>

<label>ISBN</label><br>
<input type="text" name="isbn" required><br><br>

<label>Number of Copies</label><br>
<input type="number" name="copies" min="1" required><br><br>

<label>Book Cover Image</label><br>
<input type="file" name="image" accept="image/*" required><br><br>

<button class="btn" type="submit" name="add_book">Add Book</button>

</form>

</div>

</body>

</html>