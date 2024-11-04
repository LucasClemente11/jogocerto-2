<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

check_login();

$championship_id = $_GET['id'] ?? 0;
$championship = get_championship_info($championship_id);

if (!$championship) {
    header('Location: index.php');
    exit;
}

// Buscar times do campeonato
$sql = "SELECT * FROM teams WHERE championship_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $championship_id);
mysqli_stmt_execute($stmt);
$teams = mysqli_stmt_get_result($stmt);

// Buscar partidas do campeonato
$sql = "SELECT m.*, t1.name as team1_name, t2.name as team2_name 
        FROM matches m 
        JOIN teams t1 ON m.team1_id = t1.id 
        JOIN teams t2 ON m.team2_id = t2.id 
        WHERE m.championship_id = ?
        ORDER BY m.round, m.id";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $championship_id);
mysqli_stmt_execute($stmt);
$matches = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($championship['name']); ?> - Detalhes</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">Gerenciador de Campeonatos</div>
                <nav>
                    <ul>
                        <li><a href="index.php">Início</a></li>
                        <li><a href="championship_management.php">Meus Campeonatos</a></li>
                        <li><a href="profile.php">Perfil</a></li>
                        <li><a href="logout.php">Sair</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="championship-details">
            <h1><?php echo htmlspecialchars($championship['name']); ?></h1>
            
            <div class="championship-info">
                <p><strong>Tipo:</strong> <?php echo ucfirst($championship['type']); ?></p>
                <p><strong>Capacidade:</strong> <?php echo $championship['capacity']; ?> times</p>
                <p><strong>Status:</strong> <?php echo ucfirst($championship['status']); ?></p>
            </div>

            <div class="teams-section">
                <h2>Times Participantes</h2>
                <div class="teams-grid">
                    <?php while ($team = mysqli_fetch_assoc($teams)) : ?>
                        <div class="team-card">
                            <h3><?php echo htmlspecialchars($team['name']); ?></h3>
                            <div class="team-actions">
                                <a href="view_team.php?id=<?php echo $team['id']; ?>" 
                                   class="btn btn-secondary">Ver Detalhes</a>
                                <?php if ($championship['status'] == 'preparation') : ?>
                                    <a href="edit_team.php?id=<?php echo $team['id']; ?>" 
                                       class="btn btn-outline">Editar</a>
                                    <button onclick="deleteTeam(<?php echo $team['id']; ?>)" 
                                            class="btn btn-danger">Remover</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <?php if (mysqli_num_rows($teams) < $championship['capacity'] && $championship['status'] == 'preparation') : ?>
                        <div class="add-team-card">
                            <a href="add_team.php?championship_id=<?php echo $championship_id; ?>" 
                               class="btn btn-primary">+ Adicionar Time</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($championship['status'] != 'preparation') : ?>
                <div class="matches-section">
                    <h2>Partidas</h2>
                    <div class="matches-list">
                        <?php 
                        $current_round = 0;
                        while ($match = mysqli_fetch_assoc($matches)) : 
                            if ($match['round'] != $current_round) :
                                if ($current_round != 0) echo '</div>';
                                $current_round = $match['round'];
                                echo '<h3>Rodada ' . $current_round . '</h3>';
                                echo '<div class="round-matches">';
                            endif;
                        ?>
                            <div class="match-card">
                                <div class="match-teams">
                                    <span class="team"><?php echo htmlspecialchars($match['team1_name']); ?></span>
                                    <span class="score">
                                        <?php 
                                        if (isset($match['score1']) && isset($match['score2'])) {
                                            echo $match['score1'] . ' x ' . $match['score2'];
                                        } else {
                                            echo 'vs';
                                        }
                                        ?>
                                    </span>
                                    <span class="team"><?php echo htmlspecialchars($match['team2_name']); ?></span>
                                </div>
                                <?php if (!isset($match['score1']) && $championship['status'] == 'in_progress') : ?>
                                    <a href="add_result.php?match_id=<?php echo $match['id']; ?>" 
                                       class="btn btn-secondary">Adicionar Resultado</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                        <?php if ($current_round != 0) echo '</div>'; ?>
                    </div>
                </div>

                <div class="standings-section">
                    <h2>Classificação</h2>
                    <?php include 'championship_standings.php'; ?>
                </div>
            <?php else : ?>
                <?php if (mysqli_num_rows($teams) >= 2) : ?>
                    <div class="start-championship">
                        <a href="start_championship.php?id=<?php echo $championship_id; ?>" 
                           class="btn btn-primary btn-large"
                           onclick="return confirm('Tem certeza que deseja iniciar o campeonato? Esta ação não poderá ser desfeita.')">
                            Iniciar Campeonato
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Gerenciador de Campeonatos. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
    function deleteTeam(id) {
        if (confirm('Tem certeza que deseja remover este time do campeonato?')) {
            window.location.href = 'delete_team.php?id=' + id;
        }
    }
    </script>
    <script src="js/script.js"></script>
</body>
</html>