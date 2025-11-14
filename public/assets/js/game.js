// Game-specific functionality
class GameEngine {
    constructor() {
        this.wheel = null;
        this.isSpinning = false;
        this.winnerNumbers = [];
    }
    
    initWheel() {
        this.wheel = document.getElementById('wheel');
        if (!this.wheel) return;
        
        // Создаем секции для рулетки
        this.createWheelSections();
    }
    
    createWheelSections() {
        const sections = 10;
        const colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', 
            '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F', 
            '#BB8FCE', '#85C1E9'
        ];
        
        let gradientStops = '';
        for (let i = 0; i < sections; i++) {
            const start = (i / sections) * 100;
            const end = ((i + 1) / sections) * 100;
            gradientStops += `${colors[i]} ${start}% ${end}%,`;
        }
        
        this.wheel.style.background = `conic-gradient(${gradientStops.slice(0, -1)})`;
        
        // Добавляем номера
        this.addWheelNumbers();
    }
    
    addWheelNumbers() {
        const numbersContainer = document.createElement('div');
        numbersContainer.className = 'wheel-numbers';
        
        for (let i = 1; i <= 10; i++) {
            const number = document.createElement('div');
            number.className = 'wheel-number';
            number.textContent = i;
            number.style.transform = `rotate(${(i - 1) * 36}deg)`;
            numbersContainer.appendChild(number);
        }
        
        this.wheel.appendChild(numbersContainer);
    }
    
    spinWheel(winnerNumbers) {
        if (this.isSpinning) return;
        
        this.isSpinning = true;
        this.winnerNumbers = winnerNumbers;
        
        const mainWinner = winnerNumbers[0]; // Главный победитель для остановки
        const spins = 5 + Math.random() * 3; // 5-8 вращений
        const degrees = 360 * spins + (mainWinner * 36 - 18); // Останавливаемся на победителе
        
        this.wheel.style.transition = 'transform 5s cubic-bezier(0.2, 0.8, 0.3, 1)';
        this.wheel.style.transform = `rotate(${degrees}deg)`;
        
        // Анимация замедления
        setTimeout(() => {
            this.wheel.style.transition = 'transform 0.5s ease-out';
        }, 4500);
        
        // Завершение вращения
        setTimeout(() => {
            this.isSpinning = false;
            this.highlightWinners(winnerNumbers);
        }, 5000);
    }
    
    highlightWinners(winnerNumbers) {
        const numbers = document.querySelectorAll('.wheel-number');
        numbers.forEach((num, index) => {
            if (winnerNumbers.includes(index + 1)) {
                num.classList.add('winner');
                
                // Анимация пульсации для победителей
                setTimeout(() => {
                    num.style.animation = 'pulse 1s infinite';
                }, 100 * (winnerNumbers.indexOf(index + 1) + 1));
            }
        });
    }
    
    resetWheel() {
        this.wheel.style.transition = 'none';
        this.wheel.style.transform = 'rotate(0deg)';
        
        const numbers = document.querySelectorAll('.wheel-number');
        numbers.forEach(num => {
            num.classList.remove('winner');
            num.style.animation = 'none';
        });
        
        this.isSpinning = false;
    }
}

// Стили для анимаций
const gameStyles = `
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.wheel-numbers {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
}

.wheel-number {
    position: absolute;
    top: 10px;
    left: 50%;
    transform-origin: bottom center;
    font-weight: bold;
    font-size: 1.2rem;
    color: white;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    z-index: 2;
}

.wheel-number.winner {
    color: #FFD700;
    font-weight: bold;
}

.pointer {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 30px;
    height: 40px;
    background: #E53E3E;
    clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
`;

// Добавляем стили в документ
if (!document.getElementById('game-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'game-styles';
    styleSheet.textContent = gameStyles;
    document.head.appendChild(styleSheet);
}

// Инициализация игрового движка
const gameEngine = new GameEngine();
document.addEventListener('DOMContentLoaded', () => {
    gameEngine.initWheel();
});