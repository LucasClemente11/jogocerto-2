<?php
session_start();
include 'db_connect.php';

$team_id = $_GET['team_id'];
$num_players = $_SESSION['num_players'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    for ($i = 0; $i < $num_players; $i++) {
        $name = $_POST['name'][$i];
        $number = $_POST['number'][$i];

        $sql = "INSERT INTO players (name, number, team_id) VALUES ('$name', '$number', '$team_id')";
        mysqli_query($conn, $sql);
    }
    header("Location: view_teams.php?championship_id=$championship_id");
}
?>

<head>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<form method="POST">
    <table>
        <tr>
            <th>Nome do Jogador</th>
            <th>Número do Jogador</th>
        </tr>
        <?php for ($i = 0; $i < $num_players; $i++) : ?>
            <tr>
                <td><input type="text" name="name[]" required></td>
                <td><input type="number" name="number[]" required></td>
            </tr>
        <?php endfor; ?>
    </table>
    <button type="submit">Salvar Jogadores</button>
</form>
