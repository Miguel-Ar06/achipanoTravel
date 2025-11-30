document.addEventListener('DOMContentLoaded', function() {
    let fechaInicioInput = document.querySelector('input[name="fecha_inicio"]');
    let fechaFinInput = document.querySelector('input[name="fecha_fin"]');
    

    fechaInicioInput.addEventListener('change', function() {
        const fechaInicio = this.value;
        const fechaFin = fechaFinInput.value;
        const hoy = new Date().toLocaleDateString('en-CA');
        
        if (fechaInicio <= hoy) {
            Swal.fire({
                title: "No puedes seleccionar fechas pasadas o hoy",
                text: "Seleccione una fecha futura.",
                icon: "error"
            });
            this.value = '';
            return;
        }
        
        if (fechaFin && fechaInicio >= fechaFin) {
            Swal.fire({
                title: "La fecha de inicio no puede ser posterior a la fecha de fin",
                text: "Seleccione una fecha válida.",
                icon: "error"
            });
            this.value = '';
        }
    });

    fechaFinInput.addEventListener('change', function() {
        const fechaFin = this.value;
        const fechaInicio = fechaInicioInput.value;
        const hoy = new Date().toLocaleDateString('en-CA');
        
        if (fechaFin <= hoy) {
            Swal.fire({
                title: "No puedes seleccionar fechas pasadas o hoy",
                text: "Seleccione una fecha futura.",
                icon: "error"
            });
            this.value = '';
            return;
        }
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
