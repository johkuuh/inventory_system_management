<?php 

session_start();
if (isset($_SESSION['user'])) {
   header("location: dashboard.php");
}

?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JT Kotisivu - Varastonhallintajärjestelmä</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <h1>Kirjautunen!</h1>
    <a href="login.php">Kirjaudu sisään tästä!</a>
</body>
</html>