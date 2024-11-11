<?php
session_start();
include 'db_connect.php';
require_once 'functions.php';

check_login();

$championship_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($championship_id === 0) {
    echo "ID do campeonato não fornecido.";
    exit;
}

// Verifica se o campeonato existe e obtém as informações
$champ_sql = "SELECT * FROM championships WHERE id = ?";
$stmt = mysqli_prepare($conn, $champ_sql);
mysqli_stmt_bind_param($stmt, "i", $championship_id);
mysqli_stmt_execute($stmt);
$championship = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$championship) {
    echo "Campeonato não encontrado.";
    exit;
}

// Obter todos os times registrados no campeonato
$teams_sql = "SELECT * FROM teams WHERE championship_id = ?";
$stmt = mysqli_prepare($conn, $teams_sql); // Corrigido aqui
mysqli_stmt_bind_param($stmt, "i", $championship_id);
mysqli_stmt_execute($stmt);
$teams_result = mysqli_stmt_get_result($stmt);

$teams = [];
while ($team = mysqli_fetch_assoc($teams_result)) {
    $teams[] = $team;
}

$num_teams = count($teams);

if ($num_teams < 2) {
    echo "É necessário pelo menos 2 times para iniciar o campeonato.";
    exit;
}

// Criar as partidas iniciais
if ($championship['method'] == 'mata-mata') {
    // Lógica para mata-mata
    $round = 1;
    $matchups = [];
    for ($i = 0; $i < $num_teams; $i += 2) {
        if (isset($teams[$i]) && isset($teams[$i+1])) {
            $matchups[] = [$teams[$i]['id'], $teams[$i+1]['id']];
        }
    }

    foreach ($matchups as $matchup) {
        $insert_match_sql = "INSERT INTO matches (championship_id, team1_id, team2_id, round) 
                            VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_match_sql);
        mysqli_stmt_bind_param($stmt, "iiii", $championship_id, $matchup[0], $matchup[1], $round);
        mysqli_stmt_execute($stmt);
    }
} else if ($championship['method'] == 'pontos corridos') {
    // Lógica para pontos corridos
    $round = 1;
    for ($i = 0; $i < $num_teams; $i++) {
        for ($j = $i + 1; $j < $num_teams; $j++) {
            $team1_id = $teams[$i]['id'];
            $team2_id = $teams[$j]['id'];
            
            $insert_match_sql = "INSERT INTO matches (championship_id, team1_id, team2_id, round) 
                                VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_match_sql);
            mysqli_stmt_bind_param($stmt, "iiii", $championship_id, $team1_id, $team2_id, $round);
            mysqli_stmt_execute($stmt);
            
            // Criar jogo de volta (se necessário)
            $return_round = 2;
            $insert_match_sql = "INSERT INTO matches (championship_id, team1_id, team2_id, round) 
                                VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_match_sql);
            mysqli_stmt_bind_param($stmt, "iiii", $championship_id, $team2_id, $team1_id, $return_round);
            mysqli_stmt_execute($stmt);
        }
    }
}

// Atualizar o status do campeonato para iniciado
$update_status_sql = "UPDATE championships SET status = 'in_progress' WHERE id = ?";
$stmt = mysqli_prepare($conn, $update_status_sql);
mysqli_stmt_bind_param($stmt, "i", $championship_id);
mysqli_stmt_execute($stmt);

// Redirecionar para a página de visualização do campeonato
header('Location: view_championship.php?id=' . $championship_id);
exit;
?>