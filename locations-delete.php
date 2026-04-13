<?php
require_once 'database/connexion.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

if (($user['role'] ?? '') !== 'ADMIN') {
    header("Location: dashboard.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: locations.php");
    exit;
}

$id = (int)$_GET['id'];

try {
    $stmt = $conn->prepare("DELETE FROM locations WHERE location_id = ?");
    $stmt->execute([$id]);

} catch (PDOException $e) {
    // jos foreign key estää poiston
    $_SESSION['error'] = "Paikkaa ei voi poistaa, koska sitä käytetään tapahtumissa.";
}

header("Location: locations.php");
exit;