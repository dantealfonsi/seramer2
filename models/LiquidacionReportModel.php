<?php
/**
 * Modelo de Reportes de Liquidación
 * 
 * Portado desde seramer-local/App/Models/ReportModel.php
 * Adaptado a la arquitectura de seramer2 (sin namespace, con clases planas)
 */

require_once __DIR__ . '/Model.php';

class LiquidacionReportModel extends Model {
    protected $table = 'contracts';

    // =====================================================================
    // CONTRATOS MOROSOS
    // =====================================================================
    public function getDelinquentContracts(): array {
        $query = "
            SELECT 
                c.id as contract_id,
                a.id as awardee_id,
                CONCAT(a.first_name, ' ', a.last_name) as awardee_name,
                a.id_number as awardee_id_number,
                a.phone as awardee_phone,
                a.email as awardee_email,
                COUNT(DISTINCT cp.id) as overdue_payments_count,
                SUM(
                    (SELECT SUM(COALESCE(ic.payment_count, 0) + COALESCE(ec.payment_count, 0))
                     FROM contract_business_categories cbc
                     LEFT JOIN internal_business_categories ic ON cbc.internal_category_id = ic.id
                     LEFT JOIN external_business_categories ec ON cbc.external_category_id = ec.id
                     WHERE cbc.contract_id = c.id
                    ) * er.bs_value
                ) as total_amount_due,
                SUM(
                    COALESCE((SELECT SUM(cpi.amount) 
                              FROM contract_payment_installments cpi 
                              WHERE cpi.contract_payment_id = cp.id), 0)
                ) as total_paid,
                MIN(cp.payment_date) as first_overdue_date,
                DATEDIFF(CURDATE(), MIN(cp.payment_date)) as days_overdue
            FROM contracts c
            INNER JOIN awardees a ON c.awardee_id = a.id
            INNER JOIN contract_payments cp ON c.id = cp.contract_id
            INNER JOIN euro_rates er ON cp.euro_rate_id = er.id
            WHERE cp.status = 'pending'
            AND cp.payment_date < CURDATE()
            GROUP BY c.id, a.id, a.first_name, a.last_name, a.id_number, a.phone, a.email
            HAVING overdue_payments_count > 0
            ORDER BY days_overdue DESC, total_amount_due DESC
        ";
        return $this->query($query);
    }

    // =====================================================================
    // TOTAL ACUMULADO POR ZONA
    // =====================================================================
    public function getZoneAccumulated(string $startDate, string $endDate, ?int $fiscalYearId = null): array {
        $query = "
            SELECT 
                z.id as zone_id,
                z.name as zone_name,
                SUM(cpi.amount) as total_accumulated,
                COUNT(DISTINCT c.id) as contracts_count,
                COUNT(DISTINCT cpi.id) as payments_count
            FROM zones z
            INNER JOIN sectors s ON z.id = s.zone_id
            INNER JOIN market_stalls ms ON s.id = ms.sector_id
            INNER JOIN contract_locations cl ON ms.id = cl.stall_id
            INNER JOIN contracts c ON cl.contract_id = c.id
            INNER JOIN contract_payments cp ON c.id = cp.contract_id
            INNER JOIN contract_payment_installments cpi ON cp.id = cpi.contract_payment_id
            WHERE DATE(cpi.date) BETWEEN :start_date AND :end_date
        ";
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        if ($fiscalYearId !== null) {
            $query .= " AND c.fiscal_year_id = :fiscal_year_id";
            $params['fiscal_year_id'] = $fiscalYearId;
        }
        $query .= " GROUP BY z.id, z.name ORDER BY total_accumulated DESC";
        return $this->query($query, $params);
    }

    // =====================================================================
    // REPORTE DE CAJA
    // =====================================================================
    public function getCashReport(string $startDate, string $endDate, ?int $fiscalYearId = null): array {
        $query = "
            SELECT 
                CONCAT(a.first_name, ' ', a.last_name) as adjudicatario,
                COALESCE(ibc.name, ebc.name) as rubro,
                z.name as zona,
                s.name as sector,
                ms.stall_number as local,
                DATE_FORMAT(cpi.date, '%Y-%m') as mes_pagado,
                cpi.amount as monto,
                cpi.date as fecha_pago
            FROM contract_payment_installments cpi
            INNER JOIN contract_payments cp ON cpi.contract_payment_id = cp.id
            INNER JOIN contracts c ON cp.contract_id = c.id
            INNER JOIN awardees a ON c.awardee_id = a.id
            INNER JOIN contract_locations cl ON c.id = cl.contract_id
            INNER JOIN market_stalls ms ON cl.stall_id = ms.id
            INNER JOIN sectors s ON ms.sector_id = s.id
            INNER JOIN zones z ON s.zone_id = z.id
            LEFT JOIN contract_business_categories cbc ON c.id = cbc.contract_id
            LEFT JOIN internal_business_categories ibc ON cbc.internal_category_id = ibc.id
            LEFT JOIN external_business_categories ebc ON cbc.external_category_id = ebc.id
            WHERE DATE(cpi.date) BETWEEN :start_date AND :end_date
        ";
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        if ($fiscalYearId !== null) {
            $query .= " AND c.fiscal_year_id = :fiscal_year_id";
            $params['fiscal_year_id'] = $fiscalYearId;
        }
        $query .= " ORDER BY cpi.date ASC, z.name ASC, a.last_name ASC";
        return $this->query($query, $params);
    }

    public function getCashReportTotalsByZone(string $startDate, string $endDate, ?int $fiscalYearId = null): array {
        $query = "
            SELECT z.id as zone_id, z.name as zona, SUM(cpi.amount) as total
            FROM contract_payment_installments cpi
            INNER JOIN contract_payments cp ON cpi.contract_payment_id = cp.id
            INNER JOIN contracts c ON cp.contract_id = c.id
            INNER JOIN contract_locations cl ON c.id = cl.contract_id
            INNER JOIN market_stalls ms ON cl.stall_id = ms.id
            INNER JOIN sectors s ON ms.sector_id = s.id
            INNER JOIN zones z ON s.zone_id = z.id
            WHERE DATE(cpi.date) BETWEEN :start_date AND :end_date
        ";
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        if ($fiscalYearId !== null) {
            $query .= " AND c.fiscal_year_id = :fiscal_year_id";
            $params['fiscal_year_id'] = $fiscalYearId;
        }
        $query .= " GROUP BY z.id, z.name ORDER BY total DESC";
        return $this->query($query, $params);
    }

    // =====================================================================
    // CAJA POR MÉTODOS DE PAGO
    // =====================================================================
    public function getCashReportByPaymentMethod(string $startDate, string $endDate, ?int $fiscalYearId = null, ?array $zoneIds = null): array {
        $query = "
            SELECT 
                pm.id as payment_method_id,
                pm.name as metodo_pago,
                CONCAT(a.first_name, ' ', a.last_name) as adjudicatario,
                COALESCE(ibc.name, ebc.name) as rubro,
                z.name as zona,
                s.name as sector,
                ms.stall_number as local,
                DATE_FORMAT(cpi.date, '%Y-%m') as mes_pagado,
                cpi.amount as monto,
                cpi.date as fecha_pago
            FROM contract_payment_installments cpi
            INNER JOIN payment_methods pm ON cpi.payment_method_id = pm.id
            INNER JOIN contract_payments cp ON cpi.contract_payment_id = cp.id
            INNER JOIN contracts c ON cp.contract_id = c.id
            INNER JOIN awardees a ON c.awardee_id = a.id
            INNER JOIN contract_locations cl ON c.id = cl.contract_id
            INNER JOIN market_stalls ms ON cl.stall_id = ms.id
            INNER JOIN sectors s ON ms.sector_id = s.id
            INNER JOIN zones z ON s.zone_id = z.id
            LEFT JOIN contract_business_categories cbc ON c.id = cbc.contract_id
            LEFT JOIN internal_business_categories ibc ON cbc.internal_category_id = ibc.id
            LEFT JOIN external_business_categories ebc ON cbc.external_category_id = ebc.id
            WHERE DATE(cpi.date) BETWEEN :start_date AND :end_date
        ";
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        if ($fiscalYearId !== null) {
            $query .= " AND c.fiscal_year_id = :fiscal_year_id";
            $params['fiscal_year_id'] = $fiscalYearId;
        }
        if ($zoneIds !== null && !empty($zoneIds)) {
            $placeholders = [];
            foreach ($zoneIds as $index => $zoneId) {
                $key = 'zone_id_' . $index;
                $placeholders[] = ':' . $key;
                $params[$key] = (int)$zoneId;
            }
            $query .= " AND z.id IN (" . implode(',', $placeholders) . ")";
        }
        $query .= " ORDER BY pm.name ASC, cpi.date ASC, z.name ASC";
        return $this->query($query, $params);
    }

    public function getCashReportPaymentMethodTotals(string $startDate, string $endDate, ?int $fiscalYearId = null, ?array $zoneIds = null): array {
        $query = "
            SELECT pm.id as payment_method_id, pm.name as metodo_pago, SUM(cpi.amount) as total
            FROM contract_payment_installments cpi
            INNER JOIN payment_methods pm ON cpi.payment_method_id = pm.id
            INNER JOIN contract_payments cp ON cpi.contract_payment_id = cp.id
            INNER JOIN contracts c ON cp.contract_id = c.id
            INNER JOIN contract_locations cl ON c.id = cl.contract_id
            INNER JOIN market_stalls ms ON cl.stall_id = ms.id
            INNER JOIN sectors s ON ms.sector_id = s.id
            INNER JOIN zones z ON s.zone_id = z.id
            WHERE DATE(cpi.date) BETWEEN :start_date AND :end_date
        ";
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        if ($fiscalYearId !== null) {
            $query .= " AND c.fiscal_year_id = :fiscal_year_id";
            $params['fiscal_year_id'] = $fiscalYearId;
        }
        if ($zoneIds !== null && !empty($zoneIds)) {
            $placeholders = [];
            foreach ($zoneIds as $index => $zoneId) {
                $key = 'zone_id_' . $index;
                $placeholders[] = ':' . $key;
                $params[$key] = (int)$zoneId;
            }
            $query .= " AND z.id IN (" . implode(',', $placeholders) . ")";
        }
        $query .= " GROUP BY pm.id, pm.name ORDER BY total DESC";
        return $this->query($query, $params);
    }

    // =====================================================================
    // INGRESOS POR ZONA
    // =====================================================================
    public function getIncomeByZoneReport(int $month, int $year, ?int $zoneId = null, ?string $contractType = null): array {
        $query = "
            SELECT 
                cp.id as factura_id,
                cp.payment_reference as numero_factura,
                cp.payment_date as fecha_factura,
                cp.amount as monto_total,
                s.name as sector,
                COALESCE(ibc.name, ebc.name) as rubro,
                z.name as zona,
                z.id as zone_id,
                c.type as tipo_contrato
            FROM contract_payments cp
            INNER JOIN contracts c ON cp.contract_id = c.id
            INNER JOIN contract_locations cl ON c.id = cl.contract_id
            INNER JOIN market_stalls ms ON cl.stall_id = ms.id
            INNER JOIN sectors s ON ms.sector_id = s.id
            INNER JOIN zones z ON s.zone_id = z.id
            LEFT JOIN contract_business_categories cbc ON c.id = cbc.contract_id
            LEFT JOIN internal_business_categories ibc ON cbc.internal_category_id = ibc.id
            LEFT JOIN external_business_categories ebc ON cbc.external_category_id = ebc.id
            WHERE MONTH(cp.payment_date) = :month
            AND YEAR(cp.payment_date) = :year
            AND c.status != 'canceled'
        ";
        $params = ['month' => $month, 'year' => $year];
        if ($zoneId !== null) {
            $query .= " AND z.id = :zone_id";
            $params['zone_id'] = $zoneId;
        }
        if ($contractType !== null && in_array($contractType, ['simultaneous', 'advance'])) {
            $query .= " AND c.type = :contract_type";
            $params['contract_type'] = $contractType;
        }
        $query .= " ORDER BY z.name ASC, s.name ASC, cp.payment_date ASC, cp.id ASC";
        return $this->query($query, $params);
    }

    public function getIncomeByZoneTotals(int $month, int $year, ?int $zoneId = null, ?string $contractType = null): array {
        $query = "
            SELECT COUNT(DISTINCT cp.id) as total_facturas, SUM(cp.amount) as total_ingresos
            FROM contract_payments cp
            INNER JOIN contracts c ON cp.contract_id = c.id
            INNER JOIN contract_locations cl ON c.id = cl.contract_id
            INNER JOIN market_stalls ms ON cl.stall_id = ms.id
            INNER JOIN sectors s ON ms.sector_id = s.id
            INNER JOIN zones z ON s.zone_id = z.id
            WHERE MONTH(cp.payment_date) = :month
            AND YEAR(cp.payment_date) = :year
            AND c.status != 'canceled'
        ";
        $params = ['month' => $month, 'year' => $year];
        if ($zoneId !== null) {
            $query .= " AND z.id = :zone_id";
            $params['zone_id'] = $zoneId;
        }
        if ($contractType !== null && in_array($contractType, ['simultaneous', 'advance'])) {
            $query .= " AND c.type = :contract_type";
            $params['contract_type'] = $contractType;
        }
        $result = $this->queryOne($query, $params);
        return [
            'total_facturas' => (int)($result['total_facturas'] ?? 0),
            'total_ingresos' => (float)($result['total_ingresos'] ?? 0)
        ];
    }

    // =====================================================================
    // INGRESOS POR RUBRO
    // =====================================================================
    public function getIncomeByCategoryReport(int $month, int $year, ?int $zoneId = null, ?string $contractType = null): array {
        $query = "
            SELECT 
                cp.id as factura_id,
                cp.payment_reference as numero_factura,
                cp.payment_date as fecha_factura,
                cp.amount as monto_total,
                s.name as sector,
                COALESCE(ibc.name, ebc.name) as rubro,
                COALESCE(ibc.id, ebc.id) as rubro_id,
                z.name as zona,
                z.id as zone_id,
                c.type as tipo_contrato
            FROM contract_payments cp
            INNER JOIN contracts c ON cp.contract_id = c.id
            INNER JOIN contract_locations cl ON c.id = cl.contract_id
            INNER JOIN market_stalls ms ON cl.stall_id = ms.id
            INNER JOIN sectors s ON ms.sector_id = s.id
            INNER JOIN zones z ON s.zone_id = z.id
            LEFT JOIN contract_business_categories cbc ON c.id = cbc.contract_id
            LEFT JOIN internal_business_categories ibc ON cbc.internal_category_id = ibc.id
            LEFT JOIN external_business_categories ebc ON cbc.external_category_id = ebc.id
            WHERE MONTH(cp.payment_date) = :month
            AND YEAR(cp.payment_date) = :year
            AND c.status != 'canceled'
            AND (ibc.name IS NOT NULL OR ebc.name IS NOT NULL)
        ";
        $params = ['month' => $month, 'year' => $year];
        if ($zoneId !== null) {
            $query .= " AND z.id = :zone_id";
            $params['zone_id'] = $zoneId;
        }
        if ($contractType !== null && in_array($contractType, ['simultaneous', 'advance'])) {
            $query .= " AND c.type = :contract_type";
            $params['contract_type'] = $contractType;
        }
        $query .= " ORDER BY COALESCE(ibc.name, ebc.name) ASC, z.name ASC, s.name ASC, cp.payment_date ASC";
        return $this->query($query, $params);
    }

    public function getIncomeByCategoryTotals(int $month, int $year, ?int $zoneId = null, ?string $contractType = null): array {
        $base = "
            FROM contract_payments cp
            INNER JOIN contracts c ON cp.contract_id = c.id
            INNER JOIN contract_locations cl ON c.id = cl.contract_id
            INNER JOIN market_stalls ms ON cl.stall_id = ms.id
            INNER JOIN sectors s ON ms.sector_id = s.id
            INNER JOIN zones z ON s.zone_id = z.id
            LEFT JOIN contract_business_categories cbc ON c.id = cbc.contract_id
            LEFT JOIN internal_business_categories ibc ON cbc.internal_category_id = ibc.id
            LEFT JOIN external_business_categories ebc ON cbc.external_category_id = ebc.id
            WHERE MONTH(cp.payment_date) = :month AND YEAR(cp.payment_date) = :year
            AND c.status != 'canceled' AND (ibc.name IS NOT NULL OR ebc.name IS NOT NULL)
        ";
        $params = ['month' => $month, 'year' => $year];
        if ($zoneId !== null) { $base .= " AND z.id = :zone_id"; $params['zone_id'] = $zoneId; }
        if ($contractType !== null && in_array($contractType, ['simultaneous', 'advance'])) {
            $base .= " AND c.type = :contract_type"; $params['contract_type'] = $contractType;
        }

        $result = $this->queryOne("SELECT COUNT(DISTINCT cp.id) as total_facturas, SUM(cp.amount) as total_ingresos" . $base, $params);

        $byCategory = $this->query(
            "SELECT COALESCE(ibc.name, ebc.name) as rubro, COUNT(DISTINCT cp.id) as total_facturas, SUM(cp.amount) as total_ingresos" . $base . " GROUP BY COALESCE(ibc.name, ebc.name) ORDER BY total_ingresos DESC",
            $params
        );

        return [
            'total_facturas'    => (int)($result['total_facturas'] ?? 0),
            'total_ingresos'    => (float)($result['total_ingresos'] ?? 0),
            'totals_by_category' => $byCategory
        ];
    }

    // =====================================================================
    // RESUMEN DE INGRESOS (por zona y sector)
    // =====================================================================
    public function getIncomeSummaryByZoneSector(?int $month = null, ?int $year = null, ?int $fiscalYearId = null, ?string $contractType = null): array {
        $paymentConditions = "cp.status = 'paid'";
        $contractConditions = "c.status != 'canceled'";
        $params = [];

        if ($month !== null)       { $paymentConditions .= " AND MONTH(cp.payment_date) = :month"; $params['month'] = $month; }
        if ($year !== null)        { $paymentConditions .= " AND YEAR(cp.payment_date) = :year";   $params['year']  = $year; }
        if ($fiscalYearId !== null){ $contractConditions .= " AND c.fiscal_year_id = :fiscal_year_id"; $params['fiscal_year_id'] = $fiscalYearId; }
        if ($contractType !== null && in_array($contractType, ['simultaneous', 'advance'])) {
            $contractConditions .= " AND c.type = :contract_type"; $params['contract_type'] = $contractType;
        }

        $query = "
            SELECT 
                z.id as zone_id, z.name as zona,
                s.id as sector_id, s.name as sector,
                COALESCE(COUNT(DISTINCT cp.id), 0) as cantidad_recibos,
                COALESCE(SUM(cp.amount), 0) as monto_recaudado
            FROM zones z
            INNER JOIN sectors s ON z.id = s.zone_id
            LEFT JOIN market_stalls ms ON s.id = ms.sector_id
            LEFT JOIN contract_locations cl ON ms.id = cl.stall_id
            LEFT JOIN contracts c ON cl.contract_id = c.id AND ({$contractConditions})
            LEFT JOIN contract_payments cp ON c.id = cp.contract_id AND ({$paymentConditions})
            GROUP BY z.id, z.name, s.id, s.name 
            ORDER BY z.name ASC, s.name ASC
        ";
        return $this->query($query, $params);
    }

    public function getIncomeSummaryTotals(?int $month = null, ?int $year = null, ?int $fiscalYearId = null, ?string $contractType = null): array {
        $paymentConditions = "cp.status = 'paid'";
        $contractConditions = "c.status != 'canceled'";
        $params = [];

        if ($month !== null)       { $paymentConditions .= " AND MONTH(cp.payment_date) = :month"; $params['month'] = $month; }
        if ($year !== null)        { $paymentConditions .= " AND YEAR(cp.payment_date) = :year";   $params['year']  = $year; }
        if ($fiscalYearId !== null){ $contractConditions .= " AND c.fiscal_year_id = :fiscal_year_id"; $params['fiscal_year_id'] = $fiscalYearId; }
        if ($contractType !== null && in_array($contractType, ['simultaneous', 'advance'])) {
            $contractConditions .= " AND c.type = :contract_type"; $params['contract_type'] = $contractType;
        }

        $byZone = $this->query("
            SELECT z.id as zone_id, z.name as zona,
                COALESCE(COUNT(DISTINCT cp.id), 0) as cantidad_recibos,
                COALESCE(SUM(cp.amount), 0) as monto_recaudado
            FROM zones z
            LEFT JOIN sectors s ON z.id = s.zone_id
            LEFT JOIN market_stalls ms ON s.id = ms.sector_id
            LEFT JOIN contract_locations cl ON ms.id = cl.stall_id
            LEFT JOIN contracts c ON cl.contract_id = c.id AND ({$contractConditions})
            LEFT JOIN contract_payments cp ON c.id = cp.contract_id AND ({$paymentConditions})
            GROUP BY z.id, z.name ORDER BY z.name ASC
        ", $params);

        $paramsGeneral = [];
        $whereGeneral = "WHERE cp.status = 'paid' AND c.status != 'canceled'";
        if ($month !== null)       { $whereGeneral .= " AND MONTH(cp.payment_date) = :month"; $paramsGeneral['month'] = $month; }
        if ($year !== null)        { $whereGeneral .= " AND YEAR(cp.payment_date) = :year";   $paramsGeneral['year']  = $year; }
        if ($fiscalYearId !== null){ $whereGeneral .= " AND c.fiscal_year_id = :fiscal_year_id"; $paramsGeneral['fiscal_year_id'] = $fiscalYearId; }
        if ($contractType !== null && in_array($contractType, ['simultaneous', 'advance'])) {
            $whereGeneral .= " AND c.type = :contract_type"; $paramsGeneral['contract_type'] = $contractType;
        }

        $totalGeneral = $this->queryOne("
            SELECT COUNT(DISTINCT cp.id) as total_recibos, COALESCE(SUM(cp.amount), 0) as total_recaudado
            FROM contract_payments cp
            INNER JOIN contracts c ON cp.contract_id = c.id
            $whereGeneral
        ", $paramsGeneral);

        return [
            'totals_by_zone' => $byZone,
            'total_general'  => [
                'total_recibos'    => (int)($totalGeneral['total_recibos'] ?? 0),
                'total_recaudado'  => (float)($totalGeneral['total_recaudado'] ?? 0)
            ]
        ];
    }

    // =====================================================================
    // RESUMEN DE INGRESOS POR RUBRO
    // =====================================================================
    public function getIncomeSummaryByCategory(?int $month = null, ?int $year = null, ?int $fiscalYearId = null, ?string $contractType = null): array {
        $params = [];
        $paymentFilter = "cp.status = 'paid'";

        $installmentConditions = [];
        if ($month !== null) { $installmentConditions[] = "MONTH(cpi.date) = :month"; $params['month'] = $month; }
        if ($year !== null)  { $installmentConditions[] = "YEAR(cpi.date) = :year";   $params['year']  = $year; }
        $installmentFilter = !empty($installmentConditions) ? ' AND ' . implode(' AND ', $installmentConditions) : '';

        $contractConditions = ["c.status != 'canceled'"];
        if ($fiscalYearId !== null) { $contractConditions[] = "c.fiscal_year_id = :fiscal_year_id"; $params['fiscal_year_id'] = $fiscalYearId; }
        if ($contractType !== null && in_array($contractType, ['simultaneous', 'advance'])) {
            $contractConditions[] = "c.type = :contract_type"; $params['contract_type'] = $contractType;
        }
        $contractFilter = implode(' AND ', $contractConditions);

        $query = "
            SELECT 
                cat.id as rubro_id,
                cat.name as rubro,
                COALESCE((
                    SELECT COUNT(DISTINCT cp.id)
                    FROM contract_payments cp
                    INNER JOIN contracts c ON cp.contract_id = c.id
                    INNER JOIN contract_business_categories cbc ON c.id = cbc.contract_id
                    INNER JOIN contract_payment_installments cpi ON cp.id = cpi.contract_payment_id
                    WHERE ({$paymentFilter}) AND ({$contractFilter}) AND cbc.internal_category_id = cat.id {$installmentFilter}
                ), 0) as cantidad_recibos,
                COALESCE((
                    SELECT SUM(cpi.amount)
                    FROM contract_payments cp
                    INNER JOIN contracts c ON cp.contract_id = c.id
                    INNER JOIN contract_business_categories cbc ON c.id = cbc.contract_id
                    INNER JOIN contract_payment_installments cpi ON cp.id = cpi.contract_payment_id
                    WHERE ({$paymentFilter}) AND ({$contractFilter}) AND cbc.internal_category_id = cat.id {$installmentFilter}
                ), 0) as monto_recaudado
            FROM internal_business_categories cat
            ORDER BY cat.name ASC
        ";
        return $this->query($query, $params);
    }

    public function getIncomeSummaryByCategoryTotals(?int $month = null, ?int $year = null, ?int $fiscalYearId = null, ?string $contractType = null): array {
        $params = [];
        $whereConditions = ["cp.status = 'paid'", "c.status != 'canceled'"];
        $whereInstallment = [];

        if ($month !== null) { $whereInstallment[] = "MONTH(cpi.date) = :month"; $params['month'] = $month; }
        if ($year !== null)  { $whereInstallment[] = "YEAR(cpi.date) = :year";   $params['year']  = $year; }
        if ($fiscalYearId !== null) { $whereConditions[] = "c.fiscal_year_id = :fiscal_year_id"; $params['fiscal_year_id'] = $fiscalYearId; }
        if ($contractType !== null && in_array($contractType, ['simultaneous', 'advance'])) {
            $whereConditions[] = "c.type = :contract_type"; $params['contract_type'] = $contractType;
        }

        $installmentFilter = !empty($whereInstallment) ? ' AND ' . implode(' AND ', $whereInstallment) : '';

        $query = "
            SELECT COUNT(DISTINCT cp.id) as total_recibos, COALESCE(SUM(cpi.amount), 0) as total_recaudado
            FROM contract_payments cp
            INNER JOIN contracts c ON cp.contract_id = c.id
            INNER JOIN contract_payment_installments cpi ON cp.id = cpi.contract_payment_id
            WHERE " . implode(' AND ', $whereConditions) . " $installmentFilter
        ";
        $result = $this->queryOne($query, $params);
        return [
            'total_recibos'   => (int)($result['total_recibos'] ?? 0),
            'total_recaudado' => (float)($result['total_recaudado'] ?? 0)
        ];
    }
}
