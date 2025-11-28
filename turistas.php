<?php 
include 'layout_top.php'; 

// Insertar Turista
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_turista'])) {
    $stmt = $pdo->prepare("INSERT INTO turistas (nombre, apellido, telefono, correo, ubicacion) VALUES (?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$_POST['nombre'], $_POST['apellido'], $_POST['telefono'], $_POST['correo'], $_POST['ubicacion']]);
        echo "<script>alert('Turista registrado correctamente');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>

<header>
    <h1>Gestión de Turistas</h1>
</header>

<div class="card">
    <h3>Registrar Nuevo Turista</h3>
    <form method="POST" action="">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Apellido</label>
                <input type="text" name="apellido" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Correo</label>
                <input type="email" name="correo" class="form-control" required>
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label>Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" required>
            </div>
        </div>
        <button type="submit" name="crear_turista" class="btn btn-primary">Guardar Turista</button>
    </form>
</div>

<div class="card">
    <h3>Directorio de Clientes</h3>
    <table class="datatable display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Contacto</th>
                <th>Ubicación</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM turistas ORDER BY id_turista DESC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>{$row['id_turista']}</td>";
                echo "<td>{$row['nombre']} {$row['apellido']}</td>";
                echo "<td>{$row['telefono']}<br><small>{$row['correo']}</small></td>";
                echo "<td>{$row['ubicacion']}</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'layout_bottom.php'; ?>