<?php

/* start session to manage logged in users */
session_start();

/* include database connection */
include "config/database.php";

?>

<!DOCTYPE html>
<html>

<head>

<title>University Library</title>

<!-- connect css -->
<link rel="stylesheet" href="css/style.css">

</head>

<body>

<!-- Navigation bar -->
<div class="navbar">

<h1>University Library</h1>

<div>

<a href="index.php">Home</a>

<?php

/* show login, register or dashboard depending on session */
if(isset($_SESSION['user_id'])){
    echo "<a href='user/dashboard.php'>Dashboard</a>";
    echo "<a href='auth/logout.php'>Logout</a>";
}else{
    echo "<a href='auth/login.php'>Login</a>";
    echo "<a href='auth/register.php'>Register</a>"; // added registration link
}

?>

</div>

</div>

<div class="container">

<h2>Search Library Books</h2>

<!-- search form -->
<form method="GET" class="search-box">

<input type="text" name="search" placeholder="Search by title, author or ISBN">

<button type="submit">Search</button>

</form>

<div class="book-grid">

<?php

/* if search submitted, filter books; otherwise show all books */
$search_sql = "";
if(isset($_GET['search']) && $_GET['search'] != ""){
    $search = $conn->real_escape_string($_GET['search']);
    $search_sql = "WHERE books.title LIKE '%$search%' OR authors.name LIKE '%$search%' OR books.isbn LIKE '%$search%'";
}

/* query books */
$sql = "SELECT books.*, authors.name as author
        FROM books
        JOIN authors ON books.author_id = authors.id
        $search_sql
        ORDER BY books.title ASC";

$result = $conn->query($sql);

if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        ?>
        <div class="book-card">
            <img src="images/<?php echo $row['image']; ?>">
            <h3><?php echo $row['title']; ?></h3>
            <p><?php echo $row['author']; ?></p>
            <a class="btn" href="book_details.php?id=<?php echo $row['id']; ?>">View Details</a>
        </div>
        <?php
    }
}else{
    echo "<p>No books found.</p>";
}

?>

</div>

</div>

</body>
</html>