<?php
<<<<<<< HEAD
require_once __DIR__ . '/env.php';

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonResponse(array $data): void {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function buildExternalUrl(string $baseUrl, array $params): string {
    $separator = str_contains($baseUrl, '?') ? '&' : '?';
    return rtrim($baseUrl, '&?') . $separator . http_build_query($params);
}

function createSeverpayPayment(string $orderId, float $amount, string $currency, string $email): ?string {
    $mid = getenv('SEVERPAY_MID') ?: '';
    $token = getenv('SEVERPAY_TOKEN') ?: '';
    $apiUrl = getenv('SEVERPAY_API_URL') ?: 'https://severpay.io/api/merchant/payin/create';

    if ($mid === '' || $token === '') {
        return null;
    }

    $body = [
        'amount' => number_format($amount, 2, '.', ''),
        'client_email' => $email,
        'client_id' => 'vpn_user',
        'currency' => $currency,
        'mid' => (int)$mid,
        'order_id' => $orderId,
        'salt' => bin2hex(random_bytes(8)),
        'url_return' => (getenv('PAYMENT_RETURN_URL') ?: (getenv('APP_BASE_URL') ?: '')) . '/indexfreekassa.html?order_id=' . rawurlencode($orderId) . '&status=success',
    ];

    ksort($body);
    $body['sign'] = hash_hmac('sha256', json_encode($body, JSON_UNESCAPED_UNICODE), $token);

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 15,
    ]);

    $result = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($result === false || $httpCode >= 400) {
        return null;
    }

    $response = json_decode($result, true);
    return $response['data']['url'] ?? null;
}

function createCryptoCloudPayment(string $orderId, float $amount, string $currency): ?string {
    $apiKey = getenv('CRYPTOCLOUD_API_KEY') ?: '';
    $shopId = getenv('CRYPTOCLOUD_SHOP_ID') ?: '';
    $apiUrl = getenv('CRYPTOCLOUD_API_URL') ?: 'https://api.cryptocloud.plus/v2/invoice/create';

    if ($apiKey === '' || $shopId === '') {
        return null;
    }

    $payload = [
        'shop_id' => $shopId,
        'amount' => number_format($amount, 2, '.', ''),
        'currency' => $currency,
        'order_id' => $orderId,
        'desc' => 'VPN Secure тариф',
    ];

    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Token ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 15,
    ]);

    $result = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($result === false || $httpCode >= 400) {
        return null;
    }

    $response = json_decode($result, true);
    return $response['result']['link'] ?? $response['result']['url'] ?? null;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    jsonResponse(['success' => false, 'message' => 'Ожидался JSON payload']);
}
$provider = strtolower(trim($payload['provider'] ?? ''));
$tariffId = (int)($payload['tariff_id'] ?? 0);
$orderId = trim($payload['order_id'] ?? '');
$customerEmail = trim($payload['customer_email'] ?? '');
=======
header('Content-Type: application/json');

$payload = json_decode(file_get_contents('php://input'), true);
$provider = $payload['provider'] ?? '';
$tariffId = (int)($payload['tariff_id'] ?? 0);
$orderId = trim($payload['order_id'] ?? '');
>>>>>>> main

$tariffs = [
    1 => ['name' => '1 месяц', 'price' => 150],
    2 => ['name' => '6 месяцев', 'price' => 600],
    3 => ['name' => '12 месяцев', 'price' => 1200],
];

<<<<<<< HEAD
if (!isset($tariffs[$tariffId]) || $orderId === '' || $provider === '') {
    jsonResponse(['success' => false, 'message' => 'Некорректные данные платежа']);
}

$allowedProviders = ['freekassa', 'platega', 'severpay', 'cryptocloud', 'crystalpay', 'cryptobot', 'donationalerts', 'boosty'];
if (!in_array($provider, $allowedProviders, true)) {
    jsonResponse(['success' => false, 'message' => 'Платежная система не поддерживается']);
}

if ($customerEmail === '') {
    $customerEmail = getenv('DEFAULT_CUSTOMER_EMAIL') ?: 'client@example.com';
}

$amount = (float)$tariffs[$tariffId]['price'];
$currency = getenv('PAYMENT_CURRENCY') ?: 'RUB';

$ordersFile = __DIR__ . '/orders.json';
$orders = file_exists($ordersFile) ? (json_decode(file_get_contents($ordersFile), true) ?: []) : [];
=======
if (!isset($tariffs[$tariffId]) || $orderId === '') {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные платежа']);
    exit;
}

$amount = $tariffs[$tariffId]['price'];

$ordersFile = __DIR__ . '/orders.json';
$orders = [];
if (file_exists($ordersFile)) {
    $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
}

>>>>>>> main
$orders[$orderId] = [
    'order_id' => $orderId,
    'tariff_id' => $tariffId,
    'tariff_name' => $tariffs[$tariffId]['name'],
    'amount' => $amount,
<<<<<<< HEAD
    'currency' => $currency,
    'provider' => $provider,
    'customer_email' => $customerEmail,
    'status' => 'pending',
    'created_at' => date('Y-m-d H:i:s')
];
file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

switch ($provider) {
    case 'freekassa':
        $merchantId = getenv('FREEKASSA_MERCHANT_ID') ?: '';
        $secretWord1 = getenv('FREEKASSA_SECRET_WORD_1') ?: '';
        $fkCurrency = getenv('FREEKASSA_CURRENCY') ?: 'RUB';
        if ($merchantId === '' || $secretWord1 === '') {
            jsonResponse(['success' => false, 'message' => 'Не настроены переменные окружения Freekassa']);
        }
        $sign = md5($merchantId . ':' . $amount . ':' . $secretWord1 . ':' . $fkCurrency . ':' . $orderId);
        $url = 'https://pay.fk.money/?' . http_build_query([
            'm' => $merchantId,
            'oa' => $amount,
            'currency' => $fkCurrency,
            'o' => $orderId,
            's' => $sign,
            'lang' => 'ru',
            'em' => $customerEmail,
            'us_tariff' => (string)$tariffId,
            'us_tariff_name' => $tariffs[$tariffId]['name'],
        ]);
        jsonResponse(['success' => true, 'provider_label' => 'Freekassa', 'payment_url' => $url]);

    case 'platega':
        $base = getenv('PLATEGA_PAYMENT_URL') ?: '';
        if ($base === '') {
            jsonResponse(['success' => false, 'message' => 'Не настроена переменная PLATEGA_PAYMENT_URL']);
        }
        jsonResponse(['success' => true, 'provider_label' => 'Platega', 'payment_url' => buildExternalUrl($base, ['order_id' => $orderId, 'amount' => $amount, 'currency' => $currency])]);

    case 'severpay':
        $severpayUrl = createSeverpayPayment($orderId, $amount, $currency, $customerEmail);
        if ($severpayUrl === null) {
            $fallback = getenv('SEVERPAY_PAYMENT_URL') ?: '';
            if ($fallback === '') {
                jsonResponse(['success' => false, 'message' => 'Не настроены параметры SeverPay']);
            }
            $severpayUrl = buildExternalUrl($fallback, ['order_id' => $orderId, 'amount' => $amount, 'currency' => $currency]);
        }
        jsonResponse(['success' => true, 'provider_label' => 'SeverPay', 'payment_url' => $severpayUrl]);

    case 'cryptocloud':
        $cloudUrl = createCryptoCloudPayment($orderId, $amount, $currency);
        if ($cloudUrl === null) {
            $fallback = getenv('CRYPTOCLOUD_PAYMENT_URL') ?: '';
            if ($fallback === '') {
                jsonResponse(['success' => false, 'message' => 'Не настроены параметры CryptoCloud']);
            }
            $cloudUrl = buildExternalUrl($fallback, ['order_id' => $orderId, 'amount' => $amount, 'currency' => $currency]);
        }
        jsonResponse(['success' => true, 'provider_label' => 'CryptoCloud', 'payment_url' => $cloudUrl]);

    case 'crystalpay':
        $base = getenv('CRYSTALPAY_PAYMENT_URL') ?: '';
        if ($base === '') {
            jsonResponse(['success' => false, 'message' => 'Не настроена переменная CRYSTALPAY_PAYMENT_URL']);
        }
        jsonResponse(['success' => true, 'provider_label' => 'CrystalPay', 'payment_url' => buildExternalUrl($base, ['order_id' => $orderId, 'amount' => $amount, 'currency' => $currency])]);

    case 'cryptobot':
        $base = getenv('CRYPTOBOT_PAYMENT_URL') ?: '';
        if ($base === '') {
            jsonResponse(['success' => false, 'message' => 'Не настроена переменная CRYPTOBOT_PAYMENT_URL']);
        }
        jsonResponse(['success' => true, 'provider_label' => 'CryptoBot', 'payment_url' => buildExternalUrl($base, ['order_id' => $orderId, 'amount' => $amount, 'currency' => $currency])]);

    case 'donationalerts':
        $base = getenv('DONATIONALERTS_PAYMENT_URL') ?: 'https://www.donationalerts.com/r/countvpn';
        jsonResponse(['success' => true, 'provider_label' => 'DonationAlerts', 'payment_url' => buildExternalUrl($base, ['amount' => $amount, 'order_id' => $orderId])]);

    case 'boosty':
        $base = getenv('BOOSTY_PAYMENT_URL') ?: '';
        if ($base === '') {
            jsonResponse(['success' => false, 'message' => 'Не настроена переменная BOOSTY_PAYMENT_URL']);
        }
        jsonResponse(['success' => true, 'provider_label' => 'Boosty', 'payment_url' => buildExternalUrl($base, ['amount' => $amount, 'order_id' => $orderId])]);
}

jsonResponse(['success' => false, 'message' => 'Платежная система не поддерживается']);
=======
    'provider' => $provider,
    'status' => 'pending',
    'created_at' => date('Y-m-d H:i:s')
];
file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($provider === 'freekassa') {
    $merchantId = getenv('FREEKASSA_MERCHANT_ID') ?: '';
    $secretWord1 = getenv('FREEKASSA_SECRET_WORD_1') ?: '';
    $currency = getenv('FREEKASSA_CURRENCY') ?: 'RUB';

    if ($merchantId === '' || $secretWord1 === '') {
        echo json_encode(['success' => false, 'message' => 'Не настроены переменные окружения Freekassa']);
        exit;
    }

    $sign = md5($merchantId . ':' . $amount . ':' . $secretWord1 . ':' . $currency . ':' . $orderId);
    $query = http_build_query([
        'm' => $merchantId,
        'oa' => $amount,
        'currency' => $currency,
        'o' => $orderId,
        's' => $sign,
        'lang' => 'ru',
        'us_tariff' => (string)$tariffId,
        'us_tariff_name' => $tariffs[$tariffId]['name'],
    ]);

    echo json_encode([
        'success' => true,
        'provider_label' => 'Freekassa',
        'payment_url' => 'https://pay.fk.money/?' . $query,
    ]);
    exit;
}

if ($provider === 'platega') {
    $baseUrl = getenv('PLATEGA_PAYMENT_URL') ?: '';
    if ($baseUrl === '') {
        echo json_encode(['success' => false, 'message' => 'Не настроена переменная окружения PLATEGA_PAYMENT_URL']);
        exit;
    }

    $query = http_build_query([
        'order_id' => $orderId,
        'amount' => $amount,
        'description' => 'Оплата тарифа VPN Secure: ' . $tariffs[$tariffId]['name'],
    ]);

    echo json_encode([
        'success' => true,
        'provider_label' => 'Platega',
        'payment_url' => rtrim($baseUrl, '?') . '?' . $query,
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Платежная система не поддерживается']);
>>>>>>> main
