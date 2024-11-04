<?php
session_start();
require_once 'db_connect.php';
require_once 'functions.php';

check_login();

// Buscar todos os campeonatos
$sql = "SELECT c.*, COUNT(t.id) as team_count 
        FROM championships c 
        LEFT JOIN teams t ON c.id = t.championship_id 
        GROUP BY c.id";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Campeonatos</title>
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
                        <li><a href="index.php" class="active">Início</a></li>
                        <li><a href="championship_management.php">Meus Campeonatos</a></li>
                        <li><a href="profile.php">Perfil</a></li>
                        <li><a href="logout.php">Sair</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="dashboard">
            <h1>Bem-vindo ao Gerenciador de Campeonatos</h1>
            <div class="actions">
                <a href="create_championship.php" class="btn btn-primary">Criar Novo Campeonato</a>
            </div>

            <section class="championships">
                <h2>Campeonatos Ativos</h2>
                <div class="championship-grid">
                    <?php while ($championship = mysqli_fetch_assoc($result)) : ?>
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
                                    <?php if ($championship['created_by'] == $_SESSION['user_id']) : ?>
                                        <a href="edit_championship.php?id=<?php echo $championship['id']; ?>" 
                                           class="btn btn-outline">Editar</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
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