<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM championships WHERE created_by = '$user_id'";
$result = mysqli_query($conn, $sql);
?>

<header>
    <div class="menu-btn" onclick="toggleMenu()">☰</div>
    <div class="logo">Logo</div>
    <div class="profile-btn">
        <a href="profile.php">👤</a>
    </div>
</header>

<div class="main-content">
    <h2>Meus Campeonatos</h2>
    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <div class="championship-card">
            <h3><?php echo $row['name']; ?></h3>
            <p>Capacidade: <?php echo $row['capacity']; ?></p>
            <p>Times Registrados: <?php echo $row['team_count']; ?></p>
            <a href="add_team.php?championship_id=<?php echo $row['id']; ?>">Adicionar Time</a>
            <a href="view_teams.php?championship_id=<?php echo $row['id']; ?>">Ver Times</a>
        </div>
    <?php endwhile; ?>
</div>
