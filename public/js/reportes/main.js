    const selectorMultipleCanalesVenta = $('#filtro-comisiones-canales-venta').filterMultiSelect({
        selectAllText:"SELECCIONAR TODAS"
    })
    const selectorMultipleModuloCorteCaja = $('#filtro-modulo-corte-caja').filterMultiSelect({
        selectAllText:"SELECCIONAR TODOS"
    })
    const selectorMultipleModuloComisiones = $('#filtro-modulo-comisiones').filterMultiSelect({
        selectAllText:"SELECCIONAR TODOS"
    })
    const selectorMultipleAgenciaCupon = $('#filtro-agencia-cupon').filterMultiSelect({
        selectAllText:"SELECCIONAR TODOS"
    })


    const reporteSelect = document.getElementById('reporte-select');

    const crearReporte = document.getElementById('crear-reporte');

    const filtrosCajero = document.getElementById('filtro-cajero');
    const filtrosCupones = document.getElementById('filtro-cupones');
    const filtrosComisiones = document.getElementById('filtros-comisiones');
    const filtrosModuloCorteCaja = document.getElementById('filtros-modulo-corte-caja');
    const filtrosModuloComisiones = document.getElementById('filtros-modulo-comisiones');
    const filtrosAgenciaCupon = document.getElementById('filtros-agencia-cupon');
    

    function clearFiltros(){
        crearReporte.style.display = "none";
        filtrosCajero.style.display = "none";
        filtrosCupones.style.display = "none";
        filtrosComisiones.style.display = "none";

        filtrosModuloCorteCaja.style.display = "none";
        filtrosModuloComisiones.style.display = "none";
        filtrosAgenciaCupon.style.display = "none";

        selectorMultipleCanalesVenta.selectAll();
        selectorMultipleModuloCorteCaja.selectAll();
        selectorMultipleModuloComisiones.selectAll();
        selectorMultipleAgenciaCupon.selectAll();
        // $('#start-date').datepicker('today');  
        // $('#end-date').datepicker('today');  

        
        $('#start-date').datepicker('setDate', 'today');
        $('#end-date').datepicker('setDate', 'today');
    }
    clearFiltros()
    
    if(reporteSelect !== null){
        reporteSelect.addEventListener('change', (event) =>{
            const seleccion = event.target.value;

            switch (seleccion) {
                case 'corte-caja':
                    clearFiltros();

                    filtrosCajero.style.display = "block";
                    filtrosCupones.style.display = "block";
                    crearReporte.style.display = "block";

                    if(JSON.parse(selectorMultipleModuloCorteCaja.getSelectedOptionsAsJson()).filtro_modulo_corte_caja.length > 0){
                        filtrosModuloCorteCaja.style.display = "block";
                    }
                    
                    document.getElementById('crear-reporte').setAttribute('action','corte-caja');
                    break;

                case 'reservaciones':
                    clearFiltros();

                    filtrosComisiones.style.display = "block";
                    crearReporte.style.display = "block";
                    document.getElementById('crear-reporte').setAttribute('action','reservaciones');
                    break;

                case 'comisiones':
                    clearFiltros();


                    crearReporte.style.display = "block";

                    if(JSON.parse(selectorMultipleCanalesVenta.getSelectedOptionsAsJson()).comisiones_canales_venta.length > 0){
                        filtrosComisiones.style.display = "block";
                    }

                    if(JSON.parse(selectorMultipleModuloComisiones.getSelectedOptionsAsJson()).filtro_modulo_comisiones.length > 0){
                        filtrosModuloComisiones.style.display = "block";
                    }

                    document.getElementById('crear-reporte').setAttribute('action','comisiones');
                    break;

                case 'cupones-agencia-concentrado':
                    clearFiltros();


                    crearReporte.style.display = "block";

                    if(JSON.parse(selectorMultipleAgenciaCupon.getSelectedOptionsAsJson()).filtro_agencia_cupon.length > 0){
                        filtrosAgenciaCupon.style.display = "block";
                    }


                    document.getElementById('crear-reporte').setAttribute('action','cupones-agencia-concentrado');
                    break;

                case 'cupones-agencia-detallado':
                    clearFiltros();

                    crearReporte.style.display = "block";

                    if(JSON.parse(selectorMultipleAgenciaCupon.getSelectedOptionsAsJson()).filtro_agencia_cupon.length > 0){
                        filtrosAgenciaCupon.style.display = "block";
                    }


                    document.getElementById('crear-reporte').setAttribute('action','cupones-agencia-detallado');
                    break;
            
                default:
                    clearFiltros()
                    break;
            }
        });
    }

    if(crearReporte !== null){
        crearReporte.addEventListener('click', (event) => { 
            event.preventDefault();
        
            const fechaInicio = document.getElementById('start-date').value;
            const fechaFinal  = document.getElementById('end-date').value;

            if (!formValidity('reportes-form')) {
                return false;
            }
    
            const cajero      = document.getElementById('corte-usuario').value;
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
                        'cupones' : cupones,
                        'modulo'  : selectorMultipleModuloCorteCaja.getSelectedOptionsAsJson()
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
                        'canalesVenta'  : selectorMultipleCanalesVenta.getSelectedOptionsAsJson(),
                        'modulo'  : selectorMultipleModuloComisiones.getSelectedOptionsAsJson()
                    }
                    break;

                case 'cupones-agencia-concentrado':
                    url          = '/reportes/cuponesagenciaconcentrado';
                    documentPath = `/Reportes/cupones/cupones-agencia-concentrado.xlsx`;
                    data         = {
                        'agencias'  : selectorMultipleAgenciaCupon.getSelectedOptionsAsJson()
                    }
                    break;

                case 'cupones-agencia-detallado':
                    url          = '/reportes/cuponesagenciadetallado';
                    documentPath = `/Reportes/cupones/cupones-agencia-detallado.xlsx`;
                    data         = {
                        'agencias'  : selectorMultipleAgenciaCupon.getSelectedOptionsAsJson()
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