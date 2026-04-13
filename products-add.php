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

$allowed_units = ['kpl', 'kg', 'pkt', 'm3', 'pari', 'prk', 'säkki'];

function generateSku(PDO $conn, int $categoryId): string
{
    $stmt = $conn->prepare("
        SELECT code_prefix, code_start
        FROM categories
        WHERE category_id = :category_id
        LIMIT 1
    ");
    $stmt->execute([':category_id' => $categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category || empty($category['code_prefix'])) {
        return '';
    }

    $prefix = strtoupper(trim($category['code_prefix']));
    $codeStart = (int)$category['code_start'];

    $stmt = $conn->prepare("
        SELECT sku
        FROM products
        WHERE category_id = :category_id
          AND sku LIKE :prefix
        ORDER BY CAST(SUBSTRING(sku, :prefix_length_plus_one) AS UNSIGNED) DESC
        LIMIT 1
    ");
    $stmt->execute([
        ':category_id' => $categoryId,
        ':prefix' => $prefix . '%',
        ':prefix_length_plus_one' => strlen($prefix) + 1
    ]);

    $lastSku = $stmt->fetchColumn();

    if ($lastSku) {
        $number = (int)substr($lastSku, strlen($prefix));
        $nextNumber = $number + 1;
    } else {
        $nextNumber = $codeStart > 0 ? $codeStart : 1001;
    }

    return $prefix . $nextNumber;
}

$stmt = $conn->query("
    SELECT category_id, name, code_prefix, code_start
    FROM categories
    ORDER BY name
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedCategoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$generatedSku = $selectedCategoryId > 0 ? generateSku($conn, $selectedCategoryId) : '';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $sku = generateSku($conn, $categoryId);

    $unit = trim($_POST['unit'] ?? '');
    if (!in_array($unit, $allowed_units, true)) {
        $unit = 'kpl';
    }

    $name = trim($_POST['name'] ?? '');
    $reorderPoint = (int)($_POST['reorder_point'] ?? 0);
    $active = (int)($_POST['active'] ?? 1);

    if ($categoryId <= 0 || $name === '') {
        $error = "Täytä pakolliset kentät.";
    } elseif ($sku === '') {
        $error = "Valitulta kategorialta puuttuu tunnusprefixi tai aloitusnumero.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO products (sku, name, category_id, unit, active, reorder_point)
            VALUES (:sku, :name, :category_id, :unit, 1, :reorder_point)
        ");

        $stmt->execute([
            ':sku' => $sku,
            ':name' => $name,
            ':category_id' => $categoryId,
            ':unit' => $unit,
            ':reorder_point' => $reorderPoint
        ]);

        header("Location: products.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Lisää tuote</title>
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
                <h6 class="mb-4">Lisää tuote</h6>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="get" class="mb-3">
                    <label class="form-label">Kategoria</label>
                    <select name="category_id" class="form-select" onchange="this.form.submit()" required>
                        <option value="">Valitse kategoria</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int)$c['category_id'] ?>"
                                <?= $selectedCategoryId === (int)$c['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <form method="post">
                    <input type="hidden" name="category_id" value="<?= $selectedCategoryId ?>">

                    <div class="mb-3">
                        <label class="form-label">Tunnus</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($generatedSku) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nimi</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Yksikkö</label>
                        <select name="unit" class="form-select" required>
                            <?php foreach ($allowed_units as $u): ?>
                                <option value="<?= htmlspecialchars($u) ?>"><?= htmlspecialchars($u) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hälytysraja</label>
                        <input type="number" name="reorder_point" class="form-control" min="0" required>
                    </div>

                    <button type="submit" class="btn btn-primary" <?= $selectedCategoryId === 0 ? 'disabled' : '' ?>>
                        Tallenna
                    </button>
                    <a href="products.php" class="btn btn-outline-light">Takaisin</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>