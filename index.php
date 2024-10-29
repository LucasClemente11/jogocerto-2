<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Exibir todos os campeonatos
$sql = "SELECT * FROM championships";
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
    <a href="create_championship.php" class="create-btn">Criar Campeonato</a>
    
    <div class="championship-list">
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <div class="championship-card">
                <h3><?php echo $row['name']; ?></h3>
                <p>Capacidade: <?php echo $row['capacity']; ?> times</p>
                <p>Tipo: <?php echo $row['type']; ?></p>
                <p>Times Registrados: <?php echo $row['team_count']; ?></p>
                <a href="view_teams.php?championship_id=<?php echo $row['id']; ?>">Ver Times</a>
            </div>
        <?php endwhile; ?>
    </div>
</div>
