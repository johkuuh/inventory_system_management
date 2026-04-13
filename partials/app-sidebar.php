<!-- Sivupalkki -->
<div class="sidebar pe-4 pb-3">
    <nav class="navbar jt-nav">
        <a href="dashboard.php" class="navbar-brand mx-4 mb-3">
            <h3 class="jt-brand"><i class="fa fa-boxes-stacked me-2"></i>JT Varasto</h3>
        </a>

        <div class="d-flex align-items-center ms-4 mb-3">
            <div class="position-relative">
                <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
            </div>
            <div class="ms-3">
                <h6 class="mb-0"><?= htmlspecialchars($user['name'] ?? '') ?></h6>
                <span><?= htmlspecialchars($user['role'] ?? '') ?></span>
            </div>
        </div>

        <div class="navbar-nav w-100">

            <!-- ETUSIVU -->
            <a href="dashboard.php" class="nav-item nav-link">
                <i class="fa fa-home me-2"></i>Etusivu
            </a>

            <div class="dropdown-divider my-2"></div>

            <!-- VARASTO -->
            <div class="nav-item text-muted small px-3">VARASTO</div>

            <a href="stock-balances.php" class="nav-item nav-link">
                <i class="fa fa-layer-group me-2"></i>Saldot
            </a>

            <div class="dropdown-divider my-2"></div>

            <!-- TAPAHTUMAT -->
            <div class="nav-item text-muted small px-3">TAPAHTUMAT</div>

            <a href="stock-movements-new.php" class="nav-item nav-link">
                <i class="fa fa-plus me-2"></i>Uusi tapahtuma
            </a>

            <a href="stock-movements.php" class="nav-item nav-link">
                <i class="fa fa-history me-2"></i>
                <?= (($user['role'] ?? '') === 'ADMIN') ? 'Tapahtumaloki' : 'Oma toiminta' ?>
            </a>

            <?php if (($user['role'] ?? '') === 'ADMIN'): ?>
                <div class="dropdown-divider my-2"></div>

                <!-- HALLINTA -->
                <div class="nav-item text-muted small px-3">HALLINTA</div>

                <a href="products.php" class="nav-item nav-link">
                    <i class="fa fa-box me-2"></i>Tuotteet
                </a>

                <a href="categories.php" class="nav-item nav-link">
                    <i class="fa fa-tags me-2"></i>Kategoriat
                </a>

                <a href="locations.php" class="nav-item nav-link">
                    <i class="fa fa-warehouse me-2"></i>Varastopaikat
                </a>

                <a href="reports.php" class="nav-item nav-link">
                    <i class="fa fa-chart-line me-2"></i>Raportit
                </a>

                <a href="user-add.php" class="nav-item nav-link">
                    <i class="fa fa-users me-2"></i>Käyttäjät
                </a>
            <?php endif; ?>

            <div class="dropdown-divider my-2"></div>

        </div>
    </nav>
</div>
<!-- Sivupalkki -->