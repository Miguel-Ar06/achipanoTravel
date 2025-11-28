<?php require_once 'db.php'; ?>
<?php require_once 'funciones.php'; ?> 
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AchipanoTravel - Panel Master</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
    <div class="sidebar">
        <div class="logo-area">
            <div class="logo-placeholder"><img src="public/media/logo.png" style="transform: scale(0.3);"></div>
            <h2>AchípanoTravel</h2>
            <small>Panel de Agente</small>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="turistas.php"> Turistas</a></li>
            <li><a href="index.php">Presupuesto</a></li>
            <li><a href="reservas.php">Reservas</a></li>
            <li><a href="reportes.php"> Reportes</a></li>
            <li><a href="#" onclick="perdonMoguel()"> Hoteles</a></li>
            <script>
                function perdonMoguel(){
                    Swal.fire({
                        title: "Funcion en proceso profe.",
                        text: "Perdon Moguel",
                        icon: "error"
                        });
                }
            </script>
        </ul>
    </div>
    <div class="main-content">