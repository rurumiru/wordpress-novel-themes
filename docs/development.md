# Development

## File map

```
themes/xin-com/
├── style.css                  theme header + design tokens + base + components
├── functions.php              setup, assets, sidebars, views, REST, query tweaks
├── header.php  footer.php     navbar, offcanvas, search modal, footer, bottom nav
├── front-page.php             home page composition
├── home.php                   blog listing with a lead story
├── index.php                  fallback / archives
├── single.php  page.php       blog post, static page
├── single-novel.php           title page
├── single-chapter.php         full-screen reader (own <html>, no header/footer)
├── archive-novel.php          catalog (delegates to template-parts/catalog.php)
├── archive-chapter.php        updates timeline
├── taxonomy.php               genre / tag / status archives → catalog
├── author.php                 public profile with tabs
├── search.php                 results split by post type
├── 404.php  sidebar.php  searchform.php
├── template-dashboard.php     Template Name: Author studio
├── template-library.php       Template Name: My library
├── inc/
│   ├── cpt.php                post types, taxonomies, chapter ↔ novel helpers
│   ├── meta-boxes.php         admin fields and list columns
│   ├── template-tags.php      icons, formatting, cards, sections, queries
│   ├── customizer.php         options + hex→HSL conversion
│   ├── widgets.php            two widgets
│   ├── authoring.php          studio: form handlers, permissions, pages
│   ├── i18n.php               language switch (RU/EN/PT-BR), locale filter
│   ├── skin.php               design knobs, CSS generator, presets, REST route
│   ├── glossary.php           project glossary: meta, replacement, bulk apply
│   ├── nav-walker.php         Bootstrap navbar + offcanvas walkers
│   └── cleanup.php            hides WordPress traces, restyles login
├── template-parts/            home sections, catalog, studio screens
├── assets/
│   ├── css/skin.css           Bootstrap re-skin from theme tokens
│   ├── css/pages.css          title page, reader, catalog, blog, widgets
│   ├── css/parts.css          podium, timeline, profile, studio, reader chrome
│   ├── css/editor.css         editor styles
│   ├── js/theme.js            sliders, tabs, reveal, counters, library, editor
│   ├── js/reader.js           reading settings, progress, TOC, keyboard
│   ├── js/replace.js          term matching engine, shared by reader and editor
│   ├── js/glossary.js         reader glossary: rules, panel, project rules
│   ├── js/writer.js           chapter editor: toolbar, paste cleanup, drafts
│   └── vendor/bootstrap/      5.3.3 css + bundle js
└── languages/                 en_US.po/.mo, pt_BR.po/.mo
```

## Data model

**`novel`** — a title. Archive `/novels/`, single `/novels/<slug>/`.
Meta: `_xin_author_name`, `_xin_original_title`, `_xin_translator`, `_xin_year`, `_xin_source`, `_xin_rating`, `_xin_rating_count`, `_xin_views`, `_xin_adult`, `_xin_featured`, `_xin_background`.

**`chapter`** — a chapter. Archive `/updates/`, single `/read/<slug>/`.
Meta: `_xin_novel` (parent ID), `_xin_number` (float), `_xin_locked`, `_xin_views`.

**Taxonomies** — `genre` (hierarchical), `novel_tag` (flat), `novel_status` (hierarchical, seeded with four terms).

Chapters are a separate post type rather than child posts because a chapter needs its own feed, its own view counter and its own URL space. Ordering is by numeric meta (`meta_value_num`), which is why `_xin_number` must be a float, not a string.

## Key functions

| Function | Purpose |
|---|---|
| `xin_get_novels( $type, $limit )` | Home-page queries: `popular`, `latest`, `updated`, `rating`, `featured` |
| `xin_get_chapters( $novel_id, $order, $limit )` | Ordered chapter list, cached 5 minutes |
| `xin_adjacent_chapter( $id, $dir )` | Previous / next inside a title |
| `xin_novel_card()` / `xin_chapter_card()` / `xin_post_card()` | The three card renderers |
| `xin_section_head( $args )` | Section heading with eyebrow, icon and “more” link |
| `xin_icon( $name )` | Inline SVG from the sprite in `xin_icon_path()` |
| `xin_num()` / `xin_ago()` / `xin_plural()` | Formatting helpers that do not rely on core translations |
| `xin_dashboard_url( $args )` / `xin_library_url()` | Studio and library URLs |
| `xin_can_author()` / `xin_owns( $id )` | Studio permission checks |

## Studio request flow

Forms post to `admin-post.php` with a nonce; handlers live in `inc/authoring.php`:

```
POST admin-post.php?action=xin_save_novel    → xin_handle_save_novel()
POST admin-post.php?action=xin_save_chapter  → xin_handle_save_chapter()
GET  admin-post.php?action=xin_delete&id=…   → xin_handle_delete()
```

Each handler verifies the nonce, checks `xin_can_author()` and `xin_owns()`, writes the post, then redirects back to the studio with a `msg` parameter. The dashboard view is selected by `view` and `project` query args — note it is `project`, not `novel`, because `novel` is a public query var of the post type and would 404.

## Client storage keys

| Key | Contents |
|---|---|
| `xin-theme` | `dark` / `light` |
| `xin-favorites` | array of `{id, title, url, cover}` |
| `xin-history` | last 20 `{novelId, novel, title, url, cover, progress, at}` |
| `xin-reader` | `{size, height, width, font, paper}` |
| `xin-read-<novelId>` | array of read chapter IDs |
| `xin-draft-<novel>-<chapter>` | studio draft |
| `xin-rated-<novelId>` | rating guard |
| `xin-glossary` | `{on, mark, project, global: [rule], novels: {<novelId>: [rule]}}`, one rule being `{id, from, to, ci, whole, on}` |

## Translations

Source strings are Russian; English lives in `languages/en_US.mo` and Brazilian Portuguese in `languages/pt_BR.mo`.

```bash
php tools/build-translations.php     # writes .po and .mo for every map in tools/i18n/
```

Each locale is one file in `tools/i18n/` returning `array( russian => translation )`. The script extracts every translatable string from the theme, reports what a map misses and what it no longer needs, exits non-zero when something is missing, and writes a binary `.mo` without needing `msgfmt`. To add a locale, copy a map, translate the values, name the file after the locale and add it to `xin_languages()`.

The visitor-facing switch lives in `inc/i18n.php`: `?lang=en` sets a cookie and a `locale` filter swaps the theme’s text domain. Core strings follow only if the matching language pack is installed.

## Coding style

* WordPress coding standards: tabs, Yoda conditions, `esc_*` on output, prefixed functions (`xin_`), text domain `xin-com`.
* No build step. If a change would require npm, it does not belong here.
* No external runtime requests. Everything ships in the repository.
* Template parts receive data through `get_template_part( $slug, $name, $args )`.

## Testing checklist before a PR

1. `php -l` on every changed PHP file.
2. Home, catalog, taxonomy, title, reader, updates, blog, profile, studio, library — all 200, no notices with `WP_DEBUG` on.
3. Both color schemes and both languages.
4. Mobile width ≥ 360px with no horizontal scroll.
5. Reader keyboard paging and settings persistence.
