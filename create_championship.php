<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $capacity = $_POST['capacity'];
    $type = $_POST['type'];
    $created_by = $_SESSION['user_id'];

    $image = 'default_championship.jpg';
    if ($_FILES['image']['size'] > 0) {
        $image = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    $sql = "INSERT INTO championships (name, capacity, type, created_by, image) VALUES ('$name', '$capacity', '$type', '$created_by', '$image')";
    
    if (mysqli_query($conn, $sql)) {
        header('Location: index.php');
    } else {
        echo "Erro ao criar campeonato: " . mysqli_error($conn);
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Nome do Campeonato" required>
    <input type="number" name="capacity" placeholder="Capacidade (número de times)" required>
    <select name="type">
        <option value="matamata">Mata-mata</option>
        <option value="pontos_corridos">Pontos Corridos</option>
    </select>
    <input type="file" name="image">
    <button type="submit">Criar</button>
</form>
