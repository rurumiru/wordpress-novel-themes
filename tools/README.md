# Tools

Four small scripts. None of them is required to run the theme.

## `build-zip.php`

Packs a folder into a distributable zip with forward-slash entry paths and a single root folder — the shape WordPress expects when installing a theme.

```bash
php tools/build-zip.php --src=themes/xi-novels --out=dist/xi-novels.zip
php tools/build-zip.php --src=demo --out=dist/demo.zip --root=demo
```

Use it instead of PowerShell's `Compress-Archive`: that one writes backslashes into entry names, and WordPress then reports `the theme is missing style.css`. The script checks its own output and prints the count of bad entries, which should be zero.

## `import-novels.php`

Bulk importer for titles and chapters. Two ways in:

* a **JSON or CSV manifest** — creates or updates posts, downloads covers and artwork, sets every meta field and taxonomy;
* a **folder or ZIP archive of chapter files** (`.txt`, `.html`, `.md`) — the file name gives the chapter number and title, sub-folders mean one title per folder.

```bash
php tools/import-novels.php --wp=/var/www/site --file=novels.json
php tools/import-novels.php --wp=/var/www/site --from-zip=chapters.zip --novel="Title"
php tools/import-novels.php --wp=/var/www/site --from-dir=./chapters --novel-id=412 --locked-from=40
```

Full manifest format, WP All Import mapping, WP-CLI recipes and the server limits you need for large uploads: [docs/import.md](../docs/import.md) ([RU](../docs/import.ru.md)).

## `dev-router.php`

Router for PHP's built-in web server. The built-in server has no rewrite engine, so pretty permalinks 404 without it. Apache, nginx and LiteSpeed need nothing.

```bash
php -S localhost:8080 -t /path/to/wordpress tools/dev-router.php
```

It serves existing files directly, resolves directory `index.php` (so `/wp-admin/` works) and routes everything else to WordPress.

## `build-translations.php`

Compiles the theme's translation files without needing `msgfmt` installed.

```bash
php tools/build-translations.php
```

What it does:

1. reads the string list extracted from the theme;
2. checks every string against the RU → EN map inside the script and reports anything missing;
3. writes `themes/xi-novels/languages/en_US.po` and a binary `en_US.mo`.

To add a locale, copy the map, translate the values and change the output paths. Adjust the paths at the top of the script if your checkout lives somewhere else.
