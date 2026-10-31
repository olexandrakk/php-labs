# API форуму (практична робота №6, варіант 9)

Єдина точка входу: `api.php`. Усі відповіді — JSON:
- успіх: `{"success": true, "data": ...}`
- помилка: `{"success": false, "error": "опис"}`

| Метод | Запит | Успіх | Помилки |
|-------|-------|-------|---------|
| GET | `api.php?resource=topics` | 200, список тем | — |
| GET | `api.php?resource=topics&id=N` | 200, одна тема | 400 (некоректний id), 404 (теми немає) |
| POST | `api.php?resource=topics` (title, author, is_pinned) | 201, створена тема | 400 (немає/некоректні поля) |
| POST | `api.php?resource=topics&action=pin&id=N` | 200, тема з `is_pinned = 1` | 400 (некоректний id), 404 (теми немає) |
| інші | будь-який інший метод для `topics` | — | 405 |
| будь-який | невідомий `resource` | — | 404 |

## Перевірка (PowerShell; потрібен саме `curl.exe`, а не `curl`)

```
curl.exe -i "http://localhost:8000/api.php?resource=topics"
curl.exe -i "http://localhost:8000/api.php?resource=topics&id=1"
curl.exe -i "http://localhost:8000/api.php?resource=topics&id=9999"
curl.exe -i -X POST "http://localhost:8000/api.php?resource=topics" -d "title=Test topic&author=Ira"
curl.exe -i -X POST "http://localhost:8000/api.php?resource=topics" -d "title=Only title"
curl.exe -i -X POST "http://localhost:8000/api.php?resource=topics&action=pin&id=2"
curl.exe -i -X DELETE "http://localhost:8000/api.php?resource=topics&id=1"
curl.exe -i "http://localhost:8000/api.php?resource=users"
```

Очікувані коди: 200, 200, 404, 201, 400, 200, 405, 404.