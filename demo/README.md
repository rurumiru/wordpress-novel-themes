# Demo content

A ready dataset that fills a fresh install so you can see the theme working: catalog, rankings, update feed, banners and blog all populated.

**What it creates**

| | |
|---|---|
| 12 titles | synopsis, description, genres, tags, release status, author and translation credits, ratings, view counts, five of them marked as editor's picks |
| 48 chapters | 4 per title, dated over the last few weeks so the update feed has a real timeline; the last chapter of each title is marked PLUS |
| 12 genres, 12 tags | the taxonomy the catalog filters by |
| 5 blog posts | with excerpts and images |
| 3 banners | for the home slider, with badges, buttons and text alignment |
| Covers and artwork | generated on the fly: gradients and arcs drawn by GD, no third-party images |

Every text in `content.php` is original filler written for this package. No third-party novels, no scraped covers, nothing to license.

## Install

```bash
php demo/import-demo.php --wp=/path/to/wordpress
```

| Option | Meaning |
|---|---|
| `--wp=PATH` | WordPress root (the folder with `wp-load.php`). Required |
| `--author=ID` | Author for the created posts. Default: first administrator |
| `--status=draft` | Import as drafts instead of published |
| `--covers=0` | Skip image generation (much faster, cards fall back to placeholders) |
| `--dry-run` | Report what would be created, write nothing |

Run it again any time: titles match by slug and chapters by number, so a second run updates instead of duplicating.

## Remove

Everything the importer creates carries the post meta `_xin_demo = 1`, including generated images. Nothing you wrote yourself is touched.

```bash
php demo/remove-demo.php --wp=/path/to/wordpress --dry-run   # see what would go
php demo/remove-demo.php --wp=/path/to/wordpress             # delete for good
php demo/remove-demo.php --wp=/path/to/wordpress --trash     # move to trash instead
```

## Putting it on a live site

1. Install and activate the theme first — the importer needs the `novel` and `chapter` post types.
2. Run the import over SSH from the site root. If you have no shell access, upload `demo/` next to WordPress and run it once through your panel's PHP CLI.
3. Look at the front page, then remove the demo before launch with the command above and start adding your own titles.

Covers are drawn at 800×1200 and artwork at 1920×720 — the sizes the theme expects. If GD is missing, the import still runs and skips images.

---

# Демо-контент

Готовый набор данных, который наполняет свежую установку: каталог, рейтинги, лента обновлений, баннеры и блог.

**Что создаётся:** 12 тайтлов с описаниями, жанрами, статусами, оценками и просмотрами (пять помечены выбором редакции), 48 глав (по 4 на тайтл, с датами за последние недели, последняя глава каждого тайтла — PLUS), 12 жанров и 12 тегов, 5 записей блога, 3 баннера для главной. Обложки и арты рисуются на месте средствами GD — никаких чужих картинок.

Все тексты в `content.php` написаны специально для этого пакета: чужих произведений и чужих изображений здесь нет.

**Установка**

```bash
php demo/import-demo.php --wp=/путь/к/wordpress
```

Опции: `--author=ID`, `--status=draft`, `--covers=0`, `--dry-run`. Повторный запуск безопасен — тайтлы ищутся по слагу, главы по номеру, дубликатов не будет.

**Удаление**

Всё созданное помечено метой `_xin_demo = 1`, включая сгенерированные картинки, поэтому ваш собственный контент не пострадает.

```bash
php demo/remove-demo.php --wp=/путь/к/wordpress --dry-run
php demo/remove-demo.php --wp=/путь/к/wordpress
php demo/remove-demo.php --wp=/путь/к/wordpress --trash
```

**На боевом сайте:** сначала активируйте тему (импортёру нужны типы записей `novel` и `chapter`), затем запустите импорт по SSH из корня сайта. Перед запуском площадки уберите демо той же командой и заводите свои тайтлы.
