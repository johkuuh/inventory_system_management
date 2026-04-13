<?php
$servername = 'localhost';
$username = 'YOUR_DB_USER';
$password = 'YOUR_DB_PASSWORD';
$dbname = 'varasto';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB-yhteys epäonnistui: " . $e->getMessage());
}