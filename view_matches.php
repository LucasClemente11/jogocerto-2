<?php
session_start();
include 'db_connect.php';

$championship_id = $_GET['championship_id'];

// Obter partidas do campeonato
$matches_sql = "SELECT m.*, t1.name as team1_name, t2.name as team2_name FROM matches m
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
    <h2>Partidas do Campeonato</h2>
    <table>
        <thead>
            <tr>
                <th>Rodada</th>
                <th>Time 1</th>
                <th>Placar</th>
                <th>Time 2</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($match = mysqli_fetch_assoc($matches_result)) : ?>
                <tr>
                    <td><?php echo $match['round']; ?></td>
                    <td><?php echo $match['team1_name']; ?></td>
                    <td><?php echo $match['score1'] ?? '-'; ?> : <?php echo $match['score2'] ?? '-'; ?></td>
                    <td><?php echo $match['team2_name']; ?></td>
                    <td>
                        <?php if (is_null($match['score1']) || is_null($match['score2'])) : ?>
                            <a href="add_result.php?match_id=<?php echo $match['id']; ?>" class="btn">Anotar Resultado</a>
                        <?php else : ?>
                            <span>Resultado Anotado</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<style>
.btn {
    padding: 5px 10px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.btn:hover {
    background-color: #0056b3;
}
</style>
