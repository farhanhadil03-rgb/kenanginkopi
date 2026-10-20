<?php
session_start();
require "db.php";

// mau liat harus login
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['user_id'])) {
    die("You must login to view this page.");
}

$user_id = $_SESSION['user']['user_id'];
$user_name = $_SESSION['user']['username'];

// mengambil data transaksi dari data base user
$query = $conn->prepare("
    SELECT 
        t.transaction_id,
        t.transaction_date,
        s.store_name,
        s.store_location
    FROM Transactions t
    JOIN Store s ON s.store_id = t.store_id
    WHERE t.user_id = ?
    ORDER BY t.transaction_date DESC
");
$query->bind_param("i", $user_id);
$query->execute();
$transactions = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<?php include "navbar.php"; ?>

<div class="history-container">
    <h2 class="history-title">Order History</h2>

    <?php if ($transactions->num_rows === 0): ?>
        <div class="no-history">
            You have no transactions yet.<br>
            <a href="home.php" class="back-home">Order Coffee Now</a>
        </div>
    <?php else: ?>

        <?php while ($row = $transactions->fetch_assoc()): ?>

            <?php
            $tid = $row['transaction_id'];

            // menambil item per transaksi
            $itemQ = $conn->prepare("
                SELECT 
                    c.coffee_name,
                    td.quantity,
                    td.subtotal
                FROM TransactionDetails td
                JOIN Coffee c ON c.coffee_id = td.coffee_id
                WHERE td.transaction_id = ?
            ");
            $itemQ->bind_param("i", $tid);
            $itemQ->execute();
            $items = $itemQ->get_result();

            // menghitung total
            $total_price = 0;
            foreach ($items as $i) {
                $total_price += $i['subtotal'];
            }
            $items->data_seek(0);
            ?>

            <div class="history-card">
                <h3 class="transaction-id">Transaction ID: T<?= $tid ?></h3>
                <p><b>Date:</b> <?= date("d M Y, H:i", strtotime($row['transaction_date'])) ?></p>
                <p><b>Store:</b> <?= $row['store_name'] ?> (<?= $row['store_location'] ?>)</p>

                <h4>Ordered Items:</h4>

                <table class="history-table">
                    <tr>
                        <th>Coffee</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>

                    <?php foreach ($items as $i): ?>
                    <tr>
                        <td><?= htmlspecialchars($i['coffee_name']) ?></td>
                        <td><?= $i['quantity'] ?></td>
                        <td>Rp <?= number_format($i['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>

                    <tr class="total-row">
                        <td colspan="2" class="total-label">Total:</td>
                        <td class="total-value">Rp <?= number_format($total_price, 0, ',', '.') ?></td>
                    </tr>
                </table>
            </div>

        <?php endwhile; ?>

    <?php endif; ?>

    <a href="profile.php" class="back-btn">← Back</a>
</div>

</body>
</html>
