document.getElementById('crear-reporte').addEventListener('click', (event) => {
    event.preventDefault();

    const fechaInicio = document.getElementById('report-fecha-inicio').value;
    const fechaFinal  = document.getElementById('report-fecha-final').value;

    $.ajax({
        type: 'POST',
        url: '/reportes/cortecaja',
        dataType: 'json',
        data:
        {
            '_token': token(),
            'fechaInicio' : fechaInicio,
            'fechaFinal' : fechaFinal
        },
        success: function (result) {
            window.open(`/Reportes/corte_de_caja/corte-de-caja.xlsx`, '_blank').focus();
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