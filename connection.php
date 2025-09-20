<?php
// Database credentials
$servername = "localhost:3306";
$username1 = "narayani_watercan";
$password = "nw_w!J=LQAH{VcI6";
$dbname = "narayani_watercan";

// Create a connection to the database
$con = new mysqli($servername, $username1, $password, $dbname);
// Set the MySQL session time zone
$con->query("SET time_zone = '+05:30'");
// Check the connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
} 
?>
