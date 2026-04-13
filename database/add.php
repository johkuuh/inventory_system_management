<?php
session_start();

require_once 'connexion.php';

// Varmistus: vain kirjautuneena
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

// (Voit jättää tämän, mutta pakotetaan varmuuden vuoksi oikeaan tauluun)
$table_name = 'users';

if (
    !empty($_POST["name"]) &&
    !empty($_POST["email"]) &&
    !empty($_POST["password"]) &&
    !empty($_POST["role"])
) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Sallitut roolit
    if (!in_array($role, ['ADMIN', 'VARASTO'], true)) {
        $_SESSION['response'] = [
            'success' => false,
            'message' => 'Virheellinen rooli.'
        ];
        header('Location: ../user-add.php');
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Käytä prepared statementia (turvallisempi)
        $stmt = $conn->prepare("
            INSERT INTO users (name, email, password_hash, role)
            VALUES (:name, :email, :password_hash, :role)
        ");

        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password_hash' => $password_hash,
            ':role' => $role
        ]);

        $_SESSION['response'] = [
            'success' => true,
            'message' => $name . ' lisättiin käyttäjäksi.'
        ];
    } catch (PDOException $e) {
        $_SESSION['response'] = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }

    header('Location: ../user-add.php');
    exit;

} else {
    $_SESSION['response'] = [
        'success' => false,
        'message' => 'Täytä kaikki kentät.'
    ];
    header('Location: ../user-add.php');
    exit;
}