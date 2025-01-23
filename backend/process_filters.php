<?php
require 'db.php';

$datasetId = $_GET['dataset'] ?? null;

if (!$datasetId) {
    echo json_encode(['error' => 'Не указан датасет']);
    exit;
}

$tableName = "f_dataset" . $datasetId;

$filtersMap = [
    '1' => ['name_set', 'adm_area_set', 'web_site_set', 'paid_set'],
    '2' => ['name_set'],
    '3' => ['name_set', 'adm_area_set', 'service_set', 'object_set'],
    '4' => ['name_set', 'adm_area_set', 'year_set']
];

$filters = $filtersMap[$datasetId] ;

if (empty($filters)) {
    echo json_encode(['error' => 'Неверный идентификатор датасета']);
    exit;
}

$whereClauses = [];
$params = [];

foreach ($_GET as $key => $value) {
    if (in_array($key, ['dataset', 'sort_by', 'sort_order'])) {
        continue;
    }

    if (!empty($value) && in_array($key, $filters)) {
        $whereClauses[] = "$key = ?";
        $params[] = $value;
    }

}

$orderBySql = '';
if (!empty($_GET['sort_by']) && !empty($_GET['sort_order'])) {
    $sortField = $_GET['sort_by'];
    if (in_array($sortField, $filters) && in_array($_GET['sort_order'], ['ASC', 'DESC'])) {
        $orderBySql = "ORDER BY $sortField {$_GET['sort_order']}";
    }
}

$whereSql = $whereClauses ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
$query = "SELECT * FROM $tableName $whereSql $orderBySql";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
?>