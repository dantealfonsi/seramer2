/**
 * app-ecommerce-product-list
 */

"use strict";

const baseUrl = document.querySelector("html").getAttribute("data-base-url");
const assetsPath = document
    .querySelector("html")
    .getAttribute("data-assets-path");
const numberFormat = new Intl.NumberFormat("es-MX");
const numberFormat2 = new Intl.NumberFormat("es-MX");

// Datatable (jquery)
$(function () {

    // Variable declaration for table
    var dt_product_table = $(".datatables-sales"),
        productAdd = "/pos";

    if (dt_product_table.length) {
        var dt_products = dt_product_table.DataTable({
            ajax: {
                url: baseUrl + "/ventas",
                data: function(d) {
                    d.filterday = $('#filterday').val();
                    d.user_id = $('#users').val();
                }
            },
            columns: [
                // columns according to JSON
                { data: "id" },
                { data: "id" },
                { data: "caja" },
                { data: "cliente" },
                { data: "sales.created_at" },
                { data: "payment_methods" },
                { data: "total_amount" },
                { data: "total_pagado" },
                { data: "total_pendiente" },
                { data: "user_name" },
                { data: "actions" },
            ],
            columnDefs: [
                {
                    // For Responsive
                    className: "control",
                    searchable: false,
                    orderable: false,
                    responsivePriority: 2,
                    targets: 0,
                    render: function (data, type, full, meta) {
                        return "";
                    },
                },
                {
                    targets: 1,
                    render: function (data, type, full, meta) {
                        var $id = full["id"];
                        return "<a href='javascript:void();'> #" + $id + "</a>";
                    },
                },
                {
                    targets: 2,
                    render: function (data, type, full, meta) {
                        var $name = full['caja'];
                        return "<span>" + $name + "</span>";
                    },
                },
                {
                    // Product name and product_brand
                    targets: 3,
                    responsivePriority: 1,
                    render: function (data, type, full, meta) {
                        var $name = full['cliente'];
                        return "<span>" + $name + "</span>";
                    },
                },
                {
                    targets: 4,
                    responsivePriority: 5,
                    render: function (data, type, full, meta) {
                        var $fecha = moment(full["created_at"]).format("DD/MM/YYYY");
                        return ("<span class='text-nowrap'>" + $fecha + "</span>");
                    },
                },
                {
                    // Métodos de pago: convertir en lista
                    targets: 5,
                    render: function (data, type, full, meta) {
                        var paymentMethods = full["payment_methods"] || ""; // Obtener los métodos de pago
                        if (!paymentMethods.trim()) return "<em>Sin métodos de pago</em>"; // Manejo de datos vacíos
                        // Convertir a array y mapear para generar lista
                        var methodsArray = paymentMethods.split(",");
                        var methodsList = methodsArray
                            .map(function (method) {
                                return `
                                    <h6 class="mb-0 w-px-100 d-flex align-items-center text-secondary">
                                        <i class="ri-circle-fill ri-10px me-1"></i>${method.trim()}
                                    </h6>`;
                            })
                            .join(""); // Unir el HTML generado
                        return `<div>${methodsList}</div>`; // Envolver en un contenedor
                    },
                },
                {
                    targets: 6,
                    render: function (data, type, full, meta) {
                        return "<span>" + numberFormat.format(full["total_amount"]) + "</span>";
                    },
                },
                {
                    targets: 7,
                    render: function (data, type, full, meta) {
                        return "<span>" + numberFormat.format(full["total_pagado"]) + "</span>";
                    },
                },
                {
                    targets: 8,
                    render: function (data, type, full, meta) {
                        return "<span>" + numberFormat.format(full["total_pendiente"]) + "</span>";
                    },
                },
                {
                    targets: 9,
                    render: function (data, type, full, meta) {
                        var $vendedor = full["user_name"];
                        return "<span>" + $vendedor + "</span>";
                    },
                },
            ],
            dom:
                '<"card-header d-flex border-top rounded-0 flex-wrap pb-md-0 pt-0"' +
                '<"me-5 ms-n2"f>' +
                '<"d-flex justify-content-start justify-content-md-end align-items-baseline"<"dt-action-buttons d-flex align-items-start align-items-md-center justify-content-sm-center gap-4"lB>>' +
                ">t" +
                '<"row mx-1"' +
                '<"col-sm-12 col-md-6"i>' +
                '<"col-sm-12 col-md-6"p>' +
                ">",
            lengthMenu: [7, 10, 20, 50, 70, 100], //for length of menu
            language: {
                url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json",
                paginate: {
                    next: '<i class="ri-arrow-right-s-line"></i>',
                    previous: '<i class="ri-arrow-left-s-line"></i>'
                }
            },
            // Buttons with Dropdown
            buttons: [
                {
                    extend: "collection",
                    className:
                        "btn btn-outline-success dropdown-toggle me-4 waves-effect waves-light",
                    text: '<i class="ri-upload-2-line ri-16px me-2"></i><span class="d-none d-sm-inline-block">Exportar </span>',
                    buttons: [
                        {
                            extend: "pdf",
                            text: '<i class="ri-file-pdf-line me-1"></i>Pdf',
                            className: "dropdown-item",
                            action: function (e, dt, button, config) {
                                var user = $("#users").val();
                                var day = $("#filterday").val();

                                window.open("/ventas/generar-pdf?user=" + user + "&day=" + day, "_blank");
                            },
                        }
                    ],
                },
            ],
            // For responsive popup
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return "Detalles de Venta - " + data["name"];
                        },
                    }),
                    type: "column",
                    renderer: function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col, i) {
                            return col.title !== "" // ? Do not show row in modal popup if title is blank (for check box)
                                ? '<tr data-dt-row="' +
                                      col.rowIndex +
                                      '" data-dt-column="' +
                                      col.columnIndex +
                                      '">' +
                                      "<td>" +
                                      col.title +
                                      ":" +
                                      "</td> " +
                                      "<td>" +
                                      col.data +
                                      "</td>" +
                                      "</tr>"
                                : "";
                        }).join("");

                        return data
                            ? $('<table class="table"/><tbody />').append(data)
                            : false;
                    },
                },
            },
            // contador de ventas y que si se filtra los contadores se actualicen cuando se filtre
            drawCallback: function(settings) {
                // acceder a la información del json de la tabla
                var data = this.api().data();
                var ventas = data.length;
                $('#total_sales').html(ventas);
                // tomar un solo registro de la data
                var $total = 0;
                data.each(function(item) {
                    $total += parseFloat(item.total_amount);
                });

                $('#total_amount').html(numberFormat.format($total));
            }
        });
        $(".dt-action-buttons").addClass("pt-0");
        $(".dt-buttons").addClass("d-flex flex-wrap");
    }

    $('#filterday').on('change', function() {
        dt_products.ajax.reload();
    });
    $('#users').on('change', function() {
        dt_products.ajax.reload();
    })
    $('#reset_filter').on('click', function () {
        $('#filterday').val('');
        $('#users').val('');
        dt_products.ajax.reload();
    });
});
function payOrder(id, amount) {
    $('#modalpreorden_id').html('');
    $('#amount').val('');
    $('#modalamount').text(numberFormat2.format(amount));
    $('#modalpreorden_id').html(id);
    $('#order_id').val(id);
    $('#amount').val(amount);
    $('#PayOrderModal').modal('show');
}

function cancelSale(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción cancelará la venta y reintegrará los productos al inventario. Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, cancelar venta',
        cancelButtonText: 'Cancelar',
        input: 'text',
        inputLabel: 'Observación (opcional)',
        inputPlaceholder: 'Motivo de la cancelación...',
        inputValidator: (value) => {
            // La observación es opcional, no hay validación
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Cancelando venta...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Realizar la petición AJAX
            $.ajax({
                url: baseUrl + '/ventas/cancelar-venta',
                type: 'POST',
                data: {
                    sale_id: id,
                    observation: result.value || 'Cancelación de venta',
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        title: '¡Venta cancelada!',
                        text: 'La venta ha sido cancelada exitosamente y los productos han sido reintegrados al inventario',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Recargar la tabla
                        $('.datatables-sales').DataTable().ajax.reload();
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Error al cancelar la venta';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        title: 'Error',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }
    });
}