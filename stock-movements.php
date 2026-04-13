<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("location: login.php");
    exit;
}
require_once "database/connexion.php";

$user = $_SESSION["user"];

$typeNames = [
    "IN" => "Varastoon",
    "OUT" => "Varastosta ulos",
    "ADJUST" => "Saldon korjaus"
];

$typeBadge = [
    "IN" => "bg-success",
    "OUT" => "bg-danger",
    "ADJUST" => "bg-info text-dark"
];

$currentUserId = (int)($user['user_id'] ?? 0);

$sql = "
SELECT 
    sm.movement_type,
    sm.created_at,
    sm.note,
    u.name AS created_by_name,
    p.sku,
    p.name AS product_name,
    p.unit,
    smr.quantity,
    lf.code AS from_code,
    lt.code AS to_code
FROM stock_movements sm
LEFT JOIN users u ON u.user_id = sm.created_by
JOIN stock_movements_rows smr ON smr.movement_id = sm.movement_id
JOIN products p ON p.product_id = smr.product_id
LEFT JOIN locations lf ON lf.location_id = smr.from_location_id
LEFT JOIN locations lt ON lt.location_id = smr.to_location_id
WHERE sm.movement_type IN ('IN', 'OUT', 'ADJUST')
";

if (($user['role'] ?? '') === 'VARASTO') {
    $sql .= " AND sm.created_by = :user_id ";
}

$sql .= "
ORDER BY sm.created_at DESC, sm.movement_id DESC
LIMIT 200
";

$stmt = $conn->prepare($sql);

if (($user['role'] ?? '') === 'VARASTO') {
    $stmt->execute([':user_id' => $currentUserId]);
} else {
    $stmt->execute();
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="utf-8">
    <title>
    JT Varasto - <?= (($user['role'] ?? '') === 'ADMIN') ? 'Tapahtumaloki' : 'Oma toiminta' ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="img/favicon.ico" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
<div class="container-fluid position-relative d-flex p-0">
    <?php include_once "partials/app-sidebar.php"; ?>

    <div class="content">
        <?php include_once "partials/navbar.php"; ?>

        <div class="container-fluid pt-4 px-4">
            <div class="bg-secondary rounded p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="mb-0">
                        <?= (($user['role'] ?? '') === 'ADMIN') ? 'Tapahtumaloki' : 'Oma toiminta' ?>
                    </h6>
                    <a href="stock-movements-new.php" class="btn btn-primary btn-sm">+ Kirjaa uusi tapahtuma</a>
                </div>

                <div class="table-responsive">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th>Aika</th>
                                <th>Tapahtuma</th>
                                <th>Tuote</th>
                                <th class="text-end">Määrä</th>
                                <th>Tekijä</th>
                                <th>Huomio</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($rows) > 0): ?>
                            <?php foreach ($rows as $r): ?>
                                <?php $t = $r["movement_type"]; ?>
                                <tr>
                                    <td><?= htmlspecialchars($r["created_at"]) ?></td>
                                    <td>
                                        <span class="badge <?= $typeBadge[$t] ?? "bg-secondary" ?>">
                                            <?= htmlspecialchars($typeNames[$t] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($r["sku"]) ?> - <?= htmlspecialchars($r["product_name"]) ?></td>
                                    <td class="text-end"><?= (int)$r["quantity"] ?> <?= htmlspecialchars($r["unit"]) ?></td>
                                    <td><?= htmlspecialchars($r["created_by_name"] ?? "-") ?></td>
                                    <td><?= htmlspecialchars($r["note"] ?? "") ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-muted">
                                    Ei vielä tapahtumia. Lisää ensimmäinen kohdasta <strong>Uusi tapahtuma</strong>.
                                </td>
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