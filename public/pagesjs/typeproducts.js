/**
 * Configuración avanzada de DataTables con jQuery
 * 
 * Features:
 * - Inicialización segura con DOMContentLoaded
 * - Configuración modularizada
 * - Mejor manejo de eventos
 * - Sintaxis ES6+
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const dataTable = $('.datatables');
    
    if (dataTable.length) {
        initDataTable(dataTable);
    }
});

/**
 * Inicializa y configura DataTable
 * @param {jQuery} table - Elemento jQuery de la tabla
 */
const initDataTable = (table) => {
    const config = {
        processing: true,
        serverSide: true,
        ajax: "/tipo-de-producto",
        dataType: 'json',
        type: "POST",
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>><"table-responsive"t><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: getLanguageConfig(),
        columns: [
            { data: 'name', name: 'name' },
            { data: 'material.name', name: 'material.name' },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false 
            }
        ],
        columnDefs: []
    };

    table.DataTable(config);
};

/**
 * Configuración de idioma para DataTables
 * @returns {Object} Configuración de idioma
 */
const getLanguageConfig = () => ({
    url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json",
    paginate: {
        next: '<i class="ri-arrow-right-s-line"></i>',
        previous: '<i class="ri-arrow-left-s-line"></i>'
    }
});

/**
 * Muestra diálogo de confirmación para eliminar registro
 * @param {string|number} id - ID del registro a eliminar
 */
const deleteRecord = (id) => {
    Swal.fire({
        title: '¿Está seguro de eliminar este registro?',
        text: "¡No podrá recuperar esta información!",
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
            window.location.href = `/tipo-de-producto/${id}/destroy`;
        }
    });
};