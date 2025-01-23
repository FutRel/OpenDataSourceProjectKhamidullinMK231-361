<?php
require 'db.php';

ini_set('max_execution_time', 180);

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
            isset($location["global_id"]) &&
            isset($location["ObjectName"]) &&
            isset($location["AdmArea"]) &&
            isset($location["WebSite"]) &&
            isset($location["Paid"]) &&
            isset($location["geodata_center"]["coordinates"])
        ) {
            $latitude = $location["geodata_center"]["coordinates"][1];
            $longitude = $location["geodata_center"]["coordinates"][0];

            $stmt = $pdo->prepare("INSERT INTO f_dataset1 (id_set, name_set, adm_area_set, web_site_set, paid_set, lat_set, long_set) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $location['global_id'],
                $location['ObjectName'],
                $location['AdmArea'],
                $location['WebSite'],
                $location['Paid'],
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
            isset($location["global_id"]) &&
            isset($location["stop_name"]) &&
            isset($location["geodata_center"]["coordinates"])
        ) {
            $latitude = $location["geodata_center"]["coordinates"][1];
            $longitude = $location["geodata_center"]["coordinates"][0];

            $stmt = $pdo->prepare("INSERT INTO f_dataset2 (id_set, name_set, lat_set, long_set) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $location['global_id'],
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
            isset($location["global_id"]) &&
            isset($location["Name"]) &&
            isset($location["AdmArea"]) &&
            isset($location["TypeService"]) &&
            isset($location["TypeObject"]) &&
            isset($location["geoData"]["coordinates"])
        ) {
            $latitude = $location["geoData"]["coordinates"][1];
            $longitude = $location["geoData"]["coordinates"][0];

            $stmt = $pdo->prepare("INSERT INTO f_dataset3 (id_set, name_set, adm_area_set, service_set, object_set, lat_set, long_set) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $location['global_id'],
                $location['Name'],
                $location['AdmArea'],
                $location['TypeService'],
                $location['TypeObject'],
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
    $logFile = __DIR__ . '/../logs/import_errors.log'; // Укажите путь к файлу лога

    // Проверьте, существует ли директория для логов
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }

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
            isset($location["global_id"]) &&
            isset($location["SchoolNumber"]) &&
            isset($location["AdmArea"]) &&
            isset($location["Year"]) &&
            isset($location["RoadAccidents"]) &&
            isset($location["geodata_center"]["coordinates"])
        ) {
            $latitude = $location["geodata_center"]["coordinates"][1];
            $longitude = $location["geodata_center"]["coordinates"][0];

            $stmt = $pdo->prepare("INSERT INTO f_dataset4 (id_set, name_set, adm_area_set, year_set, road_set, lat_set, long_set) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $location['global_id'],
                $location['SchoolNumber'],
                $location['AdmArea'],
                $location['Year'],
                $location['RoadAccidents'],
                $latitude,
                $longitude
            ]);
        } else {
            error_log("Пропущена запись: " . json_encode($location) . "\n", 3, $logFile);
        }
    }

    echo "Импорт JSON данных для сета 4 завершён.\n";
}

try {
    importJSON1($pdo);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>