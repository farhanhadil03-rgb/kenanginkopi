<?php
session_start();
require "db.php";

// validasi admin biar yang masuk cuma bisa admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    die("Access denied!");
}

// ini biar keren aja si hehe
if (!isset($_GET['store_id'])) {

    $stores = $conn->query("SELECT store_id, store_name FROM Store ORDER BY store_name ASC");
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>Pilih Store</title>
        <link rel="stylesheet" href="style.css">
        <!-- gak tau lagi harus to dimana -->
        <style>
            .store-picker {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background-image: url('./kopi\ 2.jpg');
            }

            .picker-card {
                background: white;
                padding: 30px;
                border-radius: 12px;
                width: 400px;
                text-align: center;
                box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            }

            .picker-card h2 {
                margin-bottom: 20px;
            }

            select, button {
                width: 100%;
                padding: 10px;
                margin-top: 15px;
                border-radius: 8px;
                border: 1px solid #ccc;
            }

            button {
                background: #c69b6d;
                color: white;
                font-weight: bold;
                border: none;
                cursor: pointer;
            }

            button:hover {
                background: #b18657;
            }
        </style>
    </head>
    <body>

    <div class="store-picker">
        <div class="picker-card">
            <h2>Pilih Store</h2>

            <form method="GET">
                <select name="store_id" required>
                    <option value="">-- Pilih Store --</option>

                    <?php while ($s = $stores->fetch_assoc()): ?>
                        <option value="<?= $s['store_id'] ?>">
                            <?= htmlspecialchars($s['store_name']) ?>
                        </option>
                    <?php endwhile; ?>

                </select>

                <button type="submit">Lanjut</button>
            </form>
        </div>
    </div>

    </body>
    </html>

    <?php
    exit;
}

// cuman bisa nambahin kopi kalo ada store id nya
$store_id = intval($_GET['store_id']);

// mengambil nama store
$storeStmt = $conn->prepare("SELECT store_name FROM Store WHERE store_id = ?");
$storeStmt->bind_param("i", $store_id);
$storeStmt->execute();
$storeData = $storeStmt->get_result()->fetch_assoc();
$storeName = $storeData['store_name'] ?? "Store Tidak Diketahui";

// buat delet kopi dari store
if (isset($_GET['delete'])) {
    $coffee_id = intval($_GET['delete']);

    $delStmt = $conn->prepare("DELETE FROM StoreCoffee WHERE coffee_id = ? AND store_id = ?");
    $delStmt->bind_param("ii", $coffee_id, $store_id);
    $delStmt->execute();

    header("Location: manage_coffee.php?store_id=$store_id");
    exit;
}

// mengabil kopi per store
$coffeeStmt = $conn->prepare("
    SELECT c.coffee_id, c.coffee_name, c.price, c.description
    FROM Coffee c
    JOIN StoreCoffee sc ON c.coffee_id = sc.coffee_id
    WHERE sc.store_id = ?
");
$coffeeStmt->bind_param("i", $store_id);
$coffeeStmt->execute();
$coffeeList = $coffeeStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Coffee</title>
    <link rel="stylesheet" href="manage_store.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="page">
    <div class="coffee-container">

        <h2 class="title">Manage Coffee - <?= htmlspecialchars($storeName) ?></h2>
        <a href="coffee_add.php?store_id=<?= $store_id ?>" class="add-btn">Add Coffee</a>

        <table class="coffee-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

            <?php if ($coffeeList->num_rows > 0): ?>
                <?php while ($row = $coffeeList->fetch_assoc()): ?>
                <tr>
                    <td>C<?= $row['coffee_id'] ?></td>
                    <td><?= htmlspecialchars($row['coffee_name']) ?></td>
                    <td>Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>
                        <a href="manage_coffee.php?store_id=<?= $store_id ?>&delete=<?= $row['coffee_id'] ?>"
                           class="delete-btn"
                           onclick="return confirm('Delete this coffee?');">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" align="center">Belum ada coffee di store ini.</td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>

        <a href="manage_store.php" class="back-btn">← Back</a>
    </div>
</div>

<footer>© 2025 KenanginKopi</footer>

</body>
</html>
