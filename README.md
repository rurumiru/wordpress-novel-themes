<div align="center">

<h1>XI&nbsp;Novels</h1>

<h3>Stop running a blog. Run a novel platform.</h3>

<p>
A free, GPL, zero-dependency WordPress theme that turns a plain install into a full<br>
light-novel · web-novel · ranobe site — catalog, chapters, a distraction-free reader,<br>
rankings, an author studio, a reader library and a reader hub.<br>
<b>And it never looks like WordPress.</b>
</p>

[![Live demo](https://img.shields.io/badge/Live_demo-xi.community-f59e0b?style=for-the-badge)](https://xi.community)
[![Install](https://img.shields.io/badge/Install-two_minutes-2ea44f?style=for-the-badge&logo=wordpress&logoColor=white)](#install)
[![Docs](https://img.shields.io/badge/Docs-read-21759b?style=for-the-badge)](docs/)
[![Changelog](https://img.shields.io/badge/Changelog-beta_0.7.0-6366f1?style=for-the-badge)](CHANGELOG.md)

<br>

![Version](https://img.shields.io/badge/version-beta%200.7.0-f59e0b?style=flat-square)
[![License](https://img.shields.io/badge/license-GPL--2.0--or--later-e1173f?style=flat-square)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-6.4%20%E2%86%92%207.x-21759b?style=flat-square&logo=wordpress&logoColor=white)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.3-7952b3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)

![Build step](https://img.shields.io/badge/build%20step-none-2ea44f?style=flat-square)
![npm dependencies](https://img.shields.io/badge/npm%20dependencies-0-2ea44f?style=flat-square)
![External runtime calls](https://img.shields.io/badge/external%20runtime%20calls-0-2ea44f?style=flat-square)
[![i18n](https://img.shields.io/badge/interface-RU%20%2F%20EN%20%2F%20PT--BR-3b82f6?style=flat-square)](#languages)
[![PRs welcome](https://img.shields.io/badge/PRs-welcome-brightgreen?style=flat-square)](#contributing)

**English** · [Русский](README.ru.md) · [Português&nbsp;(BR)](README.pt-BR.md)

[📢 Telegram channel](https://t.me/licht_re) · [💬 Community chat](https://t.me/xicommunity)

</div>

![Home page](screenshots/01-home.jpg)

> [!NOTE]
> **Beta 0.7.0 — the platform works end to end.** You can install it today and publish titles and chapters. What is not final is the **presentation layer**: Bootstrap 5 is the current base, not the destination.

> [!TIP]
> **Live demo — [xi.community](https://xi.community).** A real site running this theme: browse the catalog, open a title, try the reader and its settings.


<details>
<summary><b>What is still moving, and what is already stable</b></summary>

<br>

The plan is to benchmark alternatives to Bootstrap and keep whichever wins on real numbers.

**What gets measured before a framework stays:** payload after gzip, render-blocking bytes, Largest Contentful Paint on a mid-range phone, layout shift on the catalog grid, and how the reader feels during a 40-minute session — line rhythm, contrast at night, how quickly settings apply.

**Candidates on the bench:** Bootstrap 5 (now) · Tailwind with a build-free CDN-less subset · UnoCSS · Bulma · Pico.css · plain CSS with only the theme's own tokens and no framework at all.

Expect the markup of shared components (navbar, offcanvas, modal, forms, tabs) to change between betas. **The data model, template hierarchy and hooks are already stable** — themes built on top of them will survive the swap. Benchmarks and the decision will land in [CHANGELOG.md](CHANGELOG.md); measurements from your own installs are welcome in issues.

**About the demo:** xi.community runs on the WordPress build until the team's own platform on **Elixir** ships. Once that launches, the site moves over to it and this theme stays here as the WordPress implementation — free, GPL and maintained on its own track.

</details>

---

## Why a novel site is not a blog

Novel sites are their own genre of website. A title has chapters, chapters have order, readers return for the next one, authors publish several times a week, and everyone reads at night on a phone. WordPress out of the box gives you posts and categories — which fits exactly none of that.

Every other answer is either a **$59–$99 marketplace theme** welded to a page builder, or a **SaaS that owns your readers**. This repository is the third option: the entire platform as one theme you can read, fork, rename and ship — for free, forever.

> **No page builder. No “pro version”. No subscription. No npm. No CDN. No phone-home.**

<table>
<tr>
<td width="33%" align="center">

📚<br><b>Catalog & rankings</b><br>
<sub>Covers, genres, filters that survive pagination, and a weighted ranking page of its own</sub>

</td>
<td width="33%" align="center">

📖<br><b>Full-screen reader</b><br>
<sub>No chrome, four papers, glossary, paragraph tools, read-aloud in a device voice</sub>

</td>
<td width="33%" align="center">

✍️<br><b>Author studio</b><br>
<sub>Projects, chapters and a prose editor built for chapters — never a trip to <code>/wp-admin</code></sub>

</td>
</tr>
<tr>
<td align="center">

🛠️<br><b>Control panel on the site</b><br>
<sub><code>/manage/</code> — users, roles, PLUS access, the review queue, every title, the settings</sub>

</td>
<td align="center">

📥<br><b>EPUB & FB2</b><br>
<sub>Any title downloads as a proper e-book, with locked chapters honoured per reader</sub>

</td>
<td align="center">

🛰️<br><b>Reader Hub</b><br>
<sub><code>/hub/</code> shows the site from the inside — talk, arguments, what is being read now</sub>

</td>
</tr>
<tr>
<td align="center">

🎨<br><b>Theme studio</b><br>
<sub>Knobs on the left, the live site on the right; colour, shape, fonts, reading defaults</sub>

</td>
<td align="center">

🌍<br><b>Three languages</b><br>
<sub>RU / EN / PT-BR end to end — theme, studio and both bundled plugins</sub>

</td>
<td align="center">

🚫<br><b>Zero dependencies</b><br>
<sub>No build step, no npm, no CDN, no tracker — PHP, CSS and JS you can read in an evening</sub>

</td>
</tr>
</table>

---

## What landed recently

| | |
|:--|:--|
| **0.7.0** | **Bootstrap is gone, the look is rebuilt.** The framework was used at 3.1% and cost 54.5 KB gzipped on every page; 8.2 KB of the theme's own CSS and JS replace it without a single template edit. On top of that, a library reading room: white sheets, serif type, one colour for actions, a two-column table of contents |
| **0.6.0** | **Book downloads close behind a role.** The control panel decides who may export EPUB and FB2: everyone, any signed-in reader, PLUS holders, “PLUS or selected roles”, or selected roles only — checkboxes over the site roles, plugin-made ones included. The button and the direct link ask the same check |
| **0.5.0** | **Reader Hub** at `/hub/` — one page that shows the site from the inside: who talks, what they argue about, what is being read right now. Six site counters in the header, both metrics at once on the leaderboard, a profile card with a bar to the next level. Drawn as a terminal — grid and sweep on the canvas, cut corners, monospace readouts, segmented bars — and it collects nothing new about anyone beyond a 40-entry ring of recent reads |
| **0.4.0** | **Paragraph tools and on-device text-to-speech.** Click a paragraph: bookmark it in one of four colours, link straight to it, quote it into the discussion, suggest an edit with a live diff, or have the chapter read aloud from that point. **Queued chapters say when they go out**, and the release schedule moved into the author’s own project settings |
| **0.3.3** | **Rankings became a page of their own** at `/ranking/`: three boards, three time windows, a genre filter and a weighted score, so one five-star vote cannot outrun four hundred honest ones. **Bulk title management** arrived as a bundled plugin |
| **0.3.0** | **A chapter editor of the theme’s own** instead of TinyMCE, a **project glossary** the translator keeps, and **XI Studio** — the theme studio with a live preview of the site beside the knobs |

Every release, in full: **[CHANGELOG.md](CHANGELOG.md)**.

---

## Features

<details open>
<summary><b>📖 &nbsp; For readers</b></summary>

| | |
|:--|:--|
| 📚 **Catalog** | Covers, genres, tags, release status, sorting by views / rating / freshness, filters that survive pagination |
| 📖 **Full-screen reader** | No site header, no footer, no sidebar. Auto-hiding top bar, contents drawer, progress dock, `←` / `→` paging |
| 🎨 **Reading settings** | Font size, line height, column width, serif / sans, four paper themes (site / white / sepia / night) — saved per browser, applied to every chapter |
| 🔤 **Glossary in the reader** | Rename anything while you read: select a word, type how it should read, and every chapter follows — any case or exact case, whole word or not, for one title or for the whole site. Kept in the browser and exportable as a file, so a machine-translated release gets fixed once and passed on |
| 🗣️ **Paragraph tools and read-aloud** | Click a paragraph: bookmark it in one of four colours, link to it, quote it, suggest an edit with a live diff, or have the chapter read aloud from there in a voice already installed on the device — rate, pitch, volume and a preview included |
| 🔖 **Library without an account** | Bookmarks, reading history and “continue reading” live in `localStorage` |
| 🕒 **Updates feed** | Every fresh chapter on the site, grouped into a Today / Yesterday / date timeline |
| 🏆 **Rankings** | A page of their own at `/ranking/`: three boards — score, views, chapter count — three time windows and a genre filter. The top three stand on a podium, the rest run as rows with a bar against the leader |
| 🛰️ **Reader Hub** | `/hub/` shows the site from the inside — talk, arguments, what is being read right now, six counters and a leaderboard. Styled as a terminal, and every animation stops under `prefers-reduced-motion` |
| 🌙 **Light, dark, or system** | Light by default, dark and “follow the system” one switch away in the header — and no white flash on load |
| 📥 **EPUB and FB2** | Any title downloads as a proper e-book — cover, table of contents, chapters. Locked chapters are included only for readers who may read them. Who may download at all — everyone, signed-in readers, PLUS holders or selected roles — is set in the control panel |
| 🏅 **Streaks and achievements** | Days in a row, chapters read, ten quiet achievements on the profile — no points, no leaderboards |
| 🔑 **Sign-in on the site itself** | Sign in, sign up and password recovery on one centered page in your own design — readers never see `/wp-login.php` |
| 🌍 **RU / EN / PT-BR interface** | Language switch in the header, remembered in a cookie |

</details>

<details>
<summary><b>✍️ &nbsp; For authors and translators</b></summary>

| | |
|:--|:--|
| ✍️ **Author studio on the front end** | Create projects and chapters without ever opening `/wp-admin` |
| 🧰 **An editor built for chapters** | The theme’s own editor, not TinyMCE: paste from Word arrives clean, a scene break is one button, «tidy» fixes quotes, dashes and stray spaces, find-and-replace works across the chapter, and focus mode drops everything but the page |
| 🔤 **Project glossary** | Keep the names of the project in one list and every reader gets them automatically — or write them into the chapters in one pass, with a dry run that counts the matches first |
| 💾 **Drafts that survive** | Chapter text auto-saves to the browser while you write; live word count |
| 🔢 **Chapter numbering** | Next number pre-filled; fractional numbers (`12.5`) for side stories |
| 🗓️ **Release schedule and queue** | Days of the week and a release time sit in the project settings, with a summary of how many chapters wait and when the next one goes out. A queued chapter carries a “Queued” badge, its release date and the time left |
| 👑 **Early access** | Mark chapters as PLUS — locked for guests, badged in the contents |
| 🧑‍🎤 **Public profiles** | Author page with stats and tabs: projects / chapters / articles |

</details>

<details>
<summary><b>🛠️ &nbsp; For the owner</b></summary>

| | |
|:--|:--|
| 🕵️ **Nothing screams WordPress** | Admin bar off; generator, RSD, wlwmanifest, shortlink, oEmbed, emoji, X-Pingback and asset version strings stripped; REST moved from `/wp-json/` to `/api/`; login page restyled in your brand |
| 🛠️ **Control panel on the site** | `/manage/`: users and roles, PLUS access with an expiry date, the review queue for contributor submissions, every title, and the site settings — no `/wp-admin` needed |
| 🎨 **Theme studio** | A bundled plugin: one screen with the knobs on the left and the live site on the right. Colour, corner radius, shadows, site width, fonts and the reading defaults — every change visible before it is saved, five presets, JSON export |
| 🎛️ **Customizer** | The same knobs without the plugin, plus twelve home blocks you can switch off one by one, footer text, social links |
| 👥 **Co-authors** | A project can carry several translators; each of them adds and edits its chapters, and the team shows on the title page |
| 🛒 **Paid chapters** | A bridge to WooCommerce: attach a product to a chapter and it opens after purchase, next to PLUS |
| 💬 **Discussions (optional)** | Off by default. When on: own markup, one level of replies, spoilers, likes, author and team badges — nothing that looks like WordPress comments |
| 👑 **PLUS access** | Grant a reader early access for 30 / 90 / 365 days or with no expiry; chapters marked PLUS open for them automatically |
| 🗂️ **Bulk title management** | A bundled plugin: filter by owner, genre, status, cover or 18+, select with Shift — or take everything the filter found — and then publish, retag, reassign the owner or the team, set one cover on the batch, grant PLUS across a title, export CSV or delete. Every id is re-checked against `current_user_can()`, so a doctored form cannot touch a stranger’s title |
| 🧩 **Own widgets** | “Novel picks” (views / rating / new / updated) and “Latest chapters” |
| 👥 **Accounts on your terms** | Registration toggle and the role new accounts get (author / contributor / reader) live in the customizer; repeated failures are throttled and a hidden field catches bots |
| 🌐 **Translation ready** | 982 strings in the theme and 257 more across the three bundled plugins — Russian source, compiled English and Brazilian Portuguese `.mo`, plus a build script |

</details>

---

## Screenshots

<sub>Every shot is the theme itself, running with the demo content from this repository. Nothing is mocked up.</sub>

<table>
<tr>
<td width="50%" valign="top">

<img src="screenshots/02-catalog.jpg" alt="Catalog: genre chips, filters and a grid of covers">

**Catalog** — genre chips, status filter, five sort orders, six covers per row.

</td>
<td width="50%" valign="top">

<img src="screenshots/03-novel.jpg" alt="Title page: header with cover, description, contents and sidebar">

**Title page** — a compact header, then flat sections: description, contents with search, and a sidebar of facts, rating and similar titles.

</td>
</tr>
<tr>
<td valign="top">

<img src="screenshots/04-reader.jpg" alt="Full-screen reader with the chapter text">

**Reader** — no site header, no footer, no sidebar. The bar hides while you read.

</td>
<td valign="top">

<img src="screenshots/06-reader-settings.jpg" alt="Reader settings panel">

**Reading settings** — size, leading, column width, serif or sans, four papers.

</td>
</tr>
<tr>
<td valign="top">

<img src="screenshots/14-account.jpg" alt="Sign-in and sign-up page">

**Sign in and sign up** on the site itself — one centered page for login, registration and password recovery.

</td>
<td valign="top">

<img src="screenshots/13-manage.jpg" alt="Control panel with the user list">

**Control panel** at `/manage/` — roles, PLUS access with a term, review queue, titles and settings.

</td>
</tr>
<tr>
<td valign="top">

<img src="screenshots/07-profile.jpg" alt="Author profile with tabs and statistics">

**Author profile** — cover, statistics, podium of the most-read titles, tabs.

</td>
<td valign="top">

<img src="screenshots/10-library.jpg" alt="Library page">

**Library** — bookmarks and history, kept in the browser.

</td>
</tr>
<tr>
<td valign="top">

<img src="screenshots/08-updates.jpg" alt="Update feed grouped by day">

**Updates** — every fresh chapter, grouped into Today / Yesterday / date.

</td>
<td valign="top">

<img src="screenshots/09-blog.jpg" alt="Blog with a lead story">

**Blog** — lead story, category pills, sidebar.

</td>
</tr>
<tr>
<td valign="top">

<img src="screenshots/05-reader-alt.jpg" alt="Reader in the dark scheme">

**Dark scheme** — neutral charcoal, switched from the header.

</td>
<td valign="top">

<img src="screenshots/12-mobile.jpg" alt="Mobile layout with bottom navigation">

**Mobile** — bottom navigation, one-column layout.

</td>
</tr>
</table>

---

<a name="install"></a>

## Install in two minutes

```bash
git clone https://github.com/rurumiru/wordpress-novel-themes.git
cp -r wordpress-novel-themes/themes/xi-novels /path/to/wordpress/wp-content/themes/
```

1. **Appearance → Themes → XI Novels → Activate.** On activation the theme creates the “Author studio” and “My library” pages and seeds release statuses (Ongoing / Completed / On hiatus / Announced).
2. **Settings → Permalinks → Post name.**
3. **Novels → Add new** — your first title.
4. Optional: switch on the bundled plugins in `plugins/` — the theme studio, bulk import, bulk title management.

<details>
<summary><b>Try it without a database</b></summary>

<br>

No MySQL? The repo ships a sandbox recipe: PHP’s built-in server plus the official SQLite drop-in, seeded with demo titles and chapters.

```bash
php -S localhost:8080 -t wordpress tools/dev-router.php
```

Step by step: **[docs/install.md](docs/install.md)** ([RU](docs/install.ru.md)).

</details>

---

## Documentation

| | |
|:--|:--|
| **[Installation](docs/install.md)** ([RU](docs/install.ru.md)) | Production install, dev sandbox on SQLite, permalinks, first title |
| **[Authoring](docs/authoring.md)** ([RU](docs/authoring.ru.md)) | The studio, chapter numbering, early access |
| **[Import & heavy uploads](docs/import.md)** ([RU](docs/import.ru.md)) | The bundled importer: JSON/CSV manifests **and folders or ZIP archives of `.txt` / `.html` / `.md` chapter files**, plus WP All Import and WP-CLI recipes, and every PHP / nginx / LiteSpeed / Cloudflare limit you must raise before large covers will upload |
| **[Customizing](docs/customizing.md)** | Design tokens, customizer options, child theme, hooks |
| **[Development](docs/development.md)** | File map, data model, template hierarchy, translations, coding style |
| **[Demo content](demo/README.md)** | A plugin that fills the site with 12 titles, 48 chapters, blog posts and banners from **Tools → Demo content**, and removes it again with one button; CLI scripts for servers with SSH |

---

## How it compares

| | This repo | Paid marketplace themes | Novel SaaS platforms |
|:--|:--|:--|:--|
| Price | **Free, GPL** | $59–$99 + renewals | Revenue share / monthly |
| Source you can read | **Yes, ~19k lines, commented API** | Obfuscated builder JSON | None |
| Page builder required | **No** | Usually yes | n/a |
| npm / composer / build | **None** | Often | n/a |
| External runtime calls | **Zero** | CDN fonts, trackers | Everything |
| Front-end author studio | **Yes** | Rare | Yes |
| Full-screen reader with settings | **Yes** | Rare | Yes |
| You own the readers and data | **Yes** | Yes | **No** |
| Looks like WordPress | **No** | Yes | n/a |

---

<a name="tech-stack"></a>

## Tech stack

Deliberately boring and dependency-light — you can read the whole thing in an evening.

| Layer | What is used | Why |
|:--|:--|:--|
| CMS | **WordPress 6.4+** (tested to 7.0), classic theme, no FSE | The site editor cannot express a reader, a studio or a ranking without ten plugins |
| PHP | **7.4+**, plain procedural WordPress API | No composer, no autoloader, no framework — drops into any host |
| CSS framework | **Bootstrap 5.3.3**, bundled locally in `assets/vendor/` | Grid, navbar, offcanvas, modal, dropdown, tabs, forms, pagination — accessible and battle-tested |
| Design layer | **Custom CSS with HSL design tokens** (`style.css`, `skin.css`, `pages.css`, `parts.css`) | Bootstrap is re-skinned through CSS variables; the light and dark ladders are built from measured contrast |
| JS | **Vanilla ES5**, ~3.2k lines + Bootstrap bundle | No build step, no npm, no framework |
| Data model | Two post types (`novel`, `chapter`), three taxonomies (`genre`, `novel_tag`, `novel_status`), post meta | Standard WordPress — your content stays portable |
| Editor | Own `contenteditable` editor, ~600 lines | A chapter needs paste cleanup, scene breaks and focus — not a page builder |
| Client storage | `localStorage` for library, history, reading settings, glossary, drafts | Readers keep their place without an account |
| REST | Three namespaced routes under `/api/xin/v1/` — `rate`, `like`, `skin` | Anonymous rating, discussion likes and the studio’s live preview, without a plugin |
| i18n | Gettext `.po` / `.mo` + build script | No translation plugin required |
| Icons | Inline SVG sprite in PHP | No icon font, no external request |
| Fonts | System stack | Nothing loaded from Google |
| Bundled plugins | **XI Studio**, **XI Novels Import**, **XI Novels Manager** — ~3.7k lines of PHP | Optional, every one of them: the theme runs alone, the plugins add the studio, bulk import and bulk management |
| Dev sandbox | PHP built-in server + **SQLite** drop-in | Preview without installing MySQL |

<details>
<summary><b>Repository layout</b></summary>

```
themes/xi-novels/      the theme — everything above lives here
  inc/                 post types, meta boxes, template tags, customizer,
                       widgets, author studio, i18n, nav walkers, cleanup
  template-parts/      home sections, catalog, studio screens
  assets/              css (7 files), js (5 files), vendor/bootstrap
  languages/           en_US and pt_BR, .po + .mo
demo/                  demo content: a plugin with two buttons, plus CLI scripts
plugins/               xi-studio (theme studio), xi-novel-import (bulk import),
                       xi-novel-manager (bulk title management), and notes on
                       which third-party plugins the project uses and why
tools/                 dev-server router, bulk importer, translation builder,
                       i18n/ with one RU -> locale map per language
docs/                  install, authoring, import, customizing, development
screenshots/           what it looks like
```

</details>

---

<a name="languages"></a>

## Languages

The interface ships in **Russian** (source strings), **English** (`languages/en_US.mo`) and **Brazilian Portuguese** (`languages/pt_BR.mo`) — 982 strings each, plus 257 more across the bundled plugins (studio 36, import 127, manager 94). A visitor switches with the RU / EN / PT control in the header and the choice is remembered in a cookie; **Customize → Brand → Main language** decides what a first-time visitor sees.

![The same home page in English](screenshots/11-home-en.jpg)

<sub>The same site, switched with <code>?lang=en</code> or the control in the header.</sub>

Adding a fourth language is one file — a PHP map of Russian source string to translation:

```bash
cp tools/i18n/en_US.php tools/i18n/de_DE.php
# translate the right-hand side of every line, then
php tools/build-translations.php
```

The script re-reads every translatable string in the theme, reports what a map is missing or no longer uses, and writes `.po` and `.mo` for every map in `tools/i18n/`. Register the locale in `xin_languages()` (`inc/i18n.php`) and it joins the header switch. WordPress’s own strings and date formats come from the site language pack — install it under **Settings → General** if the admin should speak that language too.

---

## Roadmap

Ideas that fit the “no dependencies” rule. Vote with 👍 in issues, or send a PR.

**Next**

- [ ] **Framework bake-off** — Bootstrap vs Tailwind subset vs UnoCSS vs Bulma vs Pico vs no framework, judged on gzip size, LCP on a mid-range phone, layout shift and reading comfort
- [ ] **Zero-CSS-framework build** as the likely endgame: the theme already carries its own token system, so a framework may end up being dead weight
- [ ] Reader typography pass: measured line length, optical margins, per-language line rhythm
- [ ] Additional locales: DE, ES, ID, VI

<details>
<summary><b>Done</b></summary>

<br>

- [x] **Reader Hub** — one page that shows the site from the inside: who talks, what they argue about, what people are reading right now. Styled as a terminal: grid and sweep on the canvas, cut corners, monospace readouts, segmented bars. Nothing new is collected about people except a 40-entry ring of recent reads
- [x] **Rankings as a page of their own** — three boards, three time windows, a genre filter and a weighted score
- [x] **Bulk title management** — filter, select with Shift and act on hundreds of titles at once, CSV out
- [x] EPUB / FB2 export for a whole title
- [x] Reading streaks and simple achievements
- [x] Translator teams (several authors per project)
- [x] Optional paid chapters via WooCommerce bridge
- [x] Bulk chapter import — `.txt`, `.md`, `.html`, `.docx` and ZIP batches, processed ten files at a time so shared hosting survives it. Export a Google Doc as `.docx` and it goes through the same path
- [x] Optional discussions module (opt-in, off by default)
- [x] Reader paragraph tools and on-device text-to-speech
- [x] Scheduled chapter queue with a release countdown
- [x] Three languages end to end: Russian, English, Brazilian Portuguese — theme, studio and both plugins

</details>

---

<a name="faq"></a>

## FAQ

<details>
<summary><b>Will it work on shared hosting?</b></summary><br>
Yes. It is a classic theme: PHP files, CSS, JS. No composer, no node, no cron.
</details>

<details>
<summary><b>Do I need any plugins?</b></summary><br>
No. Caching and anti-spam are optional — see <a href="plugins/README.md">plugins/</a>. The three plugins in this repository are optional too: the theme runs without them.
</details>

<details>
<summary><b>Can I sell a site built on it?</b></summary><br>
Yes. GPL. Rename it, rebrand it, charge for it — no attribution required.
</details>

<details>
<summary><b>Can I use the design without WordPress?</b></summary><br>
The CSS and JS are additionally offered under MIT, so yes.
</details>

<details>
<summary><b>Does it work with the block editor?</b></summary><br>
Chapters and titles use the classic editor by design (authors write long text). Pages and blog posts work with blocks normally.
</details>

<details>
<summary><b>How many chapters can it handle?</b></summary><br>
Chapter lists are cached and sorted by numeric meta; sites with thousands of chapters per title are the design target.
</details>

<details>
<summary><b>Is there a demo?</b></summary><br>
Yes — <a href="https://xi.community">xi.community</a> is a live site running this theme. You can also clone the repo and run the sandbox: it seeds a demo catalog in one command, and every screenshot above comes from it.
</details>

---

## Using it? A link back is nice

The licence asks for nothing beyond the GPL: use the theme commercially, fork it, rebrand it, sell services around it. But if your site runs on it and you mention that somewhere — in the footer, in an about page, in a post — it genuinely helps the project stay alive.

The theme ships a ready line: **Customize → footer → “Running on XI Novels”**, off by default. Or paste your own:

```html
<a href="https://github.com/rurumiru/wordpress-novel-themes">Running on the XI Novels theme</a>
```

**Want to support the work?** Come to Telegram — [📢 channel](https://t.me/licht_re) and [💬 community chat](https://t.me/xicommunity). Bug reports, screenshots of your site, feature ideas and plain thanks are all welcome; every version is discussed there first.

---

<a name="contributing"></a>

## Contributing

Issues and pull requests are welcome. Two rules: **no build step** (the theme stays editable with a text editor) and **no external runtime dependencies**.

Merged work is credited by name in [CHANGELOG.md](CHANGELOG.md) — the reader’s paragraph tools and the voice studio came in that way, from [@HeavenlyCatCodes](https://github.com/HeavenlyCatCodes).

## License

**GPL-2.0-or-later** for the theme as a whole — the only correct license for a WordPress derivative work.

The parts that are not WordPress-derived — CSS in `assets/css/` and JavaScript in `assets/js/` — are additionally offered under **MIT**, so the design system can travel to non-WordPress projects. Bootstrap 5.3.3 is bundled under its own MIT license. Screenshots are illustrative and not part of the licensed code.

---

<div align="center">

### If this saved you a hundred dollars and a weekend — star the repo. ⭐

[![Live demo](https://img.shields.io/badge/Live_demo-xi.community-f59e0b?style=for-the-badge)](https://xi.community)
[![Telegram](https://img.shields.io/badge/Telegram-community-229ED9?style=for-the-badge&logo=telegram&logoColor=white)](https://t.me/xicommunity)

<sub>Keywords: wordpress light novel theme · ranobe theme · web novel wordpress · webnovel platform · manga novel site · chapter reader theme · fiction wordpress theme · free novel theme · GPL novel platform · дизайн для ранобэ · тема WordPress для новелл</sub>

</div>
