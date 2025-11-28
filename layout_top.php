<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AchipanoTravel - Panel Master</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo-area">
            <div class="logo-placeholder"><img src="public/media/logo.png" style="transform: scale(0.3);"></div>
            <h2>AchípanoTravel</h2>
            <small>Panel de Agente</small>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">📊 Dashboard</a></li>
            <li><a href="turistas.php">👥 Turistas</a></li>
            <li><a href="presupuesto.php">💰 Nuevo Presupuesto</a></li>
            <li><a href="reservas.php">📅 Reservas</a></li>
            <li><a href="reportes.php">📈 Reportes</a></li>
        </ul>
    </div>
    <div class="main-content">