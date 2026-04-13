<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once "database/connexion.php";

$user = $_SESSION["user"];
$err = "";
$ok = "";

function getTotalStock(PDO $conn, int $productId): int
{
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(quantity), 0)
        FROM stock_balances
        WHERE product_id = ?
    ");
    $stmt->execute([$productId]);
    return (int)$stmt->fetchColumn();
}

function getBestSourceLocationId(PDO $conn, int $productId): ?int
{
    $stmt = $conn->prepare("
        SELECT location_id
        FROM stock_balances
        WHERE product_id = ? AND quantity > 0
        ORDER BY quantity DESC, location_id ASC
        LIMIT 1
    ");
    $stmt->execute([$productId]);
    $id = $stmt->fetchColumn();

    return $id ? (int)$id : null;
}

$products = $conn->query("
    SELECT product_id, sku, name, unit
    FROM products
    WHERE active = 1
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$productMap = [];
foreach ($products as $p) {
    $productMap[(int)$p['product_id']] = $p;
}

$selectedProductName = '';
$selectedProductId = (int)($_GET['product_id'] ?? 0);
$selectedType = $_GET['type'] ?? '';
$selectedSign = $_GET['sign'] ?? '+';

$currentProductId = (int)($_POST['product_id'] ?? $selectedProductId ?? 0);

if (!in_array($selectedType, ['IN', 'OUT', 'ADJUST'], true)) {
    $selectedType = '';
}

if (!in_array($selectedSign, ['+', '-'], true)) {
    $selectedSign = '+';
}

$locations = $conn->query("
    SELECT location_id, code, description
    FROM locations
    WHERE active = 1
    ORDER BY code
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = $_POST["movement_type"] ?? "";
    $qty = (int)($_POST["quantity"] ?? 0);
    $note = trim($_POST["note"] ?? "");
    $uid = (int)($_SESSION["user"]["user_id"] ?? 0);
    $sign = (($_POST["adjust_sign"] ?? "+") === "-") ? -1 : 1;
    $selectedLocationId = (int)($_POST["location_id"] ?? 0);

  $pid = (int)($_POST["product_id"] ?? 0);

    if (!in_array($type, ["IN", "OUT", "ADJUST"], true) || $qty <= 0 || $uid <= 0) {
        $err = "Tarkista tiedot.";
    } elseif ($pid <= 0 || !isset($productMap[$pid])) {
    $err = "Valitse tuote listasta.";
    }

    $sourceLocationId = null;
    $targetLocationId = null;

    if (!$err) {
        if ($type === "IN") {
            if ($selectedLocationId <= 0) {
                $err = "Valitse varastopaikka.";
            } else {
                $targetLocationId = $selectedLocationId;
            }

        } elseif ($type === "OUT") {
            $currentTotal = getTotalStock($conn, $pid);

            if ($currentTotal < $qty) {
                $err = "Saldo ei riitä. Tuotteen kokonaissaldo on $currentTotal.";
            } else {
                $sourceLocationId = getBestSourceLocationId($conn, $pid);
                if (!$sourceLocationId) {
                    $err = "Varastopaikkaa ei löytynyt ulosotolle.";
                }
            }

        } elseif ($type === "ADJUST") {
            if ($sign === 1) {
                if ($selectedLocationId <= 0) {
                    $err = "Valitse varastopaikka lisäykselle.";
                } else {
                    $targetLocationId = $selectedLocationId;
                }
            } else {
                $currentTotal = getTotalStock($conn, $pid);

                if ($currentTotal < $qty) {
                    $err = "Saldo ei riitä. Tuotteen kokonaissaldo on $currentTotal.";
                } else {
                    $sourceLocationId = getBestSourceLocationId($conn, $pid);
                    if (!$sourceLocationId) {
                        $err = "Varastopaikkaa ei löytynyt vähennykselle.";
                    }
                }
            }
        }
    }

    if (!$err) {
        $conn->beginTransaction();

        try {
            $stmt = $conn->prepare("
                INSERT INTO stock_movements (movement_type, created_by, created_at, note)
                VALUES (?, ?, NOW(), ?)
            ");
            $stmt->execute([$type, $uid, $note]);
            $mid = (int)$conn->lastInsertId();

            $stmt = $conn->prepare("
                INSERT INTO stock_movements_rows (movement_id, product_id, quantity, from_location_id, to_location_id)
                VALUES (?, ?, ?, ?, ?)
            ");

            if ($type === "IN") {
                $stmt->execute([$mid, $pid, $qty, null, $targetLocationId]);
            } elseif ($type === "OUT") {
                $stmt->execute([$mid, $pid, $qty, $sourceLocationId, null]);
            } elseif ($type === "ADJUST" && $sign === 1) {
                $stmt->execute([$mid, $pid, $qty, null, $targetLocationId]);
            } else {
                $stmt->execute([$mid, $pid, $qty, $sourceLocationId, null]);
            }

            $up = $conn->prepare("
                INSERT INTO stock_balances (product_id, location_id, quantity)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
            ");

            if ($type === "IN") {
                $up->execute([$pid, $targetLocationId, $qty]);
            } elseif ($type === "OUT") {
                $up->execute([$pid, $sourceLocationId, -$qty]);
            } elseif ($type === "ADJUST" && $sign === 1) {
                $up->execute([$pid, $targetLocationId, $qty]);
            } else {
                $up->execute([$pid, $sourceLocationId, -$qty]);
            }

            $conn->commit();
            $ok = "Tapahtuma tallennettu.";
            $_POST = [];

        } catch (Exception $e) {
            $conn->rollBack();
            $err = "Tallennus epäonnistui.";
        }
    }
}
?>
<!doctype html>
<html lang="fi">
<head>
    <meta charset="utf-8">
    <title>Uusi varastotapahtuma</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="img/favicon.ico" rel="icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

<div class="container-fluid position-relative d-flex p-0">
    <?php include_once 'partials/app-sidebar.php'; ?>

    <div class="content">
        <?php include_once 'partials/navbar.php'; ?>

        <div class="container-fluid pt-4 px-4">
            <div class="bg-secondary rounded p-4">
                <h4 class="mb-4">Uusi varastotapahtuma</h4>

                <?php if ($err): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
                <?php endif; ?>

                <?php if ($ok): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
                <?php endif; ?>

                <form method="post" autocomplete="off">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label">Tapahtuma</label>
                            <select id="t" name="movement_type" class="form-select" required>
                                <option value="">Valitse...</option>
                                <option value="IN" <?= (($_POST['movement_type'] ?? $selectedType) === 'IN') ? 'selected' : '' ?>>Varastoon</option>
                                <option value="OUT" <?= (($_POST['movement_type'] ?? $selectedType) === 'OUT') ? 'selected' : '' ?>>Varastosta ulos</option>
                                <option value="ADJUST" <?= (($_POST['movement_type'] ?? $selectedType) === 'ADJUST') ? 'selected' : '' ?>>Saldon korjaus</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tuote</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Valitse tuote...</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= (int)$p['product_id'] ?>"
                                        <?= ($currentProductId === (int)$p['product_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['sku'] . ' - ' . $p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Määrä</label>
                            <input
                                name="quantity"
                                type="number"
                                min="1"
                                step="1"
                                class="form-control"
                                placeholder="Määrä"
                                required
                                value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-md-4" id="adj" style="display:none;">
                            <label class="form-label d-block">Korjaus</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="adjust_sign" value="+"
                                    <?= (($_POST['adjust_sign'] ?? $selectedSign) === '+') ? 'checked' : '' ?>>
                                <label class="form-check-label">Lisää saldoa</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="adjust_sign" value="-"
                                    <?= (($_POST['adjust_sign'] ?? $selectedSign) === '-') ? 'checked' : '' ?>>
                                <label class="form-check-label">Vähennä saldoa</label>
                            </div>
                        </div>

                        <div class="col-md-6" id="locationBox" style="display:none;">
                            <label class="form-label">Varastopaikka</label>
                            <select name="location_id" class="form-select">
                                <option value="">Valitse varastopaikka...</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= (int)$loc['location_id'] ?>"
                                        <?= ((int)($_POST['location_id'] ?? 0) === (int)$loc['location_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($loc['code']) ?> - <?= htmlspecialchars($loc['description']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Huomio</label>
                            <input
                                name="note"
                                class="form-control"
                                placeholder="Kirjoita huomio"
                                autocomplete="off"
                                autocorrect="off"
                                autocapitalize="off"
                                spellcheck="false"
                                value="<?= htmlspecialchars($_POST['note'] ?? '') ?>"
                            >
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary">Tallenna</button>
                            <a class="btn btn-outline-light ms-2" href="stock-movements.php">Historia</a>
                            <a class="btn btn-outline-light ms-2" href="stock-balances.php">Saldot</a>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const typeSelect = document.getElementById('t');
const adjBox = document.getElementById('adj');
const locationBox = document.getElementById('locationBox');
const adjustRadios = document.querySelectorAll('input[name="adjust_sign"]');

function updateFormMode() {
    const type = typeSelect.value;
    const adjustMinusChecked = document.querySelector('input[name="adjust_sign"][value="-"]')?.checked;

    adjBox.style.display = (type === 'ADJUST') ? 'block' : 'none';

    if (type === 'IN') {
        locationBox.style.display = 'block';
    } else if (type === 'ADJUST' && !adjustMinusChecked) {
        locationBox.style.display = 'block';
    } else {
        locationBox.style.display = 'none';
    }
}

typeSelect.addEventListener('change', updateFormMode);
adjustRadios.forEach(radio => radio.addEventListener('change', updateFormMode));
updateFormMode();
</script>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>

</body>
</html>