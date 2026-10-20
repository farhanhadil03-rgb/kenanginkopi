<?php
require 'db.php';
session_start();
$loginSuccess = $_SESSION['login_success'] ?? false;
unset($_SESSION['login_success']);

$user = $_SESSION['user'] ?? null;

// mengambil semua store dari database
$result = $conn->query("SELECT store_id, store_name FROM store");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Kenangin Kopi - Home</title>
<link rel="stylesheet" href="style.css">
</head>
<body>


<?php include 'navbar.php'; ?>



<main class="page">
    <section class="card">
        <h2 class="card-title">KenanginKopi</h2>
        <p>favourable taste for your mood</p>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <section class="store-card">
                    <h3><?= htmlspecialchars($row['store_name']) ?></h3>
                    <a href="store_detail.php?store_id=<?= $row['store_id'] ?>" class="view-btn">
                        <button>View Details</button>
                    </a>
                </section>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No store available.</p>
        <?php endif; ?>

    </section>
</main>


<footer>© 2025 KenanginKopi</footer>

<script src="date.js"></script>

</body>
</html>
