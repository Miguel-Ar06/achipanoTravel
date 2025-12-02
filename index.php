<?php 
include 'layout_top.php'; 



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
        
        $id_reserva = $pdo->lastInsertId(); // Obtenemos el ID de la reserva recién creada
        
        $pdo->commit();
        
        $sql_detalle = "SELECT * FROM ver_reservas_y_su_estado WHERE id_reserva = ?";
        $stmt_detalle = $pdo->prepare($sql_detalle);
        $stmt_detalle->execute([$id_reserva]);
        $detalle_reserva = $stmt_detalle->fetch(PDO::FETCH_ASSOC);
        echo "<div class='card' style='background:#d4edda; color:#155724;'><h3>¡Reserva Exitosa!</h3><p>Monto Total: $$total_final (Incluye traslado: $$traslado)</p></div>";
        
        echo "<div class='card' style='background:#d4edda; color:#155724;'>";
        echo "<h3>¡Reserva Exitosa!</h3>";
        echo "<p>Se ha creado la reserva #{$id_reserva} con los siguientes detalles:</p>";
        echo "<table class='display' style='width:100%; border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr><th style='text-align:left; padding:8px;'>Campo</th><th style='text-align:left; padding:8px;'>Valor</th></tr>";
        echo "<tr><td style='padding:8px;'>Cliente</td><td style='padding:8px;'>{$detalle_reserva['cliente']}</td></tr>";
        echo "<tr><td style='padding:8px;'>Hotel</td><td style='padding:8px;'>{$detalle_reserva['hotel']}</td></tr>";
        echo "<tr><td style='padding:8px;'>Tipo de Habitación</td><td style='padding:8px;'>{$detalle_reserva['tipo_habitacion']}</td></tr>";
        echo "<tr><td style='padding:8px;'>Fecha Desde</td><td style='padding:8px;'>{$detalle_reserva['fecha_desde']}</td></tr>";
        echo "<tr><td style='padding:8px;'>Fecha Hasta</td><td style='padding:8px;'>{$detalle_reserva['fecha_hasta']}</td></tr>";
        echo "<tr><td style='padding:8px;'>Cantidad de Personas</td><td style='padding:8px;'>{$detalle_reserva['cantidad_personas']}</td></tr>";
        echo "<tr><td style='padding:8px;'>Monto Total</td><td style='padding:8px;'>$" . number_format($detalle_reserva['monto_total'], 2) . "</td></tr>";
        echo "<tr><td style='padding:8px;'>Estado</td><td style='padding:8px;'>{$detalle_reserva['Estado']}</td></tr>";
        echo "</table>";
        echo "</div>";


    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='card' style='background:#f8d7da; color:#721c24;'>Error: " . $e->getMessage() . "</div>";
    }
}


$turistas = $pdo->query("SELECT * FROM turistas")->fetchAll();
$resultados_presupuesto = [];
$show_results = false;

// panel generador de presupuestos
if (isset($_POST['calcular_presupuesto'])) {
    $id_turista = $_POST['turista_seleccionado'];
    $f_inicio = new DateTime($_POST['fecha_inicio']);
    $f_fin = new DateTime($_POST['fecha_fin']);
    $personas = intval($_POST['cantidad_personas']);

    $interval = $f_inicio->diff($f_fin);
    $noches = $interval->days; 
    $dias = $noches + 1; 
    if ($noches < 1) $noches = 1;

    // query para toda la info de los hoteles, tipos de habitaciones y tarifas que coinciden 
      $sql = "
        SELECT h.nombre as nombre_hotel, h.ubicacion, th.id_tipo_habitacion, th.descripcion as tipo_habitacion, th.precio_base, th.cantidad_personas
        FROM tipo_habitaciones th JOIN hoteles h ON th.id_hotel = h.id_hotel WHERE th.cantidad_personas = ? LIMIT 4
    ";
    

    $fecha_inicio_compatible = $f_inicio->format('Y-m-d');
    $fecha_fin_compatible = $f_fin->format('Y-m-d');
   
    // preparamos las consultas pa que no nos inyecten una sqlyuca
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$personas]);
    $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC); // obtenemos todas las habitaciones que coinciden en los hoteles
    
    $sql_disponibilidad = "
        CALL cantidad_De_Habitaciones_DispobiblesXHotelXDias(?, ?, ?);
";

    $stmt_disponibilidad = $pdo->prepare($sql_disponibilidad);

    foreach ($opciones as $matenme => $opt) {
        $costo_total = CostoXfechas(
            $pdo, 
            $opt['id_tipo_habitacion'], 
            $opt['precio_base'], 
            $fecha_inicio_compatible, 
            $fecha_fin_compatible, 
            $personas
        );

        $opciones[$matenme]['costo_total'] = $costo_total; // mrc estoy entrando en la locura q cñ es esto
        
        $stmt_disponibilidad->execute([$opt['id_tipo_habitacion'], $fecha_inicio_compatible, $fecha_fin_compatible]);
        $result = $stmt_disponibilidad->fetch(PDO::FETCH_ASSOC);
        $stmt_disponibilidad->closeCursor();

        if (isset($result['disponibles'])) {
            $opciones[$matenme]['disponibles'] = $result['disponibles'];
        } else {
            $opciones[$matenme]['disponibles'] = 0;
        }
    }

    $show_results = true;
}
?>

<header>
    <h1>Generador de Presupuestos</h1>
</header>

<div class="card">
    <h3>1. Datos del Viaje</h3>
    <form method="POST" action="">
        <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:15px;">
            <div class="form-group">
                <label>Seleccionar Turista</label>
                <select name="turista_seleccionado" class="form-control" required>
                    <?php foreach($turistas as $t): ?>
                        <option value="<?= $t['id_turista'] ?>" <?= (isset($_POST['turista_seleccionado']) && $_POST['turista_seleccionado'] == $t['id_turista']) ? 'selected' : '' ?>>
                            <?= $t['nombre'] . ' ' . $t['apellido'] . ' ('. $t['id_turista'] . ')' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small><a href="turistas.php">¿Nuevo turista?</a></small>
            </div>
            <div class="form-group">
                <label>Cantidad Personas</label>
                <select name="cantidad_personas" id="cantidad_personas" class="form-control" required>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
            </div>
            <div class="form-group">
                <label>Fecha Entrada</label>
                <input type="date" name="fecha_inicio" class="form-control fecha_filtro_index" value="<?= $_POST['fecha_inicio'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label>Fecha Salida</label>
                <input type="date" name="fecha_fin" class="form-control fecha_filtro_index" value="<?= $_POST['fecha_fin'] ?? '' ?>" required>
            </div>
        </div>
        <button type="submit" name="calcular_presupuesto" class="btn btn-primary">Buscar Ofertas</button>
    </form>
</div>

<?php if ($show_results): ?>
    <div class="card" style="border-left: 5px solid var(--primary);">
        <h3>Resumen de Solicitud</h3>
        <p><strong>Periodo:</strong> <?= $f_inicio->format('d/m/Y') ?> al <?= $f_fin->format('d/m/Y') ?></p>
        <p><strong>Duración:</strong> <?= $dias ?> Días / <?= $noches ?> Noches</p>
        <p><strong>Pax:</strong> <?= $personas ?> Personas</p>
    </div>

    <h3>Presupuestos Disponibles (Top 4)</h3>
    <div class="budget-grid">
        <?php foreach ($opciones as $opt): 
            $total = $opt['costo_total'];
            
            if($opt['disponibles'] > 2) {
                $color_fondo = '#d4edda'; // Dios que asco que entre 3 personas a ninguna se le ocurrio que hacer esto asi era mala idea
                $icono = '✅';
                $texto = "Disponible ({$opt['disponibles']} habitaciones)";
                $puede_reservar = true;
            } elseif($opt['disponibles'] > 0) {
                $color_fondo = '#fff3cd';
                $icono = '⚠️';
                $texto = "Últimas {$opt['disponibles']} habitaciones";
                $puede_reservar = true;
            } else {
                $color_fondo = '#f8d7da';
                $icono = '❌';
                $texto = "No disponible";
                $puede_reservar = false;
            }
        ?>
        <div class="budget-card">
            <h4><?= $opt['nombre_hotel'] ?></h4>
            <small>📍 <?= $opt['ubicacion'] ?></small>
            
            <div style="background: <?= $color_fondo ?>; padding: 5px; border-radius: 3px; margin: 5px 0; font-size: 0.9em;">
                <?= $icono ?> <?= $texto ?>
            </div>
            
            <hr>
            <p><strong>Habitación:</strong> <?= $opt['tipo_habitacion'] ?></p>
            <p>Para: <?= $opt['cantidad_personas'] ?> personas</p>
            <p>Precio base (por noche): $<?= number_format($opt['precio_base'], 2) ?></p>
            

            <div style="background: #f8f9fa; padding: 8px; border-radius: 5px; margin: 8px 0; font-size: 0.85em;">
                <strong>Tarifas aplicadas:</strong><br>
                <?php 
                $tarifas = obtener_tarifas_por_fechas($pdo, $opt['id_tipo_habitacion'], $fecha_inicio_compatible, $fecha_fin_compatible);
                if (empty($tarifas)) {
                    echo "Precio estándar (x1.0) para todas las noches";
                } else {
                    foreach ($tarifas as $tarifa) {
                        echo "• {$tarifa['inicio_temporada']} a {$tarifa['fin_temporada']}: x" . $tarifa['multiplo_precio'] . "<br>";
                    }
                }
                ?>
            </div>
            
            <div class="price-tag">$<?= number_format($total, 2) ?></div>
            
            <p><small>Incluye: Desayuno, Almuerzo, Cena, Bebidas, Piscinas...</small></p>
            
            <?php if($puede_reservar): ?>
            <form method="POST" action="">
                <input type="hidden" name="id_turista" value="<?= $id_turista ?>">
                <input type="hidden" name="id_tipo_habitacion" value="<?= $opt['id_tipo_habitacion'] ?>">
                <input type="hidden" name="fecha_desde" value="<?= $fecha_inicio_compatible ?>">
                <input type="hidden" name="fecha_hasta" value="<?= $fecha_fin_compatible ?>">
                <input type="hidden" name="personas" value="<?= $personas ?>">
                <input type="hidden" name="monto_calculado" value="<?= $total ?>">
                
                <div class="form-group" style="background: #f8f9fa; padding:10px; border-radius:5px;">
                    <label>¿Traslado? (costo extra $)</label>
                    <input type="number" step="0.01" class="form-control" name="costo_traslado" placeholder="0.00" min="0.00" value="0">
                    
                <br><br>
                </div>

                <button type="submit" name="confirmar_reserva" class="btn btn-primary" style="width:100%">
                    Reservar Ahora
                </button>
            </form>
            <?php else: ?>
                <button class="btn" style="width:100%; background:#ccc; color:#666;" disabled>
                    No Disponible
                </button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <?php if(count($opciones) == 0): ?>
            <p>No se encontraron habitaciones configuradas para estos criterios.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include 'layout_bottom.php'; ?>
