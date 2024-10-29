<?php
session_start();
include 'db_connect.php';

$team_id = $_GET['team_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $num_players = $_POST['num_players'];
    $_SESSION['num_players'] = $num_players;
    header("Location: add_player_form.php?team_id=$team_id");
}
?>

<form method="POST">
    <label for="num_players">Quantos jogadores você deseja adicionar?</label>
    <input type="number" name="num_players" required>
    <button type="submit">Avançar</button>
</form>
