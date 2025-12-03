<?php include 'layout_top.php'; ?>

<style>
    /* Un estilo rápido para que el botón se vea bien si no tienes uno definido */
    .btn-detalle {
        background-color: #4CAF50; /* Verde agradable */
        color: white;
        padding: 5px 10px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 0.9em;
        border: none;
        cursor: pointer;
    }
    .btn-detalle:hover {
        background-color: #45a049;
    }
</style>

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
                <th>Fecha de Creación</th>
                <th>Estado</th>
     
                <th>Detalles</th>
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
                

                echo "<td>";
                echo "<a href='VerFactura.php?id_reserva={$fila['id_reserva']}' class='btn-detalle'>Ver Factura</a>";
                echo "</td>";
                
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'layout_bottom.php'; ?>