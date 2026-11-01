# Практична робота №7 (варіант 9, Форум): навантаження й оптимізація

Тема: вимірювання продуктивності API форуму з практикуму №6 і його оптимізація.
Працює з базою `practicum4` і таблицею `topics` (практикум №4).

## Файли

| Файл | Призначення |
|------|-------------|
| `api.php` | Єдина точка входу API: маршрутизація, GET/POST, режими `slow`/`fast`, кеш закріплених тем, блок `meta` для вимірювань |
| `db.php` | Підключення PDO до бази `practicum4` і лічильник SQL-запитів (клас `CountingStatement`, рахує кожен `execute()`) |
| `seed.php` | Наповнення таблиці тестовими темами (`seed.php 1000`) і очищення (`seed.php clean`); запускається лише з командного рядка |
| `optimize.sql` | `EXPLAIN` до й після створення індексу на `author` |

## Контракт API

Усі відповіді мають формат `{"success": true, "data": ...}` або `{"success": false, "error": "опис"}`.

| Метод | Запит | Результат |
|-------|-------|-----------|
| GET | `api.php?resource=topics` | 200, список тем, кожна з полем `author_topics_count` (режим `fast`) |
| GET | `api.php?resource=topics&mode=slow` | те саме, але з N+1 запитів (для заміру «до») |
| GET | `api.php?resource=topics&mode=fast` | те саме, один запит `GROUP BY author` (за замовчуванням) |
| GET | `api.php?resource=topics&pinned=1` | 200, закріплені теми з файлового кешу (TTL 60 с) |
| GET | `api.php?resource=topics&id=N` | 200, одна тема; 400, якщо `id` некоректний; 404, якщо теми немає |
| POST | `api.php?resource=topics` (`title`, `author`, `is_pinned`) | 201, створена тема; 400, якщо поля відсутні чи задовгі; скидає кеш |
| POST | `api.php?resource=topics&action=pin&id=N` | 200, тема з `is_pinned = 1`; 404, якщо теми немає; скидає кеш |
| інший метод | для `topics` | 405 із заголовком `Allow: GET, POST` |
| будь-який | невідомий `resource` | 404 |

Параметр `debug=1` у GET-запиті додає у відповідь блок `meta`:
`queries` (кількість SQL-запитів), `time_ms` (час виконання скрипта), `peak_memory_mb`
(пікова пам'ять) і для `pinned=1` ще `cache` (`hit` або `miss`).
Ті самі значення пишуться в `error_log` PHP.

## Що саме оптимізується

- **N+1 (`listTopicsSlow` → `listTopicsFast` в `api.php`).** У повільному режимі після основного
  запиту для кожної теми виконується окремий `SELECT COUNT(*) ... WHERE author = :author`
  (разом 1 + N запитів). У швидкому режимі виконується один запит
  `SELECT author, COUNT(*) FROM topics GROUP BY author`, а лічильники підставляються в PHP (разом 2 запити).
- **Індекс (`optimize.sql`).** `CREATE INDEX idx_topics_author ON topics (author)`: без індексу
  `EXPLAIN` показує `type = ALL` (повне сканування), з індексом `type = ref` і значно менше `rows`.
- **Кеш (`listPinned` в `api.php`).** Список закріплених тем зберігається у файлі
  `forum_cache_pinned.json` у тимчасовій папці системи на 60 секунд. Функція `invalidateCache()`
  видаляє файл при створенні теми й закріпленні, тому застарілі дані не показуються.

## Як проводити вимірювання

0. Підготовка: у XAMPP запущено MySQL.
   ```
   cd D:\php_lab\practicum07
   C:\xampp\php\php.exe seed.php 1000
   C:\xampp\php\php.exe -S 127.0.0.1:8000
   ```
   Сервер запускається саме на `127.0.0.1`, інакше `ab` не зможе підключитися (на Windows
   `localhost` може слухати лише IPv6).
1. «До»: індексу на `author` ще немає (за потреби: `DROP INDEX idx_topics_author ON topics;`).
   Відкрийте `http://127.0.0.1:8000/api.php?resource=topics&mode=slow&debug=1` і запишіть `meta`.
2. «До», навантаження:
   ```
   C:\xampp\apache\bin\ab.exe -n 200 -c 20 "http://127.0.0.1:8000/api.php?resource=topics&mode=slow"
   ```
   З виводу беруться Requests per second, Time per request з позначкою `(mean)` і Failed requests.
3. Індекс: у phpMyAdmin (база `practicum4`, вкладка SQL) виконайте `EXPLAIN` із `optimize.sql`,
   потім `CREATE INDEX`, потім `EXPLAIN` ще раз.
4. «Після»: ті самі заміри з `mode=fast` (з індексом):
   ```
   C:\xampp\apache\bin\ab.exe -n 200 -c 20 "http://127.0.0.1:8000/api.php?resource=topics&mode=fast"
   ```
5. Кеш: двічі відкрийте `http://127.0.0.1:8000/api.php?resource=topics&pinned=1&debug=1`.
   Перший запит дає `cache: miss` (1 SQL-запит), другий `cache: hit` (0 SQL-запитів).
   Після `POST ...&action=pin&id=N` наступний запит знову дає `miss`.

Вбудований сервер `php -S` однопотоковий, тому параметр `-c 20` фактично не створює паралельності:
запити стають у чергу, і `Time per request (mean)` приблизно дорівнює 20 × час одного запиту.

## Звіт «до / після»

Тестові дані: 1007 тем у таблиці `topics` (`seed.php 1000` плюс початкові записи).

| Показник | До (`mode=slow`, без індексу) | Після (`mode=fast`, з індексом) |
|----------|-------------------------------|---------------------------------|
| Час виконання, мс | 477,3 | 4,61 |
| Пікова пам'ять, МБ | 2 | 2 |
| Кількість SQL-запитів | 1012 | 2 |
| Requests per second | 2,05 | 57,03 |
| Time per request (mean), мс | 9745,36 | 350,71 |
| Failed requests | 0 | 0 |
| `EXPLAIN`: type / rows | ALL / 1007 | ref / 38 |

Пояснення різниці: замість окремого запиту `COUNT(*)` для кожної теми (1012 запитів) виконується
один запит `GROUP BY author` (2 запити), а індекс на `author` дозволяє СУБД читати лише 38 рядків
замість 1007. Через це час одного запиту зменшився приблизно з 477 до 4,6 мс, а пропускна
здатність зросла з 2,05 до 57,03 запиту за секунду (приблизно в 28 разів). Пікова пам'ять не
змінилася, бо обидва режими завантажують у пам'ять той самий список тем.