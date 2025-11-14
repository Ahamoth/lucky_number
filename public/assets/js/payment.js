class PaymentSystem {
    constructor() {
        this.currentInvoice = null;
        this.checkInterval = null;
    }
    
    async createInvoice(amount) {
        try {
            const response = await fetch('../ajax/create_invoice.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({amount: amount})
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.currentInvoice = result.invoice;
                this.showPaymentModal(result.invoice);
                this.startPaymentCheck();
                return true;
            } else {
                alert('Ошибка создания счета: ' + result.error);
                return false;
            }
        } catch (error) {
            console.error('Payment error:', error);
            alert('Ошибка соединения');
            return false;
        }
    }
    
    showPaymentModal(invoice) {
        document.getElementById('tonAmount').textContent = invoice.ton_amount;
        document.getElementById('walletAddress').textContent = invoice.wallet_address;
        
        // Генерируем QR код
        const qrText = `ton://transfer/${invoice.wallet_address}?amount=${invoice.ton_amount * 1000000000}&text=${invoice.invoice_id}`;
        document.getElementById('qrCode').innerHTML = '';
        new QRCode(document.getElementById('qrCode'), {
            text: qrText,
            width: 200,
            height: 200
        });
        
        // Прямая ссылка
        document.getElementById('tonLink').href = qrText;
        
        // Показываем модальное окно
        document.getElementById('paymentModal').style.display = 'block';
    }
    
    startPaymentCheck() {
        this.checkInterval = setInterval(async () => {
            const paid = await this.checkPayment();
            if (paid) {
                this.stopPaymentCheck();
                alert('✅ Баланс успешно пополнен!');
                location.reload();
            }
        }, 5000); // Проверка каждые 5 секунд
    }
    
    stopPaymentCheck() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
    }
    
    async checkPayment() {
        if (!this.currentInvoice) return false;
        
        try {
            const response = await fetch('../ajax/check_payment.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({invoice_id: this.currentInvoice.invoice_id})
            });
            
            const result = await response.json();
            return result.paid;
        } catch (error) {
            console.error('Check payment error:', error);
            return false;
        }
    }
}

// Инициализация системы оплаты
const paymentSystem = new PaymentSystem();

// Обработчики кнопок пополнения
document.querySelectorAll('.btn-deposit').forEach(btn => {
    btn.addEventListener('click', () => {
        const amount = btn.getAttribute('data-amount');
        paymentSystem.createInvoice(parseInt(amount));
    });
});

document.getElementById('customDepositBtn').addEventListener('click', () => {
    const amount = parseInt(document.getElementById('customAmount').value);
    if (amount >= 10 && amount <= 10000) {
        paymentSystem.createInvoice(amount);
    } else {
        alert('Сумма должна быть от 10 до 10000 рублей');
    }
});

// Закрытие модального окна
document.querySelector('.close').addEventListener('click', () => {
    document.getElementById('paymentModal').style.display = 'none';
    paymentSystem.stopPaymentCheck();
});