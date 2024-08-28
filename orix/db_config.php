<?php
global $CFG;
$servername = "localhost";

$username = "root";

$password = "";

$databas = "orix";

// Create connection
$CFG = mysqli_connect($servername, $username, $password, $databas);

// Check connection
if ($CFG->connect_error) {
  die("Connection failed: " . $CFG->connect_error);
}
// echo "Connected successfully";




?>