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

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: products.php");
    exit;
}

/* Haetaan tuote */
$stmt = $conn->prepare("
    SELECT 
        p.product_id,
        p.sku,
        p.name,
        p.category_id,
        p.unit,
        p.active,
        p.reorder_point
    FROM products p
    WHERE p.product_id = :id
");
$stmt->execute([':id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: products.php");
    exit;
}

$stmt = $conn->query("SELECT category_id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Sallitut yksiköt */
$allowed_units = ['kpl', 'kg', 'pkt', 'm3', 'pari', 'prk'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit = trim($_POST['unit'] ?? '');

    if (!in_array($unit, $allowed_units, true)) {
        $unit = 'kpl';
    }

    $stmt = $conn->prepare("
        UPDATE products
        SET sku = :sku,
            name = :name,
            category_id = :category_id,
            unit = :unit,
            reorder_point = :reorder_point
        WHERE product_id = :id
    ");

    $stmt->execute([
        ':sku' => trim($_POST['sku'] ?? ''),
        ':name' => trim($_POST['name'] ?? ''),
        ':category_id' => (int)($_POST['category_id'] ?? 0),
        ':unit' => $unit,
        ':reorder_point' => (int)($_POST['reorder_point'] ?? 0),
        ':id' => $id
    ]);

    header("Location: products.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Muokkaa tuotetta</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="img/favicon.ico" rel="icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid position-relative d-flex p-0">
    <?php include_once 'partials/app-sidebar.php'; ?>

    <div class="content">
        <?php include_once 'partials/navbar.php'; ?>

        <div class="container-fluid pt-4 px-4">
            <div class="bg-secondary rounded p-4">
                <h6 class="mb-4">Muokkaa tuotetta</h6>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Tunnus</label>
                        <input 
                            type="text" 
                            name="sku" 
                            class="form-control" 
                            required
                            value="<?= htmlspecialchars($product['sku']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nimi</label>
                        <input 
                            type="text" 
                            name="name" 
                            class="form-control" 
                            required
                            value="<?= htmlspecialchars($product['name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategoria</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $c): ?>
                                <option 
                                    value="<?= (int)$c['category_id'] ?>"
                                    <?= (int)$c['category_id'] === (int)$product['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Yksikkö</label>
                        <select name="unit" class="form-select" required>
                            <?php foreach ($allowed_units as $u): ?>
                                <option 
                                    value="<?= htmlspecialchars($u) ?>"
                                    <?= $product['unit'] === $u ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hälytysraja</label>
                        <input 
                            type="number" 
                            name="reorder_point" 
                            class="form-control" 
                            min="0" 
                            required
                            value="<?= (int)$product['reorder_point'] ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">Tallenna muutokset</button>
                    <a href="products.php" class="btn btn-outline-light">Takaisin</a>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>