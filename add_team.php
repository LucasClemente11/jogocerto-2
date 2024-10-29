<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team_name = $_POST['team_name'];
    $championship_id = $_GET['championship_id'];

    $sql = "INSERT INTO teams (name, championship_id) VALUES ('$team_name', '$championship_id')";

    if (mysqli_query($conn, $sql)) {
        $update_sql = "UPDATE championships SET team_count = team_count + 1 WHERE id = '$championship_id'";
        mysqli_query($conn, $update_sql);
        header('Location: championship_management.php');
    } else {
        echo "Erro ao adicionar time: " . mysqli_error($conn);
    }
}
?>

<form method="POST">
    <input type="text" name="team_name" placeholder="Nome do Time" required>
    <button type="submit">Adicionar Time</button>
</form>
