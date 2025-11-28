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
            $sql = "
                SELECT 
                    r.id_reserva,
                    CONCAT(t.nombre, ' ', t.apellido) as cliente,
                    h.nombre as hotel,
                    th.descripcion as tipo_habitacion,
                    r.fecha_desde,
                    r.fecha_hasta,
                    r.cantidad_personas,
                    r.monto_total
                FROM reservas r
                JOIN turistas t ON r.id_turista = t.id_turista
                JOIN habitaciones hab ON r.id_habitacion = hab.id_habitacion
                JOIN tipo_habitaciones th ON hab.id_tipo_habitacion = th.id_tipo_habitacion
                JOIN hoteles h ON th.id_hotel = h.id_hotel
                ORDER BY r.id_reserva DESC
            ";
            
            $stmt = $pdo->query($sql);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hoy = date('Y-m-d');
                $estatus = '';
                $class = '';
                
                if ($hoy < $row['fecha_desde']) {
                    $estatus = 'Pendiente';
                    $class = 'status-pendiente';
                } elseif ($hoy > $row['fecha_hasta']) {
                    $estatus = 'Cerrado';
                    $class = 'status-cerrado';
                } else {
                    $estatus = 'En Proceso';
                    $class = 'status-proceso';
                }
                
                echo "<tr>";
                echo "<td>{$row['id_reserva']}</td>";
                echo "<td>{$row['cliente']}</td>";
                echo "<td>{$row['hotel']}<br><small>{$row['tipo_habitacion']}</small></td>";
                echo "<td>Entrada: " . date('d/m/Y', strtotime($row['fecha_desde'])) . "<br>Salida: " . date('d/m/Y', strtotime($row['fecha_hasta'])) . "</td>";
                echo "<td>{$row['cantidad_personas']}</td>";
                echo "<td>$ " . number_format($row['monto_total'], 2) . "</td>";
                echo "<td><span class='status-badge $class'>{$estatus}</span></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'layout_bottom.php'; ?>