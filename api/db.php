<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "expense_tracker";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["error" => "DB Connection Failed"]));
}

header("Content-Type: application/json");
session_start();
?>
