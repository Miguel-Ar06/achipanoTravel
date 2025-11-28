<?php include 'layout_top.php'; ?>

<header>
    <h1>Control de Reservas</h1>
</header>

<div class="card">
    <table class="datatable display" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Hotel / Habitación</th>
                <th>Fechas</th>
                <th>Personas</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consulta compleja para obtener estado calculado
            $sql = "
                SELECT 
                    r.id_reserva,
                    CONCAT(t.nombre, ' ', t.apellido) as cliente,
                    h.nombre as hotel,
                    r.fecha_hora_desde,
                    r.fecha_hora_hasta,
                    r.cantidad_personas,
                    r.monto_total,
                    CASE 
                        WHEN NOW() < r.fecha_hora_desde THEN 'Pendiente'
                        WHEN NOW() BETWEEN r.fecha_hora_desde AND r.fecha_hora_hasta THEN 'En Proceso'
                        ELSE 'Cerrado'
                    END as estatus
                FROM reservas r
                JOIN turistas t ON r.id_turista = t.id_turista
                JOIN habitaciones hab ON r.id_habitacion = hab.id_habitacion
                JOIN tipo_habitaciones th ON hab.id_tipo_habitacion = th.id_tipo_habitacion
                JOIN hoteles h ON th.id_hotel = h.id_hotel
                ORDER BY r.id_reserva DESC
            ";
            
            $stmt = $pdo->query($sql);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $class = '';
                if($row['estatus'] == 'Pendiente') $class = 'status-pendiente';
                if($row['estatus'] == 'En Proceso') $class = 'status-proceso';
                if($row['estatus'] == 'Cerrado') $class = 'status-cerrado';

                echo "<tr>";
                echo "<td>{$row['id_reserva']}</td>";
                echo "<td>{$row['cliente']}</td>";
                echo "<td>{$row['hotel']}</td>";
                echo "<td>Desde: " . date('d/m/Y', strtotime($row['fecha_hora_desde'])) . "<br>Hasta: " . date('d/m/Y', strtotime($row['fecha_hora_hasta'])) . "</td>";
                echo "<td>{$row['cantidad_personas']}</td>";
                echo "<td>$ " . number_format($row['monto_total'], 2) . "</td>";
                echo "<td><span class='status-badge $class'>{$row['estatus']}</span></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'layout_bottom.php'; ?>