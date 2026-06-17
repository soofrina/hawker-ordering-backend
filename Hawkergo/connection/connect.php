<?php

//main connection file for both admin & front end
$servername = "localhost"; //server
$username = "root"; //username
$password = ""; //password
$dbname = "hawker";  //database

// Create connection
$db = mysqli_connect($servername, $username, $password, $dbname); // connecting 
// Check connection
if (!$db) {       //checking connection to DB	
    die("Connection failed: " . mysqli_connect_error());
}

require_once dirname(__DIR__) . '/includes/schema.php';
require_once dirname(__DIR__) . '/includes/order_helpers.php';
require_once dirname(__DIR__) . '/includes/auth_helpers.php';
hawkergo_ensure_schema($db);

?>