<?php
session_start();
require_once "database/connexion.php";

if (($_SESSION['user']['role'] ?? '') !== 'ADMIN') {
    header("Location: dashboard.php");
    exit;
}

$user = $_SESSION["user"];

/* Varmistus: vain admin saa hallita käyttäjiä */
if (($user['role'] ?? '') !== 'ADMIN') {
    header("Location: dashboard.php");
    exit;
}

$success = "";
$error = "";

/* LISÄÄ KÄYTTÄJÄ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'VARASTO');

    if ($name === '' || $email === '' || $password === '') {
        $error = "Täytä kaikki kentät.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Sähköpostiosoite ei ole kelvollinen.";
    } elseif (!in_array($role, ['ADMIN', 'VARASTO'], true)) {
        $error = "Virheellinen rooli.";
    } else {
        $check = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $check->execute([$email]);

        if ((int)$check->fetchColumn() > 0) {
            $error = "Tällä sähköpostilla on jo käyttäjä.";
        } else {
            $hashedPassword = $password;

            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password_hash, role)
                VALUES (?, ?, ?, ?)
            ");

            if ($stmt->execute([$name, $email, $hashedPassword, $role])) {
                $success = "Käyttäjä lisättiin onnistuneesti.";
            } else {
                $error = "Käyttäjän lisääminen epäonnistui.";
            }
        }
    }
}

/* POISTA KÄYTTÄJÄ */
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $currentUserId = (int)($user['user_id'] ?? 0);

    if ($deleteId > 0) {
        if ($deleteId === $currentUserId) {
            $error = "Et voi poistaa omaa käyttäjätunnustasi.";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            if ($stmt->execute([$deleteId])) {
                $success = "Käyttäjä poistettiin onnistuneesti.";
            } else {
                $error = "Käyttäjän poistaminen epäonnistui.";
            }
        }
    }
}

/* HAE KÄYTTÄJÄT */
$stmt = $conn->query("
    SELECT user_id, name, email, role
    FROM users
    ORDER BY name ASC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fi">

<head>
    <meta charset="utf-8">
    <title>Käyttäjähallinta</title>
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
            <div class="row g-4">

                <div class="col-sm-12 col-xl-5">
                    <div class="bg-secondary rounded h-100 p-4">
                        <h6 class="mb-4">Lisää käyttäjä</h6>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="post">
                            <input type="hidden" name="add_user" value="1">

                            <div class="mb-3">
                                <label class="form-label">Nimi</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Sähköposti</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Salasana</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Rooli</label>
                                <select class="form-select" name="role" required>
                                    <option value="VARASTO">VARASTO</option>
                                    <option value="ADMIN">ADMIN</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Lisää käyttäjä</button>
                        </form>
                    </div>
                </div>

                <div class="col-sm-12 col-xl-7">
                    <div class="bg-secondary rounded h-100 p-4">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h6 class="mb-0">Nykyiset käyttäjät</h6>
                        </div>

                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-white">
                                        <th>Nimi</th>
                                        <th>Sähköposti</th>
                                        <th>Rooli</th>
                                        <th>Toiminnot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($users) > 0): ?>
                                        <?php foreach ($users as $u): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($u['name']) ?></td>
                                                <td><?= htmlspecialchars($u['email']) ?></td>
                                                <td><?= htmlspecialchars($u['role']) ?></td>
                                                <td>
                                                    <?php if ((int)$u['user_id'] !== (int)($user['user_id'] ?? 0)): ?>
                                                        <a href="user-add.php?delete=<?= (int)$u['user_id'] ?>"
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Poistetaanko käyttäjä varmasti?')">
                                                           Poista
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Oma käyttäjä</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-muted">Ei käyttäjiä.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
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