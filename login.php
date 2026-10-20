<?php
session_start();
require "db.php";

$errors = [];

// lamgsung login kalo ada cookie
if (isset($_COOKIE['remember_user'])) {
    $user_id = $_COOKIE['remember_user'];

    $stmt = $conn->prepare("SELECT user_id, full_name, username, email, role FROM Users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $_SESSION['user'] = $user;
        header("Location: home.php");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']); 

    if ($username === '') $errors['username'] = "Username harus diisi";
    if ($password === '') $errors['password'] = "Password harus diisi";

    if (empty($errors)) {

        $stmt = $conn->prepare("SELECT user_id, full_name, username, email, password, role FROM Users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors['general'] = "Username atau password salah.";
        } else {

            $_SESSION['user'] = [
                'user_id'   => $user['user_id'],
                'full_name' => $user['full_name'],
                'username'  => $user['username'],
                'email'     => $user['email'],
                'role'      => $user['role']
            ];

            //  remember me buat nyipen cookie 1 minggu
            if ($remember) {
                setcookie("remember_user", $user['user_id'], time() + (7 * 24 * 60 * 60), "/");
            }

            // mau user taupun admin kalo login masuk ke home
            $_SESSION['login_success'] = true;
            header("Location: home.php");
            exit;

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>KenanginKopi - Login</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="page">
    <section class="card">
        <h1 class="card-title">Login</h1>

        <?php if (!empty($errors['general'])): ?>
            <div class="error"><?= $errors['general']; ?></div>
        <?php endif; ?>

        <form method="POST" class="login-form">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <div class="error"><?= $errors['username'] ?? '' ?></div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password">
                <div class="error"><?= $errors['password'] ?? '' ?></div>
            </div>

            <div class="form-group remember-me">
                <label for="remember">Remember Me</label>
                <input type="checkbox" name="remember" id="remember">
                
            </div>


            <button type="submit" class="login-btn">Login</button>

            <div class="register-text">
                Don't have an account?
                <a href="register.php">Register Here</a>
            </div>

        </form>
    </section>
</main>

<footer>© 2025 KenanginKopi</footer>
<script src="date.js"></script>

</body>
</html>
