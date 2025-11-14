<div id="paymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>💰 Пополнение через TON</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div id="paymentInfo">
                <div class="payment-instructions">
                    <p>Для пополнения отправьте <span id="tonAmount" class="ton-amount">0</span> TON на адрес:</p>
                    <div class="wallet-address-container">
                        <code id="walletAddress" class="wallet-address">...</code>
                        <button id="copyAddressBtn" class="btn-copy">📋</button>
                    </div>
                    <div class="qr-container">
                        <div id="qrCode" class="qr-code"></div>
                    </div>
                    <p class="payment-alternative">Или используйте прямую ссылку:</p>
                    <a href="#" id="tonLink" class="ton-link" target="_blank">🔗 Открыть в кошельке</a>
                    <div class="payment-timer">
                        <p>⏰ Счет действителен: <span id="paymentTimer">59:59</span></p>
                    </div>
                    <p class="payment-note">После оплаты баланс обновится автоматически в течение 1-2 минут</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="gameResultModal" class="modal">
    <div class="modal-content game-result">
        <div class="result-header">
            <h3>🎉 Результаты игры</h3>
        </div>
        <div class="result-body">
            <div id="resultContent"></div>
        </div>
        <div class="result-footer">
            <button id="closeResultBtn" class="btn-primary">Закрыть</button>
        </div>
    </div>
</div>