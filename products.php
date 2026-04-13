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

/* POISTO = passivointi */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("
        UPDATE products
        SET active = 0
        WHERE product_id = :id
    ");
    $stmt->execute([':id' => $id]);

    header("Location: products.php");
    exit;
}

/* SIJAINNIT-NÄYTTÖ */
$locationsData = [];
$selectedProduct = null;

if (isset($_GET['show_locations'])) {
    $pid = (int)$_GET['show_locations'];

    $stmt = $conn->prepare("
        SELECT product_id, sku, name, unit
        FROM products
        WHERE product_id = :id AND active = 1
    ");
    $stmt->execute([':id' => $pid]);
    $selectedProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selectedProduct) {
        $stmt = $conn->prepare("
            SELECT 
                l.code,
                l.description,
                sb.quantity
            FROM stock_balances sb
            JOIN locations l ON l.location_id = sb.location_id
            WHERE sb.product_id = :id
              AND sb.quantity <> 0
            ORDER BY l.code
        ");
        $stmt->execute([':id' => $pid]);
        $locationsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/* TUOTTEET + KATEGORIA + KOKONAISMÄÄRÄ */
$sql = "
    SELECT 
        p.product_id,
        p.sku,
        p.name,
        p.category_id,
        p.unit,
        p.reorder_point,
        p.active,
        c.name AS category_name,
        COALESCE(SUM(s.quantity), 0) AS total_quantity
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN stock_balances s ON p.product_id = s.product_id
    WHERE p.active = 1
    GROUP BY 
        p.product_id,
        p.sku,
        p.name,
        p.category_id,
        p.unit,
        p.reorder_point,
        p.active,
        c.name
    ORDER BY p.name
";
$stmt = $conn->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Tuotteet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="img/favicon.ico" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Tuotteet</h6>
                    <a href="products-add.php" class="btn btn-primary">+ Lisää tuote</a>
                </div>

                <?php if ($selectedProduct): ?>
                    <div class="bg-dark border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>
                                <?= htmlspecialchars($selectedProduct['name']) ?>
                            </strong>
                            <a href="products.php" class="btn btn-sm btn-outline-light">Sulje</a>
                        </div>

                        <hr>

                        <?php if (count($locationsData) > 0): ?>
                            <?php foreach ($locationsData as $l): ?>
                                <div class="mb-1">
                                    <?= htmlspecialchars($l['code']) ?> (<?= htmlspecialchars($l['description']) ?>): <?= (int)$l['quantity'] ?> <?= htmlspecialchars($selectedProduct['unit']) ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted">Ei saldoa missään varastopaikassa.</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th>Tunnus</th>
                                <th>Nimi</th>
                                <th>Kategoria</th>
                                <th>Määrä</th>
                                <th>Yksikkö</th>
                                <th>Hälytysraja</th>
                                <th>Toiminnot</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) > 0): ?>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['sku']) ?></td>
                                        <td><?= htmlspecialchars($p['name']) ?></td>
                                        <td><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>

                                        <td>
                                            <span class="<?= ((int)$p['total_quantity'] <= (int)$p['reorder_point']) ? 'text-danger fw-bold' : '' ?>">
                                                <?= (int)$p['total_quantity'] ?>
                                            </span>

                                            <?php if ((int)$p['total_quantity'] <= (int)$p['reorder_point']): ?>
                                                <span class="badge bg-danger ms-2">Hälytys</span>
                                            <?php endif; ?>
                                        </td>

                                        <td><?= htmlspecialchars($p['unit']) ?></td>
                                        <td><?= (int)$p['reorder_point'] ?></td>

                                        <td>
                                            <a href="products.php?show_locations=<?= (int)$p['product_id'] ?>" class="btn btn-sm btn-outline-info">
                                                Sijainnit
                                            </a>

                                            <a href="products-edit.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-sm btn-primary">
                                                Muokkaa
                                            </a>

                                            <a href="products.php?delete=<?= (int)$p['product_id'] ?>"
                                            class="btn btn-sm btn-outline-light"
                                            onclick="return confirm('Poistetaanko tuote?')">
                                            Poista
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-muted">Ei tuotteita.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>