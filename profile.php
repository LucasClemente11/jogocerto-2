<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>

<header>
    <div class="menu-btn" onclick="toggleMenu()">☰</div>
    <div class="logo">Logo</div>
    <div class="profile-btn">
        <a href="profile.php">👤</a>
    </div>
</header>

<div class="profile-info">
    <h2>Perfil</h2>
    <p>Usuário: <?php echo $user['username']; ?></p>
    <p>Email: <?php echo $user['email']; ?></p>
    <a href="championship_management.php">Gerenciar Campeonatos</a>
    <a href="logout.php">Sair</a>
</div>
