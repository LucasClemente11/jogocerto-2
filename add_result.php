<?php
session_start();
include 'db_connect.php';

$match_id = $_GET['match_id'];

// Obter dados da partida
$match_sql = "SELECT m.*, t1.name as team1_name, t2.name as team2_name FROM matches m
              JOIN teams t1 ON m.team1_id = t1.id
              JOIN teams t2 ON m.team2_id = t2.id
              WHERE m.id = '$match_id'";
$match_result = mysqli_query($conn, $match_sql);
$match = mysqli_fetch_assoc($match_result);

// Anotar o resultado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $score1 = $_POST['score1'];
    $score2 = $_POST['score2'];

    // Atualizar o resultado da partida no banco de dados
    $update_sql = "UPDATE matches SET score1 = '$score1', score2 = '$score2' WHERE id = '$match_id'";
    mysqli_query($conn, $update_sql);

    // Redirecionar de volta para a lista de partidas
    header("Location: view_matches.php?championship_id=" . $match['championship_id']);
    exit;
}
?>

<header>
    <div class="menu-btn" onclick="toggleMenu()">☰</div>
    <div class="logo">Logo</div>
    <div class="profile-btn">
        <a href="profile.php">👤</a>
    </div>
</header>

<div class="main-content">
    <h2>Anotar Resultado</h2>
    <p><?php echo $match['team1_name']; ?> vs <?php echo $match['team2_name']; ?></p>

    <form method="post">
        <label for="score1"><?php echo $match['team1_name']; ?> Placar:</label>
        <input type="number" name="score1" id="score1" required>
        
        <label for="score2"><?php echo $match['team2_name']; ?> Placar:</label>
        <input type="number" name="score2" id="score2" required>
        
        <button type="submit" class="btn">Salvar Resultado</button>
    </form>
</div>

<style>
form {
    display: flex;
    flex-direction: column;
}

input {
    margin-bottom: 10px;
    padding: 5px;
}

.btn {
    padding: 5px 10px;
    background-color: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.btn:hover {
    background-color: #218838;
}
</style>
