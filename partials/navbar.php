<?php
// navigointipalkki käyttää samaa sessiota kuin dasboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
$name = $user['name'] ?? 'Käyttäjä';
$role = $user['role'] ?? '';
?>


<nav class="navbar navbar-expand bg-secondary navbar-dark sticky-top px-4 py-0">
    <a href="#" class="sidebar-toggler flex-shrink-0">
        <i class="fa fa-bars"></i>
    </a>

    <div class="navbar-nav align-items-center ms-auto">
        <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                <div class="user-avatar me-2">
                    <i class="fa fa-user text-primary"></i>
                </div>

                <span class="d-none d-lg-inline-flex align-items-center">
                    <?= htmlspecialchars($name) ?>

                    <?php if (!empty($role)): ?>
                        <span class="ms-2 badge bg-primary">
                            <?= htmlspecialchars($role) ?>
                        </span>
                    <?php endif; ?>
                </span>
            </a>

            <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                <a href="database/logout.php" class="dropdown-item">Kirjaudu ulos</a>
            </div>
        </div>
    </div>
</nav>