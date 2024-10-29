<?php
session_start();
include 'db_connect.php';

$championship_id = $_GET['championship_id'];

// Verifica se o campeonato existe e obtem as informações
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

// Após anotar o resultado, avançar o time vencedor no mata-mata
if ($championship['method'] == 'mata-mata') {
    $winner = $score1 > $score2 ? $match['team1_id'] : $match['team2_id'];
    
    // Avançar para a próxima rodada
    $next_round = $match['round'] + 1;
    $next_match_sql = "INSERT INTO matches (championship_id, team1_id, round) 
                       VALUES ('$championship_id', '$winner', '$next_round')";
    mysqli_query($conn, $next_match_sql);
}

        
        // Inserir partidas no banco de dados
        foreach ($matchups as $matchup) {
            $insert_match_sql = "INSERT INTO matches (championship_id, team1_id, team2_id, round) VALUES ('$championship_id', '{$matchup[0]}', '{$matchup[1]}', '$round')";
            mysqli_query($conn, $insert_match_sql);
        }

        // Passar apenas os vencedores para a próxima rodada (aqui você terá que implementar a lógica de resultado mais tarde)
        // Para simplificar, vamos assumir que todos os times com índices pares ganham
        $teams = array_filter($teams, function($key) {
            return $key % 2 == 0;
        }, ARRAY_FILTER_USE_KEY);

        $num_teams = count($teams);
        $round++;
    }
} else if ($championship['method'] == 'pontos corridos') {
    // PONTOS CORRIDOS: Todos contra todos
    for ($i = 0; $i < $num_teams; $i++) {
        for ($j = $i + 1; $j < $num_teams; $j++) {
            $team1_id = $teams[$i]['id'];
            $team2_id = $teams[$j]['id'];
            
            // Inserir a partida no banco de dados
            $insert_match_sql = "INSERT INTO matches (championship_id, team1_id, team2_id, round) VALUES ('$championship_id', '$team1_id', '$team2_id', '1')";
            mysqli_query($conn, $insert_match_sql);
        }
    }
}

// Redirecionar para a página de gerenciamento do campeonato após criar os jogos
header("Location: manage_championships.php?championship_id=$championship_id");
exit;
?>
