<?php
require_once("database/connexion.php");

session_start();
if (!isset($_SESSION['user'])) {
    header("location: login.php");
    exit;
}
$user = $_SESSION["user"];

$sql = "
SELECT 
    p.product_id,
    p.sku,
    p.name AS product_name,
    p.unit,
    p.reorder_point,
    COALESCE(SUM(sb.quantity), 0) AS total_quantity
FROM products p
LEFT JOIN stock_balances sb ON p.product_id = sb.product_id
WHERE p.active = 1
GROUP BY 
    p.product_id,
    p.sku,
    p.name,
    p.unit,
    p.reorder_point
ORDER BY p.name
";
$stmt = $conn->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="utf-8">
    <title>JT Varasto - Saldot</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

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
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Saldot</h6>
                    <a href="stock-movements-new.php" class="btn btn-primary btn-sm">+ Uusi tapahtuma</a>
                </div>

                <div class="table-responsive">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th>Tunnus</th>
                                <th>Tuote</th>
                                <th class="text-end">Saldo</th>
                                <th>Yksikkö</th>
                                <th class="text-end">Hälytysraja</th>
                                <th>Toiminnot</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($rows) > 0): ?>
                             <?php foreach ($rows as $r): ?>
                                 <?php
                                $qty = (int)$r['total_quantity'];
                                $reorder = (int)$r['reorder_point'];
                                $qtyClass = '';

                               if ($qty <= $reorder) {
                                    $qtyClass = 'text-danger fw-bold';
                                }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['sku']) ?></td>
                                    <td><?= htmlspecialchars($r['product_name']) ?></td>
                                    <td class="text-end <?= $qtyClass ?>"><?= $qty ?></td>
                                    <td><?= htmlspecialchars($r['unit']) ?></td>
                                    <td class="text-end"><?= $reorder ?></td>
                                    <td>
                                        <?php if (($user['role'] ?? '') === 'ADMIN'): ?>
                                            <a href="products-edit.php?id=<?= (int)$r['product_id'] ?>" class="btn btn-sm btn-primary">
                                                Muokkaa tuotetta
                                            </a>
                                        <?php endif; ?>

                                        <a href="stock-movements-new.php?product_id=<?= (int)$r['product_id'] ?>" class="btn btn-sm btn-outline-light">
                                            Muuta saldoa
                                        </a>
                                    </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-muted">Ei saldoja näytettäväksi.</td>
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