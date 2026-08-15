# Demo content

A ready dataset that fills a fresh install so you can see the theme working: catalog, rankings, update feed, banners and blog all populated.

**What it creates**

| | |
|---|---|
| 12 titles | synopsis, description, genres, tags, release status, author and translation credits, ratings, view counts, five marked as editor's picks |
| 48 chapters | 4 per title, dated over recent weeks so the update feed has a real timeline; the last chapter of each title is marked PLUS |
| 12 genres, 12 tags | the taxonomy the catalog filters by |
| 5 blog posts | with excerpts and images |
| 3 banners | for the home slider, with badges, buttons and text alignment |
| Covers and artwork | drawn at import time by GD — gradients and arcs, 800×1200 and 1920×720 |

Every text is original filler written for this package. No third-party novels, no third-party images.

---

## The easy way: the plugin

`xi-demo-content.zip` from the release is a **plugin**, not a theme. Install it the way you install any plugin:

1. **Plugins → Add New → Upload Plugin** → `xi-demo-content.zip` → *Install now* → *Activate*.
2. **Tools → Demo content**.
3. Press **Install demo content**. Two checkboxes there let you skip the generated images or create everything as drafts.

The same screen has **Remove demo content**, which takes the site back to where it was. When the demo is no longer needed, remove it and deactivate the plugin — the theme does not depend on it.

> Do not upload `xi-demo-content.zip` under *Appearance → Themes*: WordPress will look for `style.css`, not find it, and refuse the archive. It belongs under *Plugins*.

## The command-line way

For servers where you have SSH:

```bash
php demo/import-demo.php --wp=/path/to/wordpress
php demo/remove-demo.php --wp=/path/to/wordpress --dry-run
php demo/remove-demo.php --wp=/path/to/wordpress
```

| Option | Meaning |
|---|---|
| `--wp=PATH` | WordPress root (the folder with `wp-load.php`). Required |
| `--author=ID` | Author for the created posts. Default: first administrator |
| `--status=draft` | Create drafts instead of published records |
| `--covers=0` | Skip image generation |
| `--trash` | (removal) move to trash instead of deleting |

Both entry points share the same code in `plugin/xi-demo-content/importer.php`, so they behave identically.

## Safe on a live site

* **Nothing of yours is touched.** Every record the demo creates — posts, chapters, banners, generated images — carries the post meta `_xin_demo = 1`, and removal deletes exactly those.
* **Re-running is safe.** Titles match by slug and chapters by number, so a second run updates instead of duplicating.
* **The theme must be active first.** The demo creates novels and chapters; without the theme those post types do not exist, and the plugin says so instead of doing anything.

---

# Демо-контент

Готовый набор данных: 12 тайтлов с описаниями, жанрами, статусами, оценками и просмотрами (пять — выбор редакции), 48 глав (по 4 на тайтл, последняя в каждом помечена PLUS), 12 жанров и 12 тегов, 5 записей блога, 3 баннера. Обложки и арты рисуются на месте средствами GD. Все тексты написаны специально для этого пакета — чужих произведений и чужих картинок здесь нет.

## Через админку (проще)

`xi-demo-content.zip` — это **плагин**, а не тема:

1. **Плагины → Добавить новый → Загрузить плагин** → `xi-demo-content.zip` → *Установить* → *Активировать*.
2. **Инструменты → Демо-контент**.
3. Нажать **Установить демо-контент**. Галочки на экране позволяют пропустить генерацию картинок или создать всё черновиками.

Там же кнопка **Удалить демо-контент** — сайт возвращается в исходное состояние. Когда демо больше не нужно, удалите его и отключите плагин: тема от него не зависит.

> Не загружайте `xi-demo-content.zip` в разделе *Внешний вид → Темы*: WordPress будет искать там `style.css`, не найдёт и откажется ставить архив. Ему место в разделе *Плагины*.

## Через консоль

```bash
php demo/import-demo.php --wp=/путь/к/wordpress
php demo/remove-demo.php --wp=/путь/к/wordpress --dry-run
php demo/remove-demo.php --wp=/путь/к/wordpress
```

Опции: `--author=ID`, `--status=draft`, `--covers=0`, `--trash`. Оба способа используют один и тот же код в `plugin/xi-demo-content/importer.php`.

## Почему это безопасно на боевом сайте

* Всё созданное помечено метой `_xin_demo = 1`, включая сгенерированные картинки; удаление трогает только эти записи.
* Повторный запуск обновляет те же записи: тайтлы ищутся по слагу, главы — по номеру.
* Сначала должна быть активна тема: без неё типов записей «Новеллы» и «Главы» не существует, и плагин честно об этом скажет, ничего не создавая.
