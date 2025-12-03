document.addEventListener('DOMContentLoaded', function() {
    

    function actualizarHidden() {
    const checkbox = document.getElementById('CheckboxTranslado');
    const hidden = document.getElementById('valorCheckbox');
    
    if (checkbox.checked) {
        hidden.value = 80;
    } else {
        hidden.value = 0;
    }
    
    console.log('Valor enviado será:', hidden.value);
}
});
