
<?php


function asignar_habitacion_disponible($pdo, $id_tipo_habitacion, $fecha_inicio, $fecha_fin) {
    $sql = "
        CALL asignar_habitacion_disponible(?, ?, ?);
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_tipo_habitacion, $fecha_inicio, $fecha_fin]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    return $result;
}

function CostoXfechas($pdo, $id_tipo_habitacion, $precio_base, $fecha_inicio, $fecha_fin, $personas) {
    $total = 0;
    $fecha_actual = new DateTime($fecha_inicio);
    $fecha_final = new DateTime($fecha_fin);

    while ($fecha_actual < $fecha_final) {
        $fecha_compatible = $fecha_actual->format('Y-m-d'); // MEDIA HORA DE MI VIDA PERDIDA PQ NO ERA COMPATIBLE SIN ESTA VARIABLE, te amo y odio PHP
        $sql = "SELECT multiplo_precio FROM tarifas WHERE id_tipo_habitacion = ?  AND ? BETWEEN inicio_temporada AND fin_temporada LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_tipo_habitacion, $fecha_compatible]);
        $tarifa = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tarifa && isset($tarifa['multiplo_precio'])) {
            $multiplicador = $tarifa['multiplo_precio'];
        } else {
            $multiplicador = 1.0;
        }
        $total += $precio_base * $multiplicador;
        $fecha_actual->modify('+1 day');
    }
    

    return $total * $personas;
}

function obtener_tarifas_por_fechas($pdo, $id_tipo_habitacion, $fecha_inicio, $fecha_fin) {
    $sql = "SELECT multiplo_precio, inicio_temporada, fin_temporada 
            FROM tarifas 
            WHERE id_tipo_habitacion = ? 
            AND ((inicio_temporada BETWEEN ? AND DATE_SUB(?, INTERVAL 1 DAY))
            OR (fin_temporada BETWEEN ? AND DATE_SUB(?, INTERVAL 1 DAY))
            OR (? BETWEEN inicio_temporada AND fin_temporada)
            OR (? BETWEEN inicio_temporada AND fin_temporada))";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_tipo_habitacion, $fecha_inicio, $fecha_fin,  $fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_fin]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>