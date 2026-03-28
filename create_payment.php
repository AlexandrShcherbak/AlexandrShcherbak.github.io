<?php

declare(strict_types=1);

require_once __DIR__ . '/backend.php';

requireMethod('POST');
$payload = readJsonBody();

$provider = strtolower(trim((string) ($payload['provider'] ?? '')));
$tariffId = (int) ($payload['tariff_id'] ?? 0);
$orderId = trim((string) ($payload['order_id'] ?? ''));
$customerEmail = trim((string) ($payload['customer_email'] ?? ''));

$tariffs = paymentTariffs();
$allowedProviders = ['freekassa', 'platega', 'severpay', 'cryptocloud', 'crystalpay', 'cryptobot', 'donationalerts', 'boosty'];

if (!isset($tariffs[$tariffId]) || $provider === '') {
    sendJson(['success' => false, 'message' => 'Некорректные данные платежа'], 400);
}
if (!in_array($provider, $allowedProviders, true)) {
    sendJson(['success' => false, 'message' => 'Платежная система не поддерживается'], 400);
}
if (!isValidOrderId($orderId)) {
    sendJson(['success' => false, 'message' => 'Некорректный формат order_id'], 400);
}
if ($customerEmail === '') {
    $customerEmail = getenv('DEFAULT_CUSTOMER_EMAIL') ?: 'client@example.com';
}

$amount = number_format((float) $tariffs[$tariffId]['price'], 2, '.', '');
$currency = getenv('PAYMENT_CURRENCY') ?: 'RUB';

$paymentUrl = null;
$providerLabel = null;

if ($provider === 'freekassa') {
    $merchantId = getenv('FREEKASSA_MERCHANT_ID') ?: '';
    $secretWord1 = getenv('FREEKASSA_SECRET_WORD_1') ?: '';
    $fkCurrency = getenv('FREEKASSA_CURRENCY') ?: $currency;

    if ($merchantId === '' || $secretWord1 === '') {
        sendJson(['success' => false, 'message' => 'Не настроены переменные окружения Freekassa'], 500);
    }

    $sign = md5($merchantId . ':' . $amount . ':' . $secretWord1 . ':' . $fkCurrency . ':' . $orderId);
    $paymentUrl = 'https://pay.fk.money/?' . http_build_query([
        'm' => $merchantId,
        'oa' => $amount,
        'currency' => $fkCurrency,
        'o' => $orderId,
        's' => $sign,
        'lang' => 'ru',
        'em' => $customerEmail,
        'us_tariff' => (string) $tariffId,
        'us_tariff_name' => $tariffs[$tariffId]['name'],
    ]);
    $providerLabel = 'Freekassa';
}

if ($provider === 'platega') {
    $baseUrl = getenv('PLATEGA_PAYMENT_URL') ?: '';
    if ($baseUrl === '') {
        sendJson(['success' => false, 'message' => 'Не настроена переменная PLATEGA_PAYMENT_URL'], 500);
    }

    $paymentUrl = buildUrl($baseUrl, [
        'order_id' => $orderId,
        'amount' => $amount,
        'currency' => $currency,
    ]);
    $providerLabel = 'Platega';
}

if ($provider === 'severpay') {
    $baseUrl = getenv('SEVERPAY_PAYMENT_URL') ?: '';
    if ($baseUrl === '') {
        sendJson(['success' => false, 'message' => 'Не настроена переменная SEVERPAY_PAYMENT_URL'], 500);
    }

    $paymentUrl = buildUrl($baseUrl, [
        'order_id' => $orderId,
        'amount' => $amount,
        'currency' => $currency,
        'email' => $customerEmail,
    ]);
    $providerLabel = 'SeverPay';
}

if ($provider === 'cryptocloud') {
    $baseUrl = getenv('CRYPTOCLOUD_PAYMENT_URL') ?: '';
    if ($baseUrl === '') {
        sendJson(['success' => false, 'message' => 'Не настроена переменная CRYPTOCLOUD_PAYMENT_URL'], 500);
    }

    $paymentUrl = buildUrl($baseUrl, [
        'order_id' => $orderId,
        'amount' => $amount,
        'currency' => $currency,
    ]);
    $providerLabel = 'CryptoCloud';
}

if ($provider === 'crystalpay') {
    $baseUrl = getenv('CRYSTALPAY_PAYMENT_URL') ?: '';
    if ($baseUrl === '') {
        sendJson(['success' => false, 'message' => 'Не настроена переменная CRYSTALPAY_PAYMENT_URL'], 500);
    }

    $paymentUrl = buildUrl($baseUrl, [
        'order_id' => $orderId,
        'amount' => $amount,
        'currency' => $currency,
    ]);
    $providerLabel = 'CrystalPay';
}

if ($provider === 'cryptobot') {
    $baseUrl = getenv('CRYPTOBOT_PAYMENT_URL') ?: '';
    if ($baseUrl === '') {
        sendJson(['success' => false, 'message' => 'Не настроена переменная CRYPTOBOT_PAYMENT_URL'], 500);
    }

    $paymentUrl = buildUrl($baseUrl, [
        'order_id' => $orderId,
        'amount' => $amount,
        'currency' => $currency,
    ]);
    $providerLabel = 'CryptoBot';
}

if ($provider === 'donationalerts') {
    $baseUrl = getenv('DONATIONALERTS_PAYMENT_URL') ?: 'https://www.donationalerts.com/r/countvpn';
    $paymentUrl = buildUrl($baseUrl, [
        'order_id' => $orderId,
        'amount' => $amount,
    ]);
    $providerLabel = 'DonationAlerts';
}

if ($provider === 'boosty') {
    $baseUrl = getenv('BOOSTY_PAYMENT_URL') ?: '';
    if ($baseUrl === '') {
        sendJson(['success' => false, 'message' => 'Не настроена переменная BOOSTY_PAYMENT_URL'], 500);
    }

    $paymentUrl = buildUrl($baseUrl, [
        'order_id' => $orderId,
        'amount' => $amount,
    ]);
    $providerLabel = 'Boosty';
}

if ($paymentUrl === null || $providerLabel === null) {
    sendJson(['success' => false, 'message' => 'Платежная система не поддерживается'], 400);
}

saveOrder([
    'order_id' => $orderId,
    'tariff_id' => $tariffId,
    'tariff_name' => $tariffs[$tariffId]['name'],
    'amount' => (float) $amount,
    'currency' => $currency,
    'provider' => $provider,
    'customer_email' => $customerEmail,
    'status' => 'pending',
    'created_at' => date('Y-m-d H:i:s'),
]);

sendJson([
    'success' => true,
    'provider_label' => $providerLabel,
    'payment_url' => $paymentUrl,
]);
