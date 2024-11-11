<?php
session_start();
include 'db_connect.php';
include 'functions.php';

// Verificar se o usuário está logado
check_login();

$championship_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificar se o usuário é o dono do campeonato
if (!is_championship_owner($championship_id)) {
    header('Location: index.php');
    exit;
}

$championship = get_championship_info($championship_id);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete'])) {
        // Lógica para excluir o campeonato
        mysqli_begin_transaction($conn);

        try {
            // 1. Primeiro, excluir todos os jogadores dos times do campeonato
            $delete_players = "DELETE players FROM players 
                              INNER JOIN teams ON players.team_id = teams.id 
                              WHERE teams.championship_id = ?";
            $stmt = mysqli_prepare($conn, $delete_players);
            mysqli_stmt_bind_param($stmt, "i", $championship_id);
            mysqli_stmt_execute($stmt);

            // 2. Excluir todas as partidas do campeonato
            $delete_matches = "DELETE FROM matches WHERE championship_id = ?";
            $stmt = mysqli_prepare($conn, $delete_matches);
            mysqli_stmt_bind_param($stmt, "i", $championship_id);
            mysqli_stmt_execute($stmt);

            // 3. Excluir todos os times do campeonato
            $delete_teams = "DELETE FROM teams WHERE championship_id = ?";
            $stmt = mysqli_prepare($conn, $delete_teams);
            mysqli_stmt_bind_param($stmt, "i", $championship_id);
            mysqli_stmt_execute($stmt);

            // 4. Finalmente, excluir o campeonato
            $delete_championship = "DELETE FROM championships WHERE id = ?";
            $stmt = mysqli_prepare($conn, $delete_championship);
            mysqli_stmt_bind_param($stmt, "i", $championship_id);
            mysqli_stmt_execute($stmt);

            // Se chegou até aqui sem erros, confirmar todas as alterações
            mysqli_commit($conn);

            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            // Se houver qualquer erro, desfazer todas as alterações
            mysqli_rollback($conn);
            $error = "Erro ao excluir campeonato: " . $e->getMessage();
        }
    } else {
        // Lógica existente para atualizar o campeonato
        $name = sanitize_input($_POST['name']);
        $capacity = intval($_POST['capacity']);
        $method = sanitize_input($_POST['method']); // Alterado de type para method

        // Verificar se foi enviada uma nova imagem
        if ($_FILES['image']['size'] > 0) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Permitir apenas certos formatos de arquivo
            $allowed_types = array("jpg", "jpeg", "png", "gif");
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image = $target_file;
                }
            }
        } else {
            $image = $championship['image'];
        }

        $sql = "UPDATE championships SET name = ?, capacity = ?, method = ?, image = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sissi", $name, $capacity, $method, $image, $championship_id);

        if (mysqli_stmt_execute($stmt)) {
            header('Location: view_championship.php?id=' . $championship_id);
            exit;
        } else {
            $error = "Erro ao atualizar campeonato: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Campeonato</title>
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
        <h2>Editar Campeonato</h2>

        <?php if (isset($error)) : ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nome do Campeonato:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($championship['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="capacity">Capacidade (número de times):</label>
                <input type="number" id="capacity" name="capacity" value="<?php echo $championship['capacity']; ?>" required>
            </div>

            <div class="form-group">
                <label for="method">Método:</label>
                <select id="method" name="method">
                    <option value="mata-mata" <?php echo $championship['method'] == 'mata-mata' ? 'selected' : ''; ?>>Mata-mata</option>
                    <option value="pontos_corridos" <?php echo $championship['method'] == 'pontos_corridos' ? 'selected' : ''; ?>>Pontos Corridos</option>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Imagem do Campeonato:</label>
                <input type="file" id="image" name="image">
                <?php if ($championship['image']) : ?>
                    <img src="<?php echo $championship['image']; ?>" alt="Imagem atual do campeonato" style="max-width: 200px;">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <button type="submit">Atualizar Campeonato</button>
                <a href="view_championship.php?id=<?php echo $championship_id; ?>" class="btn-cancel">Cancelar</a>
                <button type="submit" name="delete" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir este campeonato? Todos os times, jogadores e partidas relacionados serão excluídos permanentemente.')">Excluir Campeonato</button>
            </div>
        </form>
    </div>

    <style>
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #3e8e41;
        }

        .btn-cancel {
            background-color: #aaa;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background-color: #999;
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-delete:hover {
            background-color: #e53935;
        }
    </style>

    <script>
        function toggleMenu() {
            document.body.classList.toggle('menu-open');
        }
    </script>
</body>

</html>