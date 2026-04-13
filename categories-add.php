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

$error = "";

function makePrefix(string $name): string
{
    $name = trim($name);
    $name = mb_strtoupper($name, 'UTF-8');

    $replace = [
        'Ä' => 'A',
        'Ö' => 'O',
        'Å' => 'A'
    ];
    $name = strtr($name, $replace);

    $name = preg_replace('/[^A-Z0-9]/u', '', $name);

    return mb_substr($name, 0, 2, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $codeStart = 1001;

    if ($name === '') {
        $error = "Anna kategorian nimi.";
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE name = :name");
        $stmt->execute([':name' => $name]);

        if ((int)$stmt->fetchColumn() > 0) {
            $error = "Kategoria on jo olemassa.";
        } else {
            $codePrefix = makePrefix($name);

            $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE code_prefix = :code_prefix");
            $stmt->execute([':code_prefix' => $codePrefix]);

            if ((int)$stmt->fetchColumn() > 0) {
                $error = "Automaattisesti muodostettu tunnusprefixi ($codePrefix) on jo käytössä. Nimeä kategoria hieman eri tavalla.";
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO categories (name, code_prefix, code_start)
                    VALUES (:name, :code_prefix, :code_start)
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':code_prefix' => $codePrefix,
                    ':code_start' => $codeStart
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
    <title>Lisää kategoria</title>
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
                <h6 class="mb-4">Lisää kategoria</h6>

                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Kategorian nimi</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Tallenna</button>
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