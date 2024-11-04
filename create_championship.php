<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

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
                    <input type="number" id="capacity" name="capacity" placeholder="Digite a quantidade de times"
                        required>
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

    <style>
        .main-content {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group input[type="file"] {
            border: none;
            padding: 0;
        }

        .error {
            color: #f44336;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #ffebee;
            border-radius: 4px;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-primary {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-primary:hover {
            background-color: #45a049;
        }

        .btn-cancel {
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
        }

        .btn-cancel:hover {
            background-color: #da190b;
        }

        /* Estilo para o cabeçalho */
        header {
            background-color: #333;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .menu-btn {
            cursor: pointer;
            font-size: 1.5rem;
        }

        .profile-btn a {
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Responsividade */
        @media (max-width: 600px) {
            .main-content {
                padding: 10px;
            }

            .form-buttons {
                flex-direction: column;
            }

            .btn-primary,
            .btn-cancel {
                width: 100%;
                text-align: center;
            }
        }
    </style>

    <script>
        function toggleMenu() {
            document.body.classList.toggle('menu-open');
        }
    </script>
</body>

</html>