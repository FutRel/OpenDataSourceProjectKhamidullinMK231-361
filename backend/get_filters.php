<?php
require 'db.php';

$datasetId = $_GET['dataset'] ?? null;

if (!$datasetId) {
    echo json_encode(['error' => 'Не указан датасет']);
    exit;
}

$tableName = "f_dataset" . $datasetId;

$filtersMap = [
    '1' => ['id_set', 'name_set', 'adm_area_set', 'web_site_set', 'paid_set'],
    '2' => ['id_set', 'name_set'],
    '3' => ['id_set', 'name_set', 'adm_area_set', 'service_set', 'object_set'],
    '4' => ['id_set', 'name_set', 'adm_area_set', 'year_set']
];

if (!array_key_exists($datasetId, $filtersMap)) {
    echo json_encode(['error' => 'Неверный идентификатор датасета']);
    exit;
}

$filters = $filtersMap[$datasetId];
$options = [];

foreach ($filters as $filter) {
    if (in_array($filter, ['adm_area_set', 'paid_set', 'service_set', 'object_set', 'year_set'])) {
        if (preg_match('/^[a-zA-Z_]+$/', $filter)) {
            $stmt = $pdo->prepare("SELECT DISTINCT $filter FROM $tableName");
            $stmt->execute();
            $options[$filter] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }
}

echo json_encode(['filters' => $filters, 'options' => $options]);
?>