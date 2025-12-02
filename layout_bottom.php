</div>
    <!-- Scripts -->
     
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="validacionesIndex.js"></script> 
    <script src="validacionRegistro.js"></script>

    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                }
            });
        });
    </script>

    <script>    
    function actualizarHidden() {
        const checkbox = document.getElementById('CheckboxTranslado');
        const hidden = document.getElementById('valorCheckbox');
        
        if (checkbox.checked) {
            hidden.value = 80;
            console.log('Traslado de $80 añadido al total');
        } else {
            hidden.value = 0;
            console.log('Traslado removido');
        }
    }
</script>

</body>
</html>