<?php
class PaymentController {
    private $payment_model;
    
    public function __construct() {
        $this->payment_model = new Payment();
    }
    
    public function processTONPayment($transaction_data) {
        // Валидация транзакции TON
        if (!$this->validateTONTransaction($transaction_data)) {
            return false;
        }
        
        // Поиск инвойса
        $invoice = $this->payment_model->findInvoiceByTONData($transaction_data);
        if (!$invoice) {
            return false;
        }
        
        // Подтверждение платежа
        return $this->payment_model->confirmPayment(
            $invoice['invoice_id'],
            $transaction_data['hash']
        );
    }
    
    private function validateTONTransaction($transaction) {
        $required_fields = ['hash', 'from', 'to', 'value'];
        
        foreach ($required_fields as $field) {
            if (!isset($transaction[$field]) || empty($transaction[$field])) {
                return false;
            }
        }
        
        // Проверяем, что платеж на наш кошелек
        if ($transaction['to'] !== TON_WALLET) {
            return false;
        }
        
        return true;
    }
    
    public function getPaymentHistory($user_id) {
        return $this->payment_model->getUserPayments($user_id);
    }
}
?>