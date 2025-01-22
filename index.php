<?php
$startFile = __DIR__ . '/frontend/start.php';

if (file_exists($startFile)) {
    require_once $startFile;
} else {
    echo "Ошибка: файл start.php не найден в папке frontend.";
}