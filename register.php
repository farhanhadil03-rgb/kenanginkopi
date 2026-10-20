<?php
require 'db.php';
session_start();

function is_alpha_space($s){
    return preg_match("/^[A-Za-z ]+$/", $s);
}

function is_alpha($s){
    return preg_match("/^[A-Za-z]+$/", $s);
}

function strong_password($s){
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $s);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi fullname
    if ($fullname === '' || !is_alpha_space($fullname))
        $errors['fullname'] = "Fullname must contain only alphabet + spaces.";

    // Validasi username
    if ($username === '' || !is_alpha($username))
        $errors['username'] = "Username must contain alphabet only.";

    // Validasi email simple
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email'] = "Invalid email format.";

    // Validasi password
    if (!strong_password($password))
        $errors['password'] = "Password must contain upper, lower, and number.";

    // di cek apakan username dan email sudah di pake
    $stmt = $conn->prepare("SELECT * FROM Users WHERE username=? OR email=?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();

    if ($exists) {
        if ($exists['username'] === $username) {
            $errors['username'] = "Username is already taken.";
        }
        if ($exists['email'] === $email) {
            $errors['email'] = "Email is already registered.";
        }
    }

    // masukkin ke data base jika tidak ada error
    if (empty($errors)) {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $insert = $conn->prepare("
            INSERT INTO Users (full_name, username, email, password, role)
            VALUES (?, ?, ?, ?, 'User')
        ");
        $insert->bind_param("ssss", $fullname, $username, $email, $hash);

        if ($insert->execute()) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $errors['general'] = "Failed to register user.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Register</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="page">
    <section class="card">
        <h2 class="card-title">Register</h2>

        <?php if (!empty($errors['general'])): ?>
            <div class="error"><?= $errors['general'] ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>Fullname:</label>
                <input type="text" name="fullname" value="<?= htmlspecialchars($fullname ?? '') ?>">
                <div class="error"><?= $errors['fullname'] ?? '' ?></div>
            </div>

            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>">
                <div class="error"><?= $errors['username'] ?? '' ?></div>
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="text" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
                <div class="error"><?= $errors['email'] ?? '' ?></div>
            </div>

            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password">
                <div class="error"><?= $errors['password'] ?? '' ?></div>
            </div>

            <button type="submit" class="submit">Register</button>

        </form>
    </section>
</main>

<footer>© 2025 KenanginKopi</footer>

</body>
</html>
