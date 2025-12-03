<?php include 'layout_top.php'; ?>

<header>
    <h1>Reportes y Estadísticas</h1>
</header>

<div class="card">
    <h3>1. Hoteles con Mayor Demanda</h3>
    <table class="datatable display" style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="background:#eee; text-align:left;"><th>Hotel</th><th>Total Reservas</th></tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT * FROM hoteles_con_mayor_demanda";
        
        $rows = $pdo->query($sql)->fetchAll();
        foreach($rows as $r) {
            echo "<tr><td style='padding:8px;'>{$r['hotel']}</td><td style='padding:8px;'>{$r['total_reservas']}</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h3>2. Clientes Recurrentes (Promociones)</h3>
    <table class="datatable display" style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="background:#eee; text-align:left;"><th>Cliente</th><th>Total Reservas</th><th>Monto Gastado</th></tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT * FROM clientes_recurrentes ";
        
        $rows = $pdo->query($sql)->fetchAll();
        foreach($rows as $r) {
            echo "<tr><td style='padding:8px;'>{$r['cliente']}</td><td style='padding:8px;'>{$r['total_reservas']}</td><td style='padding:8px;'>$ ".number_format($r['monto_gastado'],2)."</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h3>3. Reservas por Fecha</h3>
    <form method="GET" style="margin-bottom:15px; gap:10px;" class="filter-form">
        <input type="date" name="fecha_inicio_filtro" class="form-control" required>
        <input type="date" name="fecha_fin_filtro" class="form-control" required>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
    
    <?php if(isset($_GET['fecha_inicio_filtro'])): ?>
        <?php
        $sql = "CALL reservas_X_fechas(?,?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_GET['fecha_inicio_filtro'], $_GET['fecha_fin_filtro']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) === 0) {
            echo "<div>No se encontraron reservas en el rango seleccionado.</div>";
        } else {
        ?>
        <table class="datatable display" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background:#eee; text-align:left;"><th>Reserva</th><th>Cliente</th><th>Registrada</th></tr>
            </thead>
            <tbody>
            <?php
            foreach ($rows as $r) {
                echo "<tr>";
                echo "<td style='padding:8px;'>#{$r['id_reserva']}</td>";
                echo "<td style='padding:8px;'>{$r['nombre']}</td>";
                echo "<td style='padding:8px;'>{$r['fecha_registro']}</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
        <?php } ?>
    <?php endif; ?>
</div>

<?php include 'layout_bottom.php'; ?>