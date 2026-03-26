<?php
require_once __DIR__ . '/env.php';

$merchantId = getenv('FREEKASSA_MERCHANT_ID') ?: '';
$merchantSecret2 = getenv('FREEKASSA_SECRET_WORD_2') ?: '';

if ($merchantId === '' || $merchantSecret2 === '') {
    http_response_code(500);
    die('merchant config error');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    die('method not allowed');
}

function getIP(): string {
    if (isset($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    }

    return $_SERVER['REMOTE_ADDR'] ?? '';
}

$allowedIps = ['168.119.157.136', '168.119.60.227', '178.154.197.79', '51.250.54.238'];
if (!in_array(getIP(), $allowedIps, true)) {
    die('hacking attempt!');
}

$orderId = $_POST['MERCHANT_ORDER_ID'] ?? '';
$amount = $_POST['AMOUNT'] ?? '';
$intid = $_POST['intid'] ?? '';
$sign = $_POST['SIGN'] ?? '';
$tariff = $_POST['us_tariff_name'] ?? '';
$requestMerchantId = $_POST['MERCHANT_ID'] ?? '';

if ($orderId === '' || $amount === '' || $sign === '') {
    die('bad request');
}

if ($requestMerchantId !== '' && $requestMerchantId !== $merchantId) {
    die('wrong merchant');
}

$signCheck = md5($merchantId . ':' . $amount . ':' . $merchantSecret2 . ':' . $orderId);
if (!hash_equals($signCheck, $sign)) {
    die('wrong sign');
}

$ordersFile = __DIR__ . '/orders.json';
$orders = [];
if (file_exists($ordersFile)) {
    $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
}

$orders[$orderId] = array_merge($orders[$orderId] ?? [], [
    'order_id' => $orderId,
    'amount' => $amount,
    'tariff_name' => $tariff,
    'freekassa_id' => $intid,
    'provider' => 'freekassa',
    'status' => 'paid',
    'paid_at' => date('Y-m-d H:i:s'),
]);

file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
die('YES');
