<?php

declare(strict_types=1);

require_once __DIR__ . '/backend.php';

requireMethod('POST');
$payload = readJsonBody();

$orderId = trim((string) ($payload['order_id'] ?? ''));
if (!isValidOrderId($orderId)) {
    sendJson(['status' => 'error', 'message' => 'order_id is required'], 400);
}

$orders = loadOrders();
$order = $orders[$orderId] ?? null;

if (is_array($order) && ($order['status'] ?? '') === 'paid') {
    sendJson([
        'status' => 'paid',
        'provider' => $order['provider'] ?? null,
        'paid_at' => $order['paid_at'] ?? null,
    ]);
}

sendJson(['status' => 'pending']);
