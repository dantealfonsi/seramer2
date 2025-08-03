/**
 * DataTables Advanced Configuration (jQuery)
 */
'use strict';

document.addEventListener('DOMContentLoaded', function() {
    const dtAjaxTable = $('.datatables');
    
    if (dtAjaxTable.length) {
        initializeDataTable(dtAjaxTable);
    }
});

/**
 * Initialize DataTable with AJAX configuration
 * @param {jQuery} tableElement - The table element to initialize
 */
function initializeDataTable(tableElement) {
    const options = {
        processing: true,
        serverSide: true,
        ajax: "/materiales",
        dataType: 'json',
        type: "POST",
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>><"table-responsive"t><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: getDataTableLanguageConfig(),
        columns: [
            {data: 'name', name: 'name'},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        columnDefs: []
    };

    tableElement.DataTable(options);
}

/**
 * Returns DataTable language configuration
 * @returns {Object} Language configuration object
 */
function getDataTableLanguageConfig() {
    return {
        url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json",
        paginate: {
            next: '<i class="ri-arrow-right-s-line"></i>',
            previous: '<i class="ri-arrow-left-s-line"></i>'
        }
    };
}

/**
 * Delete record confirmation dialog
 * @param {number|string} id - Record ID to delete
 */
function deleteRecord(id) {
    Swal.fire({
        title: '¿Está seguro de eliminar este registro?',
        text: "¡No podrá recuperar la información!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
            cancelButton: 'btn btn-outline-danger waves-effect'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `/materiales/${id}/destroy`;
        }
    });
}