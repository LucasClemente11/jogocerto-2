<?php
// Função para verificar se o usuário está logado
function check_login()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Função para obter informações do campeonato
function get_championship_info($championship_id)
{
    global $conn;

    $sql = "SELECT * FROM championships WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $championship_id);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// Função para verificar se o usuário é o dono do campeonato
function is_championship_owner($championship_id)
{
    global $conn;

    $sql = "SELECT created_by FROM championships WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $championship_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    return $result && $result['created_by'] == $_SESSION['user_id'];
}

// Função para criar chaves do mata-mata
function create_knockout_brackets($championship_id)
{
    global $conn;

    // Buscar times do campeonato
    $teams_sql = "SELECT id FROM teams WHERE championship_id = ? ORDER BY RAND()";
    $teams_stmt = mysqli_prepare($conn, $teams_sql);
    mysqli_stmt_bind_param($teams_stmt, "i", $championship_id);
    mysqli_stmt_execute($teams_stmt);
    $teams_result = mysqli_stmt_get_result($teams_stmt);

    $teams = [];
    while ($team = mysqli_fetch_assoc($teams_result)) {
        $teams[] = $team['id'];
    }

    $num_teams = count($teams);

    // Verificar se o número de times é potência de 2
    if (!is_power_of_two($num_teams)) {
        return false;
    }

    // Criar partidas da primeira fase
    $position = 1;
    for ($i = 0; $i < $num_teams; $i += 2) {
        $sql = "INSERT INTO matches (championship_id, round, position, team1_id, team2_id) 
                VALUES (?, 1, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iiii", $championship_id, $position, $teams[$i], $teams[$i + 1]);
        mysqli_stmt_execute($stmt);
        $position++;
    }

    return true;
}

// Função para atualizar progressão no mata-mata
function update_knockout_progression($match_id, $score1, $score2) {
    global $conn;

    // Buscar informações da partida atual
    $match_sql = "SELECT * FROM matches WHERE id = ?";
    $match_stmt = mysqli_prepare($conn, $match_sql);
    mysqli_stmt_bind_param($match_stmt, "i", $match_id);
    mysqli_stmt_execute($match_stmt);
    $match = mysqli_fetch_assoc(mysqli_stmt_get_result($match_stmt));

    // Determinar o vencedor
    $winner_id = ($score1 > $score2) ? $match['team1_id'] : $match['team2_id'];

    // Verificar se existe próxima fase
    $next_round = $match['round'] + 1;
    $championship_id = $match['championship_id'];

    // Contar quantos jogos já foram realizados nesta rodada
    $count_sql = "SELECT COUNT(*) as count FROM matches 
                  WHERE championship_id = ? AND round = ? 
                  AND score1 IS NOT NULL AND score2 IS NOT NULL";
    $count_stmt = mysqli_prepare($conn, $count_sql);
    mysqli_stmt_bind_param($count_stmt, "ii", $championship_id, $match['round']);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt));
    $games_played = $count_result['count'];

    // Contar total de jogos nesta rodada
    $total_sql = "SELECT COUNT(*) as count FROM matches 
                  WHERE championship_id = ? AND round = ?";
    $total_stmt = mysqli_prepare($conn, $total_sql);
    mysqli_stmt_bind_param($total_stmt, "ii", $championship_id, $match['round']);
    mysqli_stmt_execute($total_stmt);
    $total_result = mysqli_fetch_assoc(mysqli_stmt_get_result($total_stmt));
    $total_games = $total_result['count'];

    // Se todos os jogos da rodada foram jogados, criar próxima rodada
    if ($games_played == $total_games) {
        // Buscar todos os vencedores da rodada atual
        $winners_sql = "SELECT 
            CASE 
                WHEN score1 > score2 THEN team1_id
                WHEN score2 > score1 THEN team2_id
                ELSE NULL 
            END as winner_id
            FROM matches 
            WHERE championship_id = ? AND round = ? 
            AND score1 IS NOT NULL AND score2 IS NOT NULL";

        $winners_stmt = mysqli_prepare($conn, $winners_sql);
        mysqli_stmt_bind_param($winners_stmt, "ii", $championship_id, $match['round']);
        mysqli_stmt_execute($winners_stmt);
        $winners_result = mysqli_stmt_get_result($winners_stmt);

        $winners = [];
        while ($row = mysqli_fetch_assoc($winners_result)) {
            if ($row['winner_id'] !== null) {
                $winners[] = $row['winner_id'];
            }
        }

        // Se houver vencedores, criar jogos da próxima fase
        if (count($winners) > 0) {
            // Ajustar a lógica para lidar com número ímpar de times
            for ($i = 0; $i < count($winners); $i += 2) {
                if (isset($winners[$i + 1])) {
                    $insert_sql = "INSERT INTO matches (championship_id, team1_id, team2_id, round) 
                                   VALUES (?, ?, ?, ?)";
                    $insert_stmt = mysqli_prepare($conn, $insert_sql);
                    mysqli_stmt_bind_param($insert_stmt, "iiii", 
                        $championship_id, $winners[$i], $winners[$i + 1], $next_round);
                    mysqli_stmt_execute($insert_stmt);
                } else {
                    // Se sobrar um time ímpar, ele avança automaticamente
                    $insert_sql = "INSERT INTO matches (championship_id, team1_id, round) 
                                   VALUES (?, ?, ?)";
                    $insert_stmt = mysqli_prepare($conn, $insert_sql);
                    mysqli_stmt_bind_param($insert_stmt, "iii", 
                        $championship_id, $winners[$i], $next_round);
                    mysqli_stmt_execute($insert_stmt);
                }
            }
        }

        // Finalizar o campeonato se houver apenas um vencedor
        if (count($winners) == 1) {
            $update_championship_sql = "UPDATE championships 
                                         SET status = 'finished' WHERE id = ?";
            $update_championship_stmt = mysqli_prepare($conn, $update_championship_sql);
            mysqli_stmt_bind_param($update_championship_stmt, "i", $championship_id);
            mysqli_stmt_execute($update_championship_stmt);
        }
    }

    return true;
}

// Função para criar jogos de pontos corridos
function create_league_matches($championship_id)
{
    global $conn;

    // Buscar times do campeonato
    $teams_sql = "SELECT id FROM teams WHERE championship_id = ?";
    $teams_stmt = mysqli_prepare($conn, $teams_sql);
    mysqli_stmt_bind_param($teams_stmt, "i", $championship_id);
    mysqli_stmt_execute($teams_stmt);
    $teams_result = mysqli_stmt_get_result($teams_stmt);

    $teams = [];
    while ($team = mysqli_fetch_assoc($teams_result)) {
        $teams[] = $team['id'];
    }

    $num_teams = count($teams);

    // Criar jogos (todos contra todos)
    $round = 1;
    for ($i = 0; $i < $num_teams; $i++) {
        for ($j = $i + 1; $j < $num_teams; $j++) {
            // Jogo de ida
            $sql = "INSERT INTO matches (championship_id, round, team1_id, team2_id) 
                    VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iiii", $championship_id, $round, $teams[$i], $teams[$j]);
            mysqli_stmt_execute($stmt);

            // Jogo de volta
            $sql = "INSERT INTO matches (championship_id, round, team1_id, team2_id) 
                    VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            $return_round = $num_teams - 1 + $round;
            mysqli_stmt_bind_param($stmt, "iiii", $championship_id, $return_round, $teams[$j], $teams[$i]);
            mysqli_stmt_execute($stmt);

            $round++;
            if ($round > $num_teams - 1) $round = 1;
        }
    }

    return true;
}

// Função auxiliar para verificar se um número é potência de 2
function is_power_of_two($n)
{
    return ($n & ($n - 1)) === 0;
}

// Função para sanitizar input
function sanitize_input($data)
{
    global $conn;

    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);

    return $data;
}

// Função para obter informações do usuário
function get_user_info($user_id)
{
    global $conn;

    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
}

function check_championship_completion($championship_id)
{
    global $conn;

    // Para mata-mata, verificar se existe apenas uma partida na última rodada com resultado
    $final_match_sql = "SELECT m.*, 
                        (SELECT COUNT(*) FROM matches WHERE championship_id = ? AND round > m.round) as next_matches
                        FROM matches m 
                        WHERE m.championship_id = ? 
                        AND m.score1 IS NOT NULL 
                        AND m.score2 IS NOT NULL 
                        ORDER BY m.round DESC 
                        LIMIT 1";

    $stmt = mysqli_prepare($conn, $final_match_sql);
    mysqli_stmt_bind_param($stmt, "ii", $championship_id, $championship_id);
    mysqli_stmt_execute($stmt);
    $final_match = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($final_match && $final_match['next_matches'] == 0) {
        // Determinar o campeão
        $winner_id = ($final_match['score1'] > $final_match['score2']) ?
            $final_match['team1_id'] : $final_match['team2_id'];

        // Atualizar o status do campeonato
        $update_sql = "UPDATE championships SET 
                      status = 'finalizado',
                      champion_id = ? 
                      WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "ii", $winner_id, $championship_id);
        mysqli_stmt_execute($stmt);
    }
}
