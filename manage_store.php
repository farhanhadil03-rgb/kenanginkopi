<?php
require "db.php";
session_start();

// validasi admin biar yang masuk cuma bisa admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    die("Access Denied!");
}

// buat delet store
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $del = $conn->prepare("DELETE FROM Store WHERE store_id = ?");
    $del->bind_param("i", $id);
    $del->execute();

    header("Location: manage_store.php?msg=deleted");
    exit;
}

// mengabil semua data store
$stores = $conn->query("SELECT * FROM Store ORDER BY store_id ASC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Store</title>
    <link rel="stylesheet" href="manage_store.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<?php include "navbar.php"; ?>

<main class="page">

    <div class="store-container">

        <h2 class="title">Manage Store</h2>

        <a href="store_add.php" class="add-btn">Add Store</a>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <p class="success-msg">Store deleted successfully.</p>
        <?php endif; ?>

        <table class="store-table">
            <thead>
                <tr>
                    <th>Store ID</th>
                    <th>Store Name</th>
                    <th>Location</th>
                    <th>Coffee</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($s = $stores->fetch_assoc()): ?>
                <tr>
                    <td>S<?= $s['store_id'] ?></td>
                    <td><?= htmlspecialchars($s['store_name']) ?></td>
                    <td><?= htmlspecialchars($s['store_location']) ?></td>

                    <td>
                        <a href="manage_coffee.php?store_id=<?= $s['store_id'] ?>" class="manage-btn">
                            Manage
                        </a>

                    </td>

                    <td>
                        <a href="manage_store.php?delete=<?= $s['store_id'] ?>"
                           onclick="return confirm('Delete this store?');"
                           class="delete-btn">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>

        </table>

    </div>

</main>

<footer>© 2025 KenanginKopi</footer>

</body>
</html>
