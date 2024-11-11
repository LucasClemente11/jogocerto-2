<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

check_login();

$match_id = $_GET['match_id'] ?? 0;

// Buscar informações da partida
$sql = "SELECT m.*, c.id as championship_id, c.status as championship_status, 
        c.method as championship_method, t1.name as team1_name, t2.name as team2_name
        FROM matches m
        JOIN championships c ON m.championship_id = c.id
        JOIN teams t1 ON m.team1_id = t1.id
        JOIN teams t2 ON m.team2_id = t2.id
        WHERE m.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $match_id);
mysqli_stmt_execute($stmt);
$match = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$match || $match['championship_status'] != 'in_progress') {
    header('Location: championship_management.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $score1 = $_POST['score1'];
    $score2 = $_POST['score2'];
    
    if (!is_numeric($score1) || !is_numeric($score2) || $score1 < 0 || $score2 < 0) {
        $error = "Por favor, insira valores válidos para os placares.";
    } else {
        $update_sql = "UPDATE matches SET score1 = ?, score2 = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "iii", $score1, $score2, $match_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            // Se for mata-mata, atualizar próxima fase
            if ($match['championship_method'] == 'mata-mata') {
                update_knockout_progression($match_id, $score1, $score2);
            }
            
            $success = "Resultado registrado com sucesso!";
            header("refresh:2;url=view_championship.php?id=" . $match['championship_id']);
        } else {
            $error = "Erro ao registrar o resultado. Por favor, tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Resultado</title>
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
        <div class="add-result-section">
            <h1>Adicionar Resultado</h1>
            
            <?php if ($error) : ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success) : ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="match-details">
                <h2><?php echo htmlspecialchars($match['team1_name']); ?> vs <?php echo htmlspecialchars($match['team2_name']); ?></h2>
                <p>Rodada: <?php echo $match['round']; ?></p>
            </div>

            <form method="POST" class="add-result-form">
                <div class="form-group">
                    <label for="score1"><?php echo htmlspecialchars($match['team1_name']); ?></label>
                    <input type="number" id="score1" name="score1" min="0" required>
                </div>

                <div class="form-group">
                    <label for="score2"><?php echo htmlspecialchars($match['team2_name']); ?></label>
                    <input type="number" id="score2" name="score2" min="0" required>
                </div>

                <button type="submit" class="btn btn-primary">Registrar Resultado</button>
            </form>

            <a href="view_championship.php?id=<?php echo $match['championship_id']; ?>" class="btn btn-secondary">Voltar para o Campeonato</a>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Gerenciador de Campeonatos. Todos os direitos reservados.</p>
        </div>
    </footer>

    <style>
        .add-result-section {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .match-details {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .match-details h2 {
            margin: 0;
            color: #333;
        }

        .add-result-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-weight: bold;
            color: #333;
        }

        .form-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            text-align: center;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            margin-top: 20px;
            display: inline-block;
        }

        .btn-primary:hover { background-color: #0056b3;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</body>
</html>