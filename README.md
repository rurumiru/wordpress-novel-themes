# XI Novels 3 — beta

**Beta:** [3.0.4-beta.1](https://github.com/rurumiru/wordpress-novel-themes/releases/tag/v3.0.4-beta.1) is the current release. Development continues; this is not a stable release yet.

**XIN-V2 is the primary WordPress theme for XI Novels:** an illustrated novel portal, discovery catalog, single-chapter reader and frontend studio for authors and translators.

[Русский](README.ru.md) · [Português](README.pt-BR.md) · [Design presentation](docs/design-v3.md) · [Installation](docs/install.md) · [Changelog](CHANGELOG.md)

![XI Novels — new home design](screenshots/xin-v2-3/home-light.jpg)

## The new design

- New XI Novels logo and two-level navigation with search, account controls, resources and studio access.
- Editorial homepage with featured titles, reader rankings, illustrated carousel, genre modules, reading history and chapter updates.
- Cover-first catalog with search, genre/status/year/tag filters and sorting.
- Title pages with credits, statistics, personal 1–5 ratings, bookmarks, chapter list, reviews and EPUB/FB2 exports.
- Focused reader: **one chapter per page**, fixed navigation, contents and persistent reading preferences.
- Manrope typography, distinct discussion cards, role badges, replies and reactions.
- Responsive layout, mobile drawer and bottom navigation, light/dark themes, RU/EN/PT-BR interface translations.
- Frontend studio for title and chapter editing, scheduling, project teams and glossary management, subject to WordPress permissions.

The layout draws on Wuxiaworld, KakaoPage and Novelpia. The code, logo and demo illustrations are project assets; third-party brands and covers are not shipped. See the [full visual presentation](docs/design-v3.md).

| Catalog | Title |
|---|---|
| ![Catalog](screenshots/xin-v2-3/catalog-desktop.jpg) | ![Title](screenshots/xin-v2-3/title-desktop.jpg) |

| Mobile | Reader |
|---|---|
| ![Mobile](screenshots/xin-v2-3/home-mobile.jpg) | ![Reader](screenshots/xin-v2-3/reader-mobile.jpg) |

## Install

Requires WordPress 6.4+ and PHP 8.0+. PHP `mbstring`, image support and `ZipArchive` are needed for relevant text, cover and EPUB features. No frontend build step is required; fonts, styles and scripts are local.

1. Copy `themes/xin-v2` to `wp-content/themes/xin-v2` and activate **XI Community Library**.
2. Copy `plugins/xin-setup` to `wp-content/plugins/xin-setup` and activate **XIN Setup**.
3. Open the WordPress dashboard after activation/import. Setup creates missing pages and menus, binds templates, configures pretty permalinks, taxonomies and counters. Its report is under **Tools → Theme setup**.
4. Import your titles, chapters and covers with the appropriate importer, then inspect the catalog and reader.

[Detailed installation](docs/install.md) · [Automatic setup module](plugins/xin-setup/README.md) · [Import documentation](docs/import.md)

Apache needs a writable `.htaccess`; Nginx needs the usual WordPress `try_files` fallback. The plugin cannot change Nginx configuration. Existing editorial content and assigned menus are preserved.

## Repository

| Path | Purpose |
|---|---|
| `themes/xin-v2` | Primary 3.0 theme |
| `themes/xin-com` | Retained alternative theme |
| `plugins/xin-setup` | Automatic installation/import setup |
| `plugins/xi-novel-import`, `plugins/xi-from-fictioneer` | Import tools |
| `plugins/xi-novel-manager`, `plugins/xi-studio` | Existing management tools |
| `plugins/xin-turbo`, `plugins/xin-v2-kit` | Optional performance/content utilities |
| `screenshots/xin-v2-3` | Current design screenshots |
| `docs/media/covers` | Three generated demo covers and exact prompts |
| `tools/verify-release.php` | Local setup/rating regression checks |

Demo screenshots use local sample content. The repository does not contain the WordPress database, uploads, local accounts or passwords. Old screenshots remain as historical references; the new design is shown in `screenshots/xin-v2-3`.

## Validation

See [the 3.0 local validation report](docs/validation-v3.md). Automated checks cover setup idempotency, route configuration, imported rating totals, replacement votes and access/validation errors. Browser checks cover the actual desktop/mobile rendering and core reading/discovery interactions.

```bash
php tools/verify-release.php /path/to/disposable-wordpress/wp-load.php
```

Run this against a local or disposable installation: it activates setup, changes site settings and creates temporary fixtures that are moved to trash. Browser coverage is not a claim of compatibility with every hosting stack or third-party plugin.

GPL-2.0-or-later. See [LICENSE](LICENSE).
