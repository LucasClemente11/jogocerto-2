<?php
if (!defined('INCLUDED_FROM_CHAMPIONSHIP')) {
    header('Location: index.php');
    exit;
}

// Buscar todas as partidas do campeonato
$sql = "SELECT m.*, t1.name as team1_name, t2.name as team2_name 
        FROM matches m 
        JOIN teams t1 ON m.team1_id = t1.id 
        JOIN teams t2 ON m.team2_id = t2.id 
        WHERE m.championship_id = ? AND m.score1 IS NOT NULL AND m.score2 IS NOT NULL";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $championship_id);
mysqli_stmt_execute($stmt);
$matches = mysqli_stmt_get_result($stmt);

// Array para armazenar as estatísticas dos times
$standings = [];

// Processar cada partida
while ($match = mysqli_fetch_assoc($matches)) {
    // Processar Time 1
    if (!isset($standings[$match['team1_id']])) {
        $standings[$match['team1_id']] = [
            'name' => $match['team1_name'],
            'matches' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'points' => 0
        ];
    }
    
    // Processar Time 2
    if (!isset($standings[$match['team2_id']])) {
        $standings[$match['team2_id']] = [
            'name' => $match['team2_name'],
            'matches' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'points' => 0
        ];
    }
    
    // Atualizar estatísticas
    $standings[$match['team1_id']]['matches']++;
    $standings[$match['team2_id']]['matches']++;
    
    $standings[$match['team1_id']]['goals_for'] += $match['score1'];
    $standings[$match['team1_id']]['goals_against'] += $match['score2'];
    
    $standings[$match['team2_id']]['goals_for'] += $match['score2'];
    $standings[$match['team2_id']]['goals_against'] += $match['score1'];
    
    if ($match['score1'] > $match['score2']) {
        // Time 1 venceu
        $standings[$match['team1_id']]['wins']++;
        $standings[$match['team1_id']]['points'] += 3;
        $standings[$match['team2_id']]['losses']++;
    } elseif ($match['score1'] < $match['score2']) {
        // Time 2 venceu
        $standings[$match['team2_id']]['wins']++;
        $standings[$match['team2_id']]['points'] += 3;
        $standings[$match['team1_id']]['losses']++;
    } else {
        // Empate
        $standings[$match['team1_id']]['draws']++;
        $standings[$match['team2_id']]['draws']++;
        $standings[$match['team1_id']]['points']++;
        $standings[$match['team2_id']]['points']++;
    }
}

// Ordenar times por pontos (e outros critérios de desempate)
usort($standings, function($a, $b) {
    if ($a['points'] != $b['points']) {
        return $b['points'] - $a['points'];
    }
    // Saldo de gols
    $goalDiffA = $a['goals_for'] - $a['goals_against'];
    $goalDiffB = $b['goals_for'] - $b['goals_against'];
    if ($goalDiffA != $goalDiffB) {
        return $goalDiffB - $goalDiffA;
    }
    // Gols marcados
    if ($a['goals_for'] != $b['goals_for']) {
        return $b['goals_for'] - $a['goals_for'];
    }
    // Ordem alfabética
    return strcmp($a['name'], $b['name']);
});
?>

<div class="standings-table">
    <table>
        <thead>
            <tr>
                <th>Pos</th>
                <th>Time</th>
                <th>P</th>
                <th>J</th>
                <th>V</th>
                <th>E</th>
                <th>D</th>
                <th>GP</th>
                <th>GC</th>
                <th>SG</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($standings as $position => $team) : ?>
                <tr>
                    <td><?php echo $position + 1; ?>º</td>
                    <td><?php echo htmlspecialchars($team['name']); ?></td>
                    <td><?php echo $team['points']; ?></td>
                    <td><?php echo $team['matches']; ?></td>
                    <td><?php echo $team['wins']; ?></td>
                    <td><?php echo $team['draws']; ?></td>
                    <td><?php echo $team['losses']; ?></td>
                    <td><?php echo $team['goals_for']; ?></td>
                    <td><?php echo $team['goals_against']; ?></td>
                    <td><?php echo $team['goals_for'] - $team['goals_against']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="standings-legend">
    <p>P = Pontos, J = Jogos, V = Vitórias, E = Empates, D = Derrotas</p>
    <p>GP = Gols Pró, GC = Gols Contra, SG = Saldo de Gols</p>
</div>