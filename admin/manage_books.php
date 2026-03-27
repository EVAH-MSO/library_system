<?php
session_start();
include "../config/database.php";

/* ensure librarian access */
if (!isset($_SESSION['role']) || $_SESSION['role'] != "librarian") {
    die("Access denied");
}

// Optional: suppress notices for undefined variables
error_reporting(E_ALL & ~E_NOTICE);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Books</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #2f3640;
            color: #fff;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 { margin: 0; font-size: 22px; }
        .navbar a {
            color: #f5f6fa;
            text-decoration: none;
            margin-left: 15px;
            font-weight: bold;
        }
        .navbar a:hover { text-decoration: underline; }

        .container { padding: 20px 40px; }
        h2 { color: #2f3640; margin-bottom: 20px; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table th, table td { padding: 12px 15px; text-align: left; }
        table th {
            background-color: #40739e;
            color: #fff;
            text-transform: uppercase;
            font-size: 13px;
        }
        table tr:nth-child(even) { background-color: #f2f3f7; }
        table tr:hover { background-color: #dcdde1; }

        .btn {
            padding: 6px 12px;
            background-color: #40739e;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
            margin-right: 5px;
            display: inline-block;
        }
        .btn:hover { background-color: #2f3640; }

        img.book-cover {
            width: 50px;
            height: 70px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #dcdde1;
        }

        .search-box { margin-bottom: 15px; }
        .search-box input {
            padding: 7px 12px;
            width: 250px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
    </style>
    <script>
        function searchBooks() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const table = document.getElementById("booksTable");
            const rows = table.getElementsByTagName("tr");
            for (let i = 1; i < rows.length; i++) {
                let title = rows[i].cells[1].innerText.toLowerCase();
                let author = rows[i].cells[2].innerText.toLowerCase();
                let isbn = rows[i].cells[3].innerText.toLowerCase();
                rows[i].style.display = (title.includes(input) || author.includes(input) || isbn.includes(input)) ? "" : "none";
            }
        }
    </script>
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

    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search by Title, Author, or ISBN..." onkeyup="searchBooks()">
    </div>

    <table id="booksTable">
        <tr>
            <th>Image</th>
            <th>Title</th>
            <th>Author</th>
            <th>ISBN</th>
            <th>Actions</th>
        </tr>

        <?php
        $sql = "SELECT books.*, authors.name as author
                FROM books
                JOIN authors ON books.author_id = authors.id
                ORDER BY books.id DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                // Base image folder
                $image_folder = "../images/books/";

                // Default to placeholder
                $image_path = $image_folder . "placeholder.png";

                // Check if 'image' exists and file is valid
                if (!empty($row['image'])) {
                    $possible_path = $image_folder . basename($row['image']);
                    $old_path = "../images/" . basename($row['image']); // old folder fallback

                    if (file_exists($possible_path)) {
                        $image_path = $possible_path;
                    } elseif (file_exists($old_path)) {
                        $image_path = $old_path;
                    }
                }
        ?>
        <tr>
            <td><img class="book-cover" src="<?php echo htmlspecialchars($image_path); ?>" alt="Book Cover"></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['author']); ?></td>
            <td><?php echo htmlspecialchars($row['isbn']); ?></td>
            <td>
                <a class="btn" href="edit_book.php?id=<?php echo $row['id']; ?>">Edit</a>
                <a class="btn" href="delete_book.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
            </td>
        </tr>
        <?php
            }
        } else {
            echo '<tr><td colspan="5">No books found.</td></tr>';
        }
        ?>
    </table>
</div>

</body>
</html>