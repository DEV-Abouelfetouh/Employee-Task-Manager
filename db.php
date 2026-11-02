<?php
$host = 'localhost'; $user = 'root'; $pass = ''; $dbname = 'tasks';
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) { $db_error = $conn->connect_error; } else { $db_error = null; }
if (session_status() === PHP_SESSION_NONE) session_start();
?>
