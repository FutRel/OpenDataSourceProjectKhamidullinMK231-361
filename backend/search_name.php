<?php
require 'db.php';

$datasetId = $_GET['dataset'] ?? null;
$query = $_GET['query'] ?? null;

if (!$datasetId || !$query) {
    echo json_encode(['error' => 'Не указан датасет или запрос']);
    exit;
}

$tableName = "f_dataset" . $datasetId;

$stmt = $pdo->prepare("SELECT DISTINCT name_set FROM $tableName WHERE name_set LIKE ?");
$stmt->execute(['%' . $query . '%']);
$results = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($results);
?>
