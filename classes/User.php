<?php

/* User class manages user operations */

class User {

private $conn;

/* constructor */
public function __construct($db){

$this->conn = $db;

}


/* login function */
public function login($email,$password){

$password = md5($password);

$sql = "SELECT * FROM users
WHERE email='$email'
AND password='$password'";

$result = $this->conn->query($sql);

if($result->num_rows == 1){

return $result->fetch_assoc();

}

return false;

}


/* borrowed books for user */
public function borrowedBooks($user_id){

$sql = "SELECT books.title, loans.loan_date, loans.due_date, loans.return_date
FROM loans
JOIN book_copies ON loans.copy_id = book_copies.id
JOIN books ON book_copies.book_id = books.id
WHERE loans.user_id='$user_id'";

return $this->conn->query($sql);

}

}