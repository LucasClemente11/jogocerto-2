<?php
session_start();
include 'db_connect.php';

$championship_id = $_GET['championship_id'];

// Obter detalhes do campeonato
$champ_sql = "SELECT * FROM championships WHERE id = '$championship_id'";
$champ_result = mysqli_query($conn, $champ_sql);
$championship = mysqli_fetch_assoc($champ_result);

$method = $championship['method']; // 'mata-mata' ou 'pontos corridos'

// Obter as partidas associadas ao campeonato
$matches_sql = "SELECT * FROM matches WHERE championship_id = '$championship_id'";
$matches_result = mysqli_query($conn, $matches_sql);
?>

<header>
    <div class="menu-btn" onclick="toggleMenu()">☰</div>
    <div class="logo">Logo</div>
    <div class="profile-btn">
        <a href="profile.php">👤</a>
    </div>
</header>

<div class="main-content">
    <h2><?php echo $championship['name']; ?></h2>
    <p>Capacidade: <?php echo $championship['capacity']; ?> times</p>
    <p>Método: <?php echo ucfirst($championship['method']); ?></p>

    <h3>Partidas</h3>
    <table>
        <tr>
            <th>Time 1</th>
            <th>Time 2</th>
            <th>Rodada</th>
            <th>Status</th>
        </tr>
        <?php while ($match = mysqli_fetch_assoc($matches_result)) : ?>
            <tr>
                <td><?php echo getTeamName($match['team1_id']); ?></td>
                <td><?php echo getTeamName($match['team2_id']); ?></td>
                <td><?php echo $match['round']; ?></td>
                <td><?php echo ucfirst($match['status']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php
// Função para obter o nome do time a partir do ID
function getTeamName($team_id) {
    global $conn;
    $sql = "SELECT name FROM teams WHERE id = '$team_id'";
    $result = mysqli_query($conn, $sql);
    $team = mysqli_fetch_assoc($result);
    return $team['name'];
}
?>
