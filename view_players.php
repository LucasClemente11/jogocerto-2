<?php
session_start();
include 'db_connect.php';

$team_id = $_GET['team_id'];

$sql = "SELECT * FROM players WHERE team_id = '$team_id'";
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
    <h2>Jogadores do Time</h2>
    <table>
        <tr>
            <th>Nome do Jogador</th>
            <th>Número</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['number']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
