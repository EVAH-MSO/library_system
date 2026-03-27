<?php
session_start();
include "config/database.php";
?>

<!DOCTYPE html>
<html>
<head>
<title>University Library</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<!-- Navbar -->
<div class="navbar">
    <h1>University Library</h1>
    <div>
        <a href="index.php">Home</a>

        <?php
        if(isset($_SESSION['user_id'])){
            echo "<a href='user/dashboard.php'>Dashboard</a>";
            echo "<a href='auth/logout.php'>Logout</a>";
        } else {
            echo "<a href='auth/login.php'>Login</a>";
            echo "<a href='auth/register.php'>Register</a>";
        }
        ?>
    </div>
</div>

<?php
/* Force login */
if(!isset($_SESSION['user_id'])){
    header("Location: auth/login.php");
    exit();
}
?>

<div class="container">

<h2>Search Library Books</h2>

<!-- Search -->
<form method="GET" class="search-box">
    <input type="text" name="search" placeholder="Search by title, author or ISBN"
           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
    <button type="submit">Search</button>
</form>

<div class="book-grid">

<?php
/* Search filter */
$search_sql = "";
if(isset($_GET['search']) && $_GET['search'] != ""){
    $search = $conn->real_escape_string($_GET['search']);
    $search_sql = "WHERE books.title LIKE '%$search%' 
                   OR authors.name LIKE '%$search%' 
                   OR books.isbn LIKE '%$search%'";
}

/* Query */
$sql = "SELECT books.*, authors.name as author
        FROM books
        JOIN authors ON books.author_id = authors.id
        $search_sql
        ORDER BY books.title ASC";

$result = $conn->query($sql);

/* Display */
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){

        // ✅ IMAGE FIX (same as admin panel)
        $image_folder = "images/books/";
        $image_path = $image_folder . "placeholder.png";

        if (!empty($row['image'])) {
            $possible_path = $image_folder . basename($row['image']);
            $old_path = "images/" . basename($row['image']);

            if (file_exists($possible_path)) {
                $image_path = $possible_path;
            } elseif (file_exists($old_path)) {
                $image_path = $old_path;
            }
        }
        ?>

        <div class="book-card">
            <img src="<?php echo htmlspecialchars($image_path); ?>" 
                 alt="<?php echo htmlspecialchars($row['title']); ?>">

            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
            <p><?php echo htmlspecialchars($row['author']); ?></p>

            <a class="btn" href="book_details.php?id=<?php echo $row['id']; ?>">
                View Details
            </a>
        </div>

        <?php
    }
} else {
    echo "<p>No books found.</p>";
}
?>

</div>

</div>

</body>
</html>