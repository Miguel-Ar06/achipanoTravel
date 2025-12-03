<?php
include 'header.php';

$detalle_reserva = null;
$mensaje_error = null;
$mensaje_exito = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_reserva'])) {
    try {
        $pdo->beginTransaction();
        
        $id_turista = $_POST['id_turista'];
        $id_tipo_habitacion = $_POST['id_tipo_habitacion'];
        $entrada = $_POST['fecha_desde'];
        $salida = $_POST['fecha_hasta'];
        $personas = $_POST['personas'];
        $monto_base = $_POST['monto_calculado'];
        $traslado = floatval($_POST['costo_traslado']);
        $total_final = $monto_base + $traslado;

        $habitacion = asignar_habitacion_disponible($pdo, $id_tipo_habitacion, $entrada, $salida);
        
        if (!$habitacion) {
            throw new Exception("No hay habitaciones disponibles para las fechas seleccionadas.");
        }

        $sql = "INSERT INTO reservas (fecha_desde, fecha_hasta, cantidad_personas, monto_total, id_turista, id_habitacion) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$entrada, $salida, $personas, $total_final, $id_turista, $habitacion['id_habitacion']]);
        
        $id_reserva = $pdo->lastInsertId(); 
        
        $pdo->commit();
        
        $sql_detalle = "SELECT * FROM ver_reservas_y_su_estado WHERE id_reserva = ?";
        $stmt_detalle = $pdo->prepare($sql_detalle);
        $stmt_detalle->execute([$id_reserva]);
        $detalle_reserva = $stmt_detalle->fetch(PDO::FETCH_ASSOC);

        $mensaje_exito = "¡Reserva realizada con éxito!";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $mensaje_error = $e->getMessage();
    }
}
?>

<?php if ($mensaje_error): ?>
    <div class="card alert-error">
        <h3>Error</h3>
        <p><?php echo $mensaje_error; ?></p>
        <a href="index.php" class="btn btn-primary" style="background: #721c24;">Volver al Presupuesto</a>
    </div>
<?php endif; ?>

<?php if ($detalle_reserva): ?>
    <div id="facturaPreview" class="card invoice-container">
        
        <div class="invoice-header">
            <div class="invoice-title">
                <h2>Factura de Reserva</h2>
                <span class="invoice-subtitle">Comprobante de operación #<?php echo $detalle_reserva['id_reserva']; ?></span>
            </div>
            <div>
                <div class="invoice-logo-wrapper">
                    <img src="public/media/logo.png" alt="Logo" class="invoice-logo-img">
                </div>
            </div>
        </div>
        
        <div class="invoice-intro">
            <p>Gracias por su preferencia. A continuación se detallan los datos confirmados de su viaje.</p>
        </div>

        <div class="invoice-details-box">
            <table class="invoice-table">
                <tbody>
                    <tr class="invoice-row">
                        <td class="invoice-cell invoice-label">Cliente Titular</td>
                        <td class="invoice-cell invoice-value"><?php echo $detalle_reserva['cliente']; ?></td>
                    </tr>
                    <tr class="invoice-row">
                        <td class="invoice-cell invoice-label">Hotel Seleccionado</td>
                        <td class="invoice-cell invoice-value"><?php echo $detalle_reserva['hotel']; ?></td>
                    </tr>
                    <tr class="invoice-row">
                        <td class="invoice-cell invoice-label">Tipo de Habitación</td>
                        <td class="invoice-cell invoice-value"><?php echo $detalle_reserva['tipo_habitacion']; ?></td>
                    </tr>
                    <tr class="invoice-row">
                        <td class="invoice-cell invoice-label">Fecha de Entrada</td>
                        <td class="invoice-cell invoice-value"><?php echo $detalle_reserva['fecha_desde']; ?></td>
                    </tr>
                    <tr class="invoice-row">
                        <td class="invoice-cell invoice-label">Fecha de Salida</td>
                        <td class="invoice-cell invoice-value"><?php echo $detalle_reserva['fecha_hasta']; ?></td>
                    </tr>
                    <tr class="invoice-row">
                        <td class="invoice-cell invoice-label">Fecha de creación</td>
                        <td class="invoice-cell invoice-value">
                            <?php 
                                $dt = new DateTime($detalle_reserva['fecha_de_creacion']);
                                echo $dt->format('d/m/Y');
                            ?>
                        </td>
                    </tr>
                    <tr class="invoice-row">
                        <td class="invoice-cell invoice-label">Huéspedes</td>
                        <td class="invoice-cell invoice-value"><?php echo $detalle_reserva['cantidad_personas']; ?> Personas</td>
                    </tr>

                    <tr><td colspan="2" style="height: 10px;"></td></tr>

                    <tr class="service-header-row">
                        <td colspan="2" class="service-header-cell">
                            Servicios Incluidos en el Hotel
                        </td>
                    </tr>

                    <?php
                        $stmt_id_hotel = $pdo->prepare("CALL idHotelConidReserva(?)");
                        $stmt_id_hotel->execute([$detalle_reserva['id_reserva']]);
                        $hotel_result = $stmt_id_hotel->fetch(PDO::FETCH_ASSOC);
                        $stmt_id_hotel->closeCursor(); 

                        if ($hotel_result) {
                            $stmt_servicios = $pdo->prepare("CALL serviciosXHotel(?)");
                            $stmt_servicios->execute([$hotel_result['id_hotel']]);
                            $lista_servicios = $stmt_servicios->fetchAll(PDO::FETCH_ASSOC);
                            $stmt_servicios->closeCursor(); 
                            
                            if ($lista_servicios) {
                                foreach ($lista_servicios as $servicio) {
                                    $nombre_del_servicio = isset($servicio['descripcion']) ? $servicio['descripcion'] : array_values($servicio)[0];
                                    echo '<tr class="invoice-row">';
                                    echo '  <td colspan="2" class="invoice-cell invoice-value">• ' . htmlspecialchars($nombre_del_servicio) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="2" class="invoice-cell service-empty">Este hotel no tiene servicios registrados.</td></tr>';
                            }
                        }
                    ?>

                    <tr class="total-row">
                        <td class="total-label">Monto Total</td>
                        <td class="total-amount">
                            $<?php echo number_format($detalle_reserva['monto_total'], 2); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="invoice-actions">
            <a href="index.php" class="btn btn-secondary">Volver</a>
            <button onclick="window.print();" class="btn btn-primary">Guardar PDF</button>
        </div>
    </div>
<?php endif; ?>

<?php include 'layout_bottom.php'; ?>