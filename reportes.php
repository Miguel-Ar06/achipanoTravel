<?php include 'layout_top.php'; ?>

<header>
    <h1>Reportes y Estadísticas</h1>
</header>

<div class="card">
    <h3>1. Hoteles con Mayor Demanda</h3>
    <table class="display" style="width:100%; border-collapse: collapse;">
        <tr style="background:#eee; text-align:left;"><th>Hotel</th><th>Total Reservas</th></tr>
        <?php
        $sql = "SELECT h.nombre, COUNT(r.id_reserva) as total 
                FROM reservas r 
                JOIN habitaciones hab ON r.id_habitacion = hab.id_habitacion
                JOIN tipo_habitaciones th ON hab.id_tipo_habitacion = th.id_tipo_habitacion
                JOIN hoteles h ON th.id_hotel = h.id_hotel
                GROUP BY h.nombre
                ORDER BY total DESC";
        $rows = $pdo->query($sql)->fetchAll();
        foreach($rows as $r) {
            echo "<tr><td style='padding:8px;'>{$r['nombre']}</td><td style='padding:8px;'>{$r['total']}</td></tr>";
        }
        ?>
    </table>
</div>

<div class="card">
    <h3>2. Clientes Recurrentes (Promociones)</h3>
    <table class="display" style="width:100%; border-collapse: collapse;">
        <tr style="background:#eee; text-align:left;"><th>Cliente</th><th>Total Reservas</th><th>Monto Gastado</th></tr>
        <?php
        $sql = "SELECT CONCAT(t.nombre, ' ', t.apellido) as cliente, COUNT(r.id_reserva) as total, SUM(r.monto_total) as dinero 
                FROM reservas r 
                JOIN turistas t ON r.id_turista = t.id_turista
                GROUP BY t.id_turista
                ORDER BY total DESC LIMIT 5";
        $rows = $pdo->query($sql)->fetchAll();
        foreach($rows as $r) {
            echo "<tr><td style='padding:8px;'>{$r['cliente']}</td><td style='padding:8px;'>{$r['total']}</td><td style='padding:8px;'>$ ".number_format($r['dinero'],2)."</td></tr>";
        }
        ?>
    </table>
</div>

<div class="card">
    <h3>3. Reservas por Fecha</h3>
    <form method="GET" style="margin-bottom:15px; display:flex; gap:10px;">
        <input type="date" name="f1" class="form-control" required>
        <input type="date" name="f2" class="form-control" required>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
    
    <?php if(isset($_GET['f1'])): ?>
        <ul>
        <?php
        $sql = "SELECT r.id_reserva, r.fecha_registro, t.nombre 
                FROM reservas r 
                JOIN turistas t ON r.id_turista = t.id_turista
                WHERE DATE(r.fecha_registro) BETWEEN ? AND ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_GET['f1'], $_GET['f2']]);
        while($r = $stmt->fetch()){
            echo "<li>Reserva #{$r['id_reserva']} - {$r['nombre']} (Registrada: {$r['fecha_registro']})</li>";
        }
        ?>
        </ul>
    <?php endif; ?>
</div>

<?php include 'layout_bottom.php'; ?>