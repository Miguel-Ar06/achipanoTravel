<?php include 'layout_top.php'; ?>

<header>
    <h1>Control de Reservas</h1>
</header>

<div class="card">
    <div style="overflow-x: auto;">
    <table class="datatable display" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Hotel / Habitación</th>
                <th>Fechas</th>
                <th>Personas</th>
                <th>Total</th>
                <th>Fecha de Creación</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = " SELECT * FROM ver_reservas ";
            
            $stmt = $pdo->query($sql);
            while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hoy = date('Y-m-d');
                $estatus = '';
                $class = '';
                //Profe esta parte del Backend es una obra de arte, igual cree la vista de esto mismo pero no es tan elegante como esta solucion, la quiero mucho
                if ($hoy < $fila['fecha_desde']) {
                    $estatus = 'Pendiente';
                    $class = 'status-pendiente';
                } elseif ($hoy > $fila['fecha_hasta']) {
                    $estatus = 'Cerrado';
                    $class = 'status-cerrado';
                } else {
                    $estatus = 'En Proceso';
                    $class = 'status-proceso';
                }
                
                echo "<tr>";
                echo "<td>{$fila['id_reserva']}</td>";
                echo "<td>{$fila['cliente']}</td>";
                echo "<td>{$fila['hotel']}<br><small>{$fila['tipo_habitacion']}</small></td>";
                echo "<td>Entrada: " . date('d/m/Y', strtotime($fila['fecha_desde'])) . "<br>Salida: " . date('d/m/Y', strtotime($fila['fecha_hasta'])) . "</td>";
                echo "<td>{$fila['cantidad_personas']}</td>";
                echo "<td>$ " . number_format($fila['monto_total'], 2) . "</td>";
                $timestamp = strtotime($fila['fecha_de_creacion']);
                $fecha = date('d/m/Y', $timestamp);
                $hora = date('H:i:s', $timestamp);
                echo "<td>Dia: {$fecha}<br><small>{$hora}</small></td>";
                echo "<td><span class='status-badge $class'>{$estatus}</span></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    </div>
</div>

<?php include 'layout_bottom.php'; ?>