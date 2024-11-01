<?php
session_start();
include 'db_connect.php';

$championship_id = $_GET['championship_id'];

// Verifica se o campeonato existe e obtém as informações
$champ_sql = "SELECT * FROM championships WHERE id = '$championship_id'";
$champ_result = mysqli_query($conn, $champ_sql);
$championship = mysqli_fetch_assoc($champ_result);

if (!$championship) {
    echo "Campeonato não encontrado.";
    exit;
}

// Obter todos os times registrados no campeonato
$teams_sql = "SELECT * FROM teams WHERE championship_id = '$championship_id'";
$teams_result = mysqli_query($conn, $teams_sql);

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
                             VALUES ('$championship_id', '{$matchup[0]}', '{$matchup[1]}', '$round')";
        mysqli_query($conn, $insert_match_sql);
    }
} else if ($championship['method'] == 'pontos corridos') {
    // Lógica para pontos corridos
    for ($i = 0; $i < $num_teams; $i++) {
        for ($j = $i + 1; $j < $num_teams; $j++) {
            $team1_id = $teams[$i]['id'];
            $team2_id = $teams[$j]['id'];
            
            $insert_match_sql = "INSERT INTO matches (championship_id, team1_id, team2_id, round) 
                                 VALUES ('$championship_id', '$team1_id', '$team2_id', '1')";
            mysqli_query($conn, $insert_match_sql);
        }
    }
}

// Atualizar o status do campeonato para iniciado
$update_championship_sql = "UPDATE championships SET status = 'iniciado' WHERE id = '$championship_id'";
mysqli_query($conn, $update_championship_sql);

// Redirecionar para a página de gerenciamento do campeonato
header("Location: view_championship.php?championship_id=$championship_id");
exit;
?>