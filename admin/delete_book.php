<?php

session_start();
include "../config/database.php";

/* only librarians allowed */
if(!isset($_SESSION['role']) || $_SESSION['role']!="librarian"){
    die("Access denied");
}

if(isset($_GET['id'])){

$book_id = intval($_GET['id']);

/* delete book copies first */
$conn->query("DELETE FROM book_copies WHERE book_id = $book_id");

/* then delete the book */
$conn->query("DELETE FROM books WHERE id = $book_id");

header("Location: manage_books.php");

}

?>