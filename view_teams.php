<?php
session_start();
include 'db_connect.php';

$championship_id = $_GET['championship_id'];

// Obter os detalhes do campeonato
$champ_sql = "SELECT * FROM championships WHERE id = '$championship_id'";
$champ_result = mysqli_query($conn, $champ_sql);

// Verifica se o campeonato foi encontrado
if ($champ_result && mysqli_num_rows($champ_result) > 0) {
    $championship = mysqli_fetch_assoc($champ_result);
} else {
    echo "Campeonato não encontrado.";
    exit;
}

// Verifica se a chave 'method' existe antes de tentar usá-la
$method = isset($championship['method']) ? ucfirst($championship['method']) : 'Método não definido';
?>

<head>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<header>
    <div class="menu-btn" onclick="toggleMenu()">☰</div>
    <div class="logo">Logo</div>
    <div class="profile-btn">
        <a href="profile.php">👤</a>
    </div>
</header>

<div class="main-content">
    <h2><?php echo $championship['name']; ?></h2>
    <p>Capacidade: <?php echo $championship['capacity']; ?> times</p>
    <p>Método: <?php echo $method; ?></p>

    <h3>Times Registrados</h3>
    <ul>
        <?php
        $teams_sql = "SELECT * FROM teams WHERE championship_id = '$championship_id'";
        $teams_result = mysqli_query($conn, $teams_sql);
        while ($team = mysqli_fetch_assoc($teams_result)) {
            echo '<li>' . $team['name'] . '</li>';
        }
        ?>
    </ul>

    <!-- Botões para Adicionar Time e Ver Times -->
    <a href="add_team.php?championship_id=<?php echo $championship_id; ?>" class="btn">Adicionar Time</a>
    <a href="view_teams.php?championship_id=<?php echo $championship_id; ?>" class="btn">Ver Times</a>

    <?php
    $matches_sql = "SELECT * FROM matches WHERE championship_id = '$championship_id'";
    $matches_result = mysqli_query($conn, $matches_sql);
    $matches_exist = mysqli_num_rows($matches_result) > 0;
    ?>

<?php if (!$matches_exist) : ?>
    <!-- Botão para iniciar o campeonato se ainda não tiver sido iniciado -->
    <a href="start_championship.php?championship_id=<?php echo $championship_id; ?>" class="start-btn">Iniciar Campeonato</a>
<?php else : ?>
    <p>Campeonato já iniciado.</p>
    <!-- Novo botão para ver o campeonato -->
    <a href="view_championship.php?championship_id=<?php echo $championship_id; ?>" class="view-btn">Ver Campeonato</a>
<?php endif; ?>
</div>
