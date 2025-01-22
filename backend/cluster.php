<?php
require 'db.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('memory_limit', '512M');

function kmeansClustering($points, $numClusters, $maxIterations = 100)
{
    // Шаг 1: Инициализация случайных центроидов
    $centroids = [];
    $clusters = [];

    // Инициализируем случайные центроиды
    $randomKeys = array_rand($points, $numClusters);
    foreach ($randomKeys as $key) {
        $centroids[] = [
            'lat_set' => $points[$key]['lat_set'] + (mt_rand(-10, 10) / 10000), // Добавлен случайный сдвиг
            'long_set' => $points[$key]['long_set'] + (mt_rand(-10, 10) / 10000)
        ];
    }

    // Шаг 2: Инициализация переменных
    $iterations = 0;
    $finished = false;

    // Шаг 3: Основной цикл кластеризации
    while (!$finished && $iterations < $maxIterations) {
        $iterations++;

        // Шаг 3.1: Расчет расстояния и присвоение точкам ближайшего кластера
        foreach ($points as &$point) {
            $minDistance = INF;
            $assignedCluster = -1;
            foreach ($centroids as $clusterId => $centroid) {
                $distance = sqrt(pow($point['lat_set'] - $centroid['lat_set'], 2) + pow($point['long_set'] - $centroid['long_set'], 2));
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $assignedCluster = $clusterId;
                }
            }
            $point['cluster_id'] = $assignedCluster;
        }
        unset($point);

        // Шаг 3.2: Пересчет центроидов для каждого кластера
        $newCentroids = array_fill(0, $numClusters, ['lat_set' => 0, 'long_set' => 0, 'count' => 0]);
        foreach ($points as $point) {
            $clusterId = $point['cluster_id'];
            $newCentroids[$clusterId]['lat_set'] += $point['lat_set'];
            $newCentroids[$clusterId]['long_set'] += $point['long_set'];
            $newCentroids[$clusterId]['count']++;
        }

        $finished = true;
        foreach ($newCentroids as $clusterId => $centroid) {
            if ($centroid['count'] == 0) {
                // Если кластер пустой, выбираем новый случайный центроид
                $randomPoint = $points[array_rand($points)];
                $centroids[$clusterId] = [
                    'lat_set' => $randomPoint['lat_set'],
                    'long_set' => $randomPoint['long_set']
                ];
                $finished = false; // Обязательно продолжить итерации
                continue;
            }

            $newLat = $centroid['lat_set'] / $centroid['count'];
            $newLong = $centroid['long_set'] / $centroid['count'];

            if (abs($newLat - $centroids[$clusterId]['lat_set']) > 0.001 || abs($newLong - $centroids[$clusterId]['long_set']) > 0.001) {
                $finished = false;
            }

            $centroids[$clusterId] = ['lat_set' => $newLat, 'long_set' => $newLong];
        }
    }

    // Логирование: точки с id_set и cluster_id
    $finalPoints = array_map(function ($point) {
        return ['id_set' => $point['id_set'], 'cluster_id' => $point['cluster_id']];
    }, $points);

    $finalPoints = array_map(function ($point) {
        return [
            'id_set' => $point['id_set'],
            'cluster_id' => $point['cluster_id'],
            'dataset' => $point['dataset']
        ];
    }, $points);
    
    // Результат: массив точек с добавленными cluster_id и dataset
    return $finalPoints;
}

// Функция для вычисления выпуклой оболочки (convex hull)
function convexHull($points)
{
    if (count($points) < 3) {
        return $points; // Выпуклая оболочка невозможна для менее чем 3 точек
    }

    // Сортируем точки по координате X (и Y для одинаковых X)
    usort($points, function ($a, $b) {
        return $a['lat_set'] <=> $b['lat_set'] ?: $a['long_set'] <=> $b['long_set'];
    });

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

// Функция для вычисления векторного произведения
function cross($p1, $p2, $p3)
{
    return ($p2['lat_set'] - $p1['lat_set']) * ($p3['long_set'] - $p1['long_set']) -
        ($p2['long_set'] - $p1['long_set']) * ($p3['lat_set'] - $p1['lat_set']);
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

    // Создаем временную таблицу для хранения уникальных записей
    $pdo->exec("
    CREATE TEMPORARY TABLE unique_points AS
    SELECT MIN(id_set) AS id_set
    FROM combined_data
    GROUP BY lat_set, long_set
    ");

    // Удаляем дубли из оригинальной таблицы, оставляя только уникальные записи
    $pdo->exec("
    DELETE FROM combined_data
    WHERE id_set NOT IN (SELECT id_set FROM unique_points)
    ");

    // Удаляем временную таблицу для хранения уникальных записей
    $pdo->exec("DROP TEMPORARY TABLE IF EXISTS unique_points");


    // Устанавливаем значение cluster_id по умолчанию для данных без кластеризации
    $pdo->exec("UPDATE combined_data SET cluster_id = 0 WHERE cluster_id IS NULL");

    // Запрос данных из временной таблицы
    $query = "SELECT id_set, name_set, lat_set, long_set, cluster_id, dataset FROM combined_data ORDER BY cluster_id";
    $stmt = $pdo->query($query);
    $points = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Выполнение кластеризации
    $clusteredPoints = kmeansClustering($points, $numClusters);

    // Обновление cluster_id в таблице combined_data
    $stmt = $pdo->prepare("UPDATE combined_data SET cluster_id = :cluster_id WHERE id_set = :id_set AND dataset = :dataset");
    foreach ($clusteredPoints as $point) {
        $stmt->execute([':cluster_id' => $point['cluster_id'], ':dataset' => $point['dataset'], ':id_set' => $point['id_set']]);
    }

    // Группировка точек по кластерам
    $query = "SELECT cluster_id, lat_set, long_set, name_set, dataset FROM combined_data";
    $points = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

    $clusters = [];
    foreach ($points as $point) {
        $clusterId = $point['cluster_id'];
        if (!array_key_exists($clusterId, $clusters)) {
            $clusters[$clusterId] = [
                'hull' => [],
                'points' => []
            ];
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
    unset($cluster);

    $colors = [
        '#C8E3F0',
        '#74AAC3',
        '#5A9C85',
        '#E1BFC4',
        '#AA7380',
        '#A3D3BF',
        '#8091A0',
        '#D7C4A8',
        '#253841',
        '#082E38',
        '#2A4D53',
        '#8E526A',
        '#1C2D35',
        '#726A5C',
        '#4F3B57',
        '#143D46',
        '#5C374C',
        '#3F4A4F',
        '#6D6A75',
        '#8A5A3C'
    ];

    $result = [];
    foreach ($clusters as $clusterId => $cluster) {
        $result[] = [
            'cluster_id' => $clusterId,
            'size' => count($cluster['points']),
            'hull' => $cluster['hull'],
            'color' => $colors[$clusterId],
            'points' => $cluster['points']
        ];
    }

    echo json_encode(['success' => true, 'clusters' => $result]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>