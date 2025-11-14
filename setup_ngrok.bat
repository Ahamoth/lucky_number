@echo off
echo ========================================
echo    Настройка Ngrok для Telegram бота
echo ========================================
echo.
echo Убедитесь что:
echo 1. XAMPP запущен (Apache + MySQL)
echo 2. Вы зарегистрированы на ngrok.com
echo 3. У вас есть authtoken
echo.
pause

set /p authtoken="35Te5lwdXIiP81SQ5qfq9Oc2dwz_2oDV6F1fMoL5U1yX4WRbG"
ngrok authtoken %authtoken%

echo.
echo Ngrok настроен! Запускаем туннель...
echo Не закрывайте это окно!
echo.
pause

ngrok http 80