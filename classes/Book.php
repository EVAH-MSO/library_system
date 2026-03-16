<?php

/* Book class handles book operations */

class Book {

private $conn;

/* constructor receives database connection */
public function __construct($db){

$this->conn = $db;

}


/* search books */
public function search($keyword){

$keyword = $this->conn->real_escape_string($keyword);

$sql = "SELECT books.*, authors.name AS author
FROM books
JOIN authors ON books.author_id = authors.id
WHERE books.title LIKE '%$keyword%'
OR authors.name LIKE '%$keyword%'
OR books.isbn LIKE '%$keyword%'";

return $this->conn->query($sql);

}


/* get single book */
public function getById($id){

$sql = "SELECT books.*, authors.name AS author
FROM books
JOIN authors ON books.author_id = authors.id
WHERE books.id='$id'";

return $this->conn->query($sql);

}


/* count available copies */
public function availableCopies($book_id){

$sql = "SELECT COUNT(*) AS total
FROM book_copies
WHERE book_id='$book_id'
AND status='available'";

$result = $this->conn->query($sql);

$row = $result->fetch_assoc();

return $row['total'];

}

}