<?php
session_start();
require "db.php";

//  valiadasi untuk login nya user
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'User') {
    die("<h2>Only logged-in users can access the cart.</h2>");
}

$user_id = $_SESSION['user']['user_id'];
$role = $_SESSION['user']['role'];


// tampilan supaya seperti case
$cartItems = [];
$total_items = 0;
$total_price = 0;
$successMessage = "";

// mepastikan cart session ada
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// -- buat delet (via POST atau GET) --
if (isset($_POST['delete']) && isset($_POST['coffee_id'])) {
    $del = intval($_POST['coffee_id']);
    unset($_SESSION['cart'][$del]);
    header("Location: cart.php");
    exit;
}
if (isset($_GET['delete'])) { // tamabahin aja si hehe GET delete (optional)
    $del = intval($_GET['delete']);
    unset($_SESSION['cart'][$del]);
    header("Location: cart.php");
    exit;
}

// -- buat apdate biar kalo ada mau nambah pesanan (single update) --
if (isset($_POST['update']) && isset($_POST['coffee_id']) && isset($_POST['qty'])) {
    $cid = intval($_POST['coffee_id']);
    $newQty = max(1, intval($_POST['qty']));
    if (isset($_SESSION['cart'][$cid])) {
        $_SESSION['cart'][$cid] = $newQty;
    }
    header("Location: cart.php");
    exit;
}

// -- buat bayar --
if (isset($_POST['pay'])) {

    if (empty($_SESSION['cart'])) {
        $successMessage = "Cart empty. Cannot checkout.";
    } else {
        // masuakn transaksi
        $stmt = $conn->prepare("INSERT INTO Transactions (user_id, store_id) VALUES (?, ?)");
        $store_id = $_SESSION['store_id'] ?? 1;
        $stmt->bind_param("ii", $user_id, $store_id);
        $stmt->execute();
        $transactionID = $conn->insert_id;

        // masukan details
        foreach ($_SESSION['cart'] as $coffee_id => $qty) {
            // mengambil harga saat ini
            $r = $conn->query("SELECT price FROM Coffee WHERE coffee_id = " . intval($coffee_id));
            $row = $r->fetch_assoc();
            $price = $row['price'] ?? 0;
            $subtotal = $price * intval($qty);

            $stmt2 = $conn->prepare("INSERT INTO TransactionDetails (transaction_id, coffee_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("iiii", $transactionID, $coffee_id, $qty, $subtotal);
            $stmt2->execute();
        }

        // mengkosongkan cart
        $_SESSION['cart'] = [];

        $successMessage = "Payment successful! Your Transaction ID: " . $transactionID;
    }
}

// -- baut cartItems untuk ditampilkan --
if (!empty($_SESSION['cart'])) {
    // mengambil semua coffee id yang ada
    $ids = array_keys($_SESSION['cart']);
    $ids = array_map('intval', $ids);
    $idList = implode(",", $ids);

    if ($idList !== "") {
        $sql = "SELECT coffee_id, coffee_name, description, price FROM Coffee WHERE coffee_id IN ($idList)";
        $res = $conn->query($sql);
        while ($r = $res->fetch_assoc()) {
            $cid = $r['coffee_id'];
            $qty = intval($_SESSION['cart'][$cid]);
            $subtotal = $r['price'] * $qty;

            $cartItems[] = [
                'coffee_id' => $cid,
                'coffee_name' => $r['coffee_name'],
                'description' => $r['description'],
                'price' => $r['price'],
                'qty' => $qty,
                'subtotal' => $subtotal,
                'store_id' => $_SESSION['store_id'] ?? 'S313'
            ];

            $total_items += $qty;
            $total_price += $subtotal;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Shopping Cart</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<main class="page">
    <section class="cart-wrapper">

        <h2 class="cart-title">Shopping Cart</h2>

        <div class="cart-summary">
            <?= $total_items ?> item(s) - Total: Rp <?= number_format($total_price,0,',','.') ?>
        </div>

        <?php if ($successMessage): ?>
            <div class="pay-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
    <div class="empty-cart-box">
        <?php if ($successMessage): ?>
            <div class="pay-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <p class="empty-cart-text">Cart is Empty.</p>

        <a href="home.php" class="order-now-btn">Order Coffee Now</a>
    </div>
<?php else: ?>


            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Store ID</th>
                        <th>Coffee</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['store_id']) ?></td>
                        <td><?= htmlspecialchars($item['coffee_name']) ?></td>
                        <td><?= htmlspecialchars($item['description']) ?></td>
                        <td>Rp <?= number_format($item['price'],0,',','.') ?></td>
                        <td>
                            <form method="POST" style="display:flex; gap:8px; align-items:center;">
                                <input type="hidden" name="coffee_id" value="<?= $item['coffee_id'] ?>">
                                <input type="number" name="qty" class="qty-input" value="<?= $item['qty'] ?>" min="1">
                                <button type="submit" name="update" class="update-btn">Update</button>
                            </form>
                        </td>
                        <td>Rp <?= number_format($item['subtotal'],0,',','.') ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="coffee_id" value="<?= $item['coffee_id'] ?>">
                                <button type="submit" name="delete" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <form method="POST">
                <button type="submit" name="pay" class="pay-btn">Pay</button>
            </form>

        <?php endif; ?>
    </section>
</main>

<footer>© 2025 KenanginKopi</footer>
</body>
</html>
