// Main Application JavaScript
class LuckyNumberApp {
    constructor() {
        this.currentUser = null;
        this.activeGame = null;
        this.paymentSystem = new PaymentSystem();
        this.init();
    }
    
    init() {
        this.initTelegramWebApp();
        this.initEventListeners();
        this.updateUserBalance();
        this.loadActiveGames();
    }
    
    initTelegramWebApp() {
        if (typeof Telegram !== 'undefined' && Telegram.WebApp) {
            Telegram.WebApp.ready();
            Telegram.WebApp.expand();
            
            // Получаем данные пользователя из Telegram
            const tgUser = Telegram.WebApp.initDataUnsafe.user;
            if (tgUser) {
                this.currentUser = tgUser;
                this.sendUserDataToServer(tgUser);
            }
            
            // Настройка интерфейса WebApp
            Telegram.WebApp.setHeaderColor('#667eea');
            Telegram.WebApp.setBackgroundColor('#667eea');
        }
    }
    
    async sendUserDataToServer(tgUser) {
        try {
            const response = await fetch('../ajax/update_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    tg_user: tgUser
                })
            });
            
            const result = await response.json();
            if (result.success) {
                this.currentUser = result.user;
                this.updateUI();
            }
        } catch (error) {
            console.error('Error updating user:', error);
        }
    }
    
    initEventListeners() {
        // Кнопки пополнения
        document.querySelectorAll('.btn-deposit').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const amount = parseInt(e.target.getAttribute('data-amount'));
                this.paymentSystem.createInvoice(amount);
            });
        });
        
        // Кастомное пополнение
        const customDepositBtn = document.getElementById('customDepositBtn');
        if (customDepositBtn) {
            customDepositBtn.addEventListener('click', () => {
                const amount = parseInt(document.getElementById('customAmount').value);
                if (amount >= 10 && amount <= 10000) {
                    this.paymentSystem.createInvoice(amount);
                } else {
                    this.showAlert('Сумма должна быть от 10 до 10000 рублей', 'error');
                }
            });
        }
        
        // Закрытие модальных окон
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                this.closeModals();
            });
        });
        
        // Клик вне модального окна
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModals();
            }
        });
        
        // Обновление баланса при фокусе
        window.addEventListener('focus', () => {
            this.updateUserBalance();
        });
    }
    
    async updateUserBalance() {
        try {
            const response = await fetch('../ajax/get_balance.php');
            const result = await response.json();
            
            if (result.success) {
                document.querySelectorAll('#headerBalance, #balanceAmount').forEach(el => {
                    el.textContent = result.balance;
                });
            }
        } catch (error) {
            console.error('Error updating balance:', error);
        }
    }
    
    async loadActiveGames() {
        try {
            const response = await fetch('../ajax/get_active_games.php');
            const games = await response.json();
            
            this.updateGamesList(games);
        } catch (error) {
            console.error('Error loading games:', error);
        }
    }
    
    updateGamesList(games) {
        const gamesList = document.getElementById('activeGamesList');
        if (!gamesList) return;
        
        if (games.length === 0) {
            gamesList.innerHTML = `
                <div class="empty-state">
                    <p>Нет активных игр</p>
                    <button class="btn-primary" onclick="createNewGame()">Создать игру</button>
                </div>
            `;
            return;
        }
        
        gamesList.innerHTML = games.map(game => `
            <div class="game-card" data-game-id="${game.id}">
                <div class="game-info">
                    <div class="game-players">👥 ${game.players_count}/${MAX_PLAYERS}</div>
                    <div class="game-prize">💰 ${game.prize_pool} руб.</div>
                    <div class="game-timer" id="timer-${game.id}"></div>
                </div>
                <button class="btn-join" onclick="app.joinGame(${game.id})">
                    Войти за ${TICKET_PRICE} руб.
                </button>
            </div>
        `).join('');
    }
    
    async joinGame(gameId) {
        if (this.activeGame) {
            this.showAlert('Вы уже участвуете в игре!', 'warning');
            return;
        }
        
        try {
            const response = await fetch('../ajax/join_game.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    game_id: gameId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.activeGame = gameId;
                this.showAlert('Вы успешно присоединились к игре!', 'success');
                
                if (result.started) {
                    this.showGameResult(result.winners);
                } else {
                    this.waitForGameStart(gameId);
                }
                
                this.updateUserBalance();
                this.loadActiveGames();
            } else {
                this.showAlert(result.error, 'error');
            }
        } catch (error) {
            console.error('Error joining game:', error);
            this.showAlert('Ошибка подключения к игре', 'error');
        }
    }
    
    waitForGameStart(gameId) {
        const checkInterval = setInterval(async () => {
            try {
                const response = await fetch(`../ajax/game_status.php?game_id=${gameId}`);
                const game = await response.json();
                
                if (game.status === 'active' || game.status === 'finished') {
                    clearInterval(checkInterval);
                    this.showGameResult(game.winner_numbers);
                    this.activeGame = null;
                    this.updateUserBalance();
                    this.loadActiveGames();
                }
            } catch (error) {
                console.error('Error checking game status:', error);
                clearInterval(checkInterval);
            }
        }, 2000);
    }
    
    showGameResult(winners) {
        const modal = document.getElementById('gameResultModal');
        const content = document.getElementById('resultContent');
        
        const isWinner = winners.includes(this.currentUser?.ticket_number);
        
        content.innerHTML = `
            <div class="result-message ${isWinner ? 'winner' : 'participant'}">
                <div class="result-icon">
                    ${isWinner ? '🏆' : '💫'}
                </div>
                <h3>${isWinner ? 'Поздравляем! Вы выиграли!' : 'Спасибо за участие!'}</h3>
                <p>Победительные номера: ${winners.join(', ')}</p>
                ${isWinner ? '<div class="prize-celebration">🎉 Ваш выигрыш зачислен на баланс!</div>' : ''}
            </div>
        `;
        
        modal.style.display = 'block';
    }
    
    closeModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
        
        if (this.paymentSystem) {
            this.paymentSystem.stopPaymentCheck();
        }
    }
    
    showAlert(message, type = 'info') {
        // Создаем уведомление
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <span class="alert-message">${message}</span>
            <button class="alert-close">&times;</button>
        `;
        
        // Стили для уведомлений
        const alertStyles = `
            .alert {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                z-index: 10000;
                max-width: 300px;
                animation: slideInRight 0.3s ease;
            }
            .alert-success { background: #38A169; }
            .alert-error { background: #E53E3E; }
            .alert-warning { background: #D69E2E; }
            .alert-info { background: #3182CE; }
            .alert-close {
                background: none;
                border: none;
                color: white;
                font-size: 1.2rem;
                cursor: pointer;
                margin-left: 10px;
            }
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        
        // Добавляем стили если их еще нет
        if (!document.getElementById('alert-styles')) {
            const styleSheet = document.createElement('style');
            styleSheet.id = 'alert-styles';
            styleSheet.textContent = alertStyles;
            document.head.appendChild(styleSheet);
        }
        
        document.body.appendChild(alert);
        
        // Закрытие по кнопке
        alert.querySelector('.alert-close').addEventListener('click', () => {
            alert.remove();
        });
        
        // Автоматическое закрытие
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    
    updateUI() {
        this.updateUserBalance();
        this.loadActiveGames();
    }
}

// Глобальные константы
const MIN_PLAYERS = <?= MIN_PLAYERS ?>;
const MAX_PLAYERS = <?= MAX_PLAYERS ?>;
const TICKET_PRICE = <?= TICKET_PRICE ?>;

// Инициализация приложения
const app = new LuckyNumberApp();

// Вспомогательные функции
function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        app.showAlert('Адрес скопирован!', 'success');
    }).catch(() => {
        // Fallback для старых браузеров
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        app.showAlert('Адрес скопирован!', 'success');
    });
}

// Глобальные обработчики
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация кнопки копирования
    const copyBtn = document.getElementById('copyAddressBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const address = document.getElementById('walletAddress').textContent;
            copyToClipboard(address);
        });
    }
    
    // Закрытие модального окна результатов
    const closeResultBtn = document.getElementById('closeResultBtn');
    if (closeResultBtn) {
        closeResultBtn.addEventListener('click', function() {
            document.getElementById('gameResultModal').style.display = 'none';
        });
    }
});