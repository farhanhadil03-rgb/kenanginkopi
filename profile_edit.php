<?php
session_start();
require "db.php"; // konesi ke data base


// dicek Login nya
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['user_id']; // dari SESSION saat login
$errors = [];



// mengambil data user dari database
$stmt = $conn->prepare("SELECT full_name, username, email FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

if (!$userData) {
    die("User data not found in database.");
}

$currentUsername = $userData['username'];
$currentEmail = $userData['email'];



// validasi kalo mau edit profile
function is_alpha($s) {
    return preg_match("/^[A-Za-z]+$/", $s);
}

function validate_email_custom($email) {
    $errors = [];

    if ($email === "")
        $errors[] = "Email must be filled.";

    if (substr_count($email, "@") !== 1)
        $errors[] = "Email must contain exactly one '@'.";

    if (str_starts_with($email, "@") || str_starts_with($email, "."))
        $errors[] = "Email cannot start with '@' or '.'.";

    if (str_ends_with($email, "@") || str_ends_with($email, "."))
        $errors[] = "Email cannot end with '@' or '.'.";

    $invalid = ["@@", "..", "@.", ".@"];
    foreach ($invalid as $i) {
        if (strpos($email, $i) !== false)
            $errors[] = "Email contains invalid pattern '$i'.";
    }

    $at = strpos($email, "@");
    if ($at !== false) {
        $domain = substr($email, $at + 1);
        if (!str_contains($domain, "."))
            $errors[] = "Email must contain at least one '.' after '@'.";
    }

    return $errors;
}


// buat update Profile 
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $newUsername = trim($_POST['username'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');

    // Validasi Username
    if ($newUsername === "" || !is_alpha($newUsername)) {
        $errors['username'] = "Username must be alphabetic only.";
    }

    // Validasi Email
    if ($newEmail !== $currentEmail) {

        $emailErrors = validate_email_custom($newEmail);
        if (!empty($emailErrors)) {
            $errors['email'] = implode(" ", $emailErrors);
        }

        // Cek apakah email sudah digunakan user lain
        $check = $conn->prepare("SELECT user_id FROM Users WHERE email = ? AND user_id != ?");
        $check->bind_param("si", $newEmail, $user_id);
        $check->execute();
        $duplicate = $check->get_result()->fetch_assoc();

        if ($duplicate) {
            $errors['email'] = "Email is already used by another user.";
        }
    }

    // kalo gak ada error masukin ke data base
    if (empty($errors)) {

        $update = $conn->prepare("
            UPDATE Users
            SET username = ?, email = ?
            WHERE user_id = ?
        ");
        $update->bind_param("ssi", $newUsername, $newEmail, $user_id);
        $update->execute();

        // buat update data nya
        $_SESSION['user']['username'] = $newUsername;
        $_SESSION['user']['email'] = $newEmail;

        header("Location: profile.php?updated=1");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="profile-card">
    <h2>Edit Profile</h2>

    <form method="POST" class="edit-form">

        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($currentUsername) ?>">
        <div class="error"><?= $errors['username'] ?? '' ?></div>

        <label>Email:</label>
        <input type="text" name="email" value="<?= htmlspecialchars($currentEmail) ?>">
        <div class="error"><?= $errors['email'] ?? '' ?></div>

        <button type="submit" class="btn-profile">Save</button>
    </form>

    <a href="profile.php" class="back-link">← Back</a>
</div>

<footer>© 2025 KenanginKopi</footer>

</body>
</html>
