# Installation — XIN-V2 3

The primary theme is `themes/xin-v2`. XIN-Com remains an alternative. Requires WordPress 6.4+, PHP 8.0+, `mbstring`, image support, and `ZipArchive` for EPUB exports.

1. Back up the existing database and uploads when upgrading.
2. Copy `themes/xin-v2` into `wp-content/themes`, then activate **XI Community Library**.
3. Copy `plugins/xin-setup` into `wp-content/plugins`, activate it, and open the dashboard.
4. Import novels, chapters and covers using the appropriate importer. Setup schedules another pass after supported imports.
5. Check **Tools → Theme setup** for the report and optional repair actions.

XIN Setup creates missing pages and templates, assigns unassigned menus, sets `/%postname%/` permalinks, registers rewrite routes, prepares taxonomies/thumbnails and refreshes counters. It keeps editorial page text and assigned menus. Large counter refreshes continue through authenticated AJAX while the dashboard is open.

Apache requires rewrite support and a writable `.htaccess`. For Nginx, configure the WordPress fallback yourself:

```nginx
try_files $uri $uri/ /index.php?$args;
```

See [XIN Setup details](../plugins/xin-setup/README.md), [import](import.md), [visual presentation](design-v3.md) and [validation](validation-v3.md).

The repository contains theme/plugin source and demo illustrations, not a WordPress installation or database. No test accounts are installed on production. The dev login link appears only on a loopback PHP development server using localhost and uses ordinary WordPress authentication.

Core reading, catalogs, discussions and studio are implemented in the theme. Extra import/performance/management plugins are optional. Fonts and frontend assets are bundled; no npm build is needed. Optional Gravatar, sharing links, web push and third-party integrations have their own network/service requirements.
