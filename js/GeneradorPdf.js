function genPDF(){
    let doc = new jsPDF();
    doc.text("Factura de Reserva - Achipano Travel", 20, 20);
    doc.save("achipanoTravel_reserva.pdf");
}