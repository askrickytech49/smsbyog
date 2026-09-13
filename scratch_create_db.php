<?php
$conn = new mysqli('localhost', 'root', '');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->query("DROP DATABASE IF EXISTS smsbyogc_live");
$conn->query("CREATE DATABASE smsbyogc_live");
echo "Database created.\n";
