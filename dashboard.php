<?php include 'layout_top.php'; 

// Matate Moguel
$turistas = $pdo->query("SELECT COUNT(*) FROM turistas")->fetchColumn();
$reservas = $pdo->query("SELECT COUNT(*) FROM reservas")->fetchColumn();
$hoteles = $pdo->query("SELECT COUNT(*) FROM hoteles")->fetchColumn();
?>

<header>
    <h1>Bienvenido al Panel Master</h1>
</header>

<div class="budget-grid">
    <div class="card" style="text-align:center;">
        <h3>Total Turistas</h3>
        <p class="price-tag"><?= $turistas ?></p>
        <a href="turistas.php" class="btn btn-primary">Gestionar</a>
    </div>
    <div class="card" style="text-align:center;">
        <h3>Reservas Activas</h3>
        <p class="price-tag"><?= $reservas ?></p>
        <a href="reservas.php" class="btn btn-primary">Ver Detalles</a>
    </div>
    <div class="card" style="text-align:center;">
        <h3>Hoteles Afiliados</h3>
        <p class="price-tag"><?= $hoteles ?></p>
    </div>
</div>

<div class="card">
    <h3>Acciones Rápidas</h3>
    <p>Utilice el menú lateral para navegar. Para realizar una nueva venta, diríjase a <strong>Nuevo Presupuesto</strong>.</p>
</div>

<?php include 'layout_bottom.php'; ?>