/**
 * ═══════════════════════════════════════════════════════════════
 *  SERAMER — CHART.JS CONFIGURACIÓN GLOBAL METRO UI
 *  Versión: 3.0  |  Fecha: 2026-03
 *
 *  Paleta Metro Profesional (sin neón):
 *  → Azul Acero, Verde Bosque, Ámbar, Carmesí, Cian Acero
 *  → Degradados lineales sutiles
 *  → Adaptación Light / Dark mode automática
 *  → Tipografía Inter
 * ═══════════════════════════════════════════════════════════════
 */

(function () {
    'use strict';

    /* ─── Paleta Metro (NO neón, tonos profesionales) ──────────────*/
    const METRO_PALETTE = {
        primary: { solid: '#1e6091', bg: 'rgba(30,96,145,0.12)', mid: 'rgba(30,96,145,0.55)' },
        success: { solid: '#2d7a4f', bg: 'rgba(45,122,79,0.12)', mid: 'rgba(45,122,79,0.55)' },
        warning: { solid: '#b8860b', bg: 'rgba(184,134,11,0.12)', mid: 'rgba(184,134,11,0.55)' },
        danger: { solid: '#c0392b', bg: 'rgba(192,57,43,0.12)', mid: 'rgba(192,57,43,0.55)' },
        info: { solid: '#2980b9', bg: 'rgba(41,128,185,0.12)', mid: 'rgba(41,128,185,0.55)' },
        slate: { solid: '#5d6778', bg: 'rgba(93,103,120,0.12)', mid: 'rgba(93,103,120,0.55)' },
        teal: { solid: '#1a7a72', bg: 'rgba(26,122,114,0.12)', mid: 'rgba(26,122,114,0.55)' },
        indigo: { solid: '#3d52a0', bg: 'rgba(61,82,160,0.12)', mid: 'rgba(61,82,160,0.55)' },
    };

    /* Secuencia de colores para múltiples datasets */
    const COLOR_SEQUENCE = [
        METRO_PALETTE.primary,
        METRO_PALETTE.success,
        METRO_PALETTE.warning,
        METRO_PALETTE.danger,
        METRO_PALETTE.info,
        METRO_PALETTE.slate,
        METRO_PALETTE.teal,
        METRO_PALETTE.indigo,
    ];

    /* ─── Detección del modo (Light / Dark) ──────────────────────*/
    function isDarkMode() {
        const el = document.documentElement;
        return (
            el.classList.contains('dark-style') ||
            el.getAttribute('data-bs-theme') === 'dark'
        );
    }

    function getThemeColors() {
        const dark = isDarkMode();
        return {
            gridColor: dark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)',
            tickColor: dark ? '#94a3b8' : '#4a5568',
            legendColor: dark ? '#cbd5e1' : '#1a202c',
            titleColor: dark ? '#f1f5f9' : '#1a202c',
            tooltipBg: dark ? 'rgba(17,24,39,0.97)' : 'rgba(255,255,255,0.98)',
            tooltipText: dark ? '#f1f5f9' : '#1a202c',
            tooltipBorder: dark ? 'rgba(30,96,145,0.4)' : 'rgba(30,96,145,0.35)',
        };
    }

    /* ─── Plugin: Subtle shadow en barras (Metro-style) ───────────
     * Sombra sutil para dar profundidad sin exceso.
     */
    const metroShadowPlugin = {
        id: 'metroBarShadow',
        beforeDatasetsDraw(chart) {
            if (!['bar', 'line'].includes(chart.config.type)) return;
            const ctx = chart.ctx;
            ctx.save();
            ctx.shadowColor = isDarkMode() ? 'rgba(0,0,0,0.4)' : 'rgba(0,0,0,0.15)';
            ctx.shadowBlur = 8;
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 3;
        },
        afterDatasetsDraw(chart) {
            chart.ctx.restore();
        }
    };

    /* ─── Plugin: Estado vacío (sin datos) ───────────────────────*/
    const emptyStatePlugin = {
        id: 'metroEmptyState',
        afterDraw(chart) {
            const hasData = chart.data.datasets.some(
                ds => ds.data && ds.data.some(v => v !== null && v !== 0)
            );
            if (hasData) return;
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            ctx.save();
            ctx.fillStyle = isDarkMode() ? 'rgba(148,163,184,0.5)' : 'rgba(74,85,104,0.4)';
            ctx.font = '600 13px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(
                'Sin datos disponibles',
                (chartArea.left + chartArea.right) / 2,
                (chartArea.top + chartArea.bottom) / 2
            );
            ctx.restore();
        }
    };

    /* ─── Gradient vertical helper ────────────────────────────────*/
    function createGradient(ctx, chartArea, colorEntry) {
        if (!chartArea) return colorEntry.mid;
        const g = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
        g.addColorStop(0, colorEntry.bg);
        g.addColorStop(0.6, colorEntry.mid);
        g.addColorStop(1, colorEntry.solid);
        return g;
    }

    /* ─── Main: Apply Chart.js Global Config ─────────────────────*/
    if (typeof Chart === 'undefined') return;

    // Registrar plugins globales
    Chart.register(metroShadowPlugin, emptyStatePlugin);

    const tc = getThemeColors();

    /* Fuente global */
    Chart.defaults.font.family = "'Inter', 'Segoe UI', system-ui, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = tc.tickColor;

    Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;

    /* Animación */
    Chart.defaults.animation = {
        duration: 500,
        easing: 'easeInOutQuart',
    };

    /* Escalas */
    Chart.defaults.scale.grid.color = tc.gridColor;
    Chart.defaults.scale.grid.lineWidth = 1;
    Chart.defaults.scale.ticks.color = tc.tickColor;
    Chart.defaults.scale.ticks.font = {
        family: "'Inter', sans-serif", size: 11, weight: '500'
    };
    Chart.defaults.scale.title.color = tc.titleColor;
    Chart.defaults.scale.title.font = {
        family: "'Inter', sans-serif", size: 12, weight: '700'
    };

    /* Legend */
    Chart.defaults.plugins.legend.labels.color = tc.legendColor;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.pointStyle = 'rectRounded';
    Chart.defaults.plugins.legend.labels.padding = 20;
    Chart.defaults.plugins.legend.labels.font = {
        family: "'Inter', sans-serif", size: 12, weight: '600'
    };

    /* Tooltip */
    Chart.defaults.plugins.tooltip.backgroundColor = tc.tooltipBg;
    Chart.defaults.plugins.tooltip.titleColor = tc.tooltipText;
    Chart.defaults.plugins.tooltip.bodyColor = tc.tooltipText;
    Chart.defaults.plugins.tooltip.borderColor = tc.tooltipBorder;
    Chart.defaults.plugins.tooltip.borderWidth = 1;
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 6;
    Chart.defaults.plugins.tooltip.displayColors = true;
    Chart.defaults.plugins.tooltip.boxPadding = 5;
    Chart.defaults.plugins.tooltip.titleFont = { weight: '700', size: 12 };
    Chart.defaults.plugins.tooltip.bodyFont = { weight: '400', size: 12 };

    /* ─── Overrides por tipo ──────────────────────────────────────*/

    // BAR
    Chart.overrides['bar'] = Chart.overrides['bar'] || {};
    Chart.overrides['bar'].datasets = [{
        borderWidth: 0,
        borderRadius: 6,
        borderSkipped: false,
        backgroundColor(ctx) {
            const c = COLOR_SEQUENCE[ctx.datasetIndex % COLOR_SEQUENCE.length];
            const chart = ctx.chart;
            return createGradient(chart.ctx, chart.chartArea, c);
        },
    }];

    // LINE
    Chart.overrides['line'] = Chart.overrides['line'] || {};
    Chart.overrides['line'].datasets = [{
        borderWidth: 2.5,
        pointRadius: 4,
        pointHoverRadius: 6,
        tension: 0.35,
        fill: true,
        borderColor(ctx) {
            return COLOR_SEQUENCE[ctx.datasetIndex % COLOR_SEQUENCE.length].solid;
        },
        pointBackgroundColor(ctx) {
            return COLOR_SEQUENCE[ctx.datasetIndex % COLOR_SEQUENCE.length].solid;
        },
        backgroundColor(ctx) {
            const c = COLOR_SEQUENCE[ctx.datasetIndex % COLOR_SEQUENCE.length];
            const chart = ctx.chart;
            if (!chart.chartArea) return c.bg;
            const g = chart.ctx.createLinearGradient(0, chart.chartArea.top, 0, chart.chartArea.bottom);
            g.addColorStop(0, c.mid.replace('0.55', '0.25'));
            g.addColorStop(1, 'rgba(0,0,0,0)');
            return g;
        },
    }];

    // DOUGHNUT / PIE
    const donutDefaults = {
        borderWidth: 3,
        borderColor: isDarkMode() ? '#1f2937' : '#ffffff',
        hoverBorderWidth: 3,
        backgroundColor(ctx) {
            return COLOR_SEQUENCE[ctx.dataIndex % COLOR_SEQUENCE.length].solid;
        },
    };
    Chart.overrides['doughnut'] = Chart.overrides['doughnut'] || {};
    Chart.overrides['doughnut'].datasets = [donutDefaults];
    Chart.overrides['pie'] = Chart.overrides['pie'] || {};
    Chart.overrides['pie'].datasets = [donutDefaults];

    Chart.defaults.datasets.doughnut = Chart.defaults.datasets.doughnut || {};
    Chart.defaults.datasets.doughnut.cutout = '70%';

    /* ─── Actualizar todos los charts al cambiar de tema ─────────*/
    window._seramChartUpdateTheme = function () {
        const newTc = getThemeColors();
        Chart.defaults.color = newTc.tickColor;
        Chart.defaults.scale.grid.color = newTc.gridColor;
        Chart.defaults.scale.ticks.color = newTc.tickColor;
        Chart.defaults.plugins.legend.labels.color = newTc.legendColor;
        Chart.defaults.plugins.tooltip.backgroundColor = newTc.tooltipBg;
        Chart.defaults.plugins.tooltip.titleColor = newTc.tooltipText;
        Chart.defaults.plugins.tooltip.bodyColor = newTc.tooltipText;
        Chart.defaults.plugins.tooltip.borderColor = newTc.tooltipBorder;
        Object.values(Chart.instances).forEach(c => c.update('none'));
    };

    /* Observer para cambios de tema en tiempo real */
    new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
            if (['data-bs-theme', 'class'].includes(m.attributeName)) {
                setTimeout(window._seramChartUpdateTheme, 80);
            }
        });
    }).observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-bs-theme', 'class']
    });

    /* Exponer API pública */
    window.SERAMER_CHART = {
        palette: METRO_PALETTE,
        colorSequence: COLOR_SEQUENCE,
        createGradient,
        isDark: isDarkMode,
        themeColors: getThemeColors,
    };

    console.log('%c SERAMER Chart.js Metro UI v3.0 ✓', 'color:#1e6091;font-weight:700;font-size:11px;');

})();
