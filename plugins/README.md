# Plugins

The theme needs **no plugins**. Everything the platform does — catalog, chapters, reader, studio, library, rankings, ratings, translations — lives in the theme itself.

This folder documents what the reference site runs alongside it, and why. Third-party plugins are not vendored here: they are freely installable from wordpress.org and stay updated through the normal channel instead of going stale in a git repository.

## Recommended

| Plugin | Why | Install |
|---|---|---|
| **LiteSpeed Cache** (or any page cache) | Chapter pages are read-heavy and almost entirely static. Any full-page cache — LiteSpeed, WP Super Cache, Nginx FastCGI — turns them into flat files. Use LiteSpeed only on a LiteSpeed / OpenLiteSpeed host | `wp plugin install litespeed-cache --activate` |
| **Akismet** | Only relevant if you add discussions later. The theme ships with comments disabled everywhere, so this is optional | `wp plugin install akismet --activate` |
| **SQLite Database Integration** | Development only. Lets you run the whole site without MySQL — see [docs/install.md](../docs/install.md) | `wp plugin install sqlite-database-integration` |

## Useful, but pick deliberately

| Need | Plugin | Note |
|---|---|---|
| SEO titles and sitemaps | Rank Math or SEOPress | WordPress core already emits a sitemap; add a plugin only if you want control over titles and schema |
| Image optimization | ShortPixel, Imagify, EWWW | Covers are the heaviest asset on a novel site |
| Backups | UpdraftPlus | Chapters are irreplaceable text |
| Import from another platform | WP All Import | The data model is two post types and two meta keys — see [docs/authoring.md](../docs/authoring.md#importing-an-existing-site) |
| Membership / paid chapters | Paid Memberships Pro, WooCommerce Memberships | The theme already marks chapters as PLUS and hides them from guests; a membership plugin decides who counts as a member |

## Deliberately not recommended

| Category | Why |
|---|---|
| Page builders (Elementor, WPBakery, Divi) | The theme is a designed system, not a canvas. A builder would fight its layout and drag in megabytes of runtime |
| “Novel manager” plugins | They bring their own post types and duplicate everything here |
| Comment plugins | Comments are removed by design. If you want them, undo the filters at the end of `functions.php` first |
| Font / icon plugins | The theme uses a system font stack and an inline SVG sprite on purpose — zero external requests |

## Compatibility

The theme touches only standard WordPress APIs: post types, taxonomies, meta, the customizer, widgets, `wp_editor()`, `admin-post.php`, REST and gettext. Anything that plays nicely with a classic theme plays nicely with it.

Two things to know:

* **`/wp-json/` is remapped to `/api/`** (`rest_url_prefix` filter in `inc/cleanup.php`). Plugins that hardcode `/wp-json/` instead of calling `rest_url()` will need that filter removed.
* **Comments are closed by filters**, not just by post-type support. A plugin that expects open comments will see them closed.

## XI Novels — импорт глав / chapter import

`xi-novel-import.zip` — плагин массового импорта: **.docx**, .txt, .md, .html, ZIP с ними и **Google Docs** по ссылке. Экран — *Инструменты → Импорт глав*.

* номер и название главы берутся из имени файла: `001. Десятый.docx`, `Глава 12.5 — Экстра.txt`;
* файлы сортируются естественно, повторный запуск обновляет те же главы;
* опции: начальный номер, ранний доступ с номера N, публикация или черновики, кодировка текстовых файлов;
* Google Docs: документ должен быть открыт по ссылке или опубликован (Файл → Поделиться → Опубликовать в интернете).

`xi-novel-import.zip` is the bulk import plugin: **.docx**, .txt, .md, .html, ZIP archives of them and **Google Docs** by link. It lives under *Tools → Chapter import*, reads the chapter number and title from the file name, sorts naturally, updates instead of duplicating on a re-run, and can mark chapters from a given number as early access.
