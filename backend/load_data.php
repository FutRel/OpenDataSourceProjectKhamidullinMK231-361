<?php
require 'db.php';

function importJSON1($pdo)
{
    ini_set('memory_limit', '50G');
    $filePath = __DIR__ . '/../data/f_dataset1.json';
    $fileContent = file_get_contents($filePath);

    if ($fileContent === false) {
        die("Ошибка при чтении файла.");
    }

    $data = json_decode($fileContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Ошибка при декодировании JSON файла: " . json_last_error_msg());
    }

    foreach ($data as $location) {
        if (
            isset($location["ObjectName"]) &&
            isset($location["geodata_center"]["coordinates"]) &&
            is_array($location["geodata_center"]["coordinates"]) &&
            count($location["geodata_center"]["coordinates"]) === 2 &&
            is_numeric($location["geodata_center"]["coordinates"][0]) &&
            is_numeric($location["geodata_center"]["coordinates"][1])
        ) {
            $latitude = $location["geodata_center"]["coordinates"][1];
            $longitude = $location["geodata_center"]["coordinates"][0];

            $stmt = $pdo->prepare("INSERT INTO dataset1 (name_set, lat_set, long_set) VALUES (?, ?, ?)");
            $stmt->execute([
                $location['ObjectName'],
                $latitude,
                $longitude
            ]);
        } else {
            error_log("Пропущена запись: " . json_encode($location) . "\n", 3);
        }
    }

    echo "Импорт JSON данных для сета 1 завершён.\n";
}
function importJSON2($pdo)
{
    ini_set('memory_limit', '50G');
    $filePath = __DIR__ . '/../data/f_dataset2.json';
    $fileContent = file_get_contents($filePath);

    if ($fileContent === false) {
        die("Ошибка при чтении файла.");
    }

    $data = json_decode($fileContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Ошибка при декодировании JSON файла: " . json_last_error_msg());
    }

    foreach ($data as $location) {
        if (
            isset($location["stop_name"]) &&
            isset($location["geodata_center"]["coordinates"]) &&
            is_array($location["geodata_center"]["coordinates"]) &&
            count($location["geodata_center"]["coordinates"]) === 2 &&
            is_numeric($location["geodata_center"]["coordinates"][0]) &&
            is_numeric($location["geodata_center"]["coordinates"][1])
        ) {
            $latitude = $location["geodata_center"]["coordinates"][1];
            $longitude = $location["geodata_center"]["coordinates"][0];

            $stmt = $pdo->prepare("INSERT INTO dataset2 (name_set, lat_set, long_set) VALUES (?, ?, ?)");
            $stmt->execute([
                $location['stop_name'],
                $latitude,
                $longitude
            ]);
        } else {
            error_log("Пропущена запись: " . json_encode($location) . "\n", 3);
        }
    }

    echo "Импорт JSON данных для сета 2 завершён.\n";
}
function importJSON3($pdo)
{
    ini_set('memory_limit', '50G');
    $filePath = __DIR__ . '/../data/f_dataset3.json';
    $fileContent = file_get_contents($filePath);

    if ($fileContent === false) {
        die("Ошибка при чтении файла.");
    }

    $data = json_decode($fileContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Ошибка при декодировании JSON файла: " . json_last_error_msg());
    }

    foreach ($data as $location) {
        if (
            isset($location["Name"]) &&
            isset($location["geoData"]["coordinates"]) &&
            is_array($location["geoData"]["coordinates"]) &&
            count($location["geoData"]["coordinates"]) === 2 &&
            is_numeric($location["geoData"]["coordinates"][0]) &&
            is_numeric($location["geoData"]["coordinates"][1])
        ) {
            $latitude = $location["geoData"]["coordinates"][1];
            $longitude = $location["geoData"]["coordinates"][0];

            $stmt = $pdo->prepare("INSERT INTO dataset3 (name_set, lat_set, long_set) VALUES (?, ?, ?)");
            $stmt->execute([
                $location['Name'],
                $latitude,
                $longitude
            ]);
        } else {
            error_log("Пропущена запись: " . json_encode($location) . "\n", 3);
        }
    }

    echo "Импорт JSON данных для сета 3 завершён.\n";
}
function importJSON4($pdo)
{
    ini_set('memory_limit', '50G');
    $filePath = __DIR__ . '/../data/f_dataset4.json';
    $fileContent = file_get_contents($filePath);

    if ($fileContent === false) {
        die("Ошибка при чтении файла.");
    }

    $data = json_decode($fileContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Ошибка при декодировании JSON файла: " . json_last_error_msg());
    }

    foreach ($data as $location) {
        if (
            isset($location["SchoolNumber"]) &&
            isset($location["geodata_center"]["coordinates"]) &&
            is_array($location["geodata_center"]["coordinates"]) &&
            count($location["geodata_center"]["coordinates"]) === 2 &&
            is_numeric($location["geodata_center"]["coordinates"][0]) &&
            is_numeric($location["geodata_center"]["coordinates"][1])
        ) {
            $latitude = $location["geodata_center"]["coordinates"][1];
            $longitude = $location["geodata_center"]["coordinates"][0];

            $stmt = $pdo->prepare("INSERT INTO dataset4 (name_set, lat_set, long_set) VALUES (?, ?, ?)");
            $stmt->execute([
                $location['SchoolNumber'],
                $latitude,
                $longitude
            ]);
        } else {
            error_log("Пропущена запись: " . json_encode($location) . "\n", 3);
        }
    }

    echo "Импорт JSON данных завершён.\n";
}

try {
    importJSON1($pdo);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>