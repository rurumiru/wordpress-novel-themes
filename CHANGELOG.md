# Changelog

All notable changes to this project are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Planned

- **Framework bake-off.** Bootstrap 5 is the current base, not the decision. The presentation layer will be measured against a built Tailwind subset, UnoCSS, Bulma, Pico.css and a no-framework build that relies only on the theme's own token system. Judged on gzip weight, render-blocking bytes, LCP on a mid-range phone, layout shift on the catalog grid, and reading comfort across a long session. Numbers and the verdict will be published here.
- Reader typography pass: measured line length, optical margins, per-language line rhythm.

Shared component markup (navbar, offcanvas, modal, forms, tabs) may change between betas. The data model, template hierarchy and hooks are stable.

## [0.3.1-beta] — 2026-08-21

### Fixed

- **The download menu on a title page was cut off.** The hero of a title clipped its own overflow, so that the blurred cover behind it would stay inside the block — and it clipped the EPUB / FB2 menu with it: only a strip of the dropdown showed under the buttons, the rest was cut at the edge of the hero. The clipping moved to the backdrop layer, which is the only thing that needed it, and the hero no longer cuts anything that opens out of it. The same construction on the author page was changed with it, so the next dropdown placed in a hero does not repeat the bug.
- On a phone that menu could also run past the right edge of the screen. It is now bounded by the viewport and centred under the button.

### Changed

- **The dates of a title carry the time.** *Added* and *Updated* in the sidebar are printed with the site's date **and** time formats — `20/08/2026, 15:35` rather than `20/08/2026` — and both sit inside a `<time datetime="…">` element with an ISO timestamp, so a browser and a crawler read the exact moment. No new translatable strings: the format comes from the WordPress settings.

## [0.3.0-beta] — 2026-08-20

### Added

- **A chapter editor of the theme’s own.** TinyMCE is gone from the studio; in its place is a `contenteditable` editor written for prose — about 600 lines, no npm, no vendor bundle. Pasting from Word, Google Docs or another site arrives clean: the markup is filtered down to a whitelist, `class`, `style` and `<span>` wrappers are dropped, `<div>` becomes a paragraph, `<b>`/`<i>` become `<strong>`/`<em>`, and a `javascript:` link loses its href. A scene break is one button, images come from the media library, and the block select covers paragraph, two heading levels and a quote. Below the toolbar: **find and replace** across the whole chapter, **tidy** — straight quotes to «», double hyphens to em dashes, `...` to an ellipsis, stray spaces away — and **the project glossary applied to the text in one press**. The footer counts words, characters and reading time; the draft is saved to the browser as you type and offered back if the tab died. Focus mode drops the site chrome and leaves the page. `Ctrl+B`, `Ctrl+I`, `Ctrl+K`, `Ctrl+H`, `Ctrl+S` and `Esc` do what you expect. The same editor, with a shorter toolbar, now edits a title’s description.
- **A glossary the translator keeps.** A project screen in the studio — *Chapters → Project glossary* — holds the rules as plain lines, `from = to`, so a list can be pasted in from a spreadsheet in one go. The rules travel to every reader of that title automatically and work exactly like the reader’s own, except a reader’s own rule always wins; the reader can switch the translator’s glossary off in one click, and sees it as a separate, read-only group in the panel. The same rules can be **written into the chapters themselves**: *Count the matches* runs a dry pass and reports how many hits sit in how many chapters, and *Write into the chapters* rewrites them for good. The engine that does it in PHP answers exactly like the JavaScript one — the same 24 cases are tested on both sides.
- **XI Studio — the theme studio, as a bundled plugin.** One admin screen: the knobs on the left, the live site in a frame on the right, and every movement of a slider visible immediately. Colour (accent, premium, and the neutral hue and saturation that rebuild the whole light and dark ladder from one tone), shape (corner radius, shadow depth, site width), fonts (system stacks only), and the reading defaults a new reader starts from. Five presets — graphite, paper, ink, neon, newsprint. The preview switches between the home page, the catalog, a title and a chapter, between desktop, tablet and phone widths, and between the light and dark schemes. Settings export and import as JSON, and everything is stored as ordinary theme mods: switch the plugin off and the site looks the same.

### Changed

- **The look of the theme became a set of declared knobs.** `inc/skin.php` holds one registry — name, type, range, default — and one CSS generator built from it. The customizer draws its controls from that registry (a new *Look* section), the studio plugin draws its screen from it, and the live preview asks the theme for the CSS over REST instead of keeping a second generator in JavaScript, so the preview cannot drift from the site. Adding a knob is one entry plus one line.
- **One replacement engine.** The matching code the reader glossary used moved into `assets/js/replace.js`, and the editor’s find-and-replace and glossary button now run on the very same code — longest rule first, no cascade, case carried over, Unicode word boundaries.
- **The translation builder covers plugins too.** `tools/build-translations.php` now walks a list of targets: the theme’s `xi-novels` domain and the plugin’s `xi-studio` domain, each with its own maps under `tools/i18n/`. The theme carries 815 strings in English and Brazilian Portuguese, the studio 36 more.
- Reading defaults (size, column width, leading, paper, reading font) are settings now, not constants in JavaScript: the reader takes them as its starting point and still lets each reader override everything.

## [0.2.0-beta] — 2026-08-20

### Added

- **A glossary in the reader — fix the terms yourself.** Select a word inside a chapter and an *Add to glossary* button appears next to the selection; type how it should read and every occurrence changes on the spot — in the text, the title, the contents and the chapter navigation, without a reload. A rule can ignore case, and then it carries the capitalisation over (`ye chen` stays as you typed it, `Ye Chen` gets a capital, `YE CHEN` goes all caps); it can demand a whole word, which understands Cyrillic and CJK, not only `\b`; and an empty replacement simply cuts the term. Longer rules win over shorter ones, and what a rule produced is never read again by another rule, so `A → B` next to `B → C` cannot cascade. Rules belong either to one title or to the whole site, live in `localStorage` — nothing is sent anywhere — and travel as a JSON file: *Save to a file* writes everything, *Load a file* merges it back and also accepts a hand-written `{"term": "reading"}` map. Highlighting the replacements is one switch away, and each highlight keeps the original word in its tooltip. Built for machine-translated releases, where a name changes spelling every other chapter and nobody edits it.
- **The interface speaks Brazilian Portuguese.** A third language next to Russian and English — 729 strings, compiled into `languages/pt_BR.mo`. The header switch is now RU / EN / PT, the *Main language* setting in the customizer and in the control panel lists every registered language instead of a hard-coded pair, and a locale added to `xin_languages()` shows up in all three places at once.
- **README in Brazilian Portuguese** — [README.pt-BR.md](README.pt-BR.md), linked from both other READMEs.

### Changed

- **Translations are built per locale.** The RU → EN map moved out of `tools/build-translations.php` into `tools/i18n/en_US.php`, joined by `tools/i18n/pt_BR.php`. The script now compiles `.po` and `.mo` for every map it finds, reports both what a map is missing and what it carries that the theme no longer uses, and exits non-zero when something is missing — so it works as a check, not only as a build. Adding a language is one copied file plus one line in `xin_languages()`.
- Reader panels (contents, reading settings, glossary) share one open / close mechanism, and the reader exposes `window.xinReader.open()` so another script can raise a panel of its own.
- The reader bar gained a glossary button; below 420 px the full-screen button steps aside so the chapter title keeps its room.

## [0.1.0-beta] — 2026-08-18

### Added

- **EPUB and FB2 export.** A *Download* button on every title builds a real e-book on the spot: cover, table of contents, chapters, metadata. EPUB 3 with `mimetype` first in the archive and a valid `content.opf`; FB2 with an embedded cover. Locked chapters land in the file only for readers who may read them, and the route is throttled per address.
- **Reading streaks and achievements.** Days in a row and chapters read are counted for signed-in readers, once per chapter per day. Ten achievements — first chapter, ten, fifty, a hundred, three / seven / thirty days in a row, a first project, a first release, ten releases — show on the profile. No points, no leaderboards, nothing to farm.
- **Co-authors on a project.** A title can list several translators: each of them adds and edits its chapters from the studio, the team shows on the title page, and a shared project appears in every member's project list. The field is in the studio and in the admin.
- **Paid chapters through WooCommerce.** A bridge, not a checkout: attach a product to a chapter and it opens for whoever bought it, right next to the PLUS route. The gate shows the price and an add-to-cart link. Without WooCommerce nothing changes.
- **Discussions — an optional module, off by default.** Turned on in the customizer or the control panel. Inside they are WordPress comments; outside there is nothing of WordPress about them: own markup, one level of replies, `||spoilers||`, `**bold**` and `_italic_`, likes over a REST route, author / team / PLUS badges, and no website field. Comments stay off — post types, admin menu and templates — while the module is off.
- **A separate import plugin** — `xi-novel-import.zip`: bulk chapter import from **.docx**, .txt, .md, .html, ZIP archives of them, and **Google Docs** by link. Chapter number and title are read from the file name, files are sorted naturally, re-running updates the same chapters instead of duplicating, and options cover the starting number, marking chapters from N as early access, draft or published, and the encoding of text files. The screen sits under *Tools → Chapter import* and reports what it created and what it updated.
- **An optional credit line** — *Customize → footer → “Running on XI Novels”*, off by default.

### Changed

- **Reader typography pass.** The column is measured in characters (`68ch`) rather than pixels, hanging punctuation is on, and the rhythm follows the language: Russian keeps justification, hyphenation and a paragraph indent; English drops the indent, sets ragged-right and a shorter leading. Block quotes, the drop cap and headings were retuned with it.
- Access to a locked chapter is decided in one place now — PLUS, a purchase, the project team or the author — so the reader, the contents list and the export all answer the same way.

## [0.0.10-beta] — 2026-08-18

### Fixed

- **After an update the site kept showing the old design.** Stylesheet and script URLs were served without a version, because the theme stripped `ver=` from every asset to hide the WordPress fingerprint. A browser that had the old CSS held on to it, and a page cache served the old markup on top of that. Theme assets now carry a fingerprint built from the version and the file's modification time, so one update invalidates them; `ver=` is still stripped from everything that is not the theme, so nothing leaks.
- **The theme now clears caches for you.** The first request after an update fires the purge hooks of LiteSpeed Cache, WP Rocket, W3 Total Cache, WP Super Cache and Autoptimize, and flushes the object cache. Nothing happens on later requests: the version stamp is stored.

### Added

- Both install guides gained a short section on what to do when a page still looks old: hard refresh, the panel's purge button, and where the plugin caches sit.

## [0.0.9-beta] — 2026-08-16

### Changed

- **New palette: white and graphite.** The crimson accent is gone; the light scheme is now near-white paper with a graphite accent, and the dark scheme is a neutral charcoal instead of a blue-black. Light is the default. The premium color that marks PLUS moved to the same neutral family, so nothing on a page shouts.
- **Buttons stopped being bubbles.** Pill-shaped buttons gave way to a 9–10 px radius, smaller padding, a smaller type size and a flat shadow that only lifts on hover. Icon buttons follow the same shape.
- **The site got narrower** — 1160 px instead of 1280 — with a tighter heading scale, so lines stay readable and the layout no longer sprawls on wide monitors.
- **The logo is the name.** The gradient tile is gone; the header and footer carry the site name alone, with the second word in the accent color. A custom logo still overrides it.
- **The home banner now sits inside the site container**: rounded, hairline border, 360 px tall by default, and its buttons and badges are legible against artwork.
- **The title page was rebuilt.** No more stacked cards: a compact header (cover, title, chips, one line of numbers, three actions), then flat sections separated by hairlines — description, contents with search and order, and a sidebar of facts, rating and similar titles. Chapters became a hairline list with monospace numbers.
- **The reader was rebuilt too**: a slimmer bar, a quieter title block, a hairline rule instead of a gradient bar, a calmer drop cap, flat chapter navigation, and a PLUS gate that reads as a panel instead of a dashed warning.

### Added

- **Control panel on the site** — `/manage/`, created on activation, for moderators and administrators:
  - **Overview** with users, PLUS members, titles, chapters and the number of records waiting for review;
  - **Users** — search, role changes (reader / contributor / author / moderator), PLUS access granted for 30, 90 or 365 days, with no expiry, or removed; adding days extends what is left instead of resetting it;
  - **Review queue** — publish, return to drafts or trash anything a contributor submitted;
  - **Titles** — every title with author, chapter count and status, publish or hide, jump to its chapters;
  - **Settings** — open registration, the role new accounts get, the main language and the default color scheme.
  - Administrators are never a target of role changes and you cannot change your own role, so the panel cannot lock you out.
- **PLUS access is a real membership.** Chapters marked PLUS now open for members instead of for anyone signed in; moderators and administrators always see them. The gate explains the difference to a signed-in reader and links to the PLUS page.
- **Main language is a theme setting** — customizer or the panel. It decides what a first-time visitor sees; the RU / EN switch in the header still wins for that visitor afterwards.
- English translation grew to 666 strings.

### Fixed

- Headings over banner artwork inherited the page color and turned dark on dark. They are now white, with a stronger gradient behind them.
- Sign-in page: with registration closed the footer line offered “already have an account? sign in” to someone already on the sign-in form. It is hidden in that case.

## [0.0.8-beta] — 2026-08-16

### Fixed

- **Every message on the login screen was replaced with “wrong username / password”.** The theme rewrote the `login_errors` filter unconditionally, so a visitor who pressed *Sign up* while registration was closed, or asked for a password reset, was told their credentials were wrong. The filter now touches only the three credential errors and passes everything else through untouched, so the real reason is finally visible.
- **The registration links led nowhere.** The PLUS page and the “become an author” page pointed at `wp_registration_url()` regardless of whether the site accepted registrations, which sent visitors to a screen that refused them. Every link now goes through the theme, and the button is hidden when registration is closed.
- **A signed-in reader without publishing rights saw “sign in to manage projects”.** The studio checked capability, not session, so the account that had just been created was told to sign in again. It now explains what the account can do and where to ask for the rest.

### Added

- **Sign-in, sign-up and password recovery on the site itself** — `/account/`, created on activation and restored if the page goes missing. One centered page, three states, in the theme’s own design: ambient gradient and grid, a card that rises on load, underline tabs, quiet inputs with a focus ring, square-cornered buttons. `/wp-login.php` is no longer linked anywhere on the front end but keeps working for administrators, and its title no longer ends with “— WordPress”.
- **Accounts section in the customizer**: open registration on or off, and the role new accounts get — author (publishes immediately), contributor (sends chapters to review) or reader. The theme’s form is independent of *Settings → General → Membership*, which only governs the core login screen.
- Registration signs the new account in at once and lands it in the studio; the page remembers where the visitor came from, so a locked PLUS chapter returns them to that chapter.
- Twelve failed attempts from one address pause the forms for fifteen minutes, and a hidden field that only bots fill in drops their submissions.
- English translation grew to 608 strings.

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
