# TrueBTC PHP back end

* Движок для сайта: Slim версия 3 - www.slimframework.com/docs/v3/tutorial/first-app.html
* Для установки всех либ сделайте "composer install" в папке "_private"
* Папку "_private" желательно вынести (или закрыть другим способ) из public_html зоны
  * Данные по максимальному количеству запросов на каждую биржу:
  * YObit.net - 100 в минуту - https://yobit.net/ru/rules/
  * CEX.IO - 600 в 10 минут - https://cex.io/cex-api
  * Exmo.com - 180 в минуту - https://exmo.com/en/api
  * LiveCoin.net - 1 в секунду - https://www.livecoin.net/api?lang=ru
  * Crex24.com - 6 в секунду - https://crex24.com/ru/trade-api
  * Dsx.uk - 60 в минуту - https://dsx.uk/developers/publicApiV2
* _private/cache - сюда складируется кеш по запросам к биржам
* _private/logs - сюда складируются логи если что-то пошло не так


## API Endpoints

https://truebtc.io/api/v1

/update_time — Дата/время последнего обновления. Приложение клиента забирает этот показатель, чтобы понять, обновились ли данные. Если дата/время изменились, запрашивает в следующем методе полные данные. Данные обновляются 1 раз в минуту, чаще забирать не имеет практического смысла.

/rates/latest — Данные для таблички

* timestamp — дата/время последнего обновления
* index — индекс средневзвешенной цены
* exchanges — данные по каждой бирже

/rates/latest — Данные для таблички

## Обновление данных на сервере

/api/updater.php — Обновить все цены. Запускается через cron 1 раз в минуту.