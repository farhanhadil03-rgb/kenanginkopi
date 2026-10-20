<?php
session_start();
require "db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    die("Access denied!");
}

if (!isset($_GET['store_id'])) {
    die("Store ID is missing!");
}
$store_id = intval($_GET['store_id']);

$errors = [];
$name = "";
$price = "";
$desc = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name  = trim($_POST["coffee_name"]);
    $price = intval($_POST["coffee_price"]);
    $desc  = trim($_POST["coffee_desc"]);

    if ($name === "" || !preg_match("/^[a-zA-Z ]+$/", $name)) {
        $errors['name'] = "Name must be alphabetic.";
    }

    if ($price < 10000 || $price > 100000) {
        $errors['price'] = "Price must be 10.000 - 100.000.";
    }

    if ($desc === "" || str_word_count($desc) < 3) {
        $errors['desc'] = "Description min 3 words.";
    }

    if (empty($errors)) {

        // masukan ke database Coffee
        $stmt = $conn->prepare("
            INSERT INTO Coffee (coffee_name, price, description) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sis",$name,$price,$desc);
        $stmt->execute();

        $coffee_id = $stmt->insert_id;

        // disimpan di database STORECOFFEE
        $link = $conn->prepare("
            INSERT INTO StoreCoffee (store_id, coffee_id) 
            VALUES (?,?)
        ");
        $link->bind_param("ii",$store_id,$coffee_id);
        $link->execute();

        header("Location: manage_coffee.php?store_id=$store_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Coffee</title>
    <link rel="stylesheet" href="manage_store.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<?php include "navbar.php"; ?>

<div class="page">
    <div class="form-card">

        <h2 class="title">Add Coffee</h2>

        <form method="POST" class="store-form">

            <label>Coffee Name:</label>
            <input type="text" name="coffee_name" value="<?= htmlspecialchars($name) ?>">
            <p class="error"><?= $errors['name'] ?? '' ?></p>

            <label>Price (Rp):</label>
            <input type="number" name="coffee_price" min="10000" max="100000" value="<?= htmlspecialchars($price) ?>">
            <p class="error"><?= $errors['price'] ?? '' ?></p>

            <label>Description:</label>
            <textarea name="coffee_desc"><?= htmlspecialchars($desc) ?></textarea>
            <p class="error"><?= $errors['desc'] ?? '' ?></p>

            <button type="submit" class="btn-submit">Add</button>
        </form>

        <a href="manage_coffee.php?store_id=<?= $store_id ?>" class="btn-back">← Back</a>
    </div>
</div>

<footer>© 2025 KenanginKopi</footer>

</body>
</html>
