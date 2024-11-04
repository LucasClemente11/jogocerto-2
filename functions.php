<?php
// Função para verificar se o usuário está logado
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Função para obter informações do campeonato
function get_championship_info($championship_id) {
    global $conn;
    
    $sql = "SELECT * FROM championships WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $championship_id);
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// Função para verificar se o usuário é o dono do campeonato
function is_championship_owner($championship_id) {
    global $conn;
    
    $sql = "SELECT created_by FROM championships WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $championship_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    return $result && $result['created_by'] == $_SESSION['user_id'];
}

// Função para criar chaves do mata-mata
function create_knockout_brackets($championship_id) {
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
    $next_match_position = ceil($match['position'] / 2);
    
    // Buscar ou criar próxima partida
    $next_match_sql = "SELECT * FROM matches WHERE championship_id = ? AND round = ? AND position = ?";
    $next_match_stmt = mysqli_prepare($conn, $next_match_sql);
    mysqli_stmt_bind_param($next_match_stmt, "iii", $match['championship_id'], $next_round, $next_match_position);
    mysqli_stmt_execute($next_match_stmt);
    $next_match = mysqli_fetch_assoc(mysqli_stmt_get_result($next_match_stmt));
    
    if (!$next_match) {
        // Criar próxima partida
        $insert_sql = "INSERT INTO matches (championship_id, round, position, team1_id) 
                      VALUES (?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "iiii", $match['championship_id'], $next_round, $next_match_position, $winner_id);
        mysqli_stmt_execute($insert_stmt);
    } else {
        // Atualizar time na próxima partida
        if (is_null($next_match['team1_id'])) {
            $update_sql = "UPDATE matches SET team1_id = ? WHERE id = ?";
        } else {
            $update_sql = "UPDATE matches SET team2_id = ? WHERE id = ?";
        }
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ii", $winner_id, $next_match['id']);
        mysqli_stmt_execute($update_stmt);
    }
}

// Função para criar jogos de pontos corridos
function create_league_matches($championship_id) {
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
function is_power_of_two($n) {
    return ($n & ($n - 1)) === 0;
}

// Função para sanitizar input
function sanitize_input($data) {
    global $conn ;
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    
    return $data;
}

// Função para obter informações do usuário
function get_user_info($user_id) {
    global $conn;
    
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}
?>