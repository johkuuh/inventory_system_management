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
    header("Location: categories.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = :id");
$stmt->execute([':id' => $id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: categories.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $codePrefix = strtoupper(trim($_POST['code_prefix'] ?? ''));
    $codeStart = (int)($_POST['code_start'] ?? 1001);

    if ($name === '' || $codePrefix === '') {
        $error = "Täytä kaikki kentät.";
    } elseif (!preg_match('/^[A-Z0-9]{1,5}$/', $codePrefix)) {
        $error = "Tunnusprefixissä saa olla vain isoja kirjaimia ja numeroita (max 5 merkkiä).";
    } else {
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM categories 
            WHERE name = :name AND category_id <> :id
        ");
        $stmt->execute([
            ':name' => $name,
            ':id' => $id
        ]);

        if ((int)$stmt->fetchColumn() > 0) {
            $error = "Kategoria on jo olemassa.";
        } else {
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM categories 
                WHERE code_prefix = :code_prefix AND category_id <> :id
            ");
            $stmt->execute([
                ':code_prefix' => $codePrefix,
                ':id' => $id
            ]);

            if ((int)$stmt->fetchColumn() > 0) {
                $error = "Tunnusprefixi on jo käytössä.";
            } else {
                $stmt = $conn->prepare("
                    UPDATE categories
                    SET name = :name,
                        code_prefix = :code_prefix,
                        code_start = :code_start
                    WHERE category_id = :id
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':code_prefix' => $codePrefix,
                    ':code_start' => $codeStart,
                    ':id' => $id
                ]);

                header("Location: categories.php");
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Muokkaa kategoriaa</title>
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
                <h6 class="mb-4">Muokkaa kategoriaa</h6>

                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Kategorian nimi</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= htmlspecialchars($category['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tunnusprefixi</label>
                        <input type="text" name="code_prefix" class="form-control" maxlength="5"
                               value="<?= htmlspecialchars($category['code_prefix'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Aloitusnumero</label>
                        <input type="number" name="code_start" class="form-control" min="1"
                               value="<?= (int)($category['code_start'] ?? 1001) ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Tallenna muutokset</button>
                    <a href="categories.php" class="btn btn-outline-light">Takaisin</a>
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