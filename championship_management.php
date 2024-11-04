<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

check_login();

$user_id = $_SESSION['user_id'];

// Buscar todos os campeonatos criados pelo usuário
$sql = "SELECT c.*, COUNT(t.id) as team_count 
        FROM championships c 
        LEFT JOIN teams t ON c.id = t.championship_id 
        WHERE c.created_by = ? 
        GROUP BY c.id";
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
    <title>Gerenciar Campeonatos</title>
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
                        <li><a href="championship_management.php" class="active">Meus Campeonatos</a></li>
                        <li><a href="profile.php">Perfil</a></li>
                        <li><a href="logout.php">Sair</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="management-section">
            <h1>Gerenciar Campeonatos</h1>
            
            <div class="actions">
                <a href="create_championship.php" class="btn btn-primary">Criar Novo Campeonato</a>
            </div>

            <div class="championship-grid">
                <?php while ($championship = mysqli_fetch_assoc($championships)) : ?>
                    <div class="championship-card">
                        <div class="championship-image">
                            <img src="<?php echo $championship['image'] ?? 'images/default-championship.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($championship['name']); ?>">
                        </div>
                        <div class="championship-info">
                            <h3><?php echo htmlspecialchars($championship['name']); ?></h3>
                            <p class="championship-type">
                                <span class="label">Tipo:</span> 
                                <?php echo ucfirst($championship['type']); ?>
                            </p>
                            <p class="team-count">
                                <span class="label">Times:</span> 
                                <?php echo $championship['team_count']; ?>/<?php echo $championship['capacity']; ?>
                            </p>
                            <div class="championship-actions">
                                <a href="view_championship.php?id=<?php echo $championship['id']; ?>" 
                                   class="btn btn-secondary">Ver Detalhes</a>
                                <a href="edit_championship.php?id=<?php echo $championship['id']; ?>" 
                                   class="btn btn-outline">Editar</a>
                                <?php if ($championship['team_count'] == 0) : ?>
                                    <button onclick="deleteChampionship(<?php echo $championship['id']; ?>)" 
                                            class="btn btn-danger">Excluir</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Gerenciador de Campeonatos. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script>
    function deleteChampionship(id) {
        if (confirm('Tem certeza que deseja excluir este campeonato?')) {
            window.location.href = 'delete_championship.php?id=' + id;
        }
    }
    </script>
    <script src="js/script.js"></script>
</body>
</html>