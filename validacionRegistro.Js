document.addEventListener('DOMContentLoaded', function() {
    let fechaInicioFiltro = document.querySelector('input[name="fecha_inicio_filtro"]');
    let fechaFinFiltro = document.querySelector('input[name="fecha_fin_filtro"]');

    fechaInicioFiltro.addEventListener('change', function() {
        const fechaInicio = this.value;
        const fechaFin = fechaFinFiltro.value;
        const hoy = new Date().toLocaleDateString('en-CA');
        
        
        if (fechaFin && fechaInicio >= fechaFin) {
            Swal.fire({
                title: "La fecha de inicio no puede ser posterior a la fecha de fin",
                text: "Seleccione una fecha válida.",
                icon: "error"
            });
            this.value = '';
        }
    });
    
    fechaFinFiltro.addEventListener('change', function() {
        const fechaFin = this.value;
        const fechaInicio = fechaInicioFiltro.value;
        const hoy = new Date().toLocaleDateString('en-CA');
        
        if (fechaInicio && (fechaFin <= fechaInicio)) {
            Swal.fire({
                title: "La fecha de fin no puede ser anterior a la fecha de inicio",
                text: "Seleccione una fecha válida.",
                icon: "error"
            });
            this.value = '';
        }       
    });
});