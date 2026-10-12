# XIN-V2 3 — local validation

Validation was performed against the local PHP 8.3 / WordPress installation using a SQLite database. The primary theme was active, with a normal test editor account. This report describes checked scenarios; it does not certify all hosting environments or external integrations.

## Automated checks

`php tools/verify-release.php /path/to/local-wordpress/wp-load.php`:

- Automatic setup completes; repeating it creates no duplicate published pages.
- Pretty permalinks, login/studio page bindings and novel/chapter archive rewrite rules exist.
- Import completion schedules another setup pass.
- Frontend novel/chapter/profile/glossary/delete actions have registered handlers.
- Chapter metadata added after insertion invalidates the cached contents index.
- Draft chapters appear in the studio index and remain absent from the public index.
- A first rating adds one vote while preserving imported rating totals.
- A replacement rating changes the same vote without increasing its count.
- Out-of-range, negative and fractional ratings are rejected; anonymous rating is rejected.

PHP syntax checks passed for the 90 theme/setup/optional-plugin source files initially audited; changed files were checked again after the studio fixes. EN/PT-BR translation generation reports 653 source strings and no missing translations. Theme and setup ZIPs use forward-slash paths and a single installable root folder.

## Browser checks

- Actual home, catalog, title, reader, studio and discussion screens rendered on desktop and mobile.
- Mobile drawer opens/closes and exposes languages and theme controls.
- Light/dark switching and language selection work.
- Ranking tabs change the displayed order; illustrated carousel changes slides and active indicators.
- Catalog search for `Сад` returns one title; filters expand on mobile.
- A 4/5 vote saves; changing it to 5/5 preserves the count; reload restores the personal vote.
- Favorite appears in the library; toggling it off works.
- Comment submission, reply and reaction persist; a test reply was moved to trash through the deletion flow.
- Reader navigates from chapter 1 to chapter 2 with one chapter section in the DOM. Text size changed from 19 to 18 pixels and persisted; theme settings also persisted.
- Frontend studio creates a draft title and saves a two-paragraph draft chapter; reopening its editor restores both paragraphs.
- Public EPUB and FB2 downloads succeed: EPUB is a valid ZIP with its required mimetype and nine entries; FB2 parses as XML with a `FictionBook` root.
- Responsive checks include 320, 390 and 768 pixels. Fixes cover clipped studio actions, grid overflow and hidden language controls.

## Fixes found during verification

The title rating control was missing; it is now available. Imported rating totals are no longer lost when a reader votes. Old `eri_` handler names did not match the studio forms' `xv_` actions; they now match. Chapter metadata was written after the initial save hook, leaving cached contents stale; relevant metadata changes now invalidate/rebuild it. Reply forms and mobile studio controls have corrected layout constraints.

## Scope limits

Testing uses a local demo database and a browser viewport simulation, not physical iOS/Android devices or every browser engine. Production email delivery, web-push delivery, WooCommerce payment/access integrations, all importer formats and every third-party plugin combination require their own environment tests. No production deployment was performed. Demo artwork, text and imported counters are identified as samples.
