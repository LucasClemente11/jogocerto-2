<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

check_login();

$championship_id = $_GET['championship_id'] ?? 0;
$championship = get_championship_info($championship_id);

if (!$championship) {
    header('Location: championship_management.php');
    exit;
}

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
    <title>Partidas - <?php echo htmlspecialchars($championship['name']); ?></title>
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
        <div class="matches-section">
            <h1>Partidas - <?php echo htmlspecialchars($championship['name']); ?></h1>

            <div class="matches-list">
                <?php 
                $current_round = 0;
                while ($match = mysqli_fetch_assoc($matches)) : 
                    if ($match['round'] != $current_round) :
                        if ($current_round != 0) echo '</div>';
                        $current_round = $match['round'];
                        echo '<h2>Rodada ' . $current_round . '</h2>';
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
                        
                        <div class="match-info">
                            <span class="match-date">
                                <?php echo $match['match_date'] ? date('d/m/Y H:i', strtotime($match['match_date'])) : 'Data não definida'; ?>
                            </span>
                            
                            <?php if ($championship['status'] == 'in_progress') : ?>
                                <?php if (!isset($match['score1']) && !isset($match['score2'])) : ?>
                                    <a href="add_result.php?match_id=<?php echo $match['id']; ?>" 
                                       class="btn btn-primary">Adicionar Resultado</a>
                                <?php else : ?>
                                    <a href="edit_result.php?match_id=<?php echo $match['id']; ?>" 
                                       class="btn btn-secondary">Editar Resultado</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                <?php if ($current_round != 0) echo '</div>'; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Gerenciador de Campeonatos. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>