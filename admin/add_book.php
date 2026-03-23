<?php
session_start();
include "../config/database.php";

/* Only librarians allowed */
if (!isset($_SESSION['role']) || $_SESSION['role'] != "librarian") {
    die("Access denied");
}

$message = "";

if (isset($_POST['add_book'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
    $copies = intval($_POST['copies']);

    /* -------- IMAGE UPLOAD WITH UNIQUE NAME -------- */
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

        $tmp_name = $_FILES['image']['tmp_name'];
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        /* Generate unique filename */
        $image_name = time() . '_' . uniqid() . '.' . $ext;

        /* Ensure the upload folder exists */
        $upload_dir = "../images/books/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $upload_path = $upload_dir . $image_name;

        if (!move_uploaded_file($tmp_name, $upload_path)) {
            die("Error: Could not move uploaded file. Check folder permissions.");
        }

    } else {
        die("Error: No file uploaded or invalid upload.");
    }

    /* -------- AUTHOR CHECK -------- */
    $check_author = $conn->query("SELECT id FROM authors WHERE name='$author'");
    if ($check_author->num_rows > 0) {
        $author_id = $check_author->fetch_assoc()['id'];
    } else {
        $conn->query("INSERT INTO authors(name) VALUES('$author')");
        $author_id = $conn->insert_id;
    }

    /* -------- INSERT BOOK -------- */
    $conn->query("INSERT INTO books(title,author_id,isbn,image) VALUES('$title','$author_id','$isbn','$image_name')");
    $book_id = $conn->insert_id;

    /* -------- INSERT COPIES -------- */
    for ($i = 1; $i <= $copies; $i++) {
        $conn->query("INSERT INTO book_copies(book_id,copy_number,status) VALUES('$book_id','$i','available')");
    }

    $message = "Book added successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Book</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container { max-width: 600px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);}
        h2 { text-align: center; color: #2f3640; }
        input[type=text], input[type=number], input[type=file] { width: 100%; padding: 8px; margin: 5px 0 15px; border-radius: 4px; border: 1px solid #ccc; }
        .btn { display: block; width: 100%; padding: 10px; background: #40739e; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #2f3640; }
        p.message { text-align: center; color: green; font-weight: bold; }
    </style>
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
    <?php if ($message != "") { ?>
        <p class="message"><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Book Title</label>
        <input type="text" name="title" required>

        <label>Author Name</label>
        <input type="text" name="author" required>

        <label>ISBN</label>
        <input type="text" name="isbn" required>

        <label>Number of Copies</label>
        <input type="number" name="copies" min="1" required>

        <label>Book Cover Image</label>
        <input type="file" name="image" accept="image/*" required>

        <button class="btn" type="submit" name="add_book">Add Book</button>
    </form>
</div>
</body>
</html>