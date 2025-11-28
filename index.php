<?php include 'layout_top.php'; 

// --- Lógica de Procesamiento de Reserva Final ---
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
        
        // CONSULTA DIRECTA PARA BUSCAR HABITACIÓN DISPONIBLE
        $sql_habitacion = "
            SELECT h.id_habitacion 
            FROM habitaciones h
            WHERE h.id_tipo_habitacion = ?
            AND h.id_habitacion NOT IN (
                SELECT dh.id_habitacion 
                FROM disponibilidad_habitaciones dh 
                WHERE dh.fecha >= ? AND dh.fecha < ?
                AND dh.estado = 'reservada'
            )
            LIMIT 1
        ";
        
        $stmt_hab = $pdo->prepare($sql_habitacion);
        $stmt_hab->execute([$id_tipo_habitacion, $entrada, $salida]);
        $habitacion = $stmt_hab->fetch();
        
        if (!$habitacion) {
            throw new Exception("No hay habitaciones disponibles para las fechas seleccionadas.");
        }

        // INSERTAR RESERVA
        $sql = "INSERT INTO reservas (fecha_desde, fecha_hasta, cantidad_personas, monto_total, id_turista, id_habitacion) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$entrada, $salida, $personas, $total_final, $id_turista, $habitacion['id_habitacion']]);
        
        $pdo->commit();
        echo "<div class='card' style='background:#d4edda; color:#155724;'><h3>¡Reserva Exitosa!</h3><p>Monto Total: $$total_final (Incluye traslado: $$traslado)</p></div>";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='card' style='background:#f8d7da; color:#721c24;'>Error: " . $e->getMessage() . "</div>";
    }
}

// --- Variables para el formulario ---
$turistas = $pdo->query("SELECT * FROM turistas")->fetchAll();
$resultados_presupuesto = [];
$show_results = false;

// --- Lógica de Cálculo de Presupuesto ---
if (isset($_POST['calcular_presupuesto'])) {
    $id_turista = $_POST['turista_seleccionado'];
    $f_inicio = new DateTime($_POST['fecha_inicio']);
    $f_fin = new DateTime($_POST['fecha_fin']);
    $personas = intval($_POST['cantidad_personas']);
    
    // Calcular días y noches (MANTENIENDO DateTime)
    $interval = $f_inicio->diff($f_fin);
    $noches = $interval->days; // Diferencia en días naturales = noches de hotel
    $dias = $noches + 1; // Generalmente se cuenta el día de salida como uso de instalaciones hasta check-out
    
    if ($noches < 1) $noches = 1;

    // CONSULTA DIRECTA PARA PRESUPUESTOS
    $sql = "
        SELECT 
            h.nombre as nombre_hotel,
            h.ubicacion,
            th.id_tipo_habitacion,
            th.descripcion as tipo_hab,
            th.precio_base,
            th.cantidad_personas,
            COALESCE(t.multiplo_precio, 1) as multiplicador
        FROM tipo_habitaciones th
        JOIN hoteles h ON th.id_hotel = h.id_hotel
        LEFT JOIN tarifas t ON th.id_tipo_habitacion = t.id_tipo_habitacion 
            AND t.inicio_temporada <= ? 
            AND t.fin_temporada >= ?
        WHERE th.cantidad_personas >= ?
        LIMIT 4
    ";
    
    // Convertir DateTime a string para la consulta SQL
    $f_inicio_str = $f_inicio->format('Y-m-d');
    $f_fin_str = $f_fin->format('Y-m-d');
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$f_inicio_str, $f_fin_str, $personas]);
    $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // VERIFICAR DISPONIBILIDAD PARA CADA OPCIÓN
    foreach ($opciones as &$opt) {
        $sql_disponibilidad = "
            SELECT COUNT(DISTINCT h.id_habitacion) as disponibles
            FROM habitaciones h
            WHERE h.id_tipo_habitacion = ?
            AND h.id_habitacion NOT IN (
                SELECT dh.id_habitacion 
                FROM disponibilidad_habitaciones dh 
                WHERE dh.fecha >= ? AND dh.fecha < ?
                AND dh.estado = 'reservada'
            )
        ";
        
        $stmt_disp = $pdo->prepare($sql_disponibilidad);
        $stmt_disp->execute([$opt['id_tipo_habitacion'], $f_inicio_str, $f_fin_str]);
        $result = $stmt_disp->fetch(PDO::FETCH_ASSOC);
        $opt['disponibles'] = $result['disponibles'];
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
                            <?= $t['nombre'] . ' ' . $t['apellido'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small><a href="turistas.php">¿Nuevo turista?</a></small>
            </div>
            <div class="form-group">
                <label>Cantidad Personas</label>
                <input type="number" name="cantidad_personas" class="form-control" min="1" value="<?= $_POST['cantidad_personas'] ?? 1 ?>" required>
            </div>
            <div class="form-group">
                <label>Fecha Entrada</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= $_POST['fecha_inicio'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label>Fecha Salida</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= $_POST['fecha_fin'] ?? '' ?>" required>
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
            $precio_unitario = $opt['precio_base'] * $opt['multiplicador'];
            $total = ($precio_unitario * $noches) * $personas;
            
            if($opt['disponibles'] > 2) {
                $color_fondo = '#d4edda';
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
            <p><strong>Habitación:</strong> <?= $opt['tipo_hab'] ?></p>
            <p>Para: <?= $opt['cantidad_personas'] ?> personas</p>
            <p>Precio base: $<?= number_format($opt['precio_base'], 2) ?></p>
            
            <?php if($opt['multiplicador'] > 1): ?>
                <p style="color:#e67e22;">⚠️ Tarifa Alta (x<?= $opt['multiplicador'] ?>)</p>
            <?php endif; ?>
            
            <div class="price-tag">$<?= number_format($total, 2) ?></div>
            
            <p><small>Incluye: Desayuno, Almuerzo, Cena, Bebidas, Piscinas...</small></p>
            
            <?php if($puede_reservar): ?>
            <form method="POST" action="">
                <input type="hidden" name="id_turista" value="<?= $id_turista ?>">
                <input type="hidden" name="id_tipo_habitacion" value="<?= $opt['id_tipo_habitacion'] ?>">
                <input type="hidden" name="fecha_desde" value="<?= $f_inicio_str ?>">
                <input type="hidden" name="fecha_hasta" value="<?= $f_fin_str ?>">
                <input type="hidden" name="personas" value="<?= $personas ?>">
                <input type="hidden" name="monto_calculado" value="<?= $total ?>">
                
                <div class="form-group" style="background: #f8f9fa; padding:10px; border-radius:5px;">
                    <label>¿Traslado? (Costo Extra)</label>
                    <input type="number" step="0.01" name="costo_traslado" class="form-control" placeholder="0.00" value="0">
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