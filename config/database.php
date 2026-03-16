<?php

// database.php

// Define database server name
$servername = "localhost"; // MySQL server running on localhost

// Define database username
$username = "root"; // default XAMPP username

// Define database password
$password = ""; // default password is empty in XAMPP

// Define database name
$dbname = "library_system"; // the database we created earlier


// Create connection using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);


// Check if connection failed
if ($conn->connect_error) {

    // Stop script and show error
    die("Connection failed: " . $conn->connect_error);
}


// If connection is successful
// the variable $conn will be used everywhere in the project

?>