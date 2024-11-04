<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

check_login();

$user_id = $_SESSION['user_id'];
$user = get_user_info($user_id);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verificar senha atual
    if (!empty($current_password)) {
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
                mysqli_stmt_execute($stmt);
                $success = 'Senha atualizada com sucesso!';
            } else {
                $error = 'As novas senhas não coincidem';
            }
        } else {
            $error = 'Senha atual incorreta';
        }
    }

    // Atualizar email
    if ($email !== $user['email']) {
        $sql = "UPDATE users SET email = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Perfil atualizado com sucesso!';
        } else {
            $error = 'Erro ao atualizar perfil';
        }
    }
}

// Buscar campeonatos criados pelo usuário
$sql = "SELECT * FROM championships WHERE created_by = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$championships = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Gerenciador de Campeonatos</title>
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
                        <li><a href="profile.php" class="active">Perfil</a></li>
                        <li><a href="logout.php">Sair</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="profile-section">
            <h1>Meu Perfil</h1>

            <?php if ($success) : ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error) : ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="profile-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="current_password">Senha Atual</label>
                    <input type="password" id="current_password" name="current_password">
                </div>

                <div class="form-group">
                    <label for="new_password">Nova Senha</label>
                    <input type="password" id="new_password" name="new_password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar Nova Senha</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>

                <button type="submit" class="btn btn-primary">Atualizar Perfil</button>
            </form>

            <h2>Meus Campeonatos</h2>
            <ul>
                <?php foreach ($championships as $championship) : ?>
                    <li>
                        <a href="view_championship.php?id=<?php echo $championship['id']; ?>">
                            <?php echo htmlspecialchars($championship['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
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