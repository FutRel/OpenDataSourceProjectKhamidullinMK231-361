<?php
require 'db.php';

header('Content-Type: application/json; charset=utf-8');

function kmeansClustering($points, $numClusters)
{
    $centroids = [];
    $clusters = [];
    $maxIterations = 100;

    // Шаг 1: Инициализация центроидов случайным образом
    $randomKeys = array_rand($points, $numClusters);
    foreach ($randomKeys as $key) {
        $centroids[] = $points[$key];
    }

    for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
        // Шаг 2: Распределение точек по ближайшим центроидам
        $clusters = array_fill(0, $numClusters, []);
        foreach ($points as $point) {
            $closestCentroid = null;
            $minDistance = PHP_FLOAT_MAX;
            foreach ($centroids as $centroidIndex => $centroid) {
                $distance = sqrt(pow($point['lat_set'] - $centroid['lat_set'], 2) + pow($point['long_set'] - $centroid['long_set'], 2));
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $closestCentroid = $centroidIndex;
                }
            }
            $clusters[$closestCentroid][] = $point;
        }

        // Шаг 3: Пересчет центроидов
        $newCentroids = [];
        foreach ($clusters as $clusterPoints) {
            if (count($clusterPoints) === 0) {
                continue;
            }
            $sumLat = 0;
            $sumLon = 0;
            foreach ($clusterPoints as $clusterPoint) {
                $sumLat += $clusterPoint['lat_set'];
                $sumLon += $clusterPoint['long_set'];
            }
            $newCentroids[] = [
                'lat_set' => $sumLat / count($clusterPoints),
                'long_set' => $sumLon / count($clusterPoints)
            ];
        }

        // Проверка на сходимость
        if ($centroids === $newCentroids) {
            break;
        }
        $centroids = $newCentroids;
    }

    // Присваиваем cluster_id для всех точек
    $result = [];
    foreach ($clusters as $clusterId => $clusterPoints) {
        foreach ($clusterPoints as $point) {
            $result[] = [
                'id_set' => $point['id_set'],
                'cluster_id' => $clusterId
            ];
        }
    }

    return $result;
}
function convexHull($points)
{
    // Сортируем точки по координате X (и Y для одинаковых X)
    usort($points, function ($a, $b) {
        return $a['lat_set'] <=> $b['lat_set'] ?: $a['long_set'] <=> $b['long_set'];
    });

    // Логика для построения выпуклой оболочки (алгоритм Грэхема или Джарвиса)
    $lower = [];
    foreach ($points as $point) {
        while (count($lower) >= 2 && cross($lower[count($lower) - 2], $lower[count($lower) - 1], $point) <= 0) {
            array_pop($lower);
        }
        $lower[] = $point;
    }

    $upper = [];
    foreach (array_reverse($points) as $point) {
        while (count($upper) >= 2 && cross($upper[count($upper) - 2], $upper[count($upper) - 1], $point) <= 0) {
            array_pop($upper);
        }
        $upper[] = $point;
    }

    // Убираем последнюю точку, так как она повторяется
    array_pop($upper);

    return array_merge($lower, $upper);
}
function cross($o, $a, $b)
{
    return ($a['lat_set'] - $o['lat_set']) * ($b['long_set'] - $o['long_set']) - ($a['long_set'] - $o['long_set']) * ($b['lat_set'] - $o['lat_set']);
}

try {
    $numClusters = isset($_GET['clusters']) ? (int) $_GET['clusters'] : 12;
    $datasets = isset($_GET['datasets']) ? explode(',', $_GET['datasets']) : [];
    error_log('Получены данные: clusters = ' . $numClusters . ', datasets = ' . implode(', ', $datasets));

    // Создание временной таблицы combined_data
    $pdo->exec("DROP TEMPORARY TABLE IF EXISTS combined_data");
    $pdo->exec("CREATE TEMPORARY TABLE combined_data (
        id_set INT AUTO_INCREMENT PRIMARY KEY,
        name_set VARCHAR(255),
        lat_set DOUBLE NOT NULL,
        long_set DOUBLE NOT NULL,
        cluster_id INT DEFAULT NULL,
        dataset VARCHAR(50) NOT NULL
    )");

    // Заполнение временной таблицы данными из выбранных наборов
    foreach ($datasets as $tableName) {
        $query = "INSERT INTO combined_data (name_set, lat_set, long_set, dataset)
                  SELECT name_set, lat_set, long_set, :dataset
                  FROM $tableName";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':dataset' => $tableName]);
    }

    // Устанавливаем значение cluster_id по умолчанию для данных без кластеризации
    $pdo->exec("UPDATE combined_data SET cluster_id = 0 WHERE cluster_id IS NULL");

    // Запрос данных из временной таблицы
    $query = "SELECT id_set, name_set, lat_set, long_set, cluster_id, dataset FROM combined_data ORDER BY cluster_id";
    $stmt = $pdo->query($query);
    $points = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Выполнение кластеризации
    $clusteredPoints = kmeansClustering($points, $numClusters);

    // Обновление cluster_id в таблице combined_data
    $stmt = $pdo->prepare("UPDATE combined_data SET cluster_id = :cluster_id WHERE id_set = :id_set");
    foreach ($clusteredPoints as $point) {
        $stmt->execute([':cluster_id' => $point['cluster_id'], ':id_set' => $point['id_set']]);
    }

    // Группировка точек по кластерам
    $query = "SELECT cluster_id, lat_set, long_set, name_set, dataset FROM combined_data";
    $points = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

    $clusters = [];
    foreach ($points as $point) {
        $clusterId = $point['cluster_id'];
        if (!isset($clusters[$clusterId])) {
            $clusters[$clusterId] = ['hull' => [], 'points' => []];
        }
        $clusters[$clusterId]['points'][] = [
            'lat_set' => $point['lat_set'],
            'long_set' => $point['long_set'],
            'name_set' => $point['name_set'],
            'dataset' => $point['dataset']
        ];
    }

    // Вычисление выпуклой оболочки для каждого кластера
    foreach ($clusters as $clusterId => &$cluster) {
        $cluster['hull'] = convexHull($cluster['points']);
    }

    $colors = ['#EFF8FB', '#A4D4E6', '#92C8B6', '#F4E3E5', '#D1A7AC', '#E0F7EF', '#B0B7C6', '#F5F0E6', '#394F59', '#0E3E48'];
    $result = [];
    foreach ($clusters as $clusterId => $cluster) {
        $result[] = [
            'cluster_id' => $clusterId,
            'size' => count($cluster['points']),
            'hull' => $cluster['hull'],
            'color' => $colors[$clusterId % count($colors)],
            'points' => $cluster['points']
        ];
    }

    echo json_encode(['success' => true, 'clusters' => $result]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>