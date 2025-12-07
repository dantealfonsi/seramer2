<?php
/**
 * Modelo Report
 * 
 * Gestiona los reportes del sistema
 * 
 * @package App\Models
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Models;

use Core\Model;

class ReportModel extends Model {
    protected string $table = 'contracts'; // Tabla base, no se usa directamente
    
    /**
     * Obtiene los contratos morosos
     * Contratos con al menos una factura vencida
     * 
     * @return array Lista de contratos morosos
     */
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
    
    /**
     * Obtiene el total acumulado por zona en un rango de fechas
     * 
     * @param string $startDate Fecha de inicio (Y-m-d)
     * @param string $endDate Fecha de fin (Y-m-d)
     * @return array Lista de zonas con totales
     */
    public function getZoneAccumulated(string $startDate, string $endDate): array {
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
            GROUP BY z.id, z.name
            ORDER BY total_accumulated DESC
        ";
        
        return $this->query($query, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
}

