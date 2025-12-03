</div>
    <!-- Scripts -->
     
    <script src="js/jquery-3.7.0.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/sweetalert2@11.js"></script>
    
    <script src="js/validacionesIndex.js"></script>
    <script src="js/validacionRegistro.js"></script>
    <script src="js/jspdf.min.js" type="text/javascript"></script>
    <script src="js/GeneradorPdf.js" type="text/javascript"></script>

    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
        });
    </script>
</body>
</html>