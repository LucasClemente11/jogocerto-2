<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

check_login();

$championship_id = $_GET['id'] ?? 0;
$championship = get_championship_info($championship_id);

if (!$championship || $championship['created_by'] != $_SESSION['user_id']) {
    header('Location: championship_management.php');
    exit;
}

// Buscar estatísticas dos times
$sql = "SELECT t.name, SUM(m.team1_score) as total_goals FROM matches m 
        JOIN teams t ON m.team1_id = t.id 
        WHERE m.championship_id = ? 
        GROUP BY t.id";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $championship_id);
mysqli_stmt_execute($stmt);
$team_stats = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas do Campeonato - <?php echo htmlspecialchars($championship['name']); ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <main class="container">
        <h1>Estatísticas do Campeonato</h1>
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Total de Gols</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($stat = mysqli_fetch_assoc($team_stats)) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($stat['name']); ?></td>
                        <td><?php echo $stat['total_goals']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</body>
</html>