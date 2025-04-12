<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

$category_id = intval($_GET['id'] ?? 0);

if ($category_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid category ID']);
    exit;
}

$query = "SELECT id, name, description FROM categories WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $category_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    
    if ($category) {
        echo json_encode($category);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Category not found']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}

$stmt->close(); 