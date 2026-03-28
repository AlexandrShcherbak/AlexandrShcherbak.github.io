<?php

declare(strict_types=1);

require_once __DIR__ . '/backend.php';

$merchantId = getenv('FREEKASSA_MERCHANT_ID') ?: '';
$secretWord2 = getenv('FREEKASSA_SECRET_WORD_2') ?: '';

if ($merchantId === '' || $secretWord2 === '') {
    http_response_code(500);
    die('merchant config error');
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    die('method not allowed');
}

function requesterIp(): string
{
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return (string) $_SERVER['HTTP_X_REAL_IP'];
    }

    return (string) ($_SERVER['REMOTE_ADDR'] ?? '');
}

$allowedIps = ['168.119.157.136', '168.119.60.227', '178.154.197.79', '51.250.54.238'];
if (!in_array(requesterIp(), $allowedIps, true)) {
    die('hacking attempt!');
}

$orderId = trim((string) ($_POST['MERCHANT_ORDER_ID'] ?? ''));
$amount = trim((string) ($_POST['AMOUNT'] ?? ''));
$intid = trim((string) ($_POST['intid'] ?? ''));
$sign = strtoupper(trim((string) ($_POST['SIGN'] ?? '')));
$tariffName = trim((string) ($_POST['us_tariff_name'] ?? ''));
$requestMerchantId = trim((string) ($_POST['MERCHANT_ID'] ?? ''));

if ($orderId === '' || $amount === '' || $sign === '') {
    die('bad request');
}
if (!isValidOrderId($orderId)) {
    die('bad order id');
}
if ($requestMerchantId !== '' && $requestMerchantId !== $merchantId) {
    die('wrong merchant');
}

$expectedSign = strtoupper(md5($merchantId . ':' . $amount . ':' . $secretWord2 . ':' . $orderId));
if (!hash_equals($expectedSign, $sign)) {
    die('wrong sign');
}

$existing = loadOrders();
$existingOrder = $existing[$orderId] ?? [];

saveOrder(array_merge($existingOrder, [
    'order_id' => $orderId,
    'amount' => isset($existingOrder['amount']) ? $existingOrder['amount'] : (float) $amount,
    'tariff_name' => $tariffName !== '' ? $tariffName : ($existingOrder['tariff_name'] ?? ''),
    'freekassa_id' => $intid,
    'provider' => 'freekassa',
    'status' => 'paid',
    'paid_at' => date('Y-m-d H:i:s'),
]));

die('YES');
