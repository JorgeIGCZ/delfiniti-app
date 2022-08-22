document.getElementById('reporte-corte-caja').addEventListener('click', (event) => {
    event.preventDefault();
    document.getElementById('crear-reporte').setAttribute('action','corte-caja');
});

document.getElementById('reporte-reservaciones').addEventListener('click', (event) => {
    event.preventDefault();
    document.getElementById('crear-reporte').setAttribute('action','reservaciones');
});

document.getElementById('reporte-comisiones').addEventListener('click', (event) => {
    event.preventDefault();
    document.getElementById('crear-reporte').setAttribute('action','comisiones');
});

document.getElementById('crear-reporte').addEventListener('click', (event) => {
    event.preventDefault();

    const fechaInicio = document.getElementById('report-fecha-inicio').value;
    const fechaFinal  = document.getElementById('report-fecha-final').value;
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

    $.ajax({
        type: 'POST',
        url: url,
        dataType: 'json',
        data:
        {
            '_token': token(),
            'fechaInicio' : fechaInicio,
            'fechaFinal' : fechaFinal
        },
        success: function (result) {
            window.open(documentPath, '_blank').focus();
        },
        error: function (xhr, ajaxOptions, thrownError) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Error al generar reporte'
            });
        }
    });
});