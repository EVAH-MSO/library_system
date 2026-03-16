<?php

/* start session system */
session_start();

/* remove all stored session variables */
$_SESSION = [];

/* destroy session */
session_destroy();

/* redirect user to homepage */
header("Location: ../index.php");

exit;

?>