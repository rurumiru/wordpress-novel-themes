# Authoring guide

Everything an author or translator needs, without touching `/wp-admin`.

## Roles

| Role | Can do |
|---|---|
| **Author** | Create projects and publish chapters immediately |
| **Contributor** | Create projects and chapters; they go to *Pending review* |
| **Editor / Administrator** | Everything, including other people’s projects |

Give a new translator the *Author* role under **Users → Add new**.

## Accounts

Readers sign in on the site itself: `/account/`, a page the theme creates on activation and rebuilds if it goes missing. One page holds three states — sign in, sign up and password recovery — and `/wp-login.php` is no longer linked anywhere on the front end, though it keeps working for administrators.

Two settings live in **Appearance → Customize → XI Novels: accounts**:

| Setting | Meaning |
|---|---|
| **Open registration** | The sign-up form accepts new readers. It is independent of *Settings → General → Membership*, which only governs the `/wp-login.php` screen. Uncheck it and the tab disappears, the form refuses submissions and the page says registration is closed |
| **Role for new accounts** | *Author* publishes straight away, *Contributor* sends chapters to review, *Reader* only reads. Default: author |

What the page does on its own:

* signs the new account in immediately after registration and sends the visitor to the studio (or to the library, for a reader role);
* remembers where the visitor came from, so a locked PLUS chapter returns them to that chapter after signing in;
* slows down after twelve failed attempts from one address, for fifteen minutes;
* carries a hidden field that ordinary visitors never see and bots fill in — those submissions are dropped;
* answers with a plain sentence for every case: wrong pair, name taken, email already registered, passwords not matching, password shorter than eight characters, expired form.

Password recovery sends the standard WordPress email; the link inside it opens the core reset screen.

## Control panel

Everything the site team needs day to day lives at `/manage/`, a page the theme creates on activation. Moderators (the *Moderator* role) see four tabs; administrators also get a fifth.

| Tab | What it does |
|---|---|
| **Overview** | Users, PLUS members, titles, chapters, and how many records wait for review |
| **Users** | Search by name, username or email; change the role; grant or remove PLUS access |
| **Review queue** | Everything submitted by contributors: publish, send back to drafts, or trash |
| **Titles** | Every title with its author, chapter count and status; publish or hide, jump to its chapters |
| **Settings** | Open registration on or off, the role new accounts get, the main language, the default color scheme |

**Roles** offered in the panel are Reader, Contributor, Author and Moderator. Administrators are never listed as a target and you cannot change your own role, so the panel cannot lock you out of your own site.

**PLUS access** is a per-user grant: +30 days, +90 days, +1 year, no expiry, or removed. Adding days to an account that still has time extends it rather than resetting it. Chapters marked PLUS open for anyone holding it; moderators and administrators always see them. The state shows next to the user as `PLUS until 17 September 2026`.

## The studio

Open `/dashboard/` (header → *Studio*, or the profile menu). Four screens:

* **My projects** — every project of yours with chapter count, views and status.
* **New project / Edit project** — the title card.
* **Chapters** — the table of one project.
* **New chapter / Edit chapter** — the writing screen.

## Creating a project

| Field | Notes |
|---|---|
| **Title** | Shown everywhere; the URL slug comes from it |
| **Original author** | Free text — for translations, the author of the original |
| **Original title** | Optional, shown under the heading |
| **Translation / team** | Credit line in the sidebar |
| **Release year** | Optional |
| **Short description** | One or two sentences for catalog cards, search and the hero block |
| **Full description** | The visual editor; shown under “Description”, collapsed after ~220px |
| **Genres** | Multiple; drive the catalog chips and “similar titles” |
| **Release status** | Ongoing / Completed / On hiatus / Announced |
| **Tags** | Comma separated, created on the fly |
| **Cover (2:3)** | 800×1200 or larger; used everywhere |
| **Wide artwork** | 1920×720; used by the home banner, trending block and the blurred backdrop of the title page |
| **Adult content** | Blurs the cover in listings and shows an 18+ badge |

Buttons: *Save and publish*, *Save as draft*, *Delete* (moves to trash).

## Writing a chapter

* **Chapter title** — free text; “Chapter 12” is added automatically from the number, so the title is just the name.
* **Number** — pre-filled with the next one. Fractional numbers (`12.5`) are for side stories and interludes; the reader shows them as `#12.5` and keeps them in order.
* **Text** — the WordPress visual editor. Paste from Word or Google Docs; use the *Code* tab for hand-written HTML. *Add Media* uploads illustrations into the chapter.
* **Early access (PLUS)** — the chapter is visible in the contents with a lock badge and readable only by logged-in users.

Three buttons: *Publish*, *Publish and start the next one* (keeps you in the flow for a batch), *Save as draft*.

While you type, the text is auto-saved to your browser. If the tab dies, reopen the screen — the draft is restored into an empty editor (it never overwrites a version already saved on the server). The word counter under the field updates live.

## Publishing a batch

1. *New project*, fill the card, publish.
2. *Add chapter* → write → **Publish and start the next one**.
3. Repeat. The number increments itself.

A chapter’s publication also touches the parent title, so it appears in *Recently updated* and the update feed.

## What readers see

* The chapter list on the title page has search and a reverse-order toggle.
* The reader remembers each visitor’s font size, line height, column width, typeface and paper theme.
* Reading progress is stored per browser and surfaces on the home page in *Continue reading*.
* Ratings are anonymous: one vote per browser, averaged into the title.

## Importing an existing site

There is no importer plugin, and the data model is deliberately plain:

* a title is a `novel` post — cover in the featured image, synopsis in the excerpt;
* a chapter is a `chapter` post with two meta keys: `_xin_novel` (parent title ID) and `_xin_number` (float);
* optional meta: `_xin_locked`, `_xin_adult`, `_xin_featured`, `_xin_views`, `_xin_rating`, `_xin_rating_count`, `_xin_background`, `_xin_author_name`, `_xin_original_title`, `_xin_translator`, `_xin_year`, `_xin_source`.

Anything that can write posts — WP All Import, a WP-CLI script, or 30 lines of PHP against `wp_insert_post()` — can fill it.

```php
$novel_id = wp_insert_post( array(
	'post_type'    => 'novel',
	'post_status'  => 'publish',
	'post_title'   => 'My title',
	'post_excerpt' => 'One-line synopsis',
) );

$chapter_id = wp_insert_post( array(
	'post_type'   => 'chapter',
	'post_status' => 'publish',
	'post_title'  => 'The first step',
	'post_content'=> $html,
) );
update_post_meta( $chapter_id, '_xin_novel', $novel_id );
update_post_meta( $chapter_id, '_xin_number', 1 );
```
