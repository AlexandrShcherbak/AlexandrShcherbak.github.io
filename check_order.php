<?php
header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

$orderId = trim($data['order_id'] ?? '');
if ($orderId === '') {
    echo json_encode(['status' => 'error', 'message' => 'order_id is required']);
    exit;
}

$ordersFile = __DIR__ . '/orders.json';
if (!file_exists($ordersFile)) {
    echo json_encode(['status' => 'pending']);
    exit;
}

$orders = json_decode(file_get_contents($ordersFile), true) ?: [];

if (isset($orders[$orderId]) && ($orders[$orderId]['status'] ?? '') === 'paid') {
    echo json_encode([
        'status' => 'paid',
        'provider' => $orders[$orderId]['provider'] ?? null,
        'paid_at' => $orders[$orderId]['paid_at'] ?? null,
    ]);
    exit;
}

echo json_encode(['status' => 'pending']);
