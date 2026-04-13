<?php
session_start();

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'database/connexion.php';

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([
        ':email' => $username
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['password_hash']) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit;
    } else {
        $error_message = "Tarkista käyttäjätunnus ja salasana";
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Varastonhallintajärjestelmä - Kirjautuminen</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
</head>

<body id="loginBody">

<?php if (!empty($error_message)) { ?>
    <div id="errorMessage">
        <p><?= htmlspecialchars($error_message) ?></p>
    </div>
<?php } ?>

<div class="container">
    <div class="loginHeader">
        <h1>JT</h1>
        <p>Varastonhallintajärjestelmä</p>
    </div>
    <div class="loginBody">
        <form action="login.php" method="post" autocomplete="off">
            <div class="loginInputsContainer">
                <label>Käyttäjätunnus</label>
                <input
                    placeholder="Username"
                    type="text"
                    name="username"
                    autocomplete="off"
                    autocorrect="off"
                    autocapitalize="off"
                    spellcheck="false"
                >
            </div>
            <div class="loginInputsContainer">
                <label>Salasana</label>
                <input
                    placeholder="Password"
                    type="password"
                    name="password"
                    autocomplete="new-password"
                    autocorrect="off"
                    autocapitalize="off"
                    spellcheck="false"
                >
            </div>
            <div class="loginButtonContainer">
                <button type="submit">Kirjaudu</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>