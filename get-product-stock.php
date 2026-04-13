<?php
require_once "database/connexion.php";

$product_id = (int)($_GET['product_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT COALESCE(SUM(quantity), 0)
    FROM stock_balances
    WHERE product_id = ?
");
$stmt->execute([$product_id]);

echo json_encode([
    "quantity" => (int)$stmt->fetchColumn()
]);