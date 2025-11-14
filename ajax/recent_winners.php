<?php
require_once '../config/config.php';
require_once '../controllers/GameController.php';

header('Content-Type: application/json');

$game_controller = new GameController();
$winners = $game_controller->getRecentWinners(10);

// Форматируем время для красивого отображения
foreach ($winners as &$winner) {
    $minutes = $winner['minutes_ago'];
    if ($minutes < 1) {
        $winner['time_ago'] = 'только что';
    } elseif ($minutes < 60) {
        $winner['time_ago'] = $minutes . ' мин назад';
    } elseif ($minutes < 1440) {
        $hours = floor($minutes / 60);
        $winner['time_ago'] = $hours . ' ч назад';
    } else {
        $days = floor($minutes / 1440);
        $winner['time_ago'] = $days . ' дн назад';
    }
}

echo json_encode($winners);
?>