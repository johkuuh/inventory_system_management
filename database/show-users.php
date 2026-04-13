<?php 


include 'connexion.php';

$stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
return $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// var_dump($result);

?>