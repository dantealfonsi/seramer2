"use strict";

const numberFormat = new Intl.NumberFormat('es-MX');
const formatDate = date => moment(date).format("DD/MM/YYYY");
const truncateDecimals = (num, decimals = 2) => 
  Math.floor(num * 10**decimals) / 10**decimals;

$(function() {
  const $dtProductTable = $(".datatables-tuning");
  if (!$dtProductTable.length) return;

  // Configuración común para renderizado de celdas
  const renderCell = (value, fallback = "0") => 
    `<span class='text-nowrap'>${value ?? fallback}</span>`;

  const renderStatusBadge = (status, type) => {
    const classes = {
      status: {
        'Pendiente': 'bg-warning',
        'En Proceso': 'bg-info',
        'Afinado': 'bg-success'
      },
      payment: {
        'Pendiente': 'bg-warning',
        'Pagado': 'bg-success'
      }
    };
    return `<span class="badge ${classes[type][status]}">${status}</span>`;
  };

  // Configuración DataTable
  const dtProducts = $dtProductTable.DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "/afinaciones",
      data: d => {
        d.start = $('#filterdateStart').val();
        d.end = $('#filterdateEnd').val();
        d.status = $('#filterStatus').val();
      }
    },
    dataType: 'json',
    type: "POST",
    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>><"table-responsive"t><"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    language: {
      url: "https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json",
      paginate: {
        next: '<i class="ri-arrow-right-s-line"></i>',
        previous: '<i class="ri-arrow-left-s-line"></i>'
      }
    },
    columns: [
      { data: 'id', render: (d, t, f) => `<span class='text-nowrap'>#${f.id}</span>`, responsivePriority: 1 },
      { data: 'created_at', render: d => renderCell(formatDate(d)), responsivePriority: 2 },
      { data: 'caja', render: d => renderCell(d), responsivePriority: 3 },
      { data: 'proveedor', render: d => renderCell(d), responsivePriority: 4 },
      { data: 'final_weight', render: d => renderCell(d), responsivePriority: 5 },
      { data: 'total_refined', render: d => renderCell(d), responsivePriority: 6 },
      { data: 'total_amount', render: d => renderCell(d), responsivePriority: 7 },
      { data: 'total_refined_amount', render: d => renderCell(d), responsivePriority: 8 },
      { data: 'total_gram_refined', render: d => renderCell(d), responsivePriority: 9 },
      { data: 'tuning_cost', render: d => renderCell(d), responsivePriority: 10 },
      { data: 'tuning_cost_per_gram', render: d => renderCell(d), responsivePriority: 11 },
      { data: 'gram_coopelation', render: d => renderCell(d), responsivePriority: 12 },
      { data: 'status', render: (d) => renderStatusBadge(d, 'status'), responsivePriority: 13 },
      { data: 'total', render: d => renderCell(d), responsivePriority: 14 },
      { data: 'total_pagado', render: d => renderCell(d), responsivePriority: 15 },
      { data: 'total_pendiente', render: d => renderCell(d), responsivePriority: 16 },
      { data: 'status_payment', render: (d) => renderStatusBadge(d, 'payment'), responsivePriority: 17 },
      { data: 'actions', orderable: false, searchable: false }
    ],
    lengthMenu: [7, 10, 20, 50, 70, 100],
    drawCallback: function() {
      const data = this.api().data();
      let totals = {
        refined: 0,
        refinedAmount: 0,
        coopelation: 0,
        total: 0
      };

      data.each(item => {
        totals.refined += parseFloat(item.total_refined) || 0;
        totals.refinedAmount += parseFloat(item.total_refined_amount) || 0;
        totals.coopelation += parseFloat(item.gram_coopelation) || 0;
        totals.total += parseFloat(item.total_pagado) || 0;
      });

      const totalPagado = (totals.refinedAmount + totals.coopelation) / (totals.refined || 0);
      
      $('#totalOroFino').html(truncateDecimals(totals.refined));
      $('#totalCostoPorGrAfinado').html(truncateDecimals(totals.refinedAmount));
      $('#totalCoopelacion').html(truncateDecimals(totals.coopelation));
      $('#totalpagado').html(truncateDecimals(totals.total));
    }
  });

  // Event handlers
  $('#filterdateEnd').on('change', function() {
    const start = $('#filterdateStart').val();
    const end = $(this).val();
    
    if (start && end && start > end) {
      showAlert('error', 'Oops...', 'La fecha de inicio no puede ser mayor a la fecha final');
      return false;
    }
    
    if (!start || !end) {
      showAlert('error', 'Oops...', 'Debe seleccionar ambas fechas');
      $('#filterdateStart, #filterdateEnd').val('');
    }
    
    dtProducts.ajax.reload();
  });

  $('#filterStatus, #reset_filter').on('change click', function(e) {
    if (e.currentTarget.id === 'reset_filter') {
      $('#filterStatus, #filterdateStart, #filterdateEnd').val('');
    }
    dtProducts.ajax.reload();
  });
});

function showAlert(icon, title, text) {
  Swal.fire({
    icon,
    title,
    text,
    position: 'top-center',
    customClass: { confirmButton: 'btn btn-primary waves-effect waves-light' },
    buttonsStyling: false
  });
}

function payOrder(id, amount) {
  $('#modalpreorden_id').html(id).text(id);
  $('#amount').val(amount);
  $('#modalamount').text(numberFormat.format(amount));
  $('#order_id').val(id);
  $('#PayOrderModal').modal('show');
}