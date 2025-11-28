<?php
// funciones.php

/**
 * Verifica cuántas habitaciones de un tipo están disponibles en un rango de fechas
 */
function verificar_disponibilidad_habitacion($pdo, $id_tipo_habitacion, $fecha_inicio, $fecha_fin) {
    $sql = "
        SELECT COUNT(DISTINCT h.id_habitacion) as disponibles
        FROM habitaciones h
        WHERE h.id_tipo_habitacion = ?
        AND h.id_habitacion NOT IN (
            SELECT dh.id_habitacion 
            FROM disponibilidad_habitaciones dh 
            WHERE dh.fecha BETWEEN ? AND DATE_SUB(?, INTERVAL 1 DAY)
            AND dh.estado = 'reservada'
        )
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_tipo_habitacion, $fecha_inicio, $fecha_fin]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['disponibles'];
}

/**
 * Asigna una habitación disponible de un tipo específico para un rango de fechas
 */
function asignar_habitacion_disponible($pdo, $id_tipo_habitacion, $fecha_inicio, $fecha_fin) {
    $sql = "
        SELECT h.id_habitacion 
        FROM habitaciones h
        WHERE h.id_tipo_habitacion = ?
        AND h.id_habitacion NOT IN (
            SELECT dh.id_habitacion 
            FROM disponibilidad_habitaciones dh 
            WHERE dh.fecha BETWEEN ? AND DATE_SUB(?, INTERVAL 1 DAY)
            AND dh.estado = 'reservada'
        )
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_tipo_habitacion, $fecha_inicio, $fecha_fin]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>