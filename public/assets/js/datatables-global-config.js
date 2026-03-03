/**
 * ═══════════════════════════════════════════════════════════════
 *  SERAMER — DATATABLES CONFIGURACIÓN GLOBAL — METRO UI
 *  Versión: 3.0  |  Fecha: 2026-03
 *
 *  Este archivo configura $.fn.DataTable.defaults UNA SOLA VEZ,
 *  afectando automáticamente a TODAS las tablas del sistema.
 *
 *  Requisitos:
 *  ✓ jQuery debe estar cargado antes que este archivo
 *  ✓ DataTables debe estar cargado antes que este archivo
 *
 *  Características:
 *  ✓ Order: último registro primero (desc por columna 0)
 *  ✓ lengthMenu: [5, 10, 15, 20] — máximo 20
 *  ✓ responsive: true
 *  ✓ Idioma en español (local, sin CDN)
 *  ✓ DOM layout con clases Metro UI
 *  ✓ Paginación tipo "simple_numbers" (sin círculos morados)
 * ═══════════════════════════════════════════════════════════════
 */

(function ($) {
    'use strict';

    if (typeof $.fn.DataTable === 'undefined') {
        console.warn('[SERAMER] DataTables no encontrado. datatables-global-config.js no se aplicó.');
        return;
    }

    /* ─── Traducción Español Completa (sin dependencia CDN) ────────*/
    var LANG_ES = {
        sEmptyTable: 'No hay datos disponibles en la tabla',
        sInfo: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        sInfoEmpty: 'Mostrando 0 a 0 de 0 registros',
        sInfoFiltered: '(filtrado de _MAX_ registros totales)',
        sInfoPostFix: '',
        sLengthMenu: 'Mostrar _MENU_ registros',
        sLoadingRecords: 'Cargando...',
        sProcessing: 'Procesando...',
        sSearch: 'Buscar:',
        sZeroRecords: 'No se encontraron registros coincidentes',
        oPaginate: {
            sFirst: '«',
            sLast: '»',
            sNext: '›',
            sPrevious: '‹'
        },
        oAria: {
            sSortAscending: ': activar para ordenar columna ascendente',
            sSortDescending: ': activar para ordenar columna descendente'
        },
        buttons: {
            copy: 'Copiar',
            colvis: 'Visibilidad',
            collection: 'Colección',
            colvisRestore: 'Restaurar visibilidad',
            copyKeys: 'Presione <i>ctrl</i> o <i>⌘</i> + <i>C</i> para copiar.',
            copySuccess: {
                1: '1 fila copiada al portapapeles',
                _: '%d filas copiadas al portapapeles'
            },
            copyTitle: 'Copiar al portapapeles',
            csv: 'CSV',
            excel: 'Excel',
            pageLength: {
                '-1': 'Mostrar todos los registros',
                _: 'Mostrar %d registros'
            },
            pdf: 'PDF',
            print: 'Imprimir',
            renameState: 'Renombrar estado',
            savedStates: 'Estados guardados',
            updateState: 'Actualizar estado'
        }
    };

    /* ─── DOM Layout Metro UI ──────────────────────────────────────
     *
     *  B  = Botones de Export (PDF, Excel, Imprimir...)
     *  f  = Caja de búsqueda (filter)
     *  r  = Loading indicator
     *  t  = La tabla
     *  i  = Información ("Mostrando 1 a 10 de 150")
     *  p  = Paginación
     *  l  = length selector ("Mostrar X registros") — incluido en la fila superior
     *
     *  En esta fila superior: lado izquierdo = Botones + selector de entradas,
     *                         lado derecho   = Búsqueda
     */
    var METRO_DOM =
        '<"seramer-dt-toolbar d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"' +
        '<"seramer-dt-left d-flex align-items-center gap-2 flex-wrap"Bl>' +
        '<"seramer-dt-right"f>' +
        '>' +
        'rt' +
        '<"seramer-dt-footer d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"i p>';

    /* ─── DEFAULTS GLOBALES ─────────────────────────────────────── */
    $.extend(true, $.fn.DataTable.defaults, {

        /* Responsividad */
        responsive: true,

        /* Registros por página — Máximo 20 como política del sistema */
        pageLength: 10,
        lengthMenu: [
            [5, 10, 15, 20],
            ['5 reg.', '10 reg.', '15 reg.', '20 reg.']
        ],

        /* Tipo de paginación: simple_numbers evita los círculos morados
           del tipo "full_numbers" con Bootstrap. Botones cuadrados Metro. */
        pagingType: 'simple_numbers',

        /* Idioma español completo */
        language: LANG_ES,

        /* DOM con clases Metro UI */
        dom: METRO_DOM,

        /* Ancho automático desactivado (se controla por CSS) */
        autoWidth: false,

        /* Accesibilidad */
        tabIndex: 0,

        /* NOTA: No se sobreescribe `order` globalmente para no romper
           tablas que tienen primera columna de acciones o sin orden.
           Cada vista define su propio `order` si lo necesita.
           El orden por defecto de DT (primera columna ASC) se mantiene. */

    });

    /* ─── Aplicar estilos Metro a botones de export DataTables ─────
     *  Cuando se inicializa cualquier tabla, normalizamos las clases
     *  de los botones de export para que coincidan con el sistema.
     */
    $(document).on('init.dt', function (e, settings) {
        var api = new $.fn.DataTable.Api(settings);
        var $wrapper = $(api.table().container());

        /* Arreglo de clase "dt-search" y "dt-length" en versiones nuevas */
        $wrapper.find('.dt-search input, .dataTables_filter input').attr('placeholder', 'Buscar registros...');

        /* Normalizar clases de botones export si los define la tabla */
        $wrapper.find('.dt-buttons .dt-button:not(.btn)').each(function () {
            $(this).addClass('btn btn-sm');
            if (!$(this).hasClass('btn-success') &&
                !$(this).hasClass('btn-danger') &&
                !$(this).hasClass('btn-info') &&
                !$(this).hasClass('btn-primary') &&
                !$(this).hasClass('btn-outline-secondary')) {
                $(this).addClass('btn-outline-secondary');
            }
        });
    });

    console.log('%c SERAMER DataTables Global Config v3.0 ✓', 'color:#1e6091;font-weight:700;font-size:11px;');

}(jQuery));
