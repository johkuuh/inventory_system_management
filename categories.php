<?php
require_once 'database/connexion.php';
session_start();

if (($_SESSION['user']['role'] ?? '') !== 'ADMIN') {
    header("Location: dashboard.php");
    exit;
}

$user = $_SESSION['user'];
$message = "";

/* POISTO */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = :id");
    $stmt->execute([':id' => $id]);
    $count = (int)$stmt->fetchColumn();

    if ($count > 0) {
        $message = "Kategoriaa ei voi poistaa, koska siihen liittyy tuotteita.";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = :id");
        $stmt->execute([':id' => $id]);
        header("Location: categories.php");
        exit;
    }
}

/* HAE KATEGORIAT */
$sql = "
    SELECT c.category_id, c.name, COUNT(p.product_id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.category_id
    GROUP BY c.category_id, c.name
    ORDER BY c.name
";
$stmt = $conn->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Kategoriat</title>
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
                    <h6 class="mb-0">Kategoriat</h6>
                    <a href="categories-add.php" class="btn btn-primary">+ Lisää kategoria</a>
                </div>

                <?php if ($message !== ""): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table text-start align-middle table-bordered table-hover mb-0">
                        <thead>
                            <tr class="text-white">
                                <th>Nimi</th>
                                <th>Tuotteita</th>
                                <th>Toiminnot</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['name']) ?></td>
                                    <td><?= (int)$c['product_count'] ?></td>
                                   <td>
                                        <a href="categories-edit.php?id=<?= (int)$c['category_id'] ?>" class="btn btn-sm btn-primary">
                                            Muokkaa
                                        </a>

                                        <?php if ((int)$c['product_count'] === 0): ?>
                                            <a href="categories.php?delete=<?= (int)$c['category_id'] ?>"
                                            class="btn btn-sm btn-outline-light"
                                            onclick="return confirm('Poistetaanko kategoria varmasti?')">
                                                Poista
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted ms-2"></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (count($categories) === 0): ?>
                                <tr>
                                    <td colspan="4" class="text-muted">Ei kategorioita.</td>
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