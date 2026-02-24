/**
 * DataTable con Checkbox y Acciones Bulk
 * Sistema de Gestión Municipal (Adaptado para SERAMER2)
 */

'use strict';

// Configuración global de DataTables en español
if ($.fn.dataTable) {
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            "decimal": "",
            "emptyTable": "No hay datos disponibles en la tabla",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": " _MENU_ ",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": "",
            "zeroRecords": "No se encontraron registros coincidentes",
            "paginate": {
                "first": "<i class='ri-skip-back-mini-line'></i>",
                "last": "<i class='ri-skip-forward-mini-line'></i>",
                "next": "<i class='ri-arrow-right-s-line'></i>",
                "previous": "<i class='ri-arrow-left-s-line'></i>"
            },
            "aria": {
                "sortAscending": ": activar para ordenar la columna ascendente",
                "sortDescending": ": activar para ordenar la columna descendente"
            }
        }
    });
}

/**
 * Inicializa DataTable con checkbox selection
 * @param {string} tableId - ID de la tabla
 * @param {object} options - Opciones adicionales
 */
function initDataTableWithCheckbox(tableId, options = {}) {
    const defaults = {
        processing: true,
        scrollX: true,
        responsive: false,
        dom: '<"card-header d-flex border-top rounded-0 flex-wrap py-0 flex-column flex-md-row align-items-start"' +
            '<"me-5 ms-n4 pe-5 mb-n6 mb-md-0"f>' +
            '<"d-flex justify-content-start justify-content-md-end align-items-baseline"<"dt-action-buttons d-flex flex-column flex-sm-row mb-6 mb-sm-0 pt-0 gap-4 align-items-center align-items-sm-start align-items-md-center justify-content-md-end flex-wrap"lB>>' +
            '>t' +
            '<"row"' +
            '<"col-md-12 col-lg-6 text-center text-lg-start pb-md-2 pb-lg-0 pe-0"i>' +
            '<"col-md-12 col-lg-6 d-flex justify-content-center justify-content-lg-end mb-6 mb-lg-0 pe-0"p>' +
            '>',
        buttons: [
            {
                text: '<i class="ri-add-line ri-16px me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Agregar</span>',
                className: 'btn btn-primary btn-sm waves-effect waves-light me-2',
                action: function () {
                    if (options.createUrl) {
                        window.location.href = options.createUrl;
                    }
                }
            },
            {
                text: '<i class="ri-delete-bin-7-line ri-16px me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Eliminar</span>',
                className: 'btn btn-danger btn-sm ms-2 d-none bulk-delete-btn waves-effect waves-light',
                attr: {
                    'id': 'bulk-delete-btn'
                },
                action: function () {
                    const selectedIds = getSelectedIds(tableId);
                    if (selectedIds.length > 0 && options.bulkDeleteUrl) {
                        confirmBulkDelete(selectedIds, options.bulkDeleteUrl, tableId);
                    }
                }
            }
        ],
        columnDefs: [
            {
                // For Checkboxes
                targets: 0,
                orderable: false,
                checkboxes: {
                    selectAllRender: '<input type="checkbox" class="form-check-input">'
                },
                render: function () {
                    return '<input type="checkbox" class="dt-checkboxes form-check-input" >';
                },
                searchable: false
            }
        ],
        select: {
            style: 'multi',
            selector: 'td:first-child .dt-checkboxes',
            className: 'row-selected'
        },
        order: [[1, 'asc']],
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10,
        displayLength: 10
    };

    const config = $.extend(true, {}, defaults, options);
    const table = $('#' + tableId).DataTable(config);

    // Remover clase btn-group del contenedor de botones
    $('.dt-buttons').removeClass('btn-group').addClass('d-flex gap-2');

    // Mostrar/ocultar botón de eliminar según selección
    table.on('select deselect', function () {
        const selectedCount = table.rows({ selected: true }).count();
        $('.bulk-delete-btn').toggleClass('d-none', selectedCount === 0);
    });

    return table;
}

/**
 * Obtiene los IDs de las filas seleccionadas
 * @param {string} tableId - ID de la tabla
 * @returns {array} - Array de IDs seleccionados
 */
function getSelectedIds(tableId) {
    const table = $('#' + tableId).DataTable();
    const selectedRows = table.rows({ selected: true }).nodes();
    const ids = [];

    $(selectedRows).each(function () {
        const id = $(this).attr('data-id');
        if (id) ids.push(id);
    });

    return ids;
}

/**
 * Confirma y ejecuta eliminación bulk
 * @param {array} ids - Array de IDs a eliminar
 * @param {string} url - URL del endpoint bulk delete
 * @param {string} tableId - ID de la tabla
 */
function confirmBulkDelete(ids, url, tableId) {
    Swal.fire({
        title: '¿Está seguro?',
        html: `Se eliminarán <strong>${ids.length}</strong> registro(s).<br>Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Obtener token CSRF
            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value ||
                document.querySelector('meta[name="csrf-token"]')?.content;

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    ids: ids,
                    csrf_token: csrfToken
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        if (typeof notyf !== 'undefined') {
                            notyf.success(response.message);
                        } else {
                            Swal.fire('Eliminado', response.message, 'success');
                        }

                        // Verificar si la tabla tiene AJAX configurado
                        const table = $('#' + tableId).DataTable();
                        if (table.settings()[0].ajax) {
                            // Si tiene AJAX, recargar con AJAX
                            table.ajax.reload(null, false);
                        } else {
                            // Si no tiene AJAX, recargar la página completa
                            setTimeout(() => location.reload(), 800);
                        }
                    } else {
                        if (typeof notyf !== 'undefined') {
                            notyf.error(response.message || 'Error al eliminar los registros');
                        } else {
                            Swal.fire('Error', response.message || 'Error al eliminar los registros', 'error');
                        }
                    }
                },
                error: function (xhr) {
                    const response = xhr.responseJSON;
                    if (typeof notyf !== 'undefined') {
                        notyf.error(response?.message || 'Error en la solicitud');
                    } else {
                        Swal.fire('Error', response?.message || 'Error en la solicitud', 'error');
                    }
                }
            });
        }
    });
}

/**
 * Elimina un registro individual
 * @param {number} id - ID del registro
 * @param {string} url - URL del endpoint delete
 * @param {string} tableId - ID de la tabla
 */
function deleteRecord(id, url, tableId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value ||
                document.querySelector('meta[name="csrf-token"]')?.content;

            $.ajax({
                url: url.replace(':id', id),
                method: 'POST',
                data: {
                    csrf_token: csrfToken
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        if (typeof notyf !== 'undefined') {
                            notyf.success(response.message);
                        } else {
                            Swal.fire('Eliminado', response.message, 'success');
                        }

                        // Verificar si la tabla tiene AJAX configurado
                        const table = $('#' + tableId).DataTable();
                        if (table.settings()[0].ajax) {
                            // Si tiene AJAX, recargar con AJAX
                            table.ajax.reload(null, false);
                        } else {
                            // Si no tiene AJAX, recargar la página completa
                            setTimeout(() => location.reload(), 800);
                        }
                    } else {
                        if (typeof notyf !== 'undefined') {
                            notyf.error(response.message || 'Error al eliminar el registro');
                        } else {
                            Swal.fire('Error', response.message || 'Error al eliminar el registro', 'error');
                        }
                    }
                },
                error: function (xhr) {
                    const response = xhr.responseJSON;
                    if (typeof notyf !== 'undefined') {
                        notyf.error(response?.message || 'Error en la solicitud');
                    } else {
                        Swal.fire('Error', response?.message || 'Error en la solicitud', 'error');
                    }
                }
            });
        }
    });
}
