<?php
$host = 'localhost';
$db = 'jogocerto2';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Erro ao conectar: " . mysqli_connect_error());
}
?>
