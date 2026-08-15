# Changelog

All notable changes to this project are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Planned

- **Framework bake-off.** Bootstrap 5 is the current base, not the decision. The presentation layer will be measured against a built Tailwind subset, UnoCSS, Bulma, Pico.css and a no-framework build that relies only on the theme's own token system. Judged on gzip weight, render-blocking bytes, LCP on a mid-range phone, layout shift on the catalog grid, and reading comfort across a long session. Numbers and the verdict will be published here.
- Reader typography pass: measured line length, optical margins, per-language line rhythm.

Shared component markup (navbar, offcanvas, modal, forms, tabs) may change between betas. The data model, template hierarchy and hooks are stable.

## [0.0.1-beta] — 2026-08-15

First public beta. Everything below works end to end; the presentation layer is still under evaluation.

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
