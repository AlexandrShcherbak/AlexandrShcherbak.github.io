# VPN Secure payment landing

## Что реализовано
- Поддержка оплаты через **Freekassa, Platega, SeverPay, CryptoCloud, CrystalPay, CryptoBot, DonationAlerts, Boosty**.
- Backend API вынесен в единый слой `backend.php` (валидация JSON, метод-запроса, `order_id`, работа с `orders.json`, единый JSON-ответ).
- Создание платежей выполняется через `create_payment.php` и сохраняет заказ только после успешной генерации платежной ссылки.
- Webhook `notify.php` валидирует метод, IP, `MERCHANT_ID`, подпись Freekassa и подтверждает оплату.
- Проверка статуса заказа доступна через `check_order.php`.

## Формат order_id
`order_id` должен соответствовать регулярному выражению: `^[A-Za-z0-9_-]{8,64}$`.

## Основные файлы
- `backend.php` — общие backend-функции (ответы, валидация, безопасное сохранение заказов).
- `create_payment.php` — создание платежной ссылки по провайдеру.
- `notify.php` — webhook Freekassa.
- `check_order.php` — проверка статуса заказа.
- `env.php` — загрузка переменных окружения из `.env`.

## Проверка
```bash
php -l backend.php
php -l create_payment.php
php -l notify.php
php -l check_order.php
```
