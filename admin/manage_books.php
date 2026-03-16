<?php

session_start();

include "../config/database.php";

/* ensure librarian access */
if($_SESSION['role'] != "librarian"){

die("Access denied");

}

?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Books</title>

<link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="navbar">

<h1>Librarian Panel</h1>

<div>

<a href="dashboard.php">Dashboard</a>
<a href="add_book.php">Add Book</a>
<a href="../auth/logout.php">Logout</a>

</div>

</div>


<div class="container">

<h2>Library Books</h2>

<table>

<tr>

<th>Image</th>
<th>Title</th>
<th>Author</th>
<th>ISBN</th>
<th>Actions</th>

</tr>

<?php

/* join books with authors */
$sql = "SELECT books.*, authors.name as author
FROM books
JOIN authors ON books.author_id = authors.id";

$result = $conn->query($sql);


/* display rows */
while($row = $result->fetch_assoc()){

?>

<tr>

<td>
<img src="../images/<?php echo $row['image']; ?>" width="60">
</td>

<td><?php echo $row['title']; ?></td>

<td><?php echo $row['author']; ?></td>

<td><?php echo $row['isbn']; ?></td>

<td>

<a class="btn" href="edit_book.php?id=<?php echo $row['id']; ?>">Edit</a>

<a class="btn" href="delete_book.php?id=<?php echo $row['id']; ?>">Delete</a>

</td>

</tr>

<?php

}

?>

</table>

</div>

</body>

</html>