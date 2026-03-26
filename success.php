<?php
$orderId = $_GET['MERCHANT_ORDER_ID'] ?? '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оплата успешна - VPN Secure</title>
    <meta http-equiv="refresh" content="3;url=/indexfreekassa.html?order_id=<?php echo urlencode($orderId); ?>&status=success" />
</head>
<body>
    <h1>✅ Оплата успешно завершена</h1>
    <p>Через 3 секунды откроется сайт. Далее перейдите в Telegram-бот и отправьте чек в поддержку для получения конфигурации.</p>
</body>
</html>
