<?php
class StarsPayment {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createInvoice($user_id, $stars_amount) {
        try {
            $invoice_id = uniqid('stars_');
            $rub_amount = ($stars_amount * 10) / 7; // Конвертация stars в рубли
            
            $sql = "INSERT INTO payments (invoice_id, user_id, amount, currency, payment_type, status, created_at) 
                    VALUES (?, ?, ?, 'RUB', 'stars', 'pending', NOW())";
            $this->db->query($sql, [$invoice_id, $user_id, $rub_amount]);
            
            return [
                'success' => true,
                'invoice_id' => $invoice_id,
                'stars_amount' => $stars_amount,
                'rub_amount' => $rub_amount
            ];
        } catch (Exception $e) {
            error_log("Stars create invoice error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка создания счета'];
        }
    }
    
    public function confirmPayment($invoice_id, $transaction_id) {
        try {
            // Обновляем статус платежа
            $sql = "UPDATE payments SET status = 'completed', transaction_id = ?, updated_at = NOW() 
                    WHERE invoice_id = ? AND status = 'pending'";
            $this->db->query($sql, [$transaction_id, $invoice_id]);
            
            // Пополняем баланс пользователя
            $payment = $this->getPaymentByInvoice($invoice_id);
            if ($payment && $payment['status'] == 'completed') {
                $user_model = new User();
                $user_model->updateBalance($payment['user_id'], $payment['amount']);
                
                return [
                    'success' => true,
                    'user_id' => $payment['user_id'],
                    'amount' => $payment['amount']
                ];
            }
            
            return ['success' => false, 'error' => 'Платеж не найден'];
        } catch (Exception $e) {
            error_log("Stars confirm payment error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка подтверждения'];
        }
    }
    
    public function getPaymentByInvoice($invoice_id) {
        try {
            $sql = "SELECT * FROM payments WHERE invoice_id = ?";
            return $this->db->fetch($sql, [$invoice_id]);
        } catch (Exception $e) {
            error_log("Get stars payment error: " . $e->getMessage());
            return null;
        }
    }
}
?>