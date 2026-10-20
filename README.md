# XI Novels 3 — beta

<picture>
  <source media="(prefers-color-scheme: dark)" srcset="screenshots/xin-v2-3/home-dark.jpg">
  <img alt="XI Novels 3" src="screenshots/xin-v2-3/home-light.jpg">
</picture>

**XI Novels 3 — beta.** Тема WordPress для сайтов новелл и переводов · A WordPress theme for novel and translation sites · Tema WordPress para sites de novels e traduções.

**[Скачать · Download · Baixar](https://github.com/rurumiru/wordpress-novel-themes/releases/latest)** · [Русский](README.ru.md) · [Português](README.pt-BR.md) · [Changelog](CHANGELOG.md)

<sub>Картинка выше повторяет вашу тему GitHub — светлую или тёмную · The picture above follows your GitHub theme, light or dark · A imagem acima acompanha o seu tema do GitHub, claro ou escuro</sub>

<table>
<tr>
<td width="33%"><img src="screenshots/xin-v2-3/catalog-desktop.jpg" alt="Каталог · Catalog · Catálogo"><br><sub>Каталог · Catalog · Catálogo</sub></td>
<td width="33%"><img src="screenshots/xin-v2-3/title-desktop.jpg" alt="Тайтл · Title · Título"><br><sub>Тайтл · Title · Título</sub></td>
<td width="33%"><img src="screenshots/xin-v2-3/home-carousel.jpg" alt="Карусель · Carousel · Carrossel"><br><sub>Карусель · Carousel · Carrossel</sub></td>
</tr>
<tr>
<td><img src="screenshots/xin-v2-3/reader-desktop.jpg" alt="Читалка · Reader · Leitor"><br><sub>Читалка · Reader · Leitor</sub></td>
<td><img src="screenshots/xin-v2-3/studio-editor.jpg" alt="Студия · Studio · Estúdio"><br><sub>Студия · Studio · Estúdio</sub></td>
<td><img src="screenshots/xin-v2-3/comments-desktop.jpg" alt="Обсуждения · Discussions · Discussões"><br><sub>Обсуждения · Discussions · Discussões</sub></td>
</tr>
</table>

<table>
<tr>
<td width="25%"><img src="screenshots/xin-v2-3/home-mobile.jpg" alt=""></td>
<td width="25%"><img src="screenshots/xin-v2-3/catalog-mobile.jpg" alt=""></td>
<td width="25%"><img src="screenshots/xin-v2-3/reader-mobile.jpg" alt=""></td>
<td width="25%"><img src="screenshots/xin-v2-3/ranking-mobile-pt.jpg" alt=""></td>
</tr>
</table>

<sub>Телефон, 390 px; рейтинг — на бразильском португальском · Phone, 390 px; the ranking is shown in Brazilian Portuguese · Celular, 390 px; o ranking aparece em português do Brasil</sub>

<details>
<summary><b>Русский</b> — полное описание</summary>

XI Novels — тема WordPress для сайта новелл и переводов: витрина, каталог, страница тайтла, читалка, обсуждения и студия автора и переводчика. В третьей версии основной темой стала **XIN-V2**. Прежняя тема **XIN-Com** осталась в репозитории как альтернатива. Обе работают на одной базе.

> **Бета.** Модель данных `_xin_*`, иерархия шаблонов и хуки стабильны. Разметка общих компонентов может меняться между бетами. Перед установкой на рабочий сайт сделайте резервную копию базы и `uploads`.
>
> **Скачать:** [последний релиз](https://github.com/rurumiru/wordpress-novel-themes/releases/latest) — тема, XIN Setup и демо.

### Последний релиз: 3.0.4-beta.1 — профили участников

У каждого участника появилась публичная страница: баннер, аватар, роль, год регистрации, описание, ссылки и опубликованные работы. В работы входят собственные тайтлы и проекты, где человек числится в команде. Статистика считается по всему списку, обложки листаются по двенадцать.

На публичную страницу не попадают черновики, чужие проекты, почта и история чтения. Ссылки на личную библиотеку и студию видит только владелец. Описание, псевдоним, подзаголовок, ссылки и аватар может поменять любой вошедший участник, включая подписчиков, — в той же форме профиля на сайте.

<img width="720" src="screenshots/xin-v2-3/profile-desktop.jpg" alt="Профиль участника">

### Путь: от первой беты до третьей версии

Первая публичная бета вышла 15 августа 2026 года, XI Novels 3 — 13 октября. За это время вышло 26 версий с тегами, а внешний вид менялся четыре раза.

<table>
<tr>
<td width="44%" valign="top">

**0.0.1-beta** · 15 августа

Первая публичная бета, тогда ещё под именем XI Novels. Тёмная схема по умолчанию и розовый акцент. Bootstrap 5.3.3 лежит в самой теме и перекрашен через HSL-токены. Главная собрана из двенадцати независимых блоков: ряд иконок разделов, «Сейчас в тренде», полоса счётчиков. Читалка открывается на весь экран, без шапки и подвала.

Уже с первого дня: ни одного внешнего запроса, студия автора вне `/wp-admin`, русский и английский.

</td>
<td width="56%"><img src="docs/media/history/home-0.0.1.jpg" alt="Главная 0.0.1"></td>
</tr>
<tr>
<td valign="top">

**0.0.8 – 0.0.9** · 16 августа

Вход, регистрация и восстановление пароля переехали на страницу сайта. Вместо малинового — белая бумага и графит. Кнопки перестали быть «пузырями», колонка сайта сузилась до 1160 px. Появилась панель управления на сайте для модераторов: пользователи, роли, очередь проверки, тайтлы и настройки.

</td>
<td><img src="docs/media/history/home-0.0.9.jpg" alt="Главная 0.0.9"></td>
</tr>
<tr>
<td colspan="2" valign="top">

**0.1 – 0.8** · 18–30 августа

Две недели ушли на функции. Выгрузка в EPUB и FB2, серии чтения и достижения, соавторы, платные главы через WooCommerce, обсуждения отдельным модулем, плагин импорта глав из .docx и Google Docs. Вместо TinyMCE — свой редактор глав: около 600 строк без npm, вставка из Word и Google Docs приходит чистой. Словарь переводчика, студия облика XI Studio с живым предпросмотром, бразильский португальский. Инструменты абзаца и чтение вслух — их прислал пул-реквестом HeavenlyCatCodes. Уголок читателя, рейтинг отдельной страницей, массовое управление тайтлами.

В 0.8.0 появился раздел комиксов. Формат комикса — поле тайтла, а не второй тип записи, поэтому каталог, рейтинг и студия остались общими. Добавилась выгрузка страниц в S3-совместимое хранилище, а тема сменила имя на XIN-Com.

</td>
</tr>
<tr>
<td valign="top">

**0.9.0** · 9 сентября

Облик собран заново, теперь он идёт от книги: тёплая бумага, чернила и один штемпельный акцент, антиква в заголовках, волосяные линии вместо теней. Ряд иконок и полоса счётчиков выключены по умолчанию, свечения и градиенты убраны.

Нагрузочная проверка: песочница на 400 тайтлов и 14 000 глав. Тайтл на 4000 глав открывается за полсекунды с холодным кэшем.

</td>
<td><img src="docs/media/history/home-0.9.0.jpg" alt="Главная 0.9.0"></td>
</tr>
<tr>
<td valign="top">

**2.0.0** · 12 сентября

Вторая тема, XIN-V2, написана с нуля под большие библиотеки и без CSS-фреймворка. В ней бесконечная прокрутка в читалке, чтение вслух, закладки и цитаты по абзацам, Web Push о новых главах и отложенная публикация. Рядом вышли плагины XIN Turbo (скорость) и XIN Setup (настройка сайта). Обе темы держат одну модель данных `_xin_*`.

</td>
<td><img src="docs/media/history/home-2.0.0.jpg" alt="Главная 2.0.0"></td>
</tr>
<tr>
<td valign="top">

**3.0** · 12–13 октября

Вышла XI Novels 3, и основной темой стала XIN-V2. Новый логотип и двухуровневая навигация, редакционная главная с иллюстрированной каруселью, каталог, где первой видна обложка, страница тайтла с личной оценкой от 1 до 5. Читалка вернулась к одной главе на страницу. Весь интерфейс набран Manrope, в обсуждениях видны роли.

В 3.0.1 — демо с иллюстрациями, в 3.0.3 — новый рейтинг и выверенные переводы на английский и бразильский португальский, по 683 записи.

</td>
<td><img src="screenshots/xin-v2-3/home-light.jpg" alt="Главная 3.0"></td>
</tr>
</table>

**Читалка: было и стало**

| 0.0.1 · август | 3.0 · октябрь |
|---|---|
| ![Читалка 0.0.1](docs/media/history/reader-0.0.1.jpg) | ![Читалка 3.0](screenshots/xin-v2-3/reader-desktop.jpg) |

### Фронтенд

- **Без сборки.** Нет ни npm, ни бандлера, ни CSS-фреймворка. Стили построены на собственной системе токенов. Скрипты — обычный JavaScript, разложенный по модулям: интерфейс, читалка, обсуждения, словарь, уведомления. Подключаются в конце страницы.
- **Ни одного запроса на чужой домен.** Шрифты лежат в теме: Manrope для интерфейса и ещё три семейства, только кириллица и латиница. Иконки — встроенный SVG.
- **Читалка.** Одна глава на страницу и фиксированная панель: предыдущая и следующая глава, оглавление, история, комментарии. Кегль, ширина колонки, бумага и тема запоминаются.
- **Обсуждения свои.** `comment_form()` и `wp_list_comments()` не используются: они тянут чужую разметку и перезагрузку страницы. Есть ответы, реакции и отметки ролей — читатель, редактор, автор.
- **Светлая и тёмная схемы**, переключатель RU / EN / PT. Выбор языка хранится в куке, поэтому ссылками делятся как обычно.
- **Телефон.** Шторка меню и нижняя навигация, раскладка проверена на 320, 390 и 768 px. Карусель листается стрелками, точками, клавиатурой и обычной прокруткой и учитывает `prefers-reduced-motion`. Поиск открывается клавишей <kbd>/</kbd>.
- **Web Push** о новых главах через service worker.

### Бэкенд

- **Модель данных.** Типы записей `novel` и `chapter`, таксономии жанров, меток и статусов, мета `_xin_*`. Мета общая у XIN-V2 и XIN-Com, поэтому тему можно сменить без переноса данных.
- **Порядок глав** хранится индексом в мете тайтла и пересобирается сам, когда глава появляется, меняет номер или статус либо удаляется. Оглавление тайтла на 4000 глав стоит шесть запросов — столько же, сколько у тайтла на четыре.
- **Просмотры** копятся в кэше объектов и записываются в базу одним `UPDATE` на пачку. Прибавление идёт в самой базе, поэтому два посетителя в одну секунду дают два просмотра.
- **Списки карточек** поднимают мету, термины и обложки одним заходом: каталог из 24 карточек — 9 запросов вместо 91.
- **Доступ к главе** решается в одном месте. Понимаются оба замка: ранний доступ до даты и покупка товара WooCommerce. Страница главы, оглавление, лента и выгрузка отвечают одинаково.
- **EPUB и FB2** собираются на лету и отдаются потоком. Готовые файлы не хранятся, поэтому и не устаревают после правки главы. В книгу попадает только то, что читателю и так открыто.
- **Оценки.** Повторный голос заменяет прежний, итоги, пришедшие с импортом, сохраняются. Значения вне 1–5, дробные и анонимные отклоняются.

| Плагин | Что делает |
|---|---|
| **XIN Setup 3** | После активации темы или импорта заводит страницы и меню, включает ЧПУ, проверяет маршруты и пересчитывает счётчики порциями по 100 тайтлов. Повторный запуск не создаёт дубликатов. |
| **XIN Turbo** | Кэш готовых страниц для гостей: страница тайтла отдаётся за 0,015 с вместо 0,41 с, повторный заход с ETag получает 304 и ноль байт. Вошедшим кэш не отдаётся. |
| **XIN-V2 Kit** | Держит типы записей и мету вне темы, следит за счётчиками и чинит расхождения. |
| **XI Novel Import** | Импорт глав из .docx, .txt, .md, .html, ZIP и Google Docs по ссылке; очередь по расписанию. |
| **XI From Fictioneer** | Перенос сайта с темы Fictioneer — порциями, с журналом и откатом. |
| **XI Novel Manager** | Массовая правка тайтлов: статусы, жанры, владельцы, обложки, CSV. |

### Почему всё вне админки

WordPress рассчитан на то, что работа идёт в `/wp-admin`. На сайте для чтения это неудобно почти всем, кто на нём работает, поэтому у читателя, автора и модератора есть свой экран на самом сайте. Права, данные и проверки при этом остаются за WordPress.

- **Читатель.** `wp-login.php` выглядит как чужая страница, причём ровно там, где человек впервые доверяет площадке почту и пароль. Поэтому вход, регистрация и восстановление пароля живут на обычной странице сайта в его оформлении. Имя и пароль проверяет ядро (`wp_signon()`, `register_new_user()`), своей криптографии в теме нет. После восьми неудачных попыток с одного адреса форма закрывается на 15 минут. Панель инструментов WordPress на страницах сайта скрыта.
- **Автор и переводчик.** Тому, кто выкладывает главы, нужен инструмент для текста, а не CMS. Студия на сайте: тайтлы, главы, черновики, расписание, ранний доступ, команда проекта, словарь и профиль. Отдельной роли нет — используется штатная роль WordPress «Автор». Соавторы правят тайтлы, в команде которых состоят.
- **Модератор.** Модератору нужны три вещи: очередь комментариев, роли и заявки в авторы. Гонять его ради них по таблицам админки незачем: там он увидит и всё остальное, что ему видеть не нужно. Панель на сайте даёт ровно эти три вещи. Администратора оттуда не повысить и не понизить, свою роль поменять нельзя.
- **Что остаётся за WordPress.** Каждое действие на сайте проходит проверку nonce и `current_user_can()`. Данные — обычные записи, мета и таблица комментариев. Поэтому работают модерация, антиспам, импорт и резервные копии, а `/wp-admin` по-прежнему открыт администратору для всего остального.

### Установка

Нужны WordPress 6.4+ и PHP 8.0+. Для текста, обложек и EPUB нужны `mbstring`, поддержка изображений и `ZipArchive`.

1. Установите тему из `xin-v2-3.0.4-beta.1.zip` и активируйте **XI Community Library**.
2. Установите плагин из `xin-setup-3.0.0.zip` и активируйте **XIN Setup**.
3. Откройте консоль WordPress: настройка запустится сама. Отчёт — в **Инструменты → Настройка темы**.
4. Импортируйте свои тайтлы или поставьте `xi-demo-content-3.0.1.zip`, чтобы посмотреть тему на демо.

> **Сервер.** Apache нужен доступный на запись `.htaccess`. На Nginx — обычный для WordPress `try_files $uri $uri/ /index.php?$args;`: конфигурацию Nginx плагин поменять не может.

| Файл | Что это |
|---|---|
| `xin-v2-3.0.4-beta.1.zip` | Тема. В списке тем называется **XI Community Library**. |
| `xin-setup-3.0.0.zip` | XIN Setup 3 — автоматическая настройка сайта. |
| `xi-demo-content-3.0.1.zip` | Демо: 15 тайтлов, 65 глав, три иллюстрированные обложки. Необязательно. |

Остальные плагины лежат в папке `plugins/` репозитория.

### Что проверено и чего пока нет

Проверено на локальной установке: PHP 8.3, WordPress, база SQLite. Настройка, рейтинг, студия, обсуждения, библиотека, выгрузка EPUB и FB2, переключение языка и схемы, раскладка на 320, 390 и 768 px. На физических iOS и Android тема не проверялась. Доставку почты и Web Push, оплату через WooCommerce и сочетания со сторонними плагинами нужно проверять в своём окружении.

### Состав репозитория

| Путь | Что там |
|---|---|
| `themes/xin-v2` | Основная тема 3.0 |
| `themes/xin-com` | Альтернативная тема на той же базе |
| `plugins/xin-setup` | Автоматическая настройка после установки и импорта |
| `plugins/xi-novel-import`, `plugins/xi-from-fictioneer` | Импорт |
| `plugins/xi-novel-manager`, `plugins/xi-studio` | Массовое управление тайтлами и студия облика |
| `plugins/xin-turbo`, `plugins/xin-v2-kit` | Скорость и обслуживание данных, по желанию |
| `screenshots/xin-v2-3` | Скриншоты текущего дизайна |
| `docs/media/history` | Скриншоты прошлых версий |
| `docs/media/covers` | Демо-обложки |
| `tools/verify-release.php` | Локальные проверки настройки и рейтинга |

В репозитории нет базы WordPress, `uploads`, учётных записей и паролей. Тексты и счётчики на скриншотах — демо-наполнение.

### Проверка своими руками

```bash
php tools/verify-release.php /path/to/test-wordpress/wp-load.php
```

Скрипт меняет настройки сайта и создаёт временные записи, которые потом уходят в корзину. Запускайте его только на тестовой установке.

Лицензия — GPL-2.0-or-later, см. [LICENSE](LICENSE).

[Журнал изменений](CHANGELOG.md) · [Презентация дизайна](docs/design-v3.md) · [Установка](docs/install.ru.md) · [Отчёт о проверке](docs/validation-v3.md)

</details>

<details>
<summary><b>English</b> — full description</summary>

XI Novels is a WordPress theme for novel and translation sites: a home page, catalog, title pages, a reader, discussions and a studio for authors and translators. In version 3, **XIN-V2** became the primary theme. The previous theme, **XIN-Com**, stays in the repository as an alternative, and both run on the same database.

> **Beta.** The `_xin_*` data model, the template hierarchy and the hooks are stable; markup of shared components may change between betas. Back up the database and `uploads` before installing it on a live site.
>
> **Download:** [the latest release](https://github.com/rurumiru/wordpress-novel-themes/releases/latest) — the theme, XIN Setup and the demo.

### Latest release: 3.0.4-beta.1 — member profiles

Every member now has a public page: a banner, avatar, role, year joined, bio, links and published works. Works include the member's own titles and projects where they are listed on the team. Statistics cover the whole list; covers are paginated in groups of twelve.

Drafts, unrelated projects, email addresses and reading history never reach the public page. Links to the personal library and the studio are shown only to the profile owner. Any signed-in member, subscribers included, can change their bio, pen name, tagline, links and avatar through the same profile form on the site.

<img width="720" src="screenshots/xin-v2-3/profile-desktop.jpg" alt="Member profile">

### The road: from the first beta to version 3

The first public beta came out on 15 August 2026, XI Novels 3 on 13 October. In between there were 26 tagged releases, and the look was rebuilt four times.

<table>
<tr>
<td width="44%" valign="top">

**0.0.1-beta** · 15 August

The first public beta, still called XI Novels. A dark scheme by default with a pink accent, and Bootstrap 5.3.3 bundled in the theme and re-skinned through HSL tokens. The home page was built from twelve independent blocks: a row of section icons, "Trending now" and a counter strip. The reader opened full-screen, without the site header and footer.

From day one: zero external requests, an author studio outside `/wp-admin`, Russian and English.

</td>
<td width="56%"><img src="docs/media/history/home-0.0.1.jpg" alt="Home, 0.0.1"></td>
</tr>
<tr>
<td valign="top">

**0.0.8 – 0.0.9** · 16 August

Sign-in, sign-up and password recovery moved onto a page of the site. Crimson gave way to white paper and graphite, buttons stopped being bubbles, and the site column narrowed to 1160 px. Moderators got a control panel on the site itself: users, roles, the review queue, titles and settings.

</td>
<td><img src="docs/media/history/home-0.0.9.jpg" alt="Home, 0.0.9"></td>
</tr>
<tr>
<td colspan="2" valign="top">

**0.1 – 0.8** · 18–30 August

Two weeks went into features: EPUB and FB2 export, reading streaks and achievements, co-authors, paid chapters through WooCommerce, discussions as a separate module, and a chapter import plugin for .docx and Google Docs. TinyMCE was replaced by a chapter editor of the theme's own — about 600 lines, no npm, and pasting from Word or Google Docs arrives clean. A translator's glossary, the XI Studio look editor with a live preview, Brazilian Portuguese. Paragraph tools and read-aloud, contributed by HeavenlyCatCodes in a pull request. The reader hub, a ranking page of its own, bulk title management.

0.8.0 added a comics section. The format is a field on the title rather than a second post type, so the catalog, ranking and studio stayed shared. It also added page uploads to S3-compatible storage, and the theme was renamed XIN-Com.

</td>
</tr>
<tr>
<td valign="top">

**0.9.0** · 9 September

The look was rebuilt around the book: warm paper, ink and a single stamp-red accent, serif headings, hairlines instead of shadows. The icon row and the counter strip were switched off by default; glows and gradients went away.

Load test: a sandbox with 400 titles and 14,000 chapters. A 4,000-chapter title opens in half a second on a cold cache.

</td>
<td><img src="docs/media/history/home-0.9.0.jpg" alt="Home, 0.9.0"></td>
</tr>
<tr>
<td valign="top">

**2.0.0** · 12 September

A second theme, XIN-V2, written from scratch for large libraries and without a CSS framework. It brought endless scrolling in the reader, read-aloud, per-paragraph bookmarks and quotes, Web Push for new chapters and scheduled publishing. Two plugins shipped next to it: XIN Turbo (speed) and XIN Setup (site setup). Both themes share the `_xin_*` data model.

</td>
<td><img src="docs/media/history/home-2.0.0.jpg" alt="Home, 2.0.0"></td>
</tr>
<tr>
<td valign="top">

**3.0** · 12–13 October

XI Novels 3, with XIN-V2 as the primary theme. A new logo and two-level navigation, an editorial home page with an illustrated carousel, a cover-first catalog, and title pages with a personal 1–5 rating. The reader went back to one chapter per page. The whole interface is set in Manrope, and discussions show roles.

3.0.1 added an illustrated demo; 3.0.3 rebuilt the ranking page and reviewed the English and Brazilian Portuguese translations, 683 entries each.

</td>
<td><img src="screenshots/xin-v2-3/home-light.jpg" alt="Home, 3.0"></td>
</tr>
</table>

**The reader, then and now**

| 0.0.1 · August | 3.0 · October |
|---|---|
| ![Reader, 0.0.1](docs/media/history/reader-0.0.1.jpg) | ![Reader, 3.0](screenshots/xin-v2-3/reader-desktop.jpg) |

### Front end

- **No build step.** No npm, no bundler, no CSS framework. Styles are built on the theme's own token system. Scripts are plain JavaScript split into modules (interface, reader, discussions, glossary, notifications) and loaded at the end of the page.
- **Not a single request to another domain.** Fonts ship with the theme — Manrope for the interface plus three more families, Cyrillic and Latin only. Icons are inline SVG.
- **Reader.** One chapter per page with a fixed bar: previous and next chapter, contents, history, comments. Text size, column width, paper and theme are remembered.
- **Discussions of its own.** `comment_form()` and `wp_list_comments()` are not used, because they bring foreign markup and a page reload. Replies, reactions and role badges — reader, editor, author.
- **Light and dark schemes** and an RU / EN / PT switch. The language choice lives in a cookie, so links are shared as usual.
- **Phones.** A menu drawer and bottom navigation; the layout is checked at 320, 390 and 768 px. The carousel works with arrows, dots, the keyboard and plain scrolling, and respects `prefers-reduced-motion`. <kbd>/</kbd> opens search.
- **Web Push** for new chapters through a service worker.

### Back end

- **Data model.** Post types `novel` and `chapter`, taxonomies for genres, tags and statuses, and `_xin_*` meta. The meta is shared by XIN-V2 and XIN-Com, so you can switch themes without migrating data.
- **Chapter order** is an index stored in the title's meta and rebuilt automatically when a chapter appears, changes number or status, or is deleted. The contents of a 4,000-chapter title cost six queries, the same as a title with four.
- **Views** accumulate in the object cache and reach the database as one `UPDATE` per batch. The increment happens in the database itself, so two visitors in the same second count as two.
- **Card lists** warm meta, terms and covers in one pass: a 24-card catalog page takes 9 queries instead of 91.
- **Chapter access** is decided in one place and understands both locks, early access until a date and a WooCommerce purchase. The chapter page, the contents, the feed and the export all answer the same way.
- **EPUB and FB2** are built on the fly and streamed. No ready-made files are stored, so none go stale after a chapter edit. A book contains only what that reader can already open.
- **Ratings.** A repeat vote replaces the previous one, and imported totals are preserved. Out-of-range, fractional and anonymous votes are rejected.

| Plugin | What it does |
|---|---|
| **XIN Setup 3** | After theme activation or an import, creates pages and menus, enables pretty permalinks, checks routes and recounts counters in batches of 100 titles. Running it again creates no duplicates. |
| **XIN Turbo** | A full-page cache for guests: a title page is served in 0.015 s instead of 0.41 s, and a repeat visit with an ETag gets a 304 with zero bytes. Signed-in users never get cached pages. |
| **XIN-V2 Kit** | Keeps the post types and meta outside the theme, watches the counters and repairs mismatches. |
| **XI Novel Import** | Chapter import from .docx, .txt, .md, .html, ZIP and Google Docs links, with a scheduled release queue. |
| **XI From Fictioneer** | Moves a site from the Fictioneer theme in batches, with a log and rollback. |
| **XI Novel Manager** | Bulk title editing: statuses, genres, owners, covers, CSV. |

### Why everything happens outside the admin

WordPress expects the work to be done in `/wp-admin`. On a reading site that is awkward for almost everyone who works there, so readers, authors and moderators each have their own screen on the site itself. Permissions, data and checks stay with WordPress.

- **Readers.** `wp-login.php` looks like someone else's page, and it appears exactly where a person first trusts the site with an email and a password. So sign-in, sign-up and password recovery live on an ordinary page in the site's own design. The username and password are checked by core (`wp_signon()`, `register_new_user()`); the theme has no cryptography of its own. After eight failed attempts from one address the form closes for 15 minutes. The WordPress toolbar is hidden on front-end pages.
- **Authors and translators.** Someone publishing chapters needs a writing tool, not a CMS. The studio on the site covers titles, chapters, drafts, scheduling, early access, the project team, the glossary and the profile. There is no special role: the standard WordPress Author role is used. Co-authors edit the titles whose team they are on.
- **Moderators.** A moderator needs three things: the comment queue, roles and author applications. There is no reason to send them through the admin tables for that, where they would also see everything else they don't need to see. The panel on the site offers exactly those three. An administrator cannot be promoted or demoted from it, and nobody can change their own role.
- **What stays with WordPress.** Every action on the site passes a nonce check and `current_user_can()`. The data are ordinary posts, meta and the comments table. That is why moderation, anti-spam, importers and backups keep working, and why `/wp-admin` remains available to the administrator for everything else.

### Installation

Requires WordPress 6.4+ and PHP 8.0+. Text, cover and EPUB features need `mbstring`, image support and `ZipArchive`.

1. Install the theme from `xin-v2-3.0.4-beta.1.zip` and activate **XI Community Library**.
2. Install the plugin from `xin-setup-3.0.0.zip` and activate **XIN Setup**.
3. Open the WordPress dashboard and setup runs by itself. The report is under **Tools → Настройка темы** — the plugin's own screens are in Russian for now.
4. Import your titles, or install `xi-demo-content-3.0.1.zip` to try the theme with demo content.

> **Server.** Apache needs a writable `.htaccess`. Nginx needs the usual WordPress fallback, `try_files $uri $uri/ /index.php?$args;`; the plugin cannot change the Nginx configuration.

| File | What it is |
|---|---|
| `xin-v2-3.0.4-beta.1.zip` | The theme. It appears in the theme list as **XI Community Library**. |
| `xin-setup-3.0.0.zip` | XIN Setup 3, automatic site setup. |
| `xi-demo-content-3.0.1.zip` | Demo content: 15 titles, 65 chapters, three illustrated covers. Optional. |

The other plugins are in the repository's `plugins/` folder.

### What was checked, and what wasn't

Checked on a local install: PHP 8.3, WordPress and a SQLite database. Covered: setup, ratings, the studio, discussions, the library, EPUB and FB2 export, language and scheme switching, and layouts at 320, 390 and 768 px. The theme has not been tested on physical iOS or Android devices. Email and Web Push delivery, WooCommerce payments and combinations with third-party plugins need testing in your own environment.

### Repository

| Path | Contents |
|---|---|
| `themes/xin-v2` | The primary 3.0 theme |
| `themes/xin-com` | The alternative theme on the same database |
| `plugins/xin-setup` | Automatic setup after installation and import |
| `plugins/xi-novel-import`, `plugins/xi-from-fictioneer` | Import |
| `plugins/xi-novel-manager`, `plugins/xi-studio` | Bulk title management and the look editor |
| `plugins/xin-turbo`, `plugins/xin-v2-kit` | Speed and data maintenance, optional |
| `screenshots/xin-v2-3` | Screenshots of the current design |
| `docs/media/history` | Screenshots of earlier versions |
| `docs/media/covers` | Demo covers |
| `tools/verify-release.php` | Local checks for setup and ratings |

The repository contains no WordPress database, `uploads`, accounts or passwords. Text and counters in the screenshots are demo content.

### Run the checks yourself

```bash
php tools/verify-release.php /path/to/test-wordpress/wp-load.php
```

The script changes site settings and creates temporary records that are moved to the trash afterwards. Run it on a test installation only.

Licensed under GPL-2.0-or-later, see [LICENSE](LICENSE).

[Changelog](CHANGELOG.md) · [Design presentation](docs/design-v3.md) · [Installation](docs/install.md) · [Validation report](docs/validation-v3.md)

</details>

<details>
<summary><b>Português (Brasil)</b> — descrição completa</summary>

XI Novels é um tema WordPress para sites de novels e traduções: página inicial, catálogo, página do título, leitor, discussões e um estúdio para autores e tradutores. Na versão 3, o **XIN-V2** passou a ser o tema principal. O tema anterior, **XIN-Com**, continua no repositório como alternativa, e os dois usam o mesmo banco de dados.

> **Versão beta.** O modelo de dados `_xin_*`, a hierarquia de templates e os hooks são estáveis; a marcação dos componentes compartilhados pode mudar entre as betas. Faça backup do banco de dados e da pasta `uploads` antes de instalar num site em produção.
>
> **Baixar:** [a versão mais recente](https://github.com/rurumiru/wordpress-novel-themes/releases/latest) — o tema, o XIN Setup e a demonstração.

### Versão mais recente: 3.0.4-beta.1 — perfis de membros

Cada membro agora tem uma página pública: banner, avatar, função, ano de cadastro, bio, links e obras publicadas. As obras incluem os títulos do próprio membro e os projetos em que ele faz parte da equipe. As estatísticas consideram a lista inteira; as capas são paginadas de doze em doze.

Rascunhos, projetos de outras pessoas, e-mails e histórico de leitura nunca aparecem na página pública. Os links para a biblioteca pessoal e para o estúdio só aparecem para o dono do perfil. Qualquer membro conectado, inclusive assinantes, pode alterar bio, pseudônimo, subtítulo, links e avatar pelo mesmo formulário de perfil do site.

<img width="720" src="screenshots/xin-v2-3/profile-desktop.jpg" alt="Perfil de membro">

### O caminho: da primeira beta à versão 3

A primeira beta pública saiu em 15 de agosto de 2026, e o XI Novels 3 em 13 de outubro. Nesse intervalo foram 26 versões com tag, e o visual foi refeito quatro vezes.

<table>
<tr>
<td width="44%" valign="top">

**0.0.1-beta** · 15 de agosto

A primeira beta pública, ainda com o nome XI Novels. Esquema escuro por padrão com destaque rosa, e Bootstrap 5.3.3 incluído no tema e recolorido por tokens HSL. A página inicial era montada com doze blocos independentes: uma fileira de ícones de seções, "Em alta" e uma faixa de contadores. O leitor abria em tela cheia, sem o cabeçalho e o rodapé do site.

Desde o primeiro dia: nenhuma requisição externa, estúdio do autor fora do `/wp-admin`, russo e inglês.

</td>
<td width="56%"><img src="docs/media/history/home-0.0.1.jpg" alt="Página inicial, 0.0.1"></td>
</tr>
<tr>
<td valign="top">

**0.0.8 – 0.0.9** · 16 de agosto

Login, cadastro e recuperação de senha passaram para uma página do próprio site. O carmim deu lugar ao papel branco e ao grafite, os botões deixaram de ser bolhas e a coluna do site ficou mais estreita, com 1160 px. Os moderadores ganharam um painel de controle no próprio site: usuários, funções, fila de revisão, títulos e configurações.

</td>
<td><img src="docs/media/history/home-0.0.9.jpg" alt="Página inicial, 0.0.9"></td>
</tr>
<tr>
<td colspan="2" valign="top">

**0.1 – 0.8** · 18 a 30 de agosto

Duas semanas dedicadas a recursos: exportação em EPUB e FB2, sequências de leitura e conquistas, coautores, capítulos pagos via WooCommerce, discussões como módulo separado e um plugin de importação de capítulos de .docx e Google Docs. O TinyMCE foi substituído por um editor de capítulos do próprio tema: cerca de 600 linhas, sem npm, e o texto colado do Word ou do Google Docs chega limpo. Glossário do tradutor, o editor visual XI Studio com pré-visualização ao vivo e português do Brasil. Ferramentas de parágrafo e leitura em voz alta, enviadas por HeavenlyCatCodes num pull request. O cantinho do leitor, uma página própria de ranking e o gerenciamento de títulos em massa.

A 0.8.0 trouxe uma seção de quadrinhos. O formato é um campo do título, e não um segundo tipo de post, por isso catálogo, ranking e estúdio continuaram compartilhados. Também chegaram o envio de páginas para armazenamento compatível com S3 e o novo nome do tema, XIN-Com.

</td>
</tr>
<tr>
<td valign="top">

**0.9.0** · 9 de setembro

O visual foi refeito a partir do livro: papel quente, tinta e um único destaque vermelho de carimbo, títulos com serifa, linhas finas no lugar de sombras. A fileira de ícones e a faixa de contadores ficaram desligadas por padrão; brilhos e gradientes saíram.

Teste de carga: um ambiente com 400 títulos e 14.000 capítulos. Um título com 4.000 capítulos abre em meio segundo com o cache frio.

</td>
<td><img src="docs/media/history/home-0.9.0.jpg" alt="Página inicial, 0.9.0"></td>
</tr>
<tr>
<td valign="top">

**2.0.0** · 12 de setembro

Um segundo tema, o XIN-V2, escrito do zero para bibliotecas grandes e sem framework CSS. Trouxe rolagem infinita no leitor, leitura em voz alta, marcadores e citações por parágrafo, Web Push para novos capítulos e publicação agendada. Junto com ele saíram dois plugins: XIN Turbo (velocidade) e XIN Setup (configuração do site). Os dois temas compartilham o modelo de dados `_xin_*`.

</td>
<td><img src="docs/media/history/home-2.0.0.jpg" alt="Página inicial, 2.0.0"></td>
</tr>
<tr>
<td valign="top">

**3.0** · 12 e 13 de outubro

O XI Novels 3, com o XIN-V2 como tema principal. Novo logotipo e navegação em dois níveis, página inicial editorial com carrossel ilustrado, catálogo em que a capa vem primeiro e página do título com avaliação pessoal de 1 a 5. O leitor voltou a mostrar um capítulo por página. Toda a interface usa Manrope, e as discussões mostram as funções.

A 3.0.1 trouxe uma demonstração ilustrada; a 3.0.3 refez o ranking e revisou as traduções para inglês e português do Brasil, com 683 entradas cada.

</td>
<td><img src="screenshots/xin-v2-3/home-light.jpg" alt="Página inicial, 3.0"></td>
</tr>
</table>

**O leitor, antes e agora**

| 0.0.1 · agosto | 3.0 · outubro |
|---|---|
| ![Leitor, 0.0.1](docs/media/history/reader-0.0.1.jpg) | ![Leitor, 3.0](screenshots/xin-v2-3/reader-desktop.jpg) |

### Front-end

- **Sem etapa de build.** Sem npm, sem bundler, sem framework CSS. Os estilos usam o sistema de tokens do próprio tema. Os scripts são JavaScript puro, divididos em módulos (interface, leitor, discussões, glossário, notificações) e carregados no fim da página.
- **Nenhuma requisição para outro domínio.** As fontes vêm com o tema — Manrope na interface e mais três famílias, só cirílico e latim. Os ícones são SVG embutido.
- **Leitor.** Um capítulo por página e uma barra fixa: capítulo anterior e próximo, sumário, histórico e comentários. Tamanho do texto, largura da coluna, papel e tema ficam salvos.
- **Discussões próprias.** `comment_form()` e `wp_list_comments()` não são usados, porque trazem marcação alheia e recarregam a página. Respostas, reações e selos de função — leitor, editor, autor.
- **Esquemas claro e escuro** e seletor RU / EN / PT. A escolha de idioma fica num cookie, então os links são compartilhados normalmente.
- **Celular.** Menu em gaveta e navegação inferior; o layout foi verificado em 320, 390 e 768 px. O carrossel funciona com setas, pontos, teclado e rolagem comum, e respeita `prefers-reduced-motion`. A tecla <kbd>/</kbd> abre a busca.
- **Web Push** para novos capítulos via service worker.

### Back-end

- **Modelo de dados.** Tipos de post `novel` e `chapter`, taxonomias de gêneros, tags e status, e metadados `_xin_*`. Os metadados são compartilhados pelo XIN-V2 e pelo XIN-Com, então dá para trocar de tema sem migrar dados.
- **A ordem dos capítulos** é um índice guardado nos metadados do título e reconstruído automaticamente quando um capítulo surge, muda de número ou de status, ou é excluído. O sumário de um título com 4.000 capítulos custa seis consultas — o mesmo que um título com quatro.
- **As visualizações** se acumulam no cache de objetos e vão para o banco num único `UPDATE` por lote. O incremento acontece no próprio banco, então dois visitantes no mesmo segundo contam como dois.
- **As listas de cards** carregam metadados, termos e capas de uma vez: uma página de catálogo com 24 cards faz 9 consultas em vez de 91.
- **O acesso ao capítulo** é decidido num só lugar e entende as duas travas: acesso antecipado até uma data e compra de um produto WooCommerce. A página do capítulo, o sumário, o feed e a exportação respondem do mesmo jeito.
- **EPUB e FB2** são gerados na hora e enviados em streaming. Nenhum arquivo pronto fica guardado, então nada fica desatualizado depois que um capítulo é editado. O livro contém só o que o leitor já pode abrir.
- **Avaliações.** Um novo voto substitui o anterior, e os totais importados são preservados. Votos fora de 1–5, fracionários ou anônimos são recusados.

| Plugin | O que faz |
|---|---|
| **XIN Setup 3** | Depois da ativação do tema ou de uma importação, cria páginas e menus, ativa links permanentes, verifica as rotas e recalcula os contadores em lotes de 100 títulos. Rodar de novo não cria duplicatas. |
| **XIN Turbo** | Cache de página inteira para visitantes: a página de um título sai em 0,015 s em vez de 0,41 s, e uma visita repetida com ETag recebe 304 e zero bytes. Usuários conectados nunca recebem páginas do cache. |
| **XIN-V2 Kit** | Mantém os tipos de post e os metadados fora do tema, acompanha os contadores e corrige divergências. |
| **XI Novel Import** | Importação de capítulos de .docx, .txt, .md, .html, ZIP e links do Google Docs, com fila de lançamentos agendados. |
| **XI From Fictioneer** | Migra um site do tema Fictioneer em lotes, com registro e opção de desfazer. |
| **XI Novel Manager** | Edição de títulos em massa: status, gêneros, donos, capas, CSV. |

### Por que tudo acontece fora do painel

O WordPress parte do princípio de que o trabalho é feito no `/wp-admin`. Num site de leitura isso atrapalha quase todo mundo que trabalha nele, por isso leitores, autores e moderadores têm cada um a sua tela no próprio site. Permissões, dados e verificações continuam com o WordPress.

- **Leitores.** O `wp-login.php` parece a página de outro site, e aparece justamente no momento em que a pessoa confia pela primeira vez seu e-mail e sua senha ao site. Por isso login, cadastro e recuperação de senha ficam numa página comum, com o visual do site. Usuário e senha são verificados pelo núcleo (`wp_signon()`, `register_new_user()`); o tema não tem criptografia própria. Depois de oito tentativas erradas do mesmo endereço, o formulário fecha por 15 minutos. A barra de ferramentas do WordPress fica oculta nas páginas do site.
- **Autores e tradutores.** Quem publica capítulos precisa de uma ferramenta de escrita, não de um CMS. O estúdio no site cuida de títulos, capítulos, rascunhos, agendamento, acesso antecipado, equipe do projeto, glossário e perfil. Não existe uma função especial: é usada a função padrão de Autor do WordPress. Coautores editam os títulos de cuja equipe fazem parte.
- **Moderadores.** O moderador precisa de três coisas: a fila de comentários, as funções e os pedidos para virar autor. Não faz sentido mandá-lo para as tabelas do painel por causa disso, onde ele também veria todo o resto, que não precisa ver. O painel do site oferece exatamente essas três coisas. Por ele não dá para promover nem rebaixar um administrador, e ninguém pode mudar a própria função.
- **O que continua com o WordPress.** Toda ação no site passa por verificação de nonce e por `current_user_can()`. Os dados são posts, metadados e a tabela de comentários comuns. Por isso moderação, antispam, importadores e backups continuam funcionando, e o `/wp-admin` segue disponível para o administrador fazer todo o resto.

### Instalação

Requer WordPress 6.4+ e PHP 8.0+. Os recursos de texto, capas e EPUB precisam de `mbstring`, suporte a imagens e `ZipArchive`.

1. Instale o tema a partir de `xin-v2-3.0.4-beta.1.zip` e ative **XI Community Library**.
2. Instale o plugin a partir de `xin-setup-3.0.0.zip` e ative **XIN Setup**.
3. Abra o painel do WordPress, e a configuração roda sozinha. O relatório fica em **Ferramentas → Настройка темы** — por enquanto, as telas do próprio plugin estão em russo.
4. Importe seus títulos ou instale `xi-demo-content-3.0.1.zip` para experimentar o tema com conteúdo de demonstração.

> **Servidor.** No Apache, o `.htaccess` precisa ter permissão de escrita. No Nginx, use o fallback habitual do WordPress, `try_files $uri $uri/ /index.php?$args;`; o plugin não consegue alterar a configuração do Nginx.

| Arquivo | O que é |
|---|---|
| `xin-v2-3.0.4-beta.1.zip` | O tema. Aparece na lista de temas como **XI Community Library**. |
| `xin-setup-3.0.0.zip` | XIN Setup 3, configuração automática do site. |
| `xi-demo-content-3.0.1.zip` | Conteúdo de demonstração: 15 títulos, 65 capítulos, três capas ilustradas. Opcional. |

Os demais plugins estão na pasta `plugins/` do repositório.

### O que foi verificado e o que ainda não foi

Verificado numa instalação local: PHP 8.3, WordPress e banco SQLite. Foram cobertos configuração, avaliações, estúdio, discussões, biblioteca, exportação EPUB e FB2, troca de idioma e de esquema, e layouts em 320, 390 e 768 px. O tema não foi testado em aparelhos iOS ou Android físicos. Entrega de e-mail e Web Push, pagamentos via WooCommerce e combinações com plugins de terceiros precisam ser testados no seu próprio ambiente.

### Repositório

| Caminho | Conteúdo |
|---|---|
| `themes/xin-v2` | O tema principal da 3.0 |
| `themes/xin-com` | O tema alternativo, no mesmo banco de dados |
| `plugins/xin-setup` | Configuração automática depois da instalação e da importação |
| `plugins/xi-novel-import`, `plugins/xi-from-fictioneer` | Importação |
| `plugins/xi-novel-manager`, `plugins/xi-studio` | Gerenciamento de títulos em massa e editor visual |
| `plugins/xin-turbo`, `plugins/xin-v2-kit` | Velocidade e manutenção de dados, opcionais |
| `screenshots/xin-v2-3` | Capturas do design atual |
| `docs/media/history` | Capturas das versões anteriores |
| `docs/media/covers` | Capas de demonstração |
| `tools/verify-release.php` | Verificações locais de configuração e avaliações |

O repositório não contém banco de dados do WordPress, `uploads`, contas nem senhas. Textos e contadores nas capturas são conteúdo de demonstração.

### Rode as verificações você mesmo

```bash
php tools/verify-release.php /path/to/test-wordpress/wp-load.php
```

O script altera configurações do site e cria registros temporários, que depois vão para a lixeira. Rode-o só numa instalação de teste.

Licença GPL-2.0-or-later, veja [LICENSE](LICENSE).

[Registro de alterações](CHANGELOG.md) · [Apresentação do design](docs/design-v3.md) · [Instalação](docs/install.md) · [Relatório de verificação](docs/validation-v3.md)

</details>
