<?php
/**
 * Controlador de Cobros
 * 
 * Gestiona el cobro de mensualidades y facturación
 * Implementa RF13-RF19
 * 
 * @package App\Controllers
 * @author Sistema de Gestión Municipal
 * @version 1.0
 */

namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\AwardeeModel;
use App\Models\ContractModel;
use App\Models\ContractPaymentModel;
use App\Models\ContractPaymentInstallmentModel;
use App\Models\PaymentMethodModel;
use App\Models\CashRegisterModel;
use App\Models\DailyCashRegisterModel;

class CobroController extends Controller {
    private AwardeeModel $awardeeModel;
    private ContractModel $contractModel;
    private ContractPaymentModel $paymentModel;
    private ContractPaymentInstallmentModel $installmentModel;
    private PaymentMethodModel $paymentMethodModel;
    
    public function __construct() {
        $this->requireAuth();
        $this->awardeeModel = new AwardeeModel();
        $this->contractModel = new ContractModel();
        $this->paymentModel = new ContractPaymentModel();
        $this->installmentModel = new ContractPaymentInstallmentModel();
        $this->paymentMethodModel = new PaymentMethodModel();
    }
    
    /**
     * Pantalla de búsqueda de adjudicatario por cédula (RF14)
     */
    public function index(): void {
        $data = [
            'title' => 'Gestión de Cobros'
        ];
        
        $this->view('Cobro/Index', $data);
    }
    
    /**
     * Busca todas las facturas de un adjudicatario (RF14 + RF15)
     * Incluye pagos pendientes, pagados y vencidos
     * Implementa patrón PRG (Post-Redirect-Get) para evitar problemas de reenvío de formulario
     */
    public function buscar(): void {
        $idNumber = null;
        
        // Si es POST, obtener la cédula y redirigir a GET (patrón PRG)
        if ($this->isPost()) {
            $idNumber = $this->post('id_number');
            
            if (empty($idNumber)) {
                Session::flash('error', 'Cédula requerida');
                $this->redirect('cobro/index');
                return;
            }
            
            // Redirigir a GET para evitar problemas de reenvío de formulario
            $this->redirect('cobro/buscar?id_number=' . urlencode($idNumber));
            return;
        }
        
        // Si es GET, obtener la cédula del parámetro
        $idNumber = $this->get('id_number');
        
        if (empty($idNumber)) {
            $this->redirect('cobro/index');
            return;
        }
        
        // Buscar al adjudicatario (RF14)
        $awardee = $this->awardeeModel->getByIdNumber($idNumber);
        
        if (!$awardee) {
            Session::flash('error', 'Adjudicatario no encontrado');
            $this->redirect('cobro/index');
            return;
        }
        
        // Obtener los contratos del adjudicatario
        $contracts = $this->contractModel->getByAwardee($awardee['id']);
        
        // Obtener todos los registros de pago con tasa del euro (RF14 + RF15)
        // Incluye pagos pendientes, pagados y vencidos
        $allPayments = $this->paymentModel->getAllPaymentsWithRateByAwardee($awardee['id']);
        
        $data = [
            'title' => 'Gestión de Cobros - ' . AwardeeModel::getFullName($awardee),
            'awardee' => $awardee,
            'awardee_name' => AwardeeModel::getFullName($awardee),
            'contracts' => $contracts,
            'allPayments' => $allPayments,
            'paymentMethods' => $this->paymentMethodModel->getActive(),
            'csrf_token' => \Core\Security::getCsrfToken()
        ];
        
        $this->view('Cobro/Buscar', $data);
    }
    
    /**
     * Obtiene los pagos pendientes de un contrato hasta el mes presente (RF14)
     * Solo muestra pagos que tengan tasa de euro asignada
     * 
     * @param int $contractId ID del contrato
     * @return array Pagos pendientes
     */
    private function getPendingPayments(int $contractId): array {
        return $this->paymentModel->getPendingPaymentsWithRate($contractId);
    }
    
    /**
     * Registra un pago (RF16 + RF17 + RF18)
     * Este método SIEMPRE devuelve JSON para mantener al usuario en la vista actual
     */
    public function registrarPago(): void {
        // Validar que sea POST
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Método no permitido'], 405);
            return;
        }
        
        // Verificar CSRF token manualmente (sin redireccionar)
        if (!$this->verifyCsrfToken()) {
            $this->json(['success' => false, 'message' => 'Token de seguridad inválido. Recargue la página e intente nuevamente.'], 403);
            return;
        }
        
        // Limpiar el payment_id removiendo cualquier carácter que no sea numérico (ej: #85 -> 85)
        $paymentIdRaw = $this->post('payment_id');
        $paymentId = (int) preg_replace('/[^0-9]/', '', $paymentIdRaw);
        $amount = (float) $this->post('amount');
        $paymentMethodId = (int) $this->post('payment_method_id');
        $concept = $this->post('concept', 'Pago de mensualidad');
        
        // DEBUG: Log de datos recibidos
        error_log("=== REGISTRO DE PAGO ===");
        error_log("Payment ID Raw: " . $paymentIdRaw);
        error_log("Payment ID Limpio: " . $paymentId);
        error_log("Amount: " . $amount);
        error_log("Payment Method ID: " . $paymentMethodId);
        
        // Validar campos requeridos
        if (empty($paymentId) || empty($amount) || empty($paymentMethodId)) {
            $this->json(['success' => false, 'message' => 'Todos los campos son requeridos']);
            return;
        }
        
        // Verificar que el pago existe
        $payment = $this->paymentModel->getById($paymentId);
        error_log("Payment: " . json_encode($payment));
        
        if (!$payment) {
            error_log("❌ ERROR: Pago no encontrado con ID: " . $paymentId);
            $this->json(['success' => false, 'message' => 'Pago no encontrado']);
            return;
        }
        
        // Verificar que el pago tiene tasa de euro asignada
        if (empty($payment['euro_rate_id'])) {
            error_log("❌ ERROR: Pago sin tasa de euro asignada");
            $this->json(['success' => false, 'message' => 'El pago no tiene una tasa de euro asignada. Por favor, asigne una tasa primero.']);
            return;
        }
        
        // Obtener la información completa del pago con la tasa de euro
        $paymentWithRate = $this->paymentModel->getPaymentWithRateInfo($paymentId);
        error_log("Payment with rate: " . json_encode($paymentWithRate));
        
        if (!$paymentWithRate) {
            error_log("❌ ERROR: No se pudo obtener información completa del pago");
            $this->json(['success' => false, 'message' => 'Error al obtener información del pago']);
            return;
        }
        
        // Calcular el saldo restante usando amount_bs
        $totalAmount = (float) ($paymentWithRate['amount_bs'] ?? 0);
        $totalPaid = $this->installmentModel->getTotalPaid($paymentId);
        $remainingBalance = max(0, $totalAmount - $totalPaid);
        
        if ($amount > $remainingBalance) {
            $this->json(['success' => false, 'message' => 'El monto excede el saldo restante. Saldo disponible: Bs. ' . number_format($remainingBalance, 2)]);
            return;
        }
        
        // Validar que la caja esté abierta
        $userId = \Core\Session::get('user_id');
        $cashRegisterModel = new CashRegisterModel();
        $dailyCashModel = new DailyCashRegisterModel();
        
        // Buscar la caja asignada al usuario
        $cashRegister = $cashRegisterModel->getByAssignedUser($userId);
        
        if (!$cashRegister) {
            $this->json(['success' => false, 'message' => 'No tiene una caja asignada. Contacte al administrador.']);
            return;
        }
        
        // Verificar que la caja esté abierta
        $openCash = $dailyCashModel->getOpenCashByRegister($cashRegister['id']);
        
        if (!$openCash) {
            $this->json(['success' => false, 'message' => 'Debe abrir su caja antes de registrar cobros. <a href="' . \Config\App::APP_URL . '/dailycash/index">Abrir caja</a>']);
            return;
        }
        
        // Registrar el abono (RF16 + RF17 + RF18) asociado a la caja abierta
        $installmentId = $this->installmentModel->create([
            'contract_payment_id' => $paymentId,
            'payment_method_id' => $paymentMethodId,
            'amount' => $amount,
            'concept' => $concept,
            'date' => date('Y-m-d'),
            'daily_cash_register_id' => $openCash['id']
        ]);
        
        if ($installmentId) {
            // Recalcular el saldo después del pago
            $newTotalPaid = $this->installmentModel->getTotalPaid($paymentId);
            $newRemainingBalance = max(0, $totalAmount - $newTotalPaid);
            
            // Verificar si el pago está completo y actualizar el estado
            // El estado debe ser 'paid' ÚNICAMENTE si el saldo es 0 o menor a 1 centavo
            if ($newRemainingBalance <= 0.01) {
                // Actualizar el estado a 'paid' solo si está completamente pagado
                $this->paymentModel->updateStatus($paymentId, 'paid');
                $message = 'Pago registrado exitosamente. La factura ha sido pagada completamente.';
                $paymentStatus = 'paid';
            } else {
                // Mantener en 'pending' si aún hay saldo pendiente
                $this->paymentModel->updateStatus($paymentId, 'pending');
                $message = 'Abono registrado exitosamente. Saldo pendiente: Bs. ' . number_format($newRemainingBalance, 2);
                $paymentStatus = 'pending';
            }
            
            // Devolver JSON con los datos actualizados (sin redirección)
            $this->json([
                'success' => true,
                'message' => $message,
                'payment_status' => $paymentStatus,
                'remaining_balance' => $newRemainingBalance,
                'total_paid' => $newTotalPaid,
                'installment_id' => $installmentId
            ]);
            return;
        }
        
        // Error al registrar el pago
        $this->json(['success' => false, 'message' => 'Error al registrar el pago. Intente nuevamente.'], 400);
    }
    
    /**
     * Obtiene los abonos de un pago
     */
    public function getInstallments(int $paymentId): void {
        $installments = $this->installmentModel->getByPayment($paymentId);
        
        $this->json([
            'success' => true,
            'installments' => $installments
        ]);
    }
    
    /**
     * Ver detalle de factura con abonos realizados
     */
    public function verFactura(int $paymentId): void {
        // Verificar que el pago existe
        $paymentBasic = $this->paymentModel->getById($paymentId);
        
        if (!$paymentBasic) {
            Session::flash('error', 'Pago no encontrado');
            $this->redirect('cobro/index');
            return;
        }
        
        // Obtener información completa del pago usando el nuevo método
        $payment = $this->paymentModel->getPaymentWithRateInfo($paymentId);
        
        if (!$payment) {
            Session::flash('error', 'Error al obtener información del pago');
            $this->redirect('cobro/index');
            return;
        }
        
        // Obtener el contrato
        $contract = $this->contractModel->getById($payment['contract_id']);
        
        // Obtener adjudicatario
        $awardee = $this->awardeeModel->getById($contract['awardee_id']);
        
        // Obtener abonos del pago
        $installments = $this->installmentModel->getByPayment($paymentId);
        
        // Obtener pagos vencidos del adjudicatario (si los hay)
        $overduePayments = $this->paymentModel->getOverduePaymentsByAwardee($contract['awardee_id']);
        
        $data = [
            'title' => 'Detalle de Factura #' . $payment['id'],
            'payment' => $payment,
            'contract' => $contract,
            'awardee' => $awardee,
            'awardee_name' => \App\Models\AwardeeModel::getFullName($awardee),
            'installments' => $installments,
            'overduePayments' => $overduePayments,
            'csrf_token' => \Core\Security::getCsrfToken()
        ];
        
        $this->view('Cobro/VerFactura', $data);
    }
    
    /**
     * Genera e imprime una factura de pago en formato PDF (RF13)
     */
    public function imprimirFactura(int $paymentId): void {
        // Verificar que el pago existe
        $paymentBasic = $this->paymentModel->getById($paymentId);
        
        if (!$paymentBasic) {
            Session::flash('error', 'Pago no encontrado');
            $this->redirect('cobro/index');
            return;
        }
        
        // Verificar que el pago tiene tasa de euro asignada
        if (empty($paymentBasic['euro_rate_id'])) {
            Session::flash('error', 'El pago no tiene una tasa de euro asignada. No se puede generar la factura.');
            $this->redirect('cobro/index');
            return;
        }
        
        // Obtener información completa del pago
        $payment = $this->paymentModel->getPaymentForInvoice($paymentId);
        
        if (!$payment) {
            Session::flash('error', 'Error al obtener información del pago');
            $this->redirect('cobro/index');
            return;
        }
        
        // Obtener pagos vencidos del adjudicatario
        $overduePayments = $this->paymentModel->getOverduePaymentsByAwardee($payment['awardee_id']);
        
        // Obtener abonos del pago
        $installments = $this->installmentModel->getByPayment($paymentId);
        
        // Preparar datos para la vista
        $data = [
            'payment' => $payment,
            'overduePayments' => $overduePayments,
            'installments' => $installments,
            'awardee_name' => trim($payment['first_name'] . ' ' . $payment['last_name'])
        ];
        
        // Generar el HTML usando la vista específica para PDF
        ob_start();
        extract($data);
        include __DIR__ . '/../Views/Cobro/FacturaPDF.php';
        $html = ob_get_clean();
        
        // Verificar que el HTML se generó correctamente
        if (empty($html)) {
            Session::flash('error', 'No se pudo generar el HTML de la factura');
            $this->redirect('cobro/index');
            return;
        }
        
        try {
            // Verificar si dompdf está disponible
            $dompdfPath = __DIR__ . '/../../vendor/autoload.php';
            
            if (!file_exists($dompdfPath)) {
                // Si Dompdf no está, mostrar el HTML para imprimir
                echo $html;
                return;
            }
            
            // Generar PDF con dompdf
            require_once $dompdfPath;
            
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', false);
            $options->set('defaultFont', 'Helvetica');
            $options->set('defaultPaperSize', 'letter');
            
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            
            // Tamaño de ticket (80mm de ancho)
            $dompdf->setPaper([0, 0, 226.77, 841.89], 'portrait');
            
            $dompdf->render();
            
            // Obtener el PDF generado
            $output = $dompdf->output();
            
            // Nombre del archivo
            $filename = 'Factura_' . str_replace('/', '_', $payment['payment_reference'] ?? $paymentId) . '_' . date('Ymd') . '.pdf';
            
            // Limpiar cualquier salida previa
            if (ob_get_length()) {
                ob_end_clean();
            }
            
            // Enviar headers
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($output));
            
            // Enviar el PDF
            echo $output;
            exit;
            
        } catch (\Exception $e) {
            // Si falla Dompdf, mostrar el HTML directamente
            echo $html;
            echo '<hr><div style="background: #ffcdd2; padding: 15px; margin: 20px; border: 2px solid #d32f2f; border-radius: 5px;">
                    <h3 style="color: #d32f2f;">⚠️ No se pudo generar el PDF</h3>
                    <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                    <p>Puede usar Ctrl+P para imprimir esta página directamente.</p>
                  </div>';
        }
    }
}

