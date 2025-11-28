<?php 
include 'layout_top.php'; 

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
        
        // Asignar una habitación disponible de ese tipo (Lógica simple: toma la primera)
        // En un sistema real verificaríamos disponibilidad por fechas
        $stmtHab = $pdo->prepare("SELECT id_habitacion FROM habitaciones WHERE id_tipo_habitacion = ? LIMIT 1");
        $stmtHab->execute([$id_tipo_habitacion]);
        $habitacion = $stmtHab->fetch();
        
        if (!$habitacion) {
            throw new Exception("No hay habitaciones físicas registradas para este tipo de habitación.");
        }

        $sql = "INSERT INTO reservas (fecha_hora_desde, fecha_hora_hasta, cantidad_personas, monto_total, id_turista, id_habitacion) 
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
    
    // Calcular días y noches
    $interval = $f_inicio->diff($f_fin);
    $noches = $interval->days; // Diferencia en días naturales = noches de hotel
    $dias = $noches + 1; // Generalmente se cuenta el día de salida como uso de instalaciones hasta check-out
    
    if ($noches < 1) $noches = 1; // Mínimo 1 noche

    // Buscar Tipos de Habitaciones y sus Tarifas activas
    // NOTA: Esta consulta busca si hay una tarifa especial para la fecha de inicio.
    // Si no hay tarifa, asume multiplicador 1.
    $sql = "
        SELECT 
            h.nombre as nombre_hotel,
            h.ubicacion,
            th.id_tipo_habitacion,
            th.descripcion as tipo_hab,
            th.precio_base,
            COALESCE(t.multiplo_precio, 1) as multiplicador
        FROM tipo_habitaciones th
        JOIN hoteles h ON th.id_hotel = h.id_hotel
        LEFT JOIN tarifas t ON th.id_tipo_habitacion = t.id_tipo_habitacion 
            AND t.inicio_temporada <= :inicio 
            AND t.fin_temporada >= :fin
        LIMIT 4
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':inicio' => $_POST['fecha_inicio'],
        ':fin' => $_POST['fecha_fin'] // Usamos fecha inicio para ver si cae en rango
    ]);
    
    $opciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $show_results = true;
}
?>

<header>
    <h1>Generador de Presupuestos</h1>
</header>

<!-- Paso 1: Datos de Entrada -->
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

<!-- Paso 2: Resultados -->
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
            // FÓRMULA DEL PDF: ((tipo de habitación x cantidad noches) x cantidad de personas)
            // Asumimos que "tipo de habitacion" se refiere al PRECIO ajustado por tarifa
            $precio_unitario = $opt['precio_base'] * $opt['multiplicador'];
            $total = ($precio_unitario * $noches) * $personas;
        ?>
        <div class="budget-card">
            <h4><?= $opt['nombre_hotel'] ?></h4>
            <small>📍 <?= $opt['ubicacion'] ?></small>
            <hr>
            <p><strong>Habitación:</strong> <?= $opt['tipo_hab'] ?></p>
            <p>Base: $<?= number_format($opt['precio_base'], 2) ?></p>
            <?php if($opt['multiplicador'] > 1): ?>
                <p style="color:#e67e22;">⚠️ Tarifa Alta (x<?= $opt['multiplicador'] ?>)</p>
            <?php endif; ?>
            
            <div class="price-tag">$<?= number_format($total, 2) ?></div>
            
            <p><small>Incluye: Desayuno, Almuerzo, Cena, Bebidas, Piscinas...</small></p>
            
            <!-- Formulario individual para reservar esta opción -->
            <form method="POST" action="">
                <input type="hidden" name="id_turista" value="<?= $id_turista ?>">
                <input type="hidden" name="id_tipo_habitacion" value="<?= $opt['id_tipo_habitacion'] ?>">
                <input type="hidden" name="fecha_desde" value="<?= $_POST['fecha_inicio'] ?>">
                <input type="hidden" name="fecha_hasta" value="<?= $_POST['fecha_fin'] ?>">
                <input type="hidden" name="personas" value="<?= $personas ?>">
                <input type="hidden" name="monto_calculado" value="<?= $total ?>">
                
                <div class="form-group" style="background: #f8f9fa; padding:10px; border-radius:5px;">
                    <label>¿Traslado? (Costo Extra)</label>
                    <input type="number" step="0.01" name="costo_traslado" class="form-control" placeholder="0.00" value="0">
                </div>

                <button type="submit" name="confirmar_reserva" class="btn btn-primary" style="width:100%">Reservar Ahora</button>
            </form>
        </div>
        <?php endforeach; ?>
        
        <?php if(count($opciones) == 0): ?>
            <p>No se encontraron habitaciones configuradas para estos criterios.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include 'layout_bottom.php'; ?>