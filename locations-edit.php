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
    header("Location: locations.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM locations WHERE location_id = :id");
$stmt->execute([':id' => $id]);
$location = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$location) {
    header("Location: locations.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($code === '') {
        $error = "Anna varastopaikan koodi.";
    } else {
        try {
            $stmt = $conn->prepare("
                UPDATE locations
                SET code = :code, description = :description
                WHERE location_id = :id
            ");
            $stmt->execute([
                ':code' => $code,
                ':description' => $description,
                ':id' => $id
            ]);

            header("Location: locations.php");
            exit;
        } catch (PDOException $e) {
            $error = "Koodi on jo käytössä.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Muokkaa varastopaikkaa</title>
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
                <h6 class="mb-4">Muokkaa varastopaikkaa</h6>

                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label">Koodi</label>
                        <input
                            type="text"
                            name="code"
                            class="form-control"
                            value="<?= htmlspecialchars($location['code']) ?>"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="off"
                            spellcheck="false"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kuvaus</label>
                        <input
                            type="text"
                            name="description"
                            class="form-control"
                            value="<?= htmlspecialchars($location['description'] ?? '') ?>"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="off"
                            spellcheck="false"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary">Tallenna</button>
                    <a href="locations.php" class="btn btn-outline-light">Takaisin</a>
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