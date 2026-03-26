# VPN Secure payment landing

## Что изменено
- Подключены платежные системы: **Freekassa, Platega, SeverPay.io, CryptoCloud, CrystalPay, CryptoBot, DonationAlerts, Boosty.to**.
- Секреты/ключи вынесены в отдельный файл `.env` (шаблон: `.env.example`).
- Фронтенд не содержит секретов: все чувствительные операции выполняются в `create_payment.php`.
- Логика сайта: тариф → способ оплаты → переход на оплату → после подтверждения переход в Telegram-бот и отправка чека в поддержку.

## Настройка окружения
1. Скопируйте `.env.example` в `.env`.
2. Заполните ключи и URL платежных провайдеров.
3. Убедитесь, что `.env` не попадает в git (он уже добавлен в `.gitignore`).

## Ключевые переменные
- `FREEKASSA_MERCHANT_ID`, `FREEKASSA_SECRET_WORD_1`, `FREEKASSA_SECRET_WORD_2`
- `PLATEGA_PAYMENT_URL`
- `SEVERPAY_MID`, `SEVERPAY_TOKEN`, `SEVERPAY_API_URL`
- `CRYPTOCLOUD_API_KEY`, `CRYPTOCLOUD_SHOP_ID`, `CRYPTOCLOUD_API_URL`
- `CRYSTALPAY_PAYMENT_URL`, `CRYPTOBOT_PAYMENT_URL`, `DONATIONALERTS_PAYMENT_URL`, `BOOSTY_PAYMENT_URL`
- `APP_BASE_URL`, `PAYMENT_RETURN_URL`, `PAYMENT_CURRENCY`, `DEFAULT_CUSTOMER_EMAIL`

## Файлы
- `indexfreekassa.html` — витрина и выбор способа оплаты.
- `create_payment.php` — создание ссылок/инвойсов для всех платежных систем.
- `notify.php` — webhook Freekassa для подтверждения оплат.
- `check_order.php` — проверка статуса заказа (POST JSON: `{"order_id":"..."}`).
- `env.php` — загрузка переменных из `.env`.
- `privacy.html`, `terms.html`, `contacts.html` — юридические страницы.

## Проверка после правок
- `php -l create_payment.php`
- `php -l notify.php`
- `php -l check_order.php`
