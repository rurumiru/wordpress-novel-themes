# Changelog

All notable changes to this project are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Planned

- **Framework bake-off.** Bootstrap 5 is the current base, not the decision. The presentation layer will be measured against a built Tailwind subset, UnoCSS, Bulma, Pico.css and a no-framework build that relies only on the theme's own token system. Judged on gzip weight, render-blocking bytes, LCP on a mid-range phone, layout shift on the catalog grid, and reading comfort across a long session. Numbers and the verdict will be published here.
- Reader typography pass: measured line length, optical margins, per-language line rhythm.

Shared component markup (navbar, offcanvas, modal, forms, tabs) may change between betas. The data model, template hierarchy and hooks are stable.

## [0.0.7-beta] — 2026-08-15

### Added

- **The demo content is now a plugin.** `xi-demo-content.zip` installs under *Plugins → Add New → Upload* and adds a screen at *Tools → Demo content* with two buttons: install and remove. Options on the screen cover skipping the generated images and creating everything as drafts. No SSH, no command line.
  - The screen reports how many demo records are on the site right now and warns when the theme is not active, instead of failing halfway.
  - Both entry points — the plugin and the CLI scripts — share one implementation in `demo/plugin/xi-demo-content/importer.php`, so they cannot drift apart.

### Fixed

- **Installing the theme failed on servers where the old copy was uploaded by another user.** Documented in [docs/install.ru.md](docs/install.ru.md): WordPress deletes the previous theme folder before copying the new one, and when those files belong to a different system user the update stops with "some files could not be copied". The guide now carries the ownership and permission commands that resolve it, a panel-only route and the fallback of deleting the folder.
- Made it explicit in the demo documentation that the demo package is a plugin, not a theme: uploading it under *Appearance → Themes* produces the misleading "missing style.css" error.

## [0.0.6-beta] — 2026-08-15

### Fixed

- **The theme zip would not install.** Release archives were packed with PowerShell's `Compress-Archive`, which writes entry paths with backslashes; WordPress then unpacked `xi-novels\style.css` as one long file name, found no theme folder and reported a missing stylesheet. Archives are now built by `tools/build-zip.php` (PHP `ZipArchive`, forward slashes, one root folder), and the script verifies its own output before finishing. Both release assets were rebuilt.
- Dropped `threaded-comments` from the theme tags — the theme ships without discussions — and added `custom-colors` and `editor-style`, which it actually supports.

### Added

- **Demo content package** in `demo/`. One command fills a fresh install so the theme can be judged with a full catalog instead of empty states:
  - 12 titles with synopses, descriptions, genres, tags, release statuses, author and translation credits, ratings and view counts, five of them marked as editor's picks;
  - 48 chapters (4 per title) dated across recent weeks so the update feed shows a real timeline, with the last chapter of each title marked PLUS;
  - 12 genres, 12 tags, 5 blog posts, 3 home banners;
  - covers (800×1200) and wide artwork (1920×720) drawn at import time with GD — gradients and arcs, no third-party images.
- **Reversible removal.** Everything the importer creates, including generated images, carries the post meta `_xin_demo = 1`; `demo/remove-demo.php` deletes exactly those records and nothing else, with `--dry-run` and `--trash` modes.
- Re-running the import updates the same records instead of duplicating them: titles match by slug, chapters by number.

### Changed

- **Interface icons replaced.** The hand-drawn sprite gave way to [Lucide](https://lucide.dev) (54 icons, ISC) plus brand marks from [Simple Icons](https://simpleicons.org) (CC0). The sprite in `inc/icons.php` is now generated from the upstream SVG files, so the shapes are consistent and a couple of malformed paths are gone. Stroke width dropped to 1.75 so small sizes stay readable. Licences are recorded in `assets/vendor/ICONS-LICENSE.md`.
- **Scroll work no longer fights the compositor.** The header and the back-to-top button shared two separate scroll listeners that rewrote a class on every event, forcing a style recalculation on an element with a backdrop filter. They are now one listener, throttled with `requestAnimationFrame`, writing only when the state actually changes. In the reader the progress bar updates on whole percent steps instead of four decimal places, turning three DOM writes per frame into one.

## [0.0.5-beta] — 2026-08-15

### Added

- **Chapter import from text files and ZIP archives.** Point the importer at a folder or an archive of `.txt`, `.html` or `.md` files and it turns them into chapters:
  - the file name gives the number and the chapter title — `001. The shard.txt`, `002 - First snow.txt`, `012.5_Side story.html`, `Chapter 3 - The debt.md`, `Глава 4. Ночной гость.txt`, or a bare name numbered by file order;
  - files are sorted naturally, so `2` comes before `10`;
  - plain text becomes paragraphs on blank lines with single breaks kept as `<br>`, Markdown headings and emphasis are converted, HTML documents are reduced to their `<body>`;
  - content is read as UTF-8, falls back to `windows-1251` for older exports, and any encoding can be forced with `--encoding=`;
  - sub-folders inside an archive mean one title per folder, named after the folder;
  - new options: `--from-dir`, `--from-zip`, `--novel`, `--novel-slug`, `--novel-id`, `--start`, `--locked-from`, `--encoding`.
- Re-running a file import updates the matching chapters instead of adding copies. Archives are unpacked into a temporary folder and cleaned up afterwards; entries pointing outside the archive and archiver leftovers are skipped.
- Both import guides gained a full section on the new mode, with tables of supported extensions, file-name patterns, the multi-title archive layout and encoding behaviour.

### Changed

- Wording pass across the documentation: the troubleshooting tables now describe what a reader actually sees ("the import stops halfway, the page is blank") instead of engine jargon.

## [0.0.4-beta] — 2026-08-15

### Added

- **Bulk importer** — `tools/import-novels.php`. Takes a JSON or CSV manifest and creates or updates titles and chapters, downloads covers and wide artwork from URLs or local paths, and fills every meta field and taxonomy. Idempotent: titles match by slug, chapters by number inside the title, so re-running a manifest updates instead of duplicating. Term counting and cache invalidation are deferred during the run. Options for author, post status, skipping media and a dry run.
- **[Import & heavy uploads](docs/import.md)** ([RU](docs/import.ru.md)) — the data model to import into, both manifest formats, WP All Import column mapping, WP-CLI recipes, and a complete list of the server limits that must be raised before large covers will upload: PHP (`upload_max_filesize`, `post_max_size`, `memory_limit`, `max_execution_time`, `max_input_time`, `max_input_vars`) with the four places to set them, nginx `client_max_body_size` and timeouts, LiteSpeed request body size, the Cloudflare 100 MB ceiling, `WP_MEMORY_LIMIT` / `WP_MAX_MEMORY_LIMIT`, the `upload_size_limit` and `big_image_size_threshold` filters, recommended cover dimensions, import speed tips and a troubleshooting table.
- Documentation index restored in both READMEs, now listing the import guide.

## [0.0.3-beta] — 2026-08-15

### Added

- **Landing pages that ship with the theme.** Three new page templates, created automatically on activation:
  - *Become an author* — four-step onboarding, six benefit cards, an FAQ accordion, live platform numbers and CTAs that change depending on whether the visitor is signed in.
  - *PLUS* — membership landing with benefit cards, a two-plan comparison table, a live list of chapters already in early access and an editable "how to join" block.
  - *Info page* — reusable template for help, rules, contacts and policies, with a sidebar listing every other info page, quick links and social buttons. Ships as **Help**, **Site rules** and **Contacts**.
- **Full author profile.** Cover image (own upload or artwork from the author's titles), tagline, bio, social links, a support button, a podium of the three most-read titles, tabs for projects / chapters / articles, and a feed of the author's latest chapters. Empty own profile gets a "create your first project" call to action.
- **Profile fields in the admin** — tagline, cover image ID, Telegram, VK, Discord, website and a donation link, all editable on the user profile screen.

### Changed

- **The title page backdrop stepped back.** The blurred artwork behind a title's header ran at 45% opacity and fought the cover and the buttons; it now sits at 22% under a stronger gradient, so the content in front of it reads first.
- **Buttons reworked.** Softer primary shadow that lifts on hover and disappears on press, a real focus ring, a subtle press shift instead of a scale jump, consistent heights across sizes, a visible surface on outline buttons and a proper disabled state.
- English translation grew to 557 strings.

## [0.0.2-beta] — 2026-08-15

### Added

- **Banners are now managed in WordPress.** A dedicated *Banners* section in the admin: image, title, subtitle, text, link, button label, badge, text position, optional mobile image, and display order through Page Attributes. The home slider uses them when present and falls back to titles with wide artwork when the section is empty.
- **Banner height** is a customizer option (220–900 px).
- **Quick links under the banner** come from a menu location — *Quick links (tiles under the banner)*. Icons are picked per item with an `icon-<name>` CSS class, `gold` marks the premium tile; without a menu the theme falls back to its defaults.

### Changed

- **New brand mark.** The flat pink letter tile is gone; the header and footer now carry a soft squircle with a book glyph drawn from the accent and premium colors. A custom logo still overrides it.
- **Native WordPress widgets are styled.** Search (classic and block), tag cloud, calendar, categories dropdown, archives, latest posts and comments, RSS, and the block buttons — all follow the theme tokens instead of browser defaults.
- **Avatars are round everywhere** — profile header, author cards, blog meta, studio.

## [0.0.1-beta] — 2026-08-15

First public beta. Everything below works end to end; the presentation layer is still under evaluation.

### Fixed since the first push

- **Studio submissions no longer travel through `/wp-admin/admin-post.php`.** Project and chapter forms post to the studio page itself and are handled on `template_redirect`. That route survives hosts blocking direct access to `admin-post.php`, security plugins filtering it, and page caches serving a stale nonce — which is where creating a title broke on production.
- Failures now return as a readable notice inside the studio instead of a bare WordPress error page: expired session, upload larger than `post_max_size` (the case where PHP discards the entire POST), missing permission, missing project.
- Signed-out submissions redirect to the login form and come back to the studio afterwards instead of dying with `0`.

### Platform

- Custom post types `novel` and `chapter` with a numeric-meta ordering model that survives thousands of chapters per title.
- Taxonomies `genre`, `novel_tag`, `novel_status`, with four release statuses seeded on activation.
- Catalog with genre chips, status filter, five sort orders and pagination that keeps the query.
- Update feed grouped into a Today / Yesterday / date timeline.
- Ranking block: podium for the top three, list to tenth place, three competing orders in one component.
- Anonymous star rating through a single REST route.
- View counters for titles, chapters and posts, guarded by a cookie against reloads.

### Reader

- Full-screen chapter template with no site header or footer.
- Auto-hiding top bar, contents drawer, progress dock, `←` / `→` paging.
- Reading settings — size, line height, column width, serif/sans, four paper themes — stored per browser and applied site-wide.
- Reading history and per-title read markers in `localStorage`, surfaced on the home page as *Continue reading*.

### Authors

- Front-end studio: create and edit projects and chapters without `/wp-admin`.
- WordPress visual editor with media upload and a code tab.
- Browser-side draft autosave, live word count, pre-filled chapter numbers, fractional numbering.
- Early-access (PLUS) chapters locked for guests.
- Public author profiles with statistics and tabs.

### Design

- Bootstrap 5.3.3 bundled locally and re-skinned through the theme's HSL design tokens.
- Dark and light schemes with contrast-checked palettes; dark by default, no flash on load.
- Navbar with dropdowns, offcanvas mobile menu, search modal, bottom navigation on phones.
- Home page composed of twelve independently switchable blocks.

### Localization

- Russian source strings, compiled English translation (423 strings).
- RU / EN switch in the header, remembered in a cookie.
- Relative dates rendered by the theme so they do not depend on core language packs.

### Privacy and fingerprinting

- Admin bar disabled; generator, RSD, wlwmanifest, shortlink, oEmbed, emoji, X-Pingback and asset version strings removed.
- REST namespace served from `/api/` instead of `/wp-json/`.
- Login screen restyled to the site brand with a neutral error message.
- Zero external runtime requests: no CDN, no web fonts, no analytics.

### Notes

- Comments are removed by design across templates, post-type support and the admin menu.
- No build step, no npm or composer dependencies.
