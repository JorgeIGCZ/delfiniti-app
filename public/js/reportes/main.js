const reporteCorteCaja = document.getElementById('reporte-corte-caja');
const reporteReservaciones = document.getElementById('reporte-reservaciones');
const reporteComisiones = document.getElementById('reporte-comisiones');

const crearReporte = document.getElementById('crear-reporte');

const filtrosCorteCaja = document.getElementById('filtros-corte-caja');

function clearFiltros(){
    filtrosCorteCaja.style.display = "none";
}

if(reporteCorteCaja !== null){
    reporteCorteCaja.addEventListener('click', (event) => { 
        event.preventDefault();
        clearFiltros();
        filtrosCorteCaja.style.display = "block";
        document.getElementById('crear-reporte').setAttribute('action','corte-caja');
    });
}
if(reporteReservaciones !== null){
    reporteReservaciones.addEventListener('click', (event) => {
        event.preventDefault();
        clearFiltros();
        document.getElementById('crear-reporte').setAttribute('action','reservaciones');
    });
}
if(reporteComisiones !== null){
    reporteComisiones.addEventListener('click', (event) => {
        event.preventDefault();
        clearFiltros();
        document.getElementById('crear-reporte').setAttribute('action','comisiones');
    });
}
if(crearReporte !== null){
    crearReporte.addEventListener('click', (event) => {
        event.preventDefault();
    
        const fechaInicio = document.getElementById('report-fecha-inicio').value;
        const fechaFinal  = document.getElementById('report-fecha-final').value;

        const cajero      = document.getElementById('corte-agente').value;
        const cupones     = (document.getElementById('corte-cupones').checked ? 1 : 0);

        const action      = document.getElementById('crear-reporte').getAttribute('action');

        let documentPath  = ''; 
        let url           = '';
    
        switch (action) {
            case 'corte-caja':
                url          = '/reportes/cortecaja';
                documentPath = `/Reportes/corte_de_caja/corte-de-caja.xlsx`;
                break;
            case 'reservaciones':
                url          = '/reportes/totalreservaciones';
                documentPath = `/Reportes/reservaciones/reservaciones.xlsx`;
                break;
            case 'comisiones':
                url          = '/reportes/totalcomisiones';
                documentPath = `/Reportes/comisiones/comisiones.xlsx`;
                break;
        }
        $('.loader').show();
        $.ajax({
            type: 'POST',
            url: url,
            dataType: 'json',
            data:
            {
                '_token': token(),
                'fechaInicio' : fechaInicio,
                'fechaFinal'  : fechaFinal,
                'cajero'      : cajero,
                'cupones'     : cupones
            },
            success: function (result) {
                $('.loader').hide();
                window.open(documentPath, '_blank').focus();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                $('.loader').hide();
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'Error al generar reporte'
                });
            }
        });
    });
}