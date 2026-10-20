<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? 'Guest';
$username = $user['full_name'] ?? null;
?>

<nav class="navbar-top">
    <a href="home.php" class="navbar-title">KenanginKopi</a>

    <div class="navbar-navigation">
        <div class="current-date"></div>

        <!-- buat guest -->
        <?php if ($role === 'Guest'): ?>
            <a href="login.php" class="btn-login">Login</a>

        <!-- buat admin -->
        <?php elseif ($role === 'Admin'): ?>
            <div class="dropdown">
                <button class="dropbtn">Manage ▼</button>
                <div class="dropdown-content">
                    <a href="manage_user.php">Manage User</a>
                    <a href="manage_store.php">Manage Store</a>
                    <a href="manage_coffee.php">Manage Coffee</a>
                </div>
            </div>

            <a href="profile.php" class="username"><?= htmlspecialchars($username) ?></a>
            <a href="logout.php" class="btn-logout">Logout</a>

        <!-- buat user -->
        <?php elseif ($role === 'User'): ?>
            <a href="profile.php" class="username"><?= htmlspecialchars($username) ?></a>
            <a href="logout.php" class="btn-logout">Logout</a>

        <?php endif; ?>
    </div>
</nav>

<!-- ini buat tanggal nya -->
<script src="date.js"></script>

