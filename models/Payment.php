<?php
class Payment {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createInvoice($user_id, $amount, $currency = 'RUB') {
        try {
            $invoice_id = uniqid('inv_');
            $sql = "INSERT INTO payments (invoice_id, user_id, amount, currency, status, created_at) 
                    VALUES (?, ?, ?, ?, 'pending', NOW())";
            $this->db->query($sql, [$invoice_id, $user_id, $amount, $currency]);
            
            return $invoice_id;
        } catch (Exception $e) {
            error_log("Create invoice error: " . $e->getMessage());
            return null;
        }
    }

    public function confirmPayment($invoice_id, $transaction_hash) {
        try {
            $sql = "UPDATE payments SET status = 'completed', transaction_hash = ?, updated_at = NOW() 
                    WHERE invoice_id = ? AND status = 'pending'";
            $this->db->query($sql, [$transaction_hash, $invoice_id]);

            // Пополняем баланс пользователя
            $payment = $this->getPaymentByInvoice($invoice_id);
            if ($payment && $payment['status'] == 'completed') {
                $user_model = new User();
                $user_model->updateBalance($payment['user_id'], $payment['amount']);
            }

            return true;
        } catch (Exception $e) {
            error_log("Confirm payment error: " . $e->getMessage());
            return false;
        }
    }

    public function getPaymentByInvoice($invoice_id) {
        try {
            $sql = "SELECT * FROM payments WHERE invoice_id = ?";
            return $this->db->fetch($sql, [$invoice_id]);
        } catch (Exception $e) {
            error_log("Get payment error: " . $e->getMessage());
            return null;
        }
    }

    public function findInvoiceByAmount($amount, $wallet_address) {
        try {
            $sql = "SELECT invoice_id FROM payments WHERE amount = ? AND status = 'pending' LIMIT 1";
            $result = $this->db->fetch($sql, [$amount]);
            return $result ? $result['invoice_id'] : null;
        } catch (Exception $e) {
            error_log("Find invoice error: " . $e->getMessage());
            return null;
        }
    }
}
?>