<?php
/* Database Credentials
 * $dbuser: database username
 * $dbpass: database password
 * $dbname: the name of the Database
 */
$dbuser = 'dbuser';
$dbpass = '901227';
$dbname = 'simpletodo';

$conn = new mysqli("localhost", $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
  die('Connection Failed: ' . $conn->connect_error);
}

function sanitize_user_input($str, $conn) {
	return htmlentities($conn->real_escape_string($str));
}

 ?>
