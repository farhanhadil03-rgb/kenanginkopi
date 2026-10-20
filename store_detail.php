<?php
session_start();
require "db.php"; 

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? "Guest";


// cek apakan store_id dikirim url
if (isset($_GET['store_id'])) {
    $store_id = intval($_GET['store_id']);
} elseif (isset($_GET['id'])) {
    $store_id = intval($_GET['id']);
} else {
    die("<h2>ERROR: Store ID tidak dikirim.</h2>");
}


// mengabil store data
$storeQuery = $conn->prepare("
    SELECT store_name, store_location 
    FROM Store 
    WHERE store_id = ?
");
$storeQuery->bind_param("i", $store_id);
$storeQuery->execute();
$store = $storeQuery->get_result()->fetch_assoc();

if (!$store) {
    die("<h2>ERROR: Store dengan ID $store_id tidak ditemukan di database.</h2>");
}

// ambil semua data kopi
$coffeeQuery = $conn->prepare("
    SELECT c.coffee_id, c.coffee_name, c.price, c.description
    FROM Coffee c
    JOIN StoreCoffee sc ON c.coffee_id = sc.coffee_id
    WHERE sc.store_id = ?
");
$coffeeQuery->bind_param("i", $store_id);
$coffeeQuery->execute();
$coffees = $coffeeQuery->get_result();


// masukin ke cart
$successMsg = "";

if ($role === "User" && $_SERVER["REQUEST_METHOD"] === "POST") {

    $coffee_id = intval($_POST['coffee_id']);
    $amount = intval($_POST['amount']);

    if ($amount < 1) $amount = 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$coffee_id])) {
        $_SESSION['cart'][$coffee_id] += $amount;
    } else {
        $_SESSION['cart'][$coffee_id] = $amount;
    }

    // simpan store id ke session
    $_SESSION['store_id'] = $store_id;

    $successMsg = "Coffee added to cart!";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($store['store_name']) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="manage_store.css">
</head>
<body>

<?php include "navbar.php"; ?>

<main class="page">
    <section class="card">

        <div class="store-header">
            <h2 class="card-title"><?= htmlspecialchars($store['store_name']) ?></h2>

            <?php if ($role === "User"): ?>
                <a href="cart.php" class="cart-btn cart-button">🛒 Cart</a>
            <?php endif; ?>
        </div>

        <p class="store-location">
            <b>Location:</b> <?= htmlspecialchars($store['store_location']) ?>
        </p>

        <?php if (!empty($successMsg)): ?>
            <div class="success-message"><?= $successMsg ?></div>
        <?php endif; ?>

        <h3 class="menu-title">Menu</h3>

        <?php while ($c = $coffees->fetch_assoc()): ?>
            <div class="menu-box">

                <h3 class="coffee-title">
                    <?= htmlspecialchars($c['coffee_name']) ?>
                    <span class="coffee-price">
                        Rp <?= number_format($c['price'], 0, ',', '.') ?>
                    </span>
                </h3>

                <p class="coffee-desc"><?= htmlspecialchars($c['description']) ?></p>

                <?php if ($role === "User"): ?>
                <form method="POST" class="add-cart-form">
                    <input type="hidden" name="coffee_id" value="<?= $c['coffee_id'] ?>">

                    <label class="amount-label">Amount:</label>
                    <input type="number" name="amount" value="1" min="1" class="amount-input">

                    <button type="submit" class="add-cart-btn">Add to Cart</button>
                </form>

                <?php endif; ?>

            </div>
        <?php endwhile; ?>

    </section>
</main>

<footer>© 2025 KenanginKopi</footer>

</body>
</html>
