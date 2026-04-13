<?php
require_once("database/connexion.php");

session_start();
if (!isset($_SESSION['user'])) {
    header("location: login.php");
    exit;
}
$user = $_SESSION["user"];

/* Korttien luvut adminille */
$productCount = (int)$conn->query("
    SELECT COUNT(*) 
    FROM products 
    WHERE active = 1
")->fetchColumn();

$locationCount = (int)$conn->query("
    SELECT COUNT(*) 
    FROM locations
    WHERE active = 1
")->fetchColumn();

$alertCount = (int)$conn->query("
    SELECT COUNT(*) 
    FROM (
        SELECT 
            p.product_id
        FROM products p
        LEFT JOIN stock_balances sb ON p.product_id = sb.product_id
        WHERE p.active = 1
        GROUP BY p.product_id, p.reorder_point
        HAVING COALESCE(SUM(sb.quantity), 0) <= p.reorder_point
    ) AS alerts
")->fetchColumn();

$movement7daysCount = (int)$conn->query("
    SELECT COUNT(*) 
    FROM stock_movements
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
")->fetchColumn();

/* Hälytysrajan alittavat tuotteet */
$alertStmt = $conn->query("
    SELECT 
        p.product_id,
        p.sku,
        p.name,
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
    HAVING COALESCE(SUM(sb.quantity), 0) <= p.reorder_point
    ORDER BY total_quantity ASC, p.name ASC
    LIMIT 10
");
$alerts = $alertStmt->fetchAll(PDO::FETCH_ASSOC);

/* Viimeisimmät varastotapahtumat adminille */
$movementStmt = $conn->query("
    SELECT 
        sm.movement_id,
        sm.created_at,
        sm.movement_type,
        smr.quantity,
        p.name AS product_name,
        p.unit,
        u.name AS user_name
    FROM stock_movements sm
    JOIN stock_movements_rows smr ON sm.movement_id = smr.movement_id
    JOIN products p ON smr.product_id = p.product_id
    LEFT JOIN users u ON sm.created_by = u.user_id
    ORDER BY sm.created_at DESC, sm.movement_id DESC
    LIMIT 10
");
$movements = $movementStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="utf-8">
    <title>JT Varastonhallintajärjestelmä</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link href="img/favicon.ico" rel="icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet">

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
                <h4 class="mb-0">Tervetuloa, <?= htmlspecialchars($user["name"] ?? "") ?>!</h4>
            </div>
        </div>

        <?php if (($user['role'] ?? '') === 'ADMIN'): ?>
            <!-- ADMIN: Yläkortit -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">

                    <div class="col-sm-6 col-xl-3">
                        <a href="products.php" class="text-decoration-none">
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4 h-100">
                                <i class="fa fa-box fa-3x text-primary"></i>
                                <div class="ms-3 text-end">
                                    <p class="mb-1">Tuotteet</p>
                                    <h6 class="mb-0"><?= $productCount ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <a href="locations.php" class="text-decoration-none">
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4 h-100">
                                <i class="fa fa-warehouse fa-3x text-primary"></i>
                                <div class="ms-3 text-end">
                                    <p class="mb-1">Varastopaikat</p>
                                    <h6 class="mb-0"><?= $locationCount ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <a href="reports.php" class="text-decoration-none">
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4 h-100">
                                <i class="fa fa-exclamation-triangle fa-3x text-primary"></i>
                                <div class="ms-3 text-end">
                                    <p class="mb-1">Hälytykset</p>
                                    <h6 class="mb-0"><?= $alertCount ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <a href="stock-movements.php" class="text-decoration-none">
                            <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4 h-100">
                                <i class="fa fa-exchange-alt fa-3x text-primary"></i>
                                <div class="ms-3 text-end">
                                    <p class="mb-1">Viimeaikaiset tapahtumat</p>
                                    <h6 class="mb-0"><?= $movement7daysCount ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
        <?php endif; ?>

      <?php if (($user['role'] ?? '') === 'VARASTO'): ?>
    <!-- VARASTO: Toimintakortit -->
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">

            <div class="col-sm-12 col-md-4">
                <a href="stock-movements-new.php" class="text-decoration-none">
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4 h-100">
                        <i class="fa fa-plus fa-3x text-primary"></i>
                        <div class="ms-3 text-end">
                            <p class="mb-1">Tapahtumat</p>
                            <h6 class="mb-0">Uusi tapahtuma</h6>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-sm-12 col-md-4">
                <a href="stock-balances.php" class="text-decoration-none">
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4 h-100">
                        <i class="fa fa-layer-group fa-3x text-primary"></i>
                        <div class="ms-3 text-end">
                            <p class="mb-1">Varasto</p>
                            <h6 class="mb-0">Siirry saldoihin</h6>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-sm-12 col-md-4">
                <a href="stock-movements.php" class="text-decoration-none">
                    <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4 h-100">
                        <i class="fa fa-history fa-3x text-primary"></i>
                        <div class="ms-3 text-end">
                            <p class="mb-1">Oma toiminta</p>
                            <h6 class="mb-0">Viimeisimmät</h6>
                        </div>
                    </div>
                </a>
            </div>

        </div>
    </div>
<?php endif; ?>

        <!-- Hälytysrajan alittavat tuotteet -->
        <div class="container-fluid pt-4 px-4">
            <div class="bg-secondary rounded p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">Hälytysrajan alittavat tuotteet</h6>
                </div>

                <div class="table-responsive">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                        <tr class="text-white">
                            <th>Tunnus</th>
                            <th>Nimi</th>
                            <th>Saldo</th>
                            <th>Yksikkö</th>
                            <th>Hälytysraja</th>
                            <th>Toiminnot</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (count($alerts) > 0): ?>
                            <?php foreach ($alerts as $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['sku']) ?></td>
                                    <td><?= htmlspecialchars($a['name']) ?></td>
                                    <td class="text-danger fw-bold"><?= (int)$a['total_quantity'] ?></td>
                                    <td><?= htmlspecialchars($a['unit']) ?></td>
                                    <td><?= (int)$a['reorder_point'] ?></td>
                                    <td>
                                        <?php if (($user['role'] ?? '') === 'ADMIN'): ?>
                                            <a href="products-edit.php?id=<?= (int)$a['product_id'] ?>" class="btn btn-sm btn-primary">
                                                Muokkaa
                                            </a>
                                        <?php endif; ?>

                                        <a href="stock-movements-new.php?product_id=<?= (int)$a['product_id'] ?>" class="btn btn-sm btn-outline-light">
                                            Muuta saldoa
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-muted">Ei hälytysrajan alittavia tuotteita.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if (($user['role'] ?? '') === 'ADMIN'): ?>
            <!-- ADMIN: Viimeisimmät varastotapahtumat -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">Viimeisimmät varastotapahtumat</h6>
                        <a href="stock-movements.php">Näytä koko tapahtumahistoria</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                            <tr class="text-white">
                                <th>Päiväys</th>
                                <th>Tyyppi</th>
                                <th>Tuote</th>
                                <th class="text-end">Määrä</th>
                                <th>Tekijä</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (count($movements) > 0): ?>
                                <?php foreach ($movements as $m): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($m['created_at']) ?></td>
                                        <td>
                                            <?php
                                            $typeText = match ($m['movement_type']) {
                                                'IN' => 'Varastoon',
                                                'OUT' => 'Varastosta ulos',
                                                'ADJUST' => 'Saldon korjaus',
                                                default => $m['movement_type']
                                            };
                                            ?>
                                            <?= htmlspecialchars($typeText) ?>
                                        </td>
                                        <td><?= htmlspecialchars($m['product_name']) ?></td>
                                        <td class="text-end"><?= (int)$m['quantity'] ?> <?= htmlspecialchars($m['unit']) ?></td>
                                        <td><?= htmlspecialchars($m['user_name'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-muted">
                                        Ei vielä dataa. Lisää ensimmäinen tapahtuma kohdasta <strong>Uusi tapahtuma</strong>.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        <?php endif; ?>

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

<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>
<script src="lib/tempusdominus/js/moment.min.js"></script>
<script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
<script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

<script src="js/main.js"></script>
</body>
</html>