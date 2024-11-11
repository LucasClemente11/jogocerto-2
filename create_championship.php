<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$capacity_options = [
    '32' => '32 times - 16 avos finais',
    '16' => '16 times - Oitavas',
    '8'  => '8 times - Quartas',
    '4'  => '4 times - Semifinais',
    '2'  => '2 times - Final'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $capacity = $_POST['capacity'];
    $type = $_POST['type'];
    $created_by = $_SESSION['user_id'];

    $image = 'default_championship.jpg';

    if ($_FILES['image']['size'] > 0) {
        $image = 'uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    } else {
        $image = 'uploads/default_championship.jpg'; // Caminho correto para a imagem padrão
    }

    $sql = "INSERT INTO championships (name, capacity, type, created_by, image) VALUES ('$name', '$capacity', '$type', '$created_by', '$image')";

    if (mysqli_query($conn, $sql)) {
        header('Location: index.php');
    } else {
        $error = "Erro ao criar campeonato: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Campeonato</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="menu-btn" onclick="toggleMenu()">☰</div>
        <div class="logo">Logo</div>
        <div class="profile-btn">
            <a href="profile.php">👤</a>
        </div>
    </header>

    <div class="main-content">
        <h2>Criar Novo Campeonato</h2>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Nome do Campeonato:</label>
                    <input type="text" id="name" name="name" placeholder="Digite o nome do campeonato" required>
                </div>

                <div class="form-group">
                    <label for="capacity">Capacidade (número de times):</label>
                    <select name="capacity" id="capacity" required>
                        <option value="">Selecione a capacidade</option>
                        <?php foreach ($capacity_options as $value => $label): ?>
                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="type">Tipo de Campeonato:</label>
                    <select name="type" id="type" required>
                        <option value="">Selecione o tipo</option>
                        <option value="mata-mata">Mata-mata</option>
                        <option value="pontos_corridos">Pontos Corridos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="image">Imagem do Campeonato:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-primary">Criar Campeonato</button>
                    <a href="index.php" class="btn-cancel">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleMenu() {
            document.body.classList.toggle('menu-open');
        }
    </script>
</body>
</html>