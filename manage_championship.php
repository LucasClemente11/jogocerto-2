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

// Buscar times do campeonato
$sql = "SELECT t.*, COUNT(p.id) as player_count 
        FROM teams t 
        LEFT JOIN players p ON t.id = p.team_id 
        WHERE t.championship_id = ? 
        GROUP BY t.id";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $championship_id);
mysqli_stmt_execute($stmt);
$teams = mysqli_stmt_get_result($stmt);

// Processar adição de time
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_team'])) {
    $team_name = mysqli_real_escape_string($conn, $_POST['team_name']);
    $sql = "INSERT INTO teams (name, championship_id) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $team_name, $championship_id);
    if (mysqli_stmt_execute($stmt)) {
        $championship['team_count']++;
        // Atualizar o contador de times no campeonato
        $update_sql = "UPDATE championships SET team_count = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ii", $championship['team_count'], $championship_id);
        mysqli_stmt_execute($update_stmt);
        header("Location: manage_championship.php?id=$championship_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Campeonato - <?php echo htmlspecialchars($championship['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- ... (código do header) ... -->

    <main class="container">
        <div class="manage-championship">
            <h1><?php echo htmlspecialchars($championship['name']); ?></h1>
            
            <div class="management-section">
                <h2>Gerenciamento do Campeonato</h2>
                
                <!-- Seção de Times -->
                <div class="teams-section">
                    <h3>Times</h3>
                    <?php if ($championship['team_count'] < $championship['capacity']) : ?>
                        <form method="POST" class="add-team-form">
                            <input type="text" name="team_name" placeholder="Nome do Time" required>
                            <button type="submit" name="add_team" class="btn btn-primary">Adicionar Time</button>
                        </form>
                    <?php endif; ?>

                    <div class="teams-list">
                        <?php while ($team = mysqli_fetch_assoc($teams)) : ?>
                            <div class="team-card">
                                <h4><?php echo htmlspecialchars($team['name']); ?></h4>
                                <p>Jogadores: <?php echo $team['player_count']; ?></p>
                                <div class="team-actions">
                                    <a href="manage_players.php?team_id=<?php echo $team['id']; ?>" 
                                       class="btn btn-secondary">Gerenciar Jogadores</a>
                                    <a href="edit_team.php?id=<?php echo $team['id']; ?>" 
                                       class="btn btn-outline">Editar Time</a>
                                    <?php if ($team['player_count'] == 0) : ?>
                                        <button onclick="deleteTeam(<?php echo $team['id']; ?>)" 
                                                class="btn btn-danger">Excluir Time</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Botão para Iniciar Campeonato -->
                <?php if ($championship['status'] != 'iniciado' && $championship['team_count'] >= 2) : ?>
                    <div class="start-championship">
                        <a href="start_championship.php?id=<?php echo $championship_id; ?>" 
                           class="btn btn-primary btn-large"
                           onclick="return confirm('Tem certeza que deseja iniciar o campeonato? Esta ação não poderá ser desfeita.')">
                            Iniciar Campeonato
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($championship['status'] == 'finalizado') : ?>
                    <div class="championship-statistics">
                        <a href="championship_statistics.php?id=<?php echo $championship_id; ?>" 
                           class="btn btn-secondary btn-large">
                            Ver Estatísticas
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- ... (código do footer) ... -->

    <script>
    function deleteTeam(id) {
        if (confirm('Tem certeza que deseja excluir este time?')) {
            window.location.href = 'delete_team.php?id=' + id;
        }
    }
    </script>
</body>
</html>