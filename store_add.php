<?php
session_start();
require "db.php";

// Hanya Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    die("Access denied!");
}

$errors = [];
$name = "";
$location = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["store_name"]);
    $location = $_POST["store_location"];

    // Validasi
    if ($name === "") {
        $errors['name'] = "Store name must be filled.";
    } elseif (str_word_count($name) < 2) {
        $errors['name'] = "Store name must contain at least 2 words.";
    }

    if ($location === "") {
        $errors['location'] = "Store location must be selected.";
    }

    // kalo tidak ada error masukkin ke data base
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO store (store_name, store_location) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $location);
        $stmt->execute();

        header("Location: manage_store.php?msg=added");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Store</title>
    <link rel="stylesheet" href="manage_store.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<?php include "navbar.php"; ?>

<div class="page">
    <div class="form-card">
        <h2 class="form-title">Add Store</h2>

        <form method="POST" class="store-form">

            <label>Store Name:</label>
            <input type="text" name="store_name" value="<?= htmlspecialchars($name) ?>">
            <p class="error"><?= $errors['name'] ?? '' ?></p>

            <label>Store Location:</label>
            <select name="store_location">
                <option value="">-- Choose Location --</option>
                <option value="Jakarta">Jakarta</option>
                <option value="Bandung">Bandung</option>
                <option value="Surabaya">Surabaya</option>
                <option value="Yogyakarta">Yogyakarta</option>
                <option value="Bali">Bali</option>
                <option value="Medan">Medan</option>
            </select>
            <p class="error"><?= $errors['location'] ?? '' ?></p>

            <button type="submit" class="btn-submit">Add</button>
        </form>

        <a href="manage_store.php" class="btn-back">← Back</a>
    </div>
</div>

<footer>© 2025 KenanginKopi</footer>

</body>
</html>
