<?php
session_start();
include "config/database.php"; // make sure this path is correct relative to this file

/* check login */
if(!isset($_SESSION['user_id'])){
    header("Location: auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* check if book ID is provided */
if(!isset($_GET['id']) || empty($_GET['id'])){
    die("<p style='color:red;font-weight:bold;'>Book not found.</p>");
}

$book_id = $conn->real_escape_string($_GET['id']);

/* fetch book details */
$sql = "
SELECT books.*, authors.name AS author
FROM books
JOIN authors ON books.author_id = authors.id
WHERE books.id='$book_id'
LIMIT 1
";

$result = $conn->query($sql);

if($result->num_rows == 0){
    die("<p style='color:red;font-weight:bold;'>Book not found.</p>");
}

$book = $result->fetch_assoc();

/* count available copies */
$availableCopies = $conn->query("
SELECT COUNT(*) AS total
FROM book_copies
WHERE book_id='$book_id' AND status='available'
")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($book['title']); ?> - Book Details</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Professional styling for book details */
        .book-details-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        .book-image {
            max-width: 250px;
            border-radius: 10px;
            object-fit: cover;
        }
        .book-info {
            flex: 1;
            min-width: 300px;
        }
        .book-info h2 {
            margin-bottom: 10px;
        }
        .book-info p {
            margin: 5px 0;
            font-size: 16px;
        }
        .btn-borrow {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: bold;
        }
        .btn-borrow:hover {
            background-color: #218838;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <h1>University Library</h1>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="../auth/logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <div class="book-details-container">
        <!-- Book Image -->
        <?php if(!empty($book['image'])): ?>
            <div>
                <img class="book-image" src="../images/<?php echo htmlspecialchars($book['image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
            </div>
        <?php endif; ?>

        <!-- Book Info -->
        <div class="book-info">
            <h2><?php echo htmlspecialchars($book['title']); ?></h2>
            <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
            <?php if(!empty($book['description'])): ?>
                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
            <?php endif; ?>
            <p><strong>Available Copies:</strong> <?php echo $availableCopies; ?></p>

            <?php if($availableCopies > 0): ?>
                <a class="btn-borrow" href="user/borrow.php?book_id=<?php echo $book_id; ?>">Borrow This Book</a>
            <?php else: ?>
                <p style="color:red;font-weight:bold;">No copies available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <a class="back-link" href="dashboard.php">&larr; Back to Dashboard</a>
</div>

</body>
</html>