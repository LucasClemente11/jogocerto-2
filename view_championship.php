<?php
session_start();
include 'db_connect.php';

$championship_id = $_GET['championship_id'];

// Obter detalhes do campeonato
$champ_sql = "SELECT * FROM championships WHERE id = '$championship_id'";
$champ_result = mysqli_query($conn, $champ_sql);
$championship = mysqli_fetch_assoc($champ_result);

// Obter as partidas associadas ao campeonato
$matches_sql = "SELECT m.*, t1.name as team1_name, t2.name as team2_name 
                FROM matches m
                JOIN teams t1 ON m.team1_id = t1.id
                JOIN teams t2 ON m.team2_id = t2.id
                WHERE m.championship_id = '$championship_id'";
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
    <p>Status: <?php echo ucfirst($championship['status']); ?></p>
    <p>Método: <?php echo ucfirst($championship['method']); ?></p>

    <h3>Partidas</h3>
    <table>
        <tr>
            <th>Rodada</th>
            <th>Time 1</th>
            <th>Placar</th>
            <th>Time 2</th>
            <th>Ações</th>
        </tr>
        <?php while ($match = mysqli_fetch_assoc($matches_result)) : ?>
            <tr>
                <td><?php echo $match['round']; ?></td>
                <td><?php echo $match['team1_name']; ?></td>
                <td>
                    <?php 
                    if (isset($match['score1']) && isset($match['score2'])) {
                        echo $match['score1'] . ' - ' . $match['score2'];
                    } else {
                        echo 'Não jogado';
                    }
                    ?>
                </td>
                <td><?php echo $match['team2_name']; ?></td>
                <td>
                    <?php if (!isset($match['score1']) || !isset($match['score2'])) : ?>
                        <a href="add_result.php?match_id=<?php echo $match['id']; ?>">Adicionar Resultado</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>