$(function () {
    const selectorMultipleCanalesVenta = $('#reservaciones-canales-venta').filterMultiSelect({
        selectAllText:"SELECCIONAR TODAS"
    })
    const reporteCorteCaja = document.getElementById('reporte-corte-caja');
    const reporteReservaciones = document.getElementById('reporte-reservaciones');
    const reporteComisiones = document.getElementById('reporte-comisiones');

    const crearReporte = document.getElementById('reporte-form');

    const filtrosCorteCaja = document.getElementById('filtros-corte-caja');
    const filtrosReservaciones = document.getElementById('filtros-reservaciones');

    function clearFiltros(){
        filtrosCorteCaja.style.display = "none";
        filtrosReservaciones.style.display = "none";
        selectorMultipleCanalesVenta.selectAll();
        document.getElementById('report-fecha-inicio').value = "";
        document.getElementById('report-fecha-final').value = "";
    }
    selectorMultipleCanalesVenta.selectAll()


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
            filtrosReservaciones.style.display = "block";
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

            if (!formValidity('reporte-form')) {
                return false;
            }
    
            const cajero      = document.getElementById('corte-agente').value;
            const cupones     = (document.getElementById('corte-cupones').checked ? 1 : 0);
    
            const action      = document.getElementById('crear-reporte').getAttribute('action');
    
            let documentPath  = ''; 
            let url           = '';
            let data          = {};
        
            switch (action) {
                case 'corte-caja':
                    url          = '/reportes/cortecaja';
                    documentPath = `/Reportes/corte_de_caja/corte-de-caja.xlsx`;
                    data         = {
                        'cajero'  : cajero,
                        'cupones' : cupones
                    }
    
                    break;
                case 'reservaciones':
                    url          = '/reportes/totalreservaciones';
                    documentPath = `/Reportes/reservaciones/reservaciones.xlsx`;
                    break;
                case 'comisiones':
                    url          = '/reportes/totalcomisiones';
                    documentPath = `/Reportes/comisiones/comisiones.xlsx`;
                    data         = {
                        'canalesVenta'  : selectorMultipleCanalesVenta.getSelectedOptionsAsJson()
                    }
    
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
                    'data'        : data
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
});