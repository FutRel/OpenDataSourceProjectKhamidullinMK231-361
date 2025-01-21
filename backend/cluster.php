<?php
require 'db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $numClusters = isset($_GET['clusters']) ? (int)$_GET['clusters'] : 12;
    $datasets = isset($_GET['datasets']) ? explode(',', $_GET['datasets']) : [];

    if (empty($datasets)) {
        echo json_encode(['error' => 'Не выбран ни один набор данных']);
        exit;
    }

    // Создание временной таблицы combined_data
    $pdo->exec("DROP TABLE IF EXISTS combined_data");
    $pdo->exec("
        CREATE TEMP TABLE combined_data (
            id SERIAL PRIMARY KEY,
            cluster_id INT DEFAULT NULL,
            lat_set DOUBLE PRECISION NOT NULL,
            long_set DOUBLE PRECISION NOT NULL,
            name_set VARCHAR(255),
            dataset VARCHAR(50) NOT NULL
        )
    ");

    // Заполнение временной таблицы данными из выбранных наборов
    foreach ($datasets as $tableName) {
        $query = "
            INSERT INTO combined_data (lat_set, long_set, name_set, dataset)
            SELECT lat, lon, name, :dataset
            FROM $tableName;
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':dataset' => $tableName]);
    }

    // Запрос данных из временной таблицы
    $query = "
        SELECT 
            cluster_id, 
            lat_set AS lat, 
            long_set AS lon, 
            name_set AS name 
        FROM 
            combined_data 
        ORDER BY 
            cluster_id";
    $stmt = $pdo->query($query);
    $points = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$points) {
        echo json_encode(['error' => 'Данные для выбранных наборов отсутствуют']);
        exit;
    }

    // Группировка точек по кластерам
    $clusters = [];
    foreach ($points as $point) {
        $clusters[$point['cluster_id']][] = ['lat' => $point['lat'], 'lon' => $point['lon']];
    }

    // Подсчет точек в каждом кластере
    $clusterSizes = [];
    foreach ($clusters as $clusterId => $clusterPoints) {
        $clusterSizes[$clusterId] = count($clusterPoints);
    }

    // Выпуклая оболочка (алгоритм Грэхема)
    function convexHull($points) {
        usort($points, function ($a, $b) {
            return $a['lon'] === $b['lon'] ? $a['lat'] <=> $b['lat'] : $a['lon'] <=> $b['lon'];
        });

        $hull = [];
        foreach ($points as $point) {
            while (count($hull) >= 2 && cross($hull[count($hull) - 2], $hull[count($hull) - 1], $point) <= 0) {
                array_pop($hull);
            }
            $hull[] = $point;
        }

        $t = count($hull) + 1;
        for ($i = count($points) - 2; $i >= 0; $i--) {
            $point = $points[$i];
            while (count($hull) >= $t && cross($hull[count($hull) - 2], $hull[count($hull) - 1], $point) <= 0) {
                array_pop($hull);
            }
            $hull[] = $point;
        }

        array_pop($hull);
        return $hull;
    }

    function cross($o, $a, $b) {
        return ($a['lon'] - $o['lon']) * ($b['lat'] - $o['lat']) - ($a['lat'] - $o['lat']) * ($b['lon'] - $o['lon']);
    }

    // Создание выпуклых оболочек для каждого кластера
    $clusterHulls = [];
    foreach ($clusters as $clusterId => $clusterPoints) {
        $clusterHulls[$clusterId] = convexHull($clusterPoints);
    }

    // Цвета для кластеров
    $colors = [
        '#EFF8FB', '#A4D4E6', '#92C8B6', '#F4E3E5', '#D1A7AC',
        '#E0F7EF', '#B0B7C6', '#F5F0E6', '#394F59', '#0E3E48',
        '#2D2323', '#A63E4B', '#FF6F61', '#F7A400', '#FFE5D9'
    ];

    $result = [];
    foreach ($clusters as $clusterId => $clusterPoints) {
        $result[] = [
            'cluster_id' => $clusterId,
            'size' => $clusterSizes[$clusterId],
            'hull' => $clusterHulls[$clusterId],
            'color' => $colors[$clusterId % count($colors)]
        ];
    }

    echo json_encode(['success' => true, 'clusters' => $result]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
