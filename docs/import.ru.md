# Импорт и тяжёлые загрузки

На переезде сайта новелл ломаются две вещи: залить сотни глав и протащить крупные обложки через сервер. Эта страница про обе.

---

## 1. Модель данных, в которую импортируем

| Что | Где лежит |
|---|---|
| Тайтл | запись типа `novel` — обложка в изображении записи, короткий синопсис в цитате, полное описание в контенте |
| Глава | запись типа `chapter` — текст в контенте |
| Связь главы с тайтлом | мета `_xin_novel` = ID тайтла |
| Порядок глав | мета `_xin_number` — **число** (`12`, `12.5`), не строка |
| Жанры / теги / статус | таксономии `genre`, `novel_tag`, `novel_status` |
| Необязательная мета | `_xin_author_name`, `_xin_original_title`, `_xin_translator`, `_xin_year`, `_xin_source`, `_xin_views`, `_xin_rating`, `_xin_rating_count`, `_xin_adult`, `_xin_featured`, `_xin_background` (ID вложения с широким артом), `_xin_locked` (у главы) |

Заполнить это может что угодно, умеющее создавать записи. Ниже три пути, от простого к ручному.

---

## 2. Импортёр из репозитория

`tools/import-novels.php` принимает JSON или CSV, создаёт и обновляет тайтлы и главы, скачивает обложки и проставляет всю мету.

```bash
php tools/import-novels.php --wp=/var/www/site --file=novels.json
php tools/import-novels.php --wp=/var/www/site --file=chapters.csv --format=csv --author=3
php tools/import-novels.php --wp=/var/www/site --file=novels.json --dry-run
```

| Опция | Смысл |
|---|---|
| `--wp=PATH` | Корень WordPress (папка с `wp-load.php`). Обязательна |
| `--file=PATH` | Манифест. Обязательна |
| `--format=json\|csv` | По умолчанию `json` |
| `--author=ID` | Автор импортируемых записей. По умолчанию первый администратор |
| `--status=publish\|draft` | По умолчанию `publish` |
| `--media=0` | Не скачивать обложки и арты |
| `--dry-run` | Разобрать и показать, ничего не записывая |

### JSON

```json
[
  {
    "title": "Печать девятого неба",
    "slug": "pechat-devyatogo-neba",
    "synopsis": "Одна строка для карточки каталога.",
    "description": "<p>Полное описание, HTML разрешён.</p>",
    "author_name": "Лю Чэньсин",
    "original_title": "第九天印",
    "translator": "команда «Восточный ветер»",
    "year": 2021,
    "status": "ongoing",
    "genres": ["Фэнтези", "Сянься"],
    "tags": ["культивация", "перерождение"],
    "adult": false,
    "featured": true,
    "views": 128400,
    "rating": 4.7,
    "rating_count": 214,
    "cover": "https://example.com/cover.jpg",
    "artwork": "art/wide.jpg",
    "chapters": [
      { "number": 1, "title": "Осколок", "content": "<p>…</p>", "date": "2026-01-05 10:00:00" },
      { "number": 2, "title": "Первый снег", "content_file": "chapters/002.html", "locked": true }
    ]
  }
]
```

`status` принимает слаги, которые заводит тема: `ongoing`, `completed`, `hiatus`, `announced`. Пути в `cover`, `artwork` и `content_file` могут быть абсолютными, относительными к манифесту или ссылками `http(s)`.

### CSV

Одна строка на главу, колонки тайтла повторяются. Множественные значения разделяются `|`.

```csv
novel_title,novel_slug,synopsis,genres,status,cover,chapter_number,chapter_title,chapter_file,locked
Печать девятого неба,pechat-devyatogo,Осколок древней печати.,Фэнтези|Сянься,ongoing,covers/seal.jpg,1,Осколок,chapters/001.html,0
Печать девятого неба,pechat-devyatogo,Осколок древней печати.,Фэнтези|Сянься,ongoing,covers/seal.jpg,2,Первый снег,chapters/002.html,1
```

### Повторный запуск безопасен

Тайтл ищется по слагу (или по точному названию, если слаг не задан), глава — по номеру внутри тайтла. Второй прогон того же манифеста обновляет, а не плодит копии; обложка не перекачивается, если уже прикреплена.

На время работы отключается пересчёт терминов и инвалидация кеша — именно это не даёт нескольким тысячам вставок ползти.

---

## 3. WP All Import

Если удобнее интерфейс, сопоставьте так:

| Шаг импорта | Настройка |
|---|---|
| Тип записи | `Новелла` для тайтлов, `Глава` для глав (два отдельных импорта) |
| Заголовок / контент / цитата | Название, полное описание, короткий синопсис |
| Таксономии | `genre`, `novel_tag`, `novel_status` |
| Произвольные поля (тайтл) | `_xin_author_name`, `_xin_original_title`, `_xin_translator`, `_xin_year`, `_xin_views`, `_xin_rating`, `_xin_rating_count`, `_xin_adult` |
| Произвольные поля (глава) | `_xin_novel` — ID импортированного тайтла, `_xin_number` — номер главы, `_xin_locked` |
| Картинки | Изображение записи = обложка |

Сначала импортируйте тайтлы, потом главы, подставляя ID тайтла в `_xin_novel` по уникальному ключу.

---

## 4. WP-CLI

Для скриптовых переносов:

```bash
wp post create --post_type=novel --post_status=publish --post_title="Печать девятого неба" \
  --post_excerpt="Одна строка." --porcelain
# → 412

wp post term set 412 genre Фэнтези Сянься
wp post term set 412 novel_status ongoing
wp post meta set 412 _xin_author_name "Лю Чэньсин"

wp post create ./chapters/001.html --post_type=chapter --post_status=publish \
  --post_title="Осколок" --porcelain
# → 413
wp post meta set 413 _xin_novel 412
wp post meta set 413 _xin_number 1
```

---

## 5. Настройки сервера под тяжёлые файлы

Обложки, широкие арты и файлы импорта упираются сразу в пять ограничений. Поднимать нужно все — одного низкого достаточно, чтобы загрузка сорвалась.

### PHP

| Параметр | Рекомендуемое | Зачем |
|---|---|---|
| `upload_max_filesize` | `32M` | Потолок одного файла |
| `post_max_size` | `64M` | **Должен быть больше** `upload_max_filesize`. Если POST крупнее, PHP выбрасывает **весь** запрос — форма возвращается пустой без ошибки |
| `memory_limit` | `256M` | Ресайз грузит картинку в память целиком: обложке в 6000 px нужно куда больше, чем весит её файл |
| `max_execution_time` | `300` | Долгий импорт и перегенерация миниатюр |
| `max_input_time` | `300` | Время на приём загрузки |
| `max_input_vars` | `3000` | Большие формы — список глав на сотни строк |

Куда прописывать, по убыванию надёжности:

```ini
; php.ini — лучший вариант, на весь сервер
upload_max_filesize = 32M
post_max_size = 64M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
max_input_vars = 3000
```

```ini
; .user.ini в корне WordPress — шаред-хостинг с PHP-FPM
upload_max_filesize = 32M
post_max_size = 64M
memory_limit = 256M
```

```apache
# .htaccess — работает только на Apache + mod_php
php_value upload_max_filesize 32M
php_value post_max_size 64M
php_value memory_limit 256M
php_value max_execution_time 300
```

Панели: **cPanel → MultiPHP INI Editor**, **ISPmanager → Настройки PHP**, **aaPanel → PHP → Настройки → Конфигурация**, **Plesk → Настройки PHP**. В aaPanel меняйте значения именно у той версии PHP, на которой работает сайт.

### Nginx

```nginx
client_max_body_size 64m;
client_body_timeout 300s;
fastcgi_read_timeout 300s;
```

Без первой строки браузер получит **413 Request Entity Too Large** ещё до PHP.

### LiteSpeed / OpenLiteSpeed

В разделе тюнинга поставьте *Max Request Body Size* минимум `64M` и перезапустите. LiteSpeed также понимает директивы `php_value` из `.htaccess`.

### Cloudflare и прочие прокси

Бесплатный тариф Cloudflare режет загрузку на **100 МБ**, и поднять это нельзя. Всё, что крупнее, заливайте напрямую на сервер или по SFTP.

### WordPress

```php
// wp-config.php
define( 'WP_MEMORY_LIMIT', '256M' );      // фронт
define( 'WP_MAX_MEMORY_LIMIT', '512M' );  // админка, обработка картинок, импорт
```

```php
// functions.php дочерней темы — поднять лимит, который показывает медиатека
add_filter( 'upload_size_limit', function () {
	return 32 * 1024 * 1024;
} );

// не ужимать оригиналы до 2560px
add_filter( 'big_image_size_threshold', '__return_false' );
```

Что сервер применил на самом деле, видно в **Инструменты → Здоровье сайта → Информация → Обработка медиафайлов**.

### Размеры картинок, которые использует тема

| Размер | Пиксели | Где |
|---|---|---|
| `xin-cover` | 320×480 | Сетки каталога, рельсы |
| `xin-cover-lg` | 520×780 | Страница тайтла, колода витрины |
| `xin-cover-sm` | 120×180 | Строки рейтинга, виджеты, карточки глав |
| `xin-banner` | 1920×640 | Баннер главной, широкий арт |
| `xin-wide` | 720×405 | Карточки блога |

Обложки грузите **800×1200**, арты — **1920×720**; всё, что больше, — лишние байты. После массового импорта перегенерируйте миниатюры:

```bash
wp media regenerate --only-missing --yes
```

---

## 6. Скорость импорта

Для нескольких тысяч глав:

```bash
# через CLI — веб-серверные таймауты не действуют вообще
php -d memory_limit=512M tools/import-novels.php --wp=/var/www/site --file=novels.json

# первый проход без картинок, обложки прикрепить потом
php tools/import-novels.php --wp=/var/www/site --file=novels.json --media=0
```

На время прогона выключите плагины кеша и оптимизации картинок: они цепляются к каждой вставке и легко утраивают время. После — включите и сбросьте кеш.

---

## 7. Что делать, если не получается

| Симптом | Причина | Решение |
|---|---|---|
| **413 Request Entity Too Large** | Лимит тела запроса у веб-сервера | `client_max_body_size` (nginx) или Max Request Body Size (LiteSpeed) |
| Форма вернулась пустой, ничего не сохранилось | POST превысил `post_max_size`, PHP выбросил его целиком | Поднять `post_max_size` выше `upload_max_filesize`. Кабинет на этот случай показывает понятное сообщение |
| **Ошибка HTTP** в медиатеке | Память или отсутствующая библиотека картинок | Поднять `memory_limit`, проверить наличие GD или Imagick |
| «На сайте возникла критическая ошибка» посреди импорта | Фатал по памяти или таймауту | Запускать импортёр из PHP CLI, поднять `memory_limit` |
| Главы идут не по порядку | `_xin_number` записан текстом | Хранить число: `1`, `2`, `12.5` |
| Главы есть, а в тайтле их нет | `_xin_novel` не задан или указывает не на тот ID | Сверить значение меты с ID записи тайтла |
| Обложки растянуты | Исходник не в пропорции 2:3 | Залить 800×1200 и перегенерировать миниатюры |
| Импорт прошёл, но ничего не видно | Записи импортированы черновиками | Перезапустить с `--status=publish` или опубликовать массово в админке |
