<div align="center">

# WordPress Novel Themes

### Stop running a blog. Run a novel platform.

**A free, GPL, zero-dependency WordPress theme that turns a plain install into a full light-novel / web-novel / ranobe site — catalog, chapters, distraction-free reader, rankings, author studio, reader library.**
**And it never looks like WordPress.**

[![Version](https://img.shields.io/badge/version-beta%200.3.0-f59e0b)](CHANGELOG.md)
[![License](https://img.shields.io/badge/license-GPL--2.0--or--later-e1173f)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-6.4%20%E2%86%92%207.x-21759b)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)](https://www.php.net/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.3-7952b3)](https://getbootstrap.com/)
[![Build step](https://img.shields.io/badge/build%20step-none-2ea44f)](#tech-stack)
[![npm dependencies](https://img.shields.io/badge/npm%20dependencies-0-2ea44f)](#tech-stack)
[![i18n](https://img.shields.io/badge/i18n-RU%20%2F%20EN%20%2F%20PT--BR-blue)](#languages)
[![PRs welcome](https://img.shields.io/badge/PRs-welcome-brightgreen)](#contributing)

**[🌐 Live demo — xi.community](https://xi.community)**

[Русская версия →](README.ru.md) · [Português (BR) →](README.pt-BR.md) · [Install](#install-in-two-minutes) · [Docs](docs/) · [Screenshots](#screenshots) · [FAQ](#faq)

**Telegram:** [📢 Channel](https://t.me/licht_re) · [💬 Community Chat](https://t.me/xicommunity)

</div>

![Home page](screenshots/01-home.jpg)

---

> ### 🚧 Status: **beta 0.3.0**
>
> The platform works end to end — you can install it today and publish titles and chapters. What is not final is the **presentation layer**. Bootstrap 5 is the current base, not the destination: the plan is to benchmark alternatives and keep whichever wins on real numbers.
>
> **What gets measured before a framework stays:** payload after gzip, render-blocking bytes, Largest Contentful Paint on a mid-range phone, layout shift on the catalog grid, and how the reader feels during a 40-minute session — line rhythm, contrast at night, how quickly settings apply.
>
> **Candidates on the bench:** Bootstrap 5 (now) · Tailwind with a build-free CDN-less subset · UnoCSS · Bulma · Pico.css · plain CSS with only the theme's own tokens and no framework at all.
>
> Expect the markup of shared components (navbar, offcanvas, modal, forms, tabs) to change between betas. The data model, template hierarchy and hooks are already stable — themes built on top of them will survive the swap.
>
> Benchmarks and the decision will land in [CHANGELOG.md](CHANGELOG.md). Opinions and measurements from your own installs are welcome in issues.

> ### 🌐 Live demo: [xi.community](https://xi.community)
>
> A real site running this theme — browse the catalog, open a title, try the reader and its settings.
>
> **Heads-up:** that demo runs on the WordPress build until the team's own platform on **Elixir** ships. Once the Elixir platform launches, xi.community moves over to it and this theme stays here as the WordPress implementation — free, GPL and maintained on its own track.

## ⭐ Why you are reading this

Novel sites are their own genre of website. A title has chapters, chapters have order, readers return for the next one, authors publish several times a week, and everyone reads at night on a phone. WordPress out of the box gives you posts and categories — which fits exactly none of that.

Every other answer is either a **$59–$99 ThemeForest theme** welded to a page builder, or a **SaaS that owns your readers**. This repository is the third option: the entire platform as one theme you can read, fork, rename and ship — for free, forever.

> No page builder. No “pro version”. No subscription. No npm. No CDN. No phone-home.

## What you get

### For readers

| | |
|---|---|
| 📚 **Catalog** | Covers, genres, tags, release status, sorting by views / rating / freshness, filters that survive pagination |
| 📖 **Full-screen reader** | No site header, no footer, no sidebar. Auto-hiding top bar, contents drawer, progress dock, `←` / `→` paging |
| 🎨 **Reading settings** | Font size, line height, column width, serif / sans, four paper themes (site / white / sepia / night) — saved per browser, applied to every chapter |
| 🔤 **Glossary in the reader** | Rename anything while you read: select a word, type how it should read, and every chapter follows — any case or exact case, whole word or not, for one title or for the whole site. Kept in the browser and exportable as a file, so a machine-translated release gets fixed once and passed on |
| 🔖 **Library without an account** | Bookmarks, reading history and “continue reading” live in `localStorage` |
| 🕒 **Updates feed** | Every fresh chapter on the site, grouped into a Today / Yesterday / date timeline |
| 🏆 **Rankings** | Podium for the top three plus a list to tenth place, three competing orders in one block |
| 🌙 **Dark & light** | Dark by default, toggle in the header, no white flash on load |
| 📥 **EPUB and FB2** | Any title downloads as a proper e-book — cover, table of contents, chapters. Locked chapters are included only for readers who may read them |
| 🏅 **Streaks and achievements** | Days in a row, chapters read, ten quiet achievements on the profile — no points, no leaderboards |
| 🔑 **Sign-in on the site itself** | Sign in, sign up and password recovery on one centered page in your own design — readers never see `/wp-login.php` |
| 🌍 **RU / EN / PT-BR interface** | Language switch in the header, remembered in a cookie |

### For authors

| | |
|---|---|
| ✍️ **Author studio on the front end** | Create projects and chapters without ever opening `/wp-admin` |
| 🧰 **An editor built for chapters** | The theme’s own editor, not TinyMCE: paste from Word arrives clean, a scene break is one button, «tidy» fixes quotes, dashes and stray spaces, find-and-replace works across the chapter, and focus mode drops everything but the page |
| 🔤 **Project glossary** | Keep the names of the project in one list and every reader gets them automatically — or write them into the chapters in one pass, with a dry run that counts the matches first |
| 💾 **Drafts that survive** | Chapter text auto-saves to the browser while you write; live word count |
| 🔢 **Chapter numbering** | Next number pre-filled; fractional numbers (`12.5`) for side stories |
| 👑 **Early access** | Mark chapters as PLUS — locked for guests, badged in the contents |
| 🧑‍🎤 **Public profiles** | Author page with stats and tabs: projects / chapters / articles |

### For the owner

| | |
|---|---|
| 🕵️ **Nothing screams WordPress** | Admin bar off; generator, RSD, wlwmanifest, shortlink, oEmbed, emoji, X-Pingback and asset version strings stripped; REST moved from `/wp-json/` to `/api/`; login page restyled in your brand |
| 🛠️ **Control panel on the site** | `/manage/`: users and roles, PLUS access with an expiry date, the review queue for contributor submissions, every title, and the site settings — no `/wp-admin` needed |
| 🎨 **Theme studio** | A bundled plugin: one screen with the knobs on the left and the live site on the right. Colour, corner radius, shadows, site width, fonts and the reading defaults — every change visible before it is saved, five presets, JSON export |
| 🎛️ **Customizer** | The same knobs without the plugin, plus twelve home blocks you can switch off one by one, footer text, social links |
| 👥 **Co-authors** | A project can carry several translators; each of them adds and edits its chapters, and the team shows on the title page |
| 🛒 **Paid chapters** | A bridge to WooCommerce: attach a product to a chapter and it opens after purchase, next to PLUS |
| 💬 **Discussions (optional)** | Off by default. When on: own markup, one level of replies, spoilers, likes, author and team badges — nothing that looks like WordPress comments |
| 👑 **PLUS access** | Grant a reader early access for 30 / 90 / 365 days or with no expiry; chapters marked PLUS open for them automatically |
| 🧩 **Own widgets** | “Novel picks” (views / rating / new / updated) and “Latest chapters” |
| 🚫 **No comments anywhere** | Shipped deliberately without discussions — front end, templates and admin section all clean |
| 👥 **Accounts on your terms** | Registration toggle and the role new accounts get (author / contributor / reader) live in the customizer; repeated failures are throttled and a hidden field catches bots |
| 🌐 **Translation ready** | 729 strings, Russian source + compiled English and Brazilian Portuguese `.mo`, plus a build script |

![Reader](screenshots/04-reader.jpg)

## How it compares

| | This repo | Paid marketplace themes | Novel SaaS platforms |
|---|---|---|---|
| Price | **Free, GPL** | $59–$99 + renewals | Revenue share / monthly |
| Source you can read | **Yes, ~7k lines, commented API** | Obfuscated builder JSON | None |
| Page builder required | **No** | Usually yes | n/a |
| npm / composer / build | **None** | Often | n/a |
| External runtime calls | **Zero** | CDN fonts, trackers | Everything |
| Front-end author studio | **Yes** | Rare | Yes |
| Full-screen reader with settings | **Yes** | Rare | Yes |
| You own the readers and data | **Yes** | Yes | **No** |
| Looks like WordPress | **No** | Yes | n/a |

## Tech stack

Deliberately boring and dependency-light — you can read the whole thing in an evening.

| Layer | What is used | Why |
|---|---|---|
| CMS | **WordPress 6.4+** (tested to 7.0), classic theme, no FSE | The site editor cannot express a reader, a studio or a ranking without ten plugins |
| PHP | **7.4+**, plain procedural WordPress API | No composer, no autoloader, no framework — drops into any host |
| CSS framework | **Bootstrap 5.3.3**, bundled locally in `assets/vendor/` | Grid, navbar, offcanvas, modal, dropdown, tabs, forms, pagination — accessible and battle-tested |
| Design layer | **Custom CSS with HSL design tokens** (`style.css`, `skin.css`, `pages.css`, `parts.css`) | Bootstrap is re-skinned through CSS variables; the light and dark ladders are built from measured contrast |
| JS | **Vanilla ES5**, ~2.5k lines + Bootstrap bundle | No build step, no npm, no framework |
| Data model | Two post types (`novel`, `chapter`), three taxonomies (`genre`, `novel_tag`, `novel_status`), post meta | Standard WordPress — your content stays portable |
| Editor | Own `contenteditable` editor, ~600 lines | A chapter needs paste cleanup, scene breaks and focus — not a page builder |
| Client storage | `localStorage` for library, history, reading settings, drafts | Readers keep their place without an account |
| REST | One namespaced route (`/api/xin/v1/rate`) | Anonymous rating without a plugin |
| i18n | Gettext `.po` / `.mo` + build script | No translation plugin required |
| Icons | Inline SVG sprite in PHP | No icon font, no external request |
| Fonts | System stack | Nothing loaded from Google |
| Dev sandbox | PHP built-in server + **SQLite** drop-in | Preview without installing MySQL |

## Install in two minutes

```bash
git clone https://github.com/rurumiru/wordpress-novel-themes.git
cp -r wordpress-novel-themes/themes/xi-novels /path/to/wordpress/wp-content/themes/
```

**Appearance → Themes → XI Novels → Activate.** On activation the theme creates the “Author studio” and “My library” pages and seeds release statuses (Ongoing / Completed / On hiatus / Announced). Set permalinks to *Post name* and add your first title under **Novels → Add new**.

### Try it without a database

No MySQL? The repo ships a sandbox recipe: PHP’s built-in server plus the official SQLite drop-in, seeded with demo titles and chapters.

```bash
php -S localhost:8080 -t wordpress tools/dev-router.php
```

Step by step: **[docs/install.md](docs/install.md)** ([RU](docs/install.ru.md)).

## Repository layout

```
themes/xi-novels/      the theme — everything above lives here
  inc/                 post types, meta boxes, template tags, customizer,
                       widgets, author studio, i18n, nav walkers, cleanup
  template-parts/      home sections, catalog, studio screens
  assets/              css (7 files), js (5 files), vendor/bootstrap
  languages/           en_US and pt_BR, .po + .mo
demo/                  demo content: a plugin with two buttons, plus CLI scripts
plugins/               xi-studio (theme studio), xi-novel-import, and notes
                       on which third-party plugins the project uses and why
tools/                 dev-server router, bulk importer, translation builder,
                       i18n/ with one RU -> locale map per language
docs/                  install, authoring, import, customizing, development
screenshots/           what it looks like
```

## Documentation

* **[Installation](docs/install.md)** — production install, dev sandbox on SQLite, permalinks, first title ([RU](docs/install.ru.md))
* **[Authoring](docs/authoring.md)** — the studio, chapter numbering, early access ([RU](docs/authoring.ru.md))
* **[Import & heavy uploads](docs/import.md)** — the bundled importer: JSON/CSV manifests **and folders or ZIP archives of `.txt` / `.html` / `.md` chapter files**, plus WP All Import and WP-CLI recipes, and every PHP / nginx / LiteSpeed / Cloudflare limit you must raise before large covers will upload ([RU](docs/import.ru.md))
* **[Customizing](docs/customizing.md)** — design tokens, customizer options, child theme, hooks
* **[Development](docs/development.md)** — file map, data model, template hierarchy, translations, coding style
* **[Demo content](demo/README.md)** — a plugin that fills the site with 12 titles, 48 chapters, blog posts and banners from **Tools → Demo content**, and removes it again with one button; CLI scripts for servers with SSH

## Screenshots

Every shot below is the theme itself, running with the demo content from this repository. Nothing is mocked up.

### Reading

**Home page** — banner inside the site container, quick links, and the trending block.

![Home page: banner, quick links, trending title](screenshots/01-home.jpg)

**Catalog** — genre chips, status filter, five sort orders, six covers per row.

![Catalog: genre chips, filters and a grid of covers](screenshots/02-catalog.jpg)

**Title page** — a compact header, then flat sections: description, contents with search, and a sidebar of facts, rating and similar titles.

![Title page: header with cover, description, contents and sidebar](screenshots/03-novel.jpg)

**Reader** — no site header, no footer, no sidebar. The bar hides while you read.

![Full-screen reader with the chapter text](screenshots/04-reader.jpg)

### Accounts and running the site

| | |
|---|---|
| ![Sign-in and sign-up page](screenshots/14-account.jpg) | ![Control panel with the user list](screenshots/13-manage.jpg) |
| **Sign in and sign up** on the site itself — one centered page for login, registration and password recovery | **Control panel** at `/manage/` — roles, PLUS access with a term, review queue, titles and settings |
| ![Author profile with tabs and statistics](screenshots/07-profile.jpg) | ![Reader settings panel](screenshots/06-reader-settings.jpg) |
| **Author profile** — cover, statistics, podium of the most-read titles, tabs | **Reading settings** — size, leading, column width, serif or sans, four papers |

### Everything else

| | |
|---|---|
| ![Update feed grouped by day](screenshots/08-updates.jpg) | ![Blog with a lead story](screenshots/09-blog.jpg) |
| **Updates** — every fresh chapter, grouped into Today / Yesterday / date | **Blog** — lead story, category pills, sidebar |
| ![Reader in the dark scheme](screenshots/05-reader-alt.jpg) | ![Library page](screenshots/10-library.jpg) |
| **Dark scheme** — neutral charcoal, switched from the header | **Library** — bookmarks and history, kept in the browser |
| ![The same home page in English](screenshots/11-home-en.jpg) | ![Mobile layout with bottom navigation](screenshots/12-mobile.jpg) |
| **English** — the same site, `?lang=en` or the header switch | **Mobile** — bottom navigation, one-column layout |

## Languages

The interface ships in **Russian** (source strings), **English** (`languages/en_US.mo`) and **Brazilian Portuguese** (`languages/pt_BR.mo`) — 815 strings each, plus 36 more for the studio plugin. A visitor switches with the RU / EN / PT control in the header and the choice is remembered in a cookie; **Customize → Brand → Main language** decides what a first-time visitor sees.

Adding a fourth language is one file — a PHP map of Russian source string to translation:

```bash
cp tools/i18n/en_US.php tools/i18n/de_DE.php
# translate the right-hand side of every line, then
php tools/build-translations.php
```

The script re-reads every translatable string in the theme, reports what a map is missing or no longer uses, and writes `.po` and `.mo` for every map in `tools/i18n/`. Register the locale in `xin_languages()` (`inc/i18n.php`) and it joins the header switch. WordPress’s own strings and date formats come from the site language pack — install it under **Settings → General** if the admin should speak that language too.

## Roadmap

Ideas that fit the “no dependencies” rule. Vote with 👍 in issues, or send a PR.

- [ ] **Framework bake-off** — Bootstrap vs Tailwind subset vs UnoCSS vs Bulma vs Pico vs no framework, judged on gzip size, LCP on a mid-range phone, layout shift and reading comfort
- [ ] **Zero-CSS-framework build** as the likely endgame: the theme already carries its own token system, so a framework may end up being dead weight
- [ ] Reader typography pass: measured line length, optical margins, per-language line rhythm
- [ ] EPUB / FBReader export for a whole title
- [ ] Reading streaks and simple achievements
- [ ] Translator teams (several authors per project)
- [ ] Optional paid chapters via WooCommerce bridge
- [ ] Bulk chapter import from `.docx` / `.txt` / Google Docs
- [ ] Optional discussions module (opt-in, off by default)
- [ ] Additional locales: DE, ES, ID, VI

## FAQ

**Will it work on shared hosting?**
Yes. It is a classic theme: PHP files, CSS, JS. No composer, no node, no cron.

**Do I need any plugins?**
No. Caching and anti-spam are optional — see [plugins/](plugins/README.md).

**Can I sell a site built on it?**
Yes. GPL. Rename it, rebrand it, charge for it — no attribution required.

**Can I use the design without WordPress?**
The CSS and JS are additionally offered under MIT, so yes.

**Does it work with the block editor?**
Chapters and titles use the classic editor by design (authors write long text). Pages and blog posts work with blocks normally.

**How many chapters can it handle?**
Chapter lists are cached and sorted by numeric meta; sites with thousands of chapters per title are the design target.

**Is there a demo?**
Clone it and run the sandbox — it seeds a demo catalog in one command. Screenshots above come from that sandbox.

## Using it? A link back is nice

The licence asks for nothing beyond the GPL: use the theme commercially, fork it, rebrand it, sell services around it. But if your site runs on it and you mention that somewhere — in the footer, in an about page, in a post — it genuinely helps the project stay alive.

The theme ships a ready line: **Customize → footer → “Running on XI Novels”**, off by default. Or paste your own:

```html
<a href="https://github.com/rurumiru/wordpress-novel-themes">Running on the XI Novels theme</a>
```

**Want to support the work?** Come to Telegram — [📢 channel](https://t.me/licht_re) and [💬 community chat](https://t.me/xicommunity). Bug reports, screenshots of your site, feature ideas and plain thanks are all welcome; every version is discussed there first.

## Contributing

Issues and pull requests are welcome. Two rules: **no build step** (the theme stays editable with a text editor) and **no external runtime dependencies**.

## License

**GPL-2.0-or-later** for the theme as a whole — the only correct license for a WordPress derivative work.

The parts that are not WordPress-derived — CSS in `assets/css/` and JavaScript in `assets/js/` — are additionally offered under **MIT**, so the design system can travel to non-WordPress projects. Bootstrap 5.3.3 is bundled under its own MIT license. Screenshots are illustrative and not part of the licensed code.

---

<div align="center">

**If this saved you a hundred dollars and a weekend — star the repo.** ⭐

*Keywords: wordpress light novel theme · ranobe theme · web novel wordpress · webnovel platform · manga novel site · chapter reader theme · fiction wordpress theme · free novel theme · GPL novel platform · дизайн для ранобэ · тема WordPress для новелл*

</div>
