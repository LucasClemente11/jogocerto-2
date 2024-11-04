<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

check_login();

$championship_id = $_GET['championship_id'] ?? 0;
$championship = get_championship_info($championship_id);

if (!$championship || $championship['status'] != 'preparation') {
    header('Location: championship_management.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team_name = mysqli_real_escape_string($conn, $_POST['team_name']);
    
    // Verificar se o nome do time já existe no campeonato
    $check_sql = "SELECT id FROM teams WHERE name = ? AND championship_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "si", $team_name, $championship_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {
        $error = "Um time com este nome já existe neste campeonato.";
    } else {
        $sql = "INSERT INTO teams (name, championship_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $team_name, $championship_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Time adicionado com sucesso!";
            
            // Atualizar o contador de times no campeonato
            $update_sql = "UPDATE championships SET team_count = team_count + 1 WHERE id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "i", $championship_id);
            mysqli_stmt_execute($update_stmt);
        } else {
            $error = "Erro ao adicionar o time. Por favor, tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Time - <?php echo htmlspecialchars($championship['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">Gerenciador de Campeonatos</div>
                <nav>
                    <ul>
                        <li><a href="index.php">Início</a></li>
                        <li><a href="championship_management.php">Meus Campeonatos</a></li>
                        <li><a href="profile.php">Perfil</a></li>
                        <li><a href="logout.php">Sair</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="add-team-section">
            <h1>Adicionar Time ao <?php echo htmlspecialchars($championship['name']); ?></h1>

            <?php if ($error) : ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success) : ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" class="add-team-form">
                <div class="form-group">
                    <label for="team_name">Nome do Time</label>
                    <input type="text" id="team_name" name="team_name" required>
                </div>

                <button type="submit" class="btn btn-primary">Adicionar Time</button>
            </form>

            <a href="view_championship.php?id=<?php echo $championship_id; ?>" class="btn btn-secondary">Voltar para o Campeonato</a>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Gerenciador de Campeonatos. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>