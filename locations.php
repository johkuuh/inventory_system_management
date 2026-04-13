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

$stmt = $conn->query("
    SELECT location_id, code, description, active
    FROM locations
    WHERE active = 1
    ORDER BY code
");
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Varastopaikat</title>
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
                    <h6 class="mb-0">Varastopaikat</h6>
                    <a href="locations-add.php" class="btn btn-primary">+ Lisää paikka</a>
                </div>

                <div class="table-responsive">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th>Tunnus</th>
                                <th>Kuvaus</th>
                                <th>Toiminnot</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($locations) > 0): ?>
                                <?php foreach ($locations as $l): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($l['code']) ?></td>
                                        <td><?= htmlspecialchars($l['description']) ?></td>
                                        <td>
                                            <a href="locations-edit.php?id=<?= (int)$l['location_id'] ?>" class="btn btn-sm btn-primary">
                                                Muokkaa
                                            </a>

                                            <a href="locations-delete.php?id=<?= (int)$l['location_id'] ?>" 
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Haluatko varmasti poistaa tämän varastopaikan?');">
                                                Poista
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-muted">Ei varastopaikkoja.</td>
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