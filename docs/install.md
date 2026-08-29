# Installation

## Requirements

| | Minimum | Recommended |
|---|---|---|
| WordPress | 6.4 | 6.6+ (tested through 7.0) |
| PHP | 7.4 | 8.1–8.3 |
| Database | MySQL 5.7 / MariaDB 10.3 | MySQL 8 / MariaDB 10.6 |
| PHP extensions | `gd` (covers), `mbstring` | `imagick` for sharper thumbnails |

No composer, no node, no cron jobs, no external services.

## 1. Install the theme

**From this repository**

```bash
git clone https://github.com/rurumiru/wordpress-novel-themes.git
cp -r wordpress-novel-themes/themes/xin-com /path/to/wordpress/wp-content/themes/
```

**As a zip** — download the repository, zip `themes/xin-com`, then upload it under *Appearance → Themes → Add New → Upload Theme*.

Activate under **Appearance → Themes → XIN-Com**.

## 2. What activation does

* creates the pages **Author studio** (`/dashboard/`) and **My library** (`/library/`) with the right page templates;
* seeds release statuses: Ongoing, Completed, On hiatus, Announced;
* registers the `novel` and `chapter` post types plus the `genre`, `novel_tag`, `novel_status` taxonomies;
* flushes permalinks.

## 3. Settings to check

| Where | What |
|---|---|
| Settings → Permalinks | **Post name**. Titles live at `/novels/<slug>/`, chapters at `/read/<slug>/` |
| Settings → Reading | Front page: *Your latest posts* is fine — the theme renders its own home page. If you prefer a static front page, set one and point *Posts page* at a “Blog” page |
| Settings → Discussion | Nothing to do: comments are disabled by the theme everywhere |
| Appearance → Menus | Create a menu and assign it to **Primary menu** (and optionally *Footer menu*, *Legal links*) |
| Appearance → Customize | Accent colors, default scheme, home-page blocks, footer text, social links |

## 4. First title

1. **Novels → Add new.** The title, synopsis (excerpt) and full description are the visible parts.
2. Set a **cover** as the featured image — 2:3 works best (800×1200).
3. Optional **wide artwork** in the sidebar box — used by the home banner and large blocks (1920×720).
4. Pick **genres**, **tags** and a **release status**.
5. **Chapters → Add new**, choose the novel, set a number, publish.

Or do all of it from the front end: **Author studio** → *New project* → *Add chapter*. See [authoring.md](authoring.md).

## 5. Preview without MySQL (dev sandbox)

Useful for evaluating the theme or developing on a laptop.

```bash
# 1. a WordPress copy
curl -O https://wordpress.org/latest.zip && unzip latest.zip

# 2. the official SQLite drop-in
curl -L -o sqlite.zip https://downloads.wordpress.org/plugin/sqlite-database-integration.zip
unzip sqlite.zip -d wordpress/wp-content/plugins/
cp wordpress/wp-content/plugins/sqlite-database-integration/db.copy wordpress/wp-content/db.php
# in db.php replace {SQLITE_IMPLEMENTATION_FOLDER_PATH} with sqlite-database-integration
# and {SQLITE_PLUGIN} with sqlite-database-integration/load.php

# 3. the theme
cp -r themes/xin-com wordpress/wp-content/themes/

# 4. wp-config.php with any DB_* values (SQLite ignores them) and
#    define( 'WP_HOME', 'http://localhost:8080' );
#    define( 'WP_SITEURL', 'http://localhost:8080' );

# 5. run
php -S localhost:8080 -t wordpress tools/dev-router.php
```

Open `http://localhost:8080`, finish the five-minute install, activate the theme.

`tools/dev-router.php` is only needed because PHP’s built-in server does not know about pretty permalinks; Apache, nginx and LiteSpeed need nothing extra.

## 6. Production notes

* **Caching.** Any page cache works. Views are counted through a cookie guard, so a cached page still counts a first visit correctly once the cache is bypassed for logged-in users.
* **Images.** The theme registers `xin-cover` (320×480), `xin-cover-lg` (520×780), `xin-cover-sm` (120×180), `xin-banner` (1920×640) and `xin-wide` (720×405). After importing existing media, regenerate thumbnails.
* **Hiding WordPress further.** The theme already strips generator tags, RSD/wlwmanifest links, oEmbed discovery, emoji, X-Pingback, asset version strings, and moves REST to `/api/`. To also hide `/wp-content/` in asset URLs, add to `wp-config.php`:

  ```php
  define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/assets' );
  define( 'WP_CONTENT_URL', 'https://example.com/assets' );
  ```

  and rename the folder accordingly.
* **Uploads.** Long chapters are text, but covers are not: keep `upload_max_filesize` at 8M or more so authors can attach artwork from the studio.

## Troubleshooting

| Symptom | Fix |
|---|---|
| Chapter URLs return 404 | Re-save permalinks (Settings → Permalinks) |
| The studio says “Not allowed” | The user needs at least the *Author* role to publish; *Contributor* submits for review |
| Covers look stretched | Use 2:3 images, then regenerate thumbnails |
| The visual editor does not appear in the studio | Rich editing is off in the user profile, or the request came from a browser WordPress does not recognise |
| Home page shows the empty state | The catalog has no published titles yet |

## The site still looks old after an update

The theme updated but the page did not change — that is almost always a cache, not the installation.

1. **Check that the new version is really on the server.** Open `https://your-site/wp-content/themes/xin-com/style.css`; the version sits in the file header.
2. **Reload without the cache**: `Ctrl` + `F5` (`Cmd` + `Option` + `R` in Safari).
3. **Purge the site cache.** LiteSpeed Cache: *LiteSpeed Cache → Dashboard → Purge All*. WP Rocket: *Settings → Clear cache*. W3 Total Cache: *Performance → Purge All Caches*. With Cloudflare in front, also *Caching → Purge Everything*.
4. **The host has its own cache.** Control panels built on LiteSpeed keep a server-side copy that outlives the browser one; purge it from the panel.

Since 0.0.10 the theme purges the common cache plugins itself on the first request after an update, and its stylesheet URLs carry a fingerprint of the file, so browsers pick up new CSS on their own. If a page is still old after that, something in between is holding it — a CDN or a proxy at the host.

