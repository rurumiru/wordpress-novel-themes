# Import and heavy uploads

Two things break every migration to a novel site: getting hundreds of chapters in, and getting large cover files past the server. This page covers both.

---

## 1. The data model you are importing into

| Piece | Where it lives |
|---|---|
| Title | `novel` post — cover in the featured image, one-line synopsis in the excerpt, full description in the content |
| Chapter | `chapter` post — text in the content |
| Chapter → title link | post meta `_xin_novel` = parent title ID |
| Chapter order | post meta `_xin_number` — a **float** (`12`, `12.5`), never a string |
| Genres / tags / status | taxonomies `genre`, `novel_tag`, `novel_status` |
| Optional meta | `_xin_author_name`, `_xin_original_title`, `_xin_translator`, `_xin_year`, `_xin_source`, `_xin_views`, `_xin_rating`, `_xin_rating_count`, `_xin_adult`, `_xin_featured`, `_xin_background` (wide artwork attachment ID), `_xin_locked` (chapter) |

Anything able to create posts can fill this. Three routes below, easiest first.

---

## 2. The bundled importer

`tools/import-novels.php` takes a JSON or CSV manifest and creates or updates titles and chapters, downloads covers and sets every meta field.

```bash
php tools/import-novels.php --wp=/var/www/site --file=novels.json
php tools/import-novels.php --wp=/var/www/site --file=chapters.csv --format=csv --author=3
php tools/import-novels.php --wp=/var/www/site --file=novels.json --dry-run
```

| Option | Meaning |
|---|---|
| `--wp=PATH` | WordPress root (the folder with `wp-load.php`). Required |
| `--file=PATH` | Manifest. Required |
| `--format=json\|csv` | Default `json` |
| `--author=ID` | Author for the imported posts. Default: first administrator |
| `--status=publish\|draft` | Default `publish` |
| `--media=0` | Skip cover and artwork downloads |
| `--dry-run` | Parse and report, write nothing |

### JSON manifest

```json
[
  {
    "title": "Seal of the Ninth Heaven",
    "slug": "seal-of-the-ninth-heaven",
    "synopsis": "One line for catalog cards.",
    "description": "<p>Full description, HTML allowed.</p>",
    "author_name": "Liu Chenxing",
    "original_title": "第九天印",
    "translator": "East Wind team",
    "year": 2021,
    "status": "ongoing",
    "genres": ["Fantasy", "Xianxia"],
    "tags": ["cultivation", "rebirth"],
    "adult": false,
    "featured": true,
    "views": 128400,
    "rating": 4.7,
    "rating_count": 214,
    "cover": "https://example.com/cover.jpg",
    "artwork": "art/wide.jpg",
    "chapters": [
      { "number": 1, "title": "The shard", "content": "<p>…</p>", "date": "2026-01-05 10:00:00" },
      { "number": 2, "title": "First snow", "content_file": "chapters/002.html", "locked": true }
    ]
  }
]
```

`status` accepts the slugs seeded by the theme: `ongoing`, `completed`, `hiatus`, `announced`. Paths in `cover`, `artwork` and `content_file` may be absolute, relative to the manifest, or `http(s)` URLs.

### CSV manifest

One row per chapter; the title columns repeat. Multi-value fields use `|`.

```csv
novel_title,novel_slug,synopsis,genres,status,cover,chapter_number,chapter_title,chapter_file,locked
Seal of the Ninth Heaven,seal-of-the-ninth,A shard of an old seal.,Fantasy|Xianxia,ongoing,covers/seal.jpg,1,The shard,chapters/seal-001.html,0
Seal of the Ninth Heaven,seal-of-the-ninth,A shard of an old seal.,Fantasy|Xianxia,ongoing,covers/seal.jpg,2,First snow,chapters/seal-002.html,1
```

### Re-running is safe

A title is matched by slug (or exact title when no slug is given), a chapter by its number inside that title. Running the same manifest twice updates rather than duplicates, and covers are not re-downloaded when one is already attached.

Term counting and cache invalidation are deferred during the run and restored at the end — that is what keeps a few thousand inserts from crawling.

---

## 3. Chapters from text files or a ZIP archive

The most common shape a translator has is a folder of numbered files. The importer takes that directly — no manifest required.

```bash
# a folder of files into one title
php tools/import-novels.php --wp=/var/www/site --from-dir=./chapters --novel="Seal of the Ninth Heaven"

# the same, packed as an archive
php tools/import-novels.php --wp=/var/www/site --from-zip=chapters.zip --novel-slug=seal-of-the-ninth

# add to a title that already exists, marking everything from 40 on as PLUS
php tools/import-novels.php --wp=/var/www/site --from-zip=vol3.zip --novel-id=412 --locked-from=40
```

| Option | Meaning |
|---|---|
| `--from-dir=PATH` | Folder with chapter files |
| `--from-zip=PATH` | Archive with the same. A single wrapper folder inside is unwrapped automatically |
| `--novel=NAME` | Target title. Created if it does not exist yet |
| `--novel-slug=SLUG` | Target title by slug |
| `--novel-id=ID` | Target title by post ID |
| `--start=N` | First number to use when file names carry none. Default `1` |
| `--locked-from=N` | Mark chapters from this number onwards as PLUS |
| `--encoding=ENC` | Force the source encoding, for example `windows-1251` |
| `--status`, `--author`, `--dry-run` | Same as in manifest mode |

### Supported file types

| Extension | How it is treated |
|---|---|
| `.txt` | Plain text. A blank line starts a new paragraph, a single line break inside a paragraph becomes `<br>` — which is how dialogue is usually typed |
| `.html`, `.htm` | Used as is. If the file is a whole document, only what is inside `<body>` is taken |
| `.md` | Headings (`#`, `##`, `###`), `**bold**` and `*italic*` are converted, everything else becomes paragraphs |

Files with any other extension are ignored, so cover images and notes sitting in the same folder do no harm.

### File names become numbers and titles

| File name | Number | Chapter title |
|---|---|---|
| `001. The shard.txt` | 1 | The shard |
| `002 - First snow.txt` | 2 | First snow |
| `012.5_Side story.html` | 12.5 | Side story |
| `Chapter 3 - The debt.md` | 3 | The debt |
| `Глава 4. Ночной гость.txt` | 4 | Ночной гость |
| `The shard.txt` | file order | The shard |

Files are sorted the way a person would sort them, so `2` comes before `10`. When a name carries no number, the file's position in that order is used, starting from `--start`.

### One archive, several titles

If the archive or folder contains **sub-folders** and you do not pass `--novel`, each sub-folder becomes its own title, named after the folder:

```
collection.zip
├── Seal of the Ninth Heaven/
│   ├── 001. The shard.txt
│   └── 002. First snow.txt
└── Midnight Bakery/
    ├── 001. Opening hours.txt
    └── 002. The regular.txt
```

```bash
php tools/import-novels.php --wp=/var/www/site --from-zip=collection.zip
```

Titles that already exist are matched by slug and reused; missing ones are created empty, ready for a cover and a description in the studio.

### Encodings

Files are read as UTF-8, and a byte order mark is stripped. If the content is not valid UTF-8, it is converted from `windows-1251` — the usual case for older Word and Notepad exports. Anything else can be forced with `--encoding=`, for example `--encoding=koi8-r`.

### Re-running

Chapters match by number inside the title, so re-importing the same archive updates the existing chapters instead of adding copies. Fix a typo in a file, run the command again, and only that chapter changes.

Extracted archives are unpacked into a temporary folder and removed when the run finishes; entries pointing outside the archive and the junk that archivers add (`__MACOSX`, `.DS_Store`) are skipped.

---

## 4. WP All Import

If you prefer a UI, map the columns like this:

| Import step | Setting |
|---|---|
| Post type | `Novel` for titles, `Chapter` for chapters (two separate imports) |
| Title / content / excerpt | Title, full description, one-line synopsis |
| Taxonomies | `genre`, `novel_tag`, `novel_status` |
| Custom fields (title) | `_xin_author_name`, `_xin_original_title`, `_xin_translator`, `_xin_year`, `_xin_views`, `_xin_rating`, `_xin_rating_count`, `_xin_adult` |
| Custom fields (chapter) | `_xin_novel` — the imported title's post ID, `_xin_number` — the chapter number, `_xin_locked` |
| Images | Featured image = cover |

Import the titles first, then the chapters, using the title's unique key to resolve `_xin_novel`.

---

## 5. WP-CLI

For scripted migrations:

```bash
wp post create --post_type=novel --post_status=publish --post_title="Seal of the Ninth Heaven" \
  --post_excerpt="One line." --porcelain
# → 412

wp post term set 412 genre Fantasy Xianxia
wp post term set 412 novel_status ongoing
wp post meta set 412 _xin_author_name "Liu Chenxing"

wp post create ./chapters/001.html --post_type=chapter --post_status=publish \
  --post_title="The shard" --porcelain
# → 413
wp post meta set 413 _xin_novel 412
wp post meta set 413 _xin_number 1
```

---

## 6. Server settings for heavy files

Covers, wide artwork and bulk import files hit five different limits. Raise them all — a single low one is enough to fail the upload.

### PHP

| Setting | Recommended | Why |
|---|---|---|
| `upload_max_filesize` | `32M` | Single file cap. Wide artwork at 1920×720 is small, but scans and PSD exports are not |
| `post_max_size` | `64M` | **Must exceed** `upload_max_filesize`. When a POST is bigger, PHP discards the *entire* request — forms come back empty with no error |
| `memory_limit` | `256M` | Image resizing loads the full bitmap; a 6000 px cover needs far more RAM than its file size |
| `max_execution_time` | `300` | Long imports and thumbnail regeneration |
| `max_input_time` | `300` | Time allowed to receive the upload |
| `max_input_vars` | `3000` | Large forms — a chapter list with hundreds of rows |

Where to put them, in order of preference:

```ini
; php.ini (best — server-wide)
upload_max_filesize = 32M
post_max_size = 64M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
max_input_vars = 3000
```

```ini
; .user.ini in the WordPress root — shared hosting with PHP-FPM
upload_max_filesize = 32M
post_max_size = 64M
memory_limit = 256M
```

```apache
# .htaccess — only works with Apache + mod_php
php_value upload_max_filesize 32M
php_value post_max_size 64M
php_value memory_limit 256M
php_value max_execution_time 300
```

Control panels: **cPanel → MultiPHP INI Editor**, **ISPmanager → PHP settings**, **aaPanel → PHP → Settings → Configuration**, **Plesk → PHP Settings**. On aaPanel raise the value for the exact PHP version the site runs on.

### Nginx

```nginx
client_max_body_size 64m;
client_body_timeout 300s;
fastcgi_read_timeout 300s;
```

Without the first line the browser gets **413 Request Entity Too Large** before PHP is ever reached.

### LiteSpeed / OpenLiteSpeed

Set *Max Request Body Size* to at least `64M` in the tuning section, then restart. LiteSpeed also honours `.htaccess` `php_value` directives.

### Cloudflare and other proxies

The free Cloudflare plan caps uploads at **100 MB** and cannot be raised. For anything larger, upload directly to the origin or via SFTP.

### WordPress

```php
// wp-config.php
define( 'WP_MEMORY_LIMIT', '256M' );      // front end
define( 'WP_MAX_MEMORY_LIMIT', '512M' );  // admin, image processing, imports
```

```php
// child theme functions.php — raise the limit the media library reports
add_filter( 'upload_size_limit', function () {
	return 32 * 1024 * 1024;
} );

// keep originals instead of scaling everything down to 2560px
add_filter( 'big_image_size_threshold', '__return_false' );
```

Check what the server actually applies under **Tools → Site Health → Info → Media handling**.

### Image sizes the theme uses

| Size | Dimensions | Where |
|---|---|---|
| `xin-cover` | 320×480 | Catalog grids, rails |
| `xin-cover-lg` | 520×780 | Title page, hero deck |
| `xin-cover-sm` | 120×180 | Ranking rows, widgets, chapter cards |
| `xin-banner` | 1920×640 | Home banner, wide artwork |
| `xin-wide` | 720×405 | Blog cards |

Upload covers at **800×1200** and artwork at **1920×720**; anything larger is wasted bytes. After a bulk import, regenerate thumbnails:

```bash
wp media regenerate --only-missing --yes
```

---

## 7. Import performance

For a few thousand chapters:

```bash
# run through PHP CLI, which ignores web-server timeouts entirely
php -d memory_limit=512M tools/import-novels.php --wp=/var/www/site --file=novels.json

# skip images on the first pass, attach them later
php tools/import-novels.php --wp=/var/www/site --file=novels.json --media=0
```

Turn off any page-cache and image-optimization plugin during the run — they hook every insert and can triple the time. Re-enable and purge afterwards.

---

## 8. Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| **413 Request Entity Too Large** | Web server body limit | `client_max_body_size` (nginx) or Max Request Body Size (LiteSpeed) |
| Form comes back empty, nothing saved | POST exceeded `post_max_size`, PHP discarded it | Raise `post_max_size` above `upload_max_filesize`. The studio shows a readable notice for exactly this case |
| **HTTP error** in the media library | Memory, or a missing image library | Raise `memory_limit`, check that GD or Imagick is installed |
| The import stops halfway with a blank page | Not enough memory, or the request timed out | Run the importer through PHP CLI, raise `memory_limit` |
| Chapters appear in the wrong order | `_xin_number` stored as text | Store a number: `1`, `2`, `12.5` |
| Chapters exist but the title shows none | `_xin_novel` missing or pointing at the wrong ID | Check the meta value against the title's post ID |
| Covers look stretched | Source image is not 2:3 | Upload 800×1200 and regenerate thumbnails |
| Import runs but nothing is visible | Posts imported as `draft` | Re-run with `--status=publish` or bulk-publish in the admin |
