# Tools

Two small scripts. Neither is required to run the theme.

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
