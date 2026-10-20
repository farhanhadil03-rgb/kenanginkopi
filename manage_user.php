<?php
require "db.php";
session_start();

// validasi admin biar yang masuk cuma bisa admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    die("Access denied!");
}

// buat delet user
if (isset($_GET['delete'])) {
    $uid = $_GET['delete'];

    $delete = $conn->prepare("DELETE FROM Users WHERE user_id = ?");
    $delete->bind_param("i", $uid);
    $delete->execute();

    header("Location: manage_user.php?msg=deleted");
    exit;
}

// mengabil semua data user
$result = $conn->query("SELECT user_id, full_name, username, email FROM Users ORDER BY user_id DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<?php include "navbar.php"; ?>

<main class="page">
    <div class="user-container">
        <h2 class="title">Manage User</h2>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <p class="success-msg">User deleted successfully.</p>
        <?php endif; ?>

        <table class="user-table">
            <thead>
                <tr>
                    <th>UserID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>U<?= $row['user_id'] ?></td>
                        <td><?= $row['full_name'] ?></td>
                        <td><?= $row['username'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td>
                            <a href="manage_user.php?delete=<?= $row['user_id'] ?>" 
                               onclick="return confirm('Delete this user?');"
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

</body>
</html>
