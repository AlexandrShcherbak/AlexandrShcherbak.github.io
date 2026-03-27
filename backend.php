<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

function sendJson(array $data, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requireMethod(string $method): void
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== strtoupper($method)) {
        sendJson(['success' => false, 'message' => 'Method Not Allowed'], 405);
    }
}

function readJsonBody(): array
{
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw ?: '', true);

    if (!is_array($decoded)) {
        sendJson(['success' => false, 'message' => 'Ожидался JSON payload'], 400);
    }

    return $decoded;
}

function isValidOrderId(string $orderId): bool
{
    return (bool) preg_match('/^[A-Za-z0-9_-]{8,64}$/', $orderId);
}

function paymentTariffs(): array
{
    return [
        1 => ['name' => '1 месяц', 'price' => 150.00],
        2 => ['name' => '6 месяцев', 'price' => 600.00],
        3 => ['name' => '12 месяцев', 'price' => 1200.00],
    ];
}

function buildUrl(string $baseUrl, array $params): string
{
    $separator = str_contains($baseUrl, '?') ? '&' : '?';
    return rtrim($baseUrl, '&?') . $separator . http_build_query($params);
}

function ordersFilePath(): string
{
    return __DIR__ . '/orders.json';
}

function loadOrders(): array
{
    $path = ordersFilePath();
    if (!file_exists($path)) {
        return [];
    }

    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function saveOrder(array $order): void
{
    $path = ordersFilePath();
    $fp = fopen($path, 'c+');
    if ($fp === false) {
        sendJson(['success' => false, 'message' => 'Ошибка записи заказа'], 500);
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        sendJson(['success' => false, 'message' => 'Ошибка блокировки файла заказа'], 500);
    }

    $contents = stream_get_contents($fp);
    $orders = json_decode($contents ?: '{}', true);
    if (!is_array($orders)) {
        $orders = [];
    }

    $orders[$order['order_id']] = $order;

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}
