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

/* Yhteenvetokortit */
$lowCount = (int)$conn->query("
    SELECT COUNT(*)
    FROM (
        SELECT p.product_id
        FROM products p
        LEFT JOIN stock_balances sb ON sb.product_id = p.product_id
        WHERE p.active = 1
        GROUP BY p.product_id, p.reorder_point
        HAVING COALESCE(SUM(sb.quantity), 0) <= p.reorder_point
    ) AS low_products
")->fetchColumn();

$zeroCount = (int)$conn->query("
    SELECT COUNT(*)
    FROM (
        SELECT p.product_id
        FROM products p
        LEFT JOIN stock_balances sb ON sb.product_id = p.product_id
        WHERE p.active = 1
        GROUP BY p.product_id
        HAVING COALESCE(SUM(sb.quantity), 0) = 0
    ) AS zero_products
")->fetchColumn();

$movement30daysCount = (int)$conn->query("
    SELECT COUNT(*)
    FROM stock_movements
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetchColumn();

/* Tapahtumatyyppien määrät 30 päivää */
$in30daysCount = (int)$conn->query("
    SELECT COUNT(*)
    FROM stock_movements
    WHERE movement_type = 'IN'
      AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetchColumn();

$out30daysCount = (int)$conn->query("
    SELECT COUNT(*)
    FROM stock_movements
    WHERE movement_type = 'OUT'
      AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetchColumn();

$adjust30daysCount = (int)$conn->query("
    SELECT COUNT(*)
    FROM stock_movements
    WHERE movement_type = 'ADJUST'
      AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")->fetchColumn();

/* Kategoriayhteenveto */
$categoryStats = $conn->query("
    SELECT 
        c.name AS category_name,
        COUNT(DISTINCT p.product_id) AS product_count,
        COUNT(DISTINCT CASE 
            WHEN COALESCE(sb.quantity, 0) <= p.reorder_point 
            THEN p.product_id 
        END) AS low_stock_count
    FROM categories c
    LEFT JOIN products p 
        ON p.category_id = c.category_id
       AND p.active = 1
    LEFT JOIN stock_balances sb 
        ON sb.product_id = p.product_id
    GROUP BY c.category_id, c.name
    ORDER BY product_count DESC, c.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* Eniten liikkuneet tuotteet viimeiset 30 päivää
   Huom: vain IN + OUT, ei ADJUST */
$topProducts = $conn->query("
    SELECT 
        p.sku,
        p.name,
        p.unit,
        SUM(CASE 
            WHEN sm.movement_type = 'IN' THEN smr.quantity 
            ELSE 0 
        END) AS qty_in,
        SUM(CASE 
            WHEN sm.movement_type = 'OUT' THEN smr.quantity 
            ELSE 0 
        END) AS qty_out,
        SUM(CASE 
            WHEN sm.movement_type IN ('IN', 'OUT') THEN ABS(smr.quantity)
            ELSE 0
        END) AS total_handled
    FROM stock_movements_rows smr
    JOIN products p ON p.product_id = smr.product_id
    JOIN stock_movements sm ON sm.movement_id = smr.movement_id
    WHERE sm.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY p.product_id, p.sku, p.name, p.unit
    ORDER BY total_handled DESC, p.name ASC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

/* Tuote loppu */
$zeroProducts = $conn->query("
    SELECT 
        p.product_id,
        p.sku,
        p.name,
        p.unit,
        COALESCE(SUM(sb.quantity), 0) AS total_quantity
    FROM products p
    LEFT JOIN stock_balances sb ON sb.product_id = p.product_id
    WHERE p.active = 1
    GROUP BY p.product_id, p.sku, p.name, p.unit
    HAVING COALESCE(SUM(sb.quantity), 0) = 0
    ORDER BY p.name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Raportit</title>
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
                <h6 class="mb-0">Raportit</h6>
            </div>
        </div>

        <!-- Yhteenveto -->
        <div class="container-fluid pt-4 px-4">
            <div class="row g-4">

                <div class="col-sm-6 col-xl-4">
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4 h-100">
                        <i class="fa fa-exclamation-triangle fa-3x text-primary"></i>
                        <div class="ms-3 text-end">
                            <p class="mb-2">Hälytysrajan alitukset</p>
                            <h6 class="mb-0"><?= $lowCount ?></h6>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-4">
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4 h-100">
                        <i class="fa fa-box-open fa-3x text-primary"></i>
                        <div class="ms-3 text-end">
                            <p class="mb-2">Tuote loppu</p>
                            <h6 class="mb-0"><?= $zeroCount ?></h6>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-xl-4">
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4 h-100">
                        <i class="fa fa-exchange-alt fa-3x text-primary"></i>

                        <div class="ms-3 text-end">
                            <p class="mb-1">Tapahtumat viim. 30 päivää</p>
                            <h6 class="mb-0"><?= $movement30daysCount ?></h6>

                            <div class="small mt-2">
                                <div>Varastoon: <?= $in30daysCount ?></div>
                                <div>Varastosta ulos: <?= $out30daysCount ?></div>
                                <div>Saldon korjaus: <?= $adjust30daysCount ?></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Kategoriayhteenveto -->
        <div class="container-fluid pt-4 px-4">
            <div class="bg-secondary rounded p-4">
                <h6 class="mb-3">Kategoriayhteenveto</h6>

                <div class="table-responsive">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th>Kategoria</th>
                                <th class="text-end">Tuotteita</th>
                                <th class="text-end">Hälytysrajalla</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($categoryStats) > 0): ?>
                                <?php foreach ($categoryStats as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['category_name']) ?></td>
                                        <td class="text-end"><?= (int)$c['product_count'] ?></td>
                                        <td class="text-end"><?= (int)$c['low_stock_count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-muted">Ei tietoja näytettäväksi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Eniten liikkuneet tuotteet -->
        <div class="container-fluid pt-4 px-4">
            <div class="bg-secondary rounded p-4">
                <h6 class="mb-3">Eniten liikkuneet tuotteet (30 pv)</h6>

                <div class="table-responsive">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th>Tunnus</th>
                                <th>Tuote</th>
                                <th>Yksikkö</th>
                                <th class="text-end">Varastoon tulleet</th>
                                <th class="text-end">Varastosta lähteneet</th>
                                <th class="text-end">Yhteensä</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($topProducts) > 0): ?>
                                <?php foreach ($topProducts as $t): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($t['sku']) ?></td>
                                        <td><?= htmlspecialchars($t['name']) ?></td>
                                        <td><?= htmlspecialchars($t['unit']) ?></td>
                                        <td class="text-end"><?= (int)$t['qty_in'] ?></td>
                                        <td class="text-end"><?= (int)$t['qty_out'] ?></td>
                                        <td class="text-end fw-bold"><?= (int)$t['total_handled'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-muted">Ei tapahtumatietoja viimeiseltä 30 päivältä.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tuote loppu -->
        <div class="container-fluid pt-4 px-4">
            <div class="bg-secondary rounded p-4">
                <h6 class="mb-3">Tuote loppu</h6>

                <div class="table-responsive">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th>Tunnus</th>
                                <th>Tuote</th>
                                <th>Saldo</th>
                                <th>Yksikkö</th>
                                <th>Toiminnot</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($zeroProducts) > 0): ?>
                                <?php foreach ($zeroProducts as $z): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($z['sku']) ?></td>
                                        <td><?= htmlspecialchars($z['name']) ?></td>
                                        <td class="text-danger fw-bold"><?= (int)$z['total_quantity'] ?></td>
                                        <td><?= htmlspecialchars($z['unit']) ?></td>
                                        <td>
                                            <a href="products-edit.php?id=<?= (int)$z['product_id'] ?>" class="btn btn-sm btn-primary">
                                                Muokkaa
                                            </a>
                                            <a href="stock-movements-new.php?product_id=<?= (int)$z['product_id'] ?>" class="btn btn-sm btn-outline-light">
                                                Muuta saldoa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-muted">Ei loppuneita tuotteita.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="container-fluid pt-4 px-4">
            <div class="bg-secondary rounded-top p-4">
                <div class="row">
                    <div class="col-12 col-sm-6 text-center text-sm-start">
                        &copy; JT Varasto, 2026 oppilasnäyttö.
                    </div>
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