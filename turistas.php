<?php 
include 'layout_top.php'; 


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_turista'])) {
    $stmt = $pdo->prepare("INSERT INTO turistas (id_turista, nombre, apellido, telefono, correo, ubicacion) VALUES (?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$_POST['identificacion'], $_POST['nombre'], $_POST['apellido'], $_POST['telefono'], $_POST['correo'], $_POST['ubicacion']]);
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Turista registrado',
                text: 'Turista registrado correctamente'
            });
        </script>";
    } catch (PDOException $e) {
        $msg = 'Ocurrió un error al guardar el turista.';
        $err = $e->getMessage();
        if (strpos($err, 'Duplicate') !== false || strpos($err, 'duplicate') !== false || $e->getCode() == 23000) {
            if (stripos($err, 'id_turista') !== false || stripos($err, 'PRIMARY') !== false || stripos($err, 'identificacion') !== false) {
                $msg = 'Ya hay un usuario con esta identificación.';
            } elseif (stripos($err, 'telefono') !== false) {
                $msg = 'Ya hay un usuario con este teléfono.';
            } elseif (stripos($err, 'correo') !== false) {
                $msg = 'Ya hay un usuario con este correo.';
            } else {
                $msg = 'Ya existe un registro con algunos de los datos proporcionados.';
            }
        } else {
            $msg = 'Error: ' . addslashes($e->getMessage());
        }

        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error al guardar',
                text: '{$msg}'
            });
        </script>";
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
            <div class="form-group">
                <label>Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Identificación</label>
                <input type="text" name="identificacion" class="form-control" required>
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

<?php include'layout_bottom.php';?>
