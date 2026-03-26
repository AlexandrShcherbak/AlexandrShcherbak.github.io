# VPN Secure payment landing

<<<<<<< HEAD
## Что изменено
- Подключены платежные системы: **Freekassa, Platega, SeverPay.io, CryptoCloud, CrystalPay, CryptoBot, DonationAlerts, Boosty.to**.
- Секреты/ключи вынесены в отдельный файл `.env` (шаблон: `.env.example`).
- Фронтенд больше не содержит секретов: все чувствительные операции выполняются в `create_payment.php`.
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
=======
## Что реализовано
- Поддержка двух платежных систем: **Freekassa** и **Platega**.
- Секреты убраны из фронтенда; подписи платежей формируются только на сервере в `create_payment.php`.
- После оплаты пользователь направляется в Telegram-бота `https://t.me/wireguard_easy_buy_bot` и отправляет чек в поддержку для получения конфигурации.
- Добавлены обязательные документы: политика конфиденциальности, пользовательское соглашение, контакты.

## Переменные окружения
Перед запуском задайте переменные:

- `FREEKASSA_MERCHANT_ID`
- `FREEKASSA_SECRET_WORD_1`
- `FREEKASSA_SECRET_WORD_2`
- `FREEKASSA_CURRENCY` (опционально, по умолчанию `RUB`)
- `PLATEGA_PAYMENT_URL` (базовый URL страницы оплаты Platega)

Пример (Linux):

```bash
export FREEKASSA_MERCHANT_ID="12345"
export FREEKASSA_SECRET_WORD_1="..."
export FREEKASSA_SECRET_WORD_2="..."
export PLATEGA_PAYMENT_URL="https://pay.platega.example/pay"
```

## Файлы
- `indexfreekassa.html` — витрина тарифов + сценарий оплаты.
- `create_payment.php` — создание платежной ссылки по выбранной системе.
- `notify.php` — webhook Freekassa для подтверждения оплаты.
- `check_order.php` — API проверки статуса заказа.
- `privacy.html`, `terms.html`, `contacts.html` — обязательные страницы для согласования.
>>>>>>> main
