<?php
/**
 * Builds themes/xi-novels/languages/en_US.po and en_US.mo.
 *
 *   php tools/build-translations.php
 *
 * Extracts every translatable string from the theme, checks it against the
 * RU -> EN map below, reports anything missing, then writes both files.
 * No msgfmt required.
 */

$theme    = dirname( __DIR__ ) . '/themes/xi-novels';
$lang_dir = $theme . '/languages';
$T = array(
	'%d глав' => '%d chapters',
	'%d глава' => '%d chapter',
	'%d главы' => '%d chapters',
	'%s комментариев' => '%s comments',
	'%s комментарий' => '%s comment',
	'%s комментария' => '%s comments',
	'%s назад' => '%s ago',
	'%s отзыв' => '%s review',
	'%s отзыва' => '%s reviews',
	'%s отзывов' => '%s reviews',
	'%s тайтл' => '%s title',
	'%s тайтла' => '%s titles',
	'%s тайтлов' => '%s titles',
	'Esc — закрыть' => 'Press Esc to close',
	'PLUS' => 'PLUS',
	'XI Novels: бренд и цвета' => 'XI Novels: brand & colors',
	'XI Novels: главная' => 'XI Novels: home page',
	'XI Novels: подвал и соцсети' => 'XI Novels: footer & social',
	'XI: подборка новелл' => 'XI: novel picks',
	'XI: последние главы' => 'XI: latest chapters',
	'~%d мин чтения' => '~%d min read',
	'© %1$s %2$s. Все права на тексты принадлежат их авторам.' => '© %1$s %2$s. All texts belong to their authors.',
	'Автор оригинала' => 'Original author',
	'Автор: %s' => 'Author: %s',
	'Авторам' => 'For authors',
	'Админка' => 'WP admin',
	'Акцентный цвет' => 'Accent color',
	'Анонс' => 'Announced',
	'Белая' => 'Paper',
	'Библиотека' => 'Library',
	'Блог' => 'Blog',
	'Боковая колонка блога' => 'Blog sidebar',
	'Боковая колонка тайтла' => 'Title sidebar',
	'Бумага' => 'Background',
	'Быстрая навигация' => 'Quick navigation',
	'В библиотеке' => 'In library',
	'В библиотеку' => 'Add to library',
	'В каталог' => 'Browse catalog',
	'В проекте ещё нет глав.' => 'This project has no chapters yet.',
	'В черновики' => 'Save as draft',
	'Ваша оценка' => 'Your rating',
	'Весь каталог' => 'Full catalog',
	'Весь рейтинг' => 'Full ranking',
	'Витрина «Сейчас в тренде»' => 'Hero showcase',
	'Во весь экран' => 'Fullscreen',
	'Возможно, тайтл переехал или ссылка устарела. Попробуйте найти его в каталоге.' => 'The title may have moved or the link is out of date. Try finding it in the catalog.',
	'Войдите в аккаунт, чтобы продолжить чтение и поддержать переводчика.' => 'Sign in to keep reading and support the translator.',
	'Войдите под своей учётной записью, чтобы вести проекты и публиковать главы.' => 'Sign in to manage projects and publish chapters.',
	'Войти' => 'Sign in',
	'Вперёд' => 'Next',
	'Все' => 'All',
	'Все главы' => 'All chapters',
	'Все новеллы' => 'All novels',
	'Все обновления' => 'All updates',
	'Вход для авторов' => 'Author login',
	'Вчера' => 'Yesterday',
	'Выберите настроение — остальное подберём' => 'Pick a mood — we will handle the rest',
	'Выбор редакции (показывать в витрине главной)' => "Editor's pick (show in the home showcase)",
	'Выбрать арт' => 'Choose artwork',
	'Выходит' => 'Ongoing',
	'Гл. %s' => 'Ch. %s',
	'Гл. %s — ' => 'Ch. %s — ',
	'Глав не найдено' => 'No chapters found',
	'Глава' => 'Chapter',
	'Глава %s.' => 'Chapter %s.',
	'Глава для подписчиков PLUS' => 'PLUS members only',
	'Глава сохранена.' => 'Chapter saved.',
	'Главная' => 'Home',
	'Главное меню' => 'Primary menu',
	'Главы' => 'Chapters',
	'Главы ещё не опубликованы.' => 'No chapters published yet.',
	'Год' => 'Year',
	'Год выпуска' => 'Release year',
	'Голосов: %d' => 'Votes: %d',
	'Гротеск' => 'Sans',
	'Дальше' => 'Next',
	'Дата' => 'Date',
	'Действия' => 'Actions',
	'Десятка лидеров площадки' => 'Top ten on the platform',
	'Добавить' => 'Add new',
	'Добавить главу' => 'Add chapter',
	'Добавить жанр' => 'Add genre',
	'Добавить новеллу' => 'Add novel',
	'Добавлен' => 'Added',
	'Добро пожаловать в %s' => 'Welcome to %s',
	'Дробный номер — экстра или интерлюдия: 12.5' => 'A fractional number means a side story: 12.5',
	'Дробный номер — экстра/интерлюдия: 12.5' => 'A fractional number means a side story: 12.5',
	'Ещё' => 'More',
	'Жанр' => 'Genre',
	'Жанры' => 'Genres',
	'Жанры пока не заданы' => 'No genres yet',
	'Завершён' => 'Completed',
	'Заголовок' => 'Title',
	'Задать обложку' => 'Set cover',
	'Закладки' => 'Bookmarks',
	'Закладки и история чтения хранятся в этом браузере — аккаунт не нужен.' => 'Bookmarks and reading history live in this browser — no account needed.',
	'Закрыть' => 'Close',
	'Заморожен' => 'On hiatus',
	'Здесь пока пусто' => 'Nothing here yet',
	'Имя автора' => 'Author name',
	'Искать главы' => 'Search chapters',
	'Искать новеллы' => 'Search novels',
	'Используется в баннере главной и крупных блоках.' => 'Used in the home banner and large blocks.',
	'История хранится в вашем браузере' => 'History is stored in your browser',
	'Источник' => 'Source',
	'К списку' => 'Back to list',
	'К странице тайтла' => 'Back to title',
	'Кабинет' => 'Studio',
	'Кабинет автора' => 'Author studio',
	'Как в системе' => 'Match system',
	'Как сайт' => 'Site theme',
	'Как только выйдет первая глава, она появится здесь.' => 'The first chapter will show up here as soon as it is published.',
	'Как читать' => 'Reading settings',
	'Карточка тайтла' => 'Title details',
	'Каталог' => 'Catalog',
	'Каталог новелл' => 'Novel catalog',
	'Каталог пока пуст. Добавьте первый тайтл — и главная соберётся сама: витрина, рейтинги и лента обновлений появятся автоматически.' => 'The catalog is empty. Add the first title and the home page builds itself: showcase, rankings and the update feed appear automatically.',
	'Каталог пуст. Добавьте первый тайтл:' => 'The catalog is empty. Add your first title:',
	'Каталог тайтлов' => 'Title catalog',
	'Коины, PLUS, медали рейтинга.' => 'Coins, PLUS, ranking medals.',
	'Колонки виджетов в подвале (вместо меню).' => 'Footer widget columns (instead of menus).',
	'Комментарий' => 'Comment',
	'Краткое описание' => 'Short description',
	'Крупнее' => 'Larger',
	'Листайте клавишами %1$s и %2$s' => 'Use %1$s and %2$s to turn pages',
	'Любой статус' => 'Any status',
	'Материал 18+' => 'Adult content',
	'Материал 18+ (обложка размывается в каталоге)' => 'Adult content (cover is blurred in the catalog)',
	'Межстрочный интервал' => 'Line height',
	'Мельче' => 'Smaller',
	'Меню' => 'Menu',
	'Меню подвала' => 'Footer menu',
	'Мобильное меню' => 'Mobile menu',
	'Мои проекты' => 'My projects',
	'Мой профиль' => 'My profile',
	'Моя библиотека' => 'My library',
	'Моё' => 'Mine',
	'На главную' => 'Go home',
	'На площадке с %s' => 'Member since %s',
	'На чём вы остановились' => 'Where you left off',
	'Наверх' => 'Back to top',
	'Нажмите на закладку у любой обложки в каталоге — тайтл появится здесь.' => 'Tap the bookmark on any cover in the catalog and the title shows up here.',
	'Нажмите на закладку у любой обложки — тайтл появится здесь.' => 'Tap the bookmark on any cover and the title shows up here.',
	'Назад' => 'Previous',
	'Название' => 'Title',
	'Название главы' => 'Chapter title',
	'Название тайтла, автор, глава…' => 'Title, author, chapter…',
	'Найден %s результат' => '%s result found',
	'Найдено %s результата' => '%s results found',
	'Найдено %s результатов' => '%s results found',
	'Найти' => 'Search',
	'Например: Печать девятого неба' => 'For example: Seal of the Ninth Heaven',
	'Настройки проекта' => 'Project settings',
	'Настройки профиля' => 'Profile settings',
	'Настройки сохраняются в этом браузере и применяются ко всем главам сайта.' => 'Settings are saved in this browser and apply to every chapter on the site.',
	'Настройки чтения' => 'Reading settings',
	'Начать читать' => 'Start reading',
	'Не удалось сохранить. Попробуйте ещё раз.' => 'Could not save. Please try again.',
	'Недавно обновлены' => 'Recently updated',
	'Недостаточно прав.' => 'Not allowed.',
	'Ничего не найдено' => 'Nothing found',
	'Ничего не нашлось' => 'Nothing found',
	'Новая глава' => 'New chapter',
	'Новая новелла' => 'New novel',
	'Новелл не найдено' => 'No novels found',
	'Новелла' => 'Novel',
	'Новеллы' => 'Novels',
	'Новеллы → Добавить' => 'Novels → Add new',
	'Новинки' => 'New releases',
	'Новое' => 'New',
	'Новости и статьи' => 'News & articles',
	'Новости площадки, разборы тайтлов и заметки переводчиков.' => 'Platform news, title breakdowns and translator notes.',
	'Новый проект' => 'New project',
	'Номер' => 'Number',
	'Номер главы' => 'Chapter number',
	'Ночь' => 'Night',
	'Нужно название.' => 'A title is required.',
	'О тайтле' => 'About this title',
	'Обложка' => 'Cover',
	'Обложка (2:3)' => 'Cover (2:3)',
	'Обновлений пока нет' => 'No updates yet',
	'Обновления' => 'Updates',
	'Обновлён' => 'Updated',
	'Обсуждение закрыто.' => 'Comments are closed.',
	'Оглавление' => 'Contents',
	'Одна-две фразы для карточки в каталоге' => 'One or two lines for the catalog card',
	'Описание' => 'Description',
	'Опубликованных глав пока нет.' => 'No published chapters yet.',
	'Опубликовать' => 'Publish',
	'Опубликовать и начать следующую' => 'Publish and start the next one',
	'Оригинальное название' => 'Original title',
	'Основная навигация' => 'Primary navigation',
	'Оставить комментарий' => 'Leave a comment',
	'Оставить отзыв' => 'Write a review',
	'Открыть каталог' => 'Open catalog',
	'Открыть тайтл' => 'Open title',
	'Отправить' => 'Submit',
	'Отправить на модерацию' => 'Submit for review',
	'Оценивать можно только новеллы.' => 'Only novels can be rated.',
	'Оценить на %d' => 'Rate %d',
	'Оценка' => 'Rating',
	'Оценка (0–5)' => 'Rating (0–5)',
	'Панель быстрых переходов' => 'Quick links bar',
	'Параметры главы' => 'Chapter options',
	'Перевод' => 'Translation',
	'Перевод / команда' => 'Translation / team',
	'Перейти к содержимому' => 'Skip to content',
	'Платная глава' => 'Paid chapter',
	'Платная глава (PLUS)' => 'Paid chapter (PLUS)',
	'Платформа для чтения и публикации новелл, ранобэ и переводов. Читайте бесплатно, поддерживайте авторов.' => 'A place to read and publish novels, light novels and translations. Read for free, support the authors.',
	'Плитки-приглашения' => 'Call-to-action tiles',
	'Плотнее' => 'Tighter',
	'По алфавиту' => 'A to Z',
	'По обновлению' => 'Recently updated',
	'По оценке' => 'By rating',
	'По просмотрам' => 'By views',
	'Подвал' => 'Footer',
	'Поделиться' => 'Share',
	'Подробнее' => 'Details',
	'Поиск' => 'Search',
	'Поиск по главам' => 'Search chapters',
	'Поиск по главам…' => 'Search chapters…',
	'Поиск по каталогу' => 'Search the catalog',
	'Поиск: %s' => 'Search: %s',
	'Пока вы здесь — популярное' => 'While you are here — popular now',
	'Пока пусто' => 'Nothing here yet',
	'Показывается в блоге и на записях.' => 'Shown in the blog and on posts.',
	'Показывается в витрине, поиске и на карточке тайтла.' => 'Shown in the showcase, search results and on the title page.',
	'Показывается на странице новеллы под блоком информации.' => 'Shown on the novel page under the info panel.',
	'Полное описание' => 'Full description',
	'Полоса цифр площадки' => 'Platform stats bar',
	'Попробуйте другой запрос или загляните в каталог — там больше тысячи страниц текста.' => 'Try another query or open the catalog — there are thousands of pages waiting.',
	'Попробуйте снять фильтры или загляните в другой жанр.' => 'Try clearing the filters or browsing another genre.',
	'Популярное' => 'Popular',
	'Порядок' => 'Order',
	'Последние главы' => 'Latest chapters',
	'Последняя глава' => 'Latest chapter',
	'Похожее' => 'Similar titles',
	'Править' => 'Edit',
	'Правка' => 'Edit',
	'Правка главы' => 'Edit chapter',
	'Правка проекта' => 'Edit project',
	'Правовые ссылки (низ подвала)' => 'Legal links (footer bottom)',
	'Предыдущая' => 'Previous',
	'Применить' => 'Apply',
	'Продолжить чтение' => 'Continue reading',
	'Проект' => 'Project',
	'Проект не найден.' => 'Project not found.',
	'Проект сохранён.' => 'Project saved.',
	'Проектов пока нет.' => 'No projects yet.',
	'Проектов пока нет. Создайте первый — это займёт минуту.' => 'No projects yet. Create your first one — it takes a minute.',
	'Проекты' => 'Projects',
	'Проекты, главы и черновики — всё редактируется прямо здесь.' => 'Projects, chapters and drafts — all editable right here.',
	'Просмотры' => 'Views',
	'Публикуйте свои новеллы и переводы, ведите главы в удобном редакторе, собирайте аудиторию. Свой формат, свой темп.' => 'Publish your novels and translations, write chapters in a clean editor, grow your audience. Your format, your pace.',
	'Пусто — название сайта и год.' => 'Leave empty for the site name and year.',
	'Пусто — фирменный кримсон.' => 'Leave empty for the signature crimson.',
	'Размер текста' => 'Text size',
	'Ранний доступ (PLUS)' => 'Early access (PLUS)',
	'Ранний доступ к главам, закрытые релизы и поддержка любимых переводчиков.' => 'Early access to chapters, members-only releases and a way to support your translators.',
	'Ранобэ, веб-новеллы и авторские переводы — весь каталог площадки.' => 'Light novels, web novels and original translations — the full catalog.',
	'Редактировать главу' => 'Edit chapter',
	'Редактировать новеллу' => 'Edit novel',
	'Рейтинг' => 'Ranking',
	'Рейтинг с вкладками' => 'Ranking with tabs',
	'С засечками' => 'Serif',
	'Самые любимые' => 'Reader favorites',
	'Сбросить фильтры' => 'Clear filters',
	'Свежие' => 'Fresh',
	'Свежие главы со всей площадки — в порядке публикации.' => 'Fresh chapters from across the platform, newest first.',
	'Свежие публикации со всего сайта' => 'Fresh releases from across the site',
	'Свежие публикации со всего сайта.' => 'Fresh releases from across the site.',
	'Свежие тайтлы на площадке' => 'New titles on the platform',
	'Свернуть' => 'Collapse',
	'Светлая' => 'Light',
	'Свободнее' => 'Looser',
	'Сегодня' => 'Today',
	'Сейчас в тренде' => 'Trending now',
	'Сейчас загружена:' => 'Currently uploaded:',
	'Сепия' => 'Sepia',
	'Сколько' => 'How many',
	'Слайд %d' => 'Slide %d',
	'Следить за обновлениями' => 'Follow updates',
	'Следующая' => 'Next',
	'Сменить тему' => 'Toggle theme',
	'Смотреть новеллу' => 'View novel',
	'Сначала выберите проект.' => 'Pick a project first.',
	'Сначала новые' => 'Newest first',
	'Создать аккаунт' => 'Create account',
	'Сортировка' => 'Sorting',
	'Сохранить и опубликовать' => 'Save and publish',
	'Средняя' => 'Medium',
	'Ссылка на первоисточник' => 'Original source link',
	'Статей пока нет.' => 'No articles yet.',
	'Статус' => 'Status',
	'Статус выпуска' => 'Release status',
	'Статусы' => 'Statuses',
	'Стать автором' => 'Become an author',
	'Статьи' => 'Articles',
	'Строка копирайта' => 'Copyright line',
	'Схема по умолчанию' => 'Default color scheme',
	'Счётчик растёт сам; поле — для переноса статистики.' => 'The counter grows on its own; this field is for migrating stats.',
	'Тайтлы' => 'Titles',
	'Тайтлы со свежими главами' => 'Titles with fresh chapters',
	'Тайтлы, набравшие больше всего внимания' => 'Titles pulling the most attention',
	'Тайтлы, отмеченные закладкой в каталоге' => 'Titles you bookmarked in the catalog',
	'Такой страницы нет' => 'This page does not exist',
	'Тег' => 'Tag',
	'Теги тайтлов' => 'Title tags',
	'Теги через запятую' => 'Tags, comma separated',
	'Текст главы' => 'Chapter text',
	'Текст о проекте' => 'About text',
	'Топ по оценкам сообщества' => 'Top rated by the community',
	'Топ по просмотрам, оценке, новинки или обновления.' => 'Top by views or rating, new releases or updates.',
	'Топ-авторы' => 'Top authors',
	'Топ-авторы и статьи' => 'Top authors & articles',
	'Тренд-блок с фоном' => 'Trending block with artwork',
	'Тёмная' => 'Dark',
	'Убрать' => 'Remove',
	'Удалено — запись в корзине.' => 'Deleted — moved to trash.',
	'Удалить' => 'Delete',
	'Удалить главу?' => 'Delete this chapter?',
	'Удалить проект вместе со страницей?' => 'Delete this project and its page?',
	'Узкая' => 'Narrow',
	'Узнать больше' => 'Learn more',
	'Узнать как' => 'See how',
	'Фильтр' => 'Filter',
	'Фон-арт (широкий)' => 'Wide artwork',
	'Фон-арт тайтла' => 'Title artwork',
	'Цвет премиума' => 'Premium color',
	'Читатели не могут оторваться' => 'Readers cannot put these down',
	'Читателю' => 'For readers',
	'Читать' => 'Read',
	'Читать полностью' => 'Read more',
	'Читать с начала' => 'Read from start',
	'Читать статью' => 'Read article',
	'Членство PLUS' => 'PLUS membership',
	'Что думаете о главе?' => 'What did you think of this chapter?',
	'Что ищем — тайтл, главу или статью?' => 'Looking for a title, a chapter or an article?',
	'Что показывать' => 'What to show',
	'Что читают сейчас' => 'What people are reading',
	'Ширина колонки' => 'Column width',
	'Широкая' => 'Wide',
	'Широкая картинка для hero-блока и плиток «самые любимые».' => 'A wide image for the hero block and the favorites tiles.',
	'Широкий арт для витрины' => 'Wide artwork for the showcase',
	'Шрифт' => 'Typeface',
	'Это не ваш проект.' => 'This project is not yours.',
	'Это не ваша глава.' => 'This chapter is not yours.',
	'Это первая глава' => 'This is the first chapter',
	'Это последняя глава' => 'This is the last chapter',
	'Ярлык витрины' => 'Showcase label',
	'автор' => 'author',
	'библиотека' => 'library',
	'в тренде' => 'trending',
	'вы читали' => 'you were reading',
	'выбор читателей' => 'reader favorites',
	'глав' => 'chapters',
	'глава' => 'chapter',
	'главы' => 'chapters',
	'дебюты' => 'debuts',
	'журнал' => 'journal',
	'лента' => 'feed',
	'не выбрана' => 'not selected',
	'не привязана' => 'not linked',
	'новелл' => 'novels',
	'новелла' => 'novel',
	'новеллы' => 'novels',
	'от %s' => 'by %s',
	'отзывов' => 'reviews',
	'открыть' => 'open',
	'оценка' => 'rating',
	'продолжить' => 'continue',
	'проектов' => 'projects',
	'просм.' => 'views',
	'просмотров' => 'views',
	'прочтений' => 'reads',
	'рейтинг' => 'ranking',
	'система, культивация, перерождение' => 'system, cultivation, rebirth',
	'слов' => 'words',
	'статей' => 'articles',
	'тайтлов' => 'titles',
	'только что' => 'just now',
	'черновик сохраняется в браузере' => 'draft is saved in your browser',
	'черновик сохранён' => 'draft saved',
	'читателей' => 'readers',
	'№' => '#',
	'name@example.com' => 'name@example.com',
	'Абзац=p; Заголовок=h2; Подзаголовок=h3' => 'Paragraph=p; Heading=h2; Subheading=h3',
	'Будьте первым, кто оставит мнение.' => 'Be the first to share your thoughts.',
	'Вы вошли как %s' => 'Signed in as %s',
	'Имя' => 'Name',
	'Как вас зовут' => 'Your name',
	'Комментарий ждёт проверки модератора.' => 'This comment is awaiting moderation.',
	'Не публикуется — нужна только для аватара и уведомлений.' => 'Never published — used only for your avatar and notifications.',
	'Ответить' => 'Reply',
	'Пока тишина' => 'No replies yet',
	'Показывается на странице тайтла под заголовком «Описание».' => 'Shown on the title page under the “Description” heading.',
	'Почта' => 'Email',
	'Язык интерфейса' => 'Interface language',
	'выйти' => 'sign out',
	'последний %s' => 'latest %s',
	'%1$d %2$s назад' => '%1$d %2$s ago',
	'год' => 'year',
	'года' => 'years',
	'лет' => 'years',
	'месяц' => 'month',
	'месяца' => 'months',
	'месяцев' => 'months',
	'день' => 'day',
	'дня' => 'days',
	'дней' => 'days',
	'час' => 'hour',
	'часа' => 'hours',
	'часов' => 'hours',
	'минуту' => 'minute',
	'минуты' => 'minutes',
	'минут' => 'minutes',
	'Вернуться на %s' => 'Back to %s',
	'Выйти' => 'Sign out',
	'Неверная пара логин / пароль.' => 'Wrong login or password.',
	'Популярные жанры' => 'Popular genres',
	'Профиль' => 'Profile',
);

/* ---------------------------------------------------------------- Проверка */

$all = array();
$rii = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $theme ) );
foreach ( $rii as $file ) {
	if ( $file->isDir() || 'php' !== strtolower( $file->getExtension() ) ) {
		continue;
	}
	$code = file_get_contents( $file->getPathname() );
	if ( preg_match_all( "/(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)\(\s*'((?:[^'\\\\]|\\\\.)*)'\s*,\s*'xi-novels'/", $code, $m ) ) {
		foreach ( $m[1] as $s ) {
			$all[ stripslashes( $s ) ] = true;
		}
	}
}
$all = array_keys( $all );
sort( $all );
$missing = array_values( array_diff( $all, array_keys( $T ) ) );
if ( $missing ) {
	echo "MISSING (" . count( $missing ) . "):\n" . implode( "\n", $missing ) . "\n";
}

/* --------------------------------------------------------------------- PO */

$header = "Project-Id-Version: XI Novels 1.0.0\\n"
	. "Language: en_US\\n"
	. "MIME-Version: 1.0\\n"
	. "Content-Type: text/plain; charset=UTF-8\\n"
	. "Content-Transfer-Encoding: 8bit\\n"
	. "Plural-Forms: nplurals=2; plural=(n != 1);\\n";

$po = "# English (US) translation for the XI Novels theme.\n"
	. "msgid \"\"\nmsgstr \"\"\n";
foreach ( explode( '\\n', $header ) as $line ) {
	if ( '' === $line ) {
		continue;
	}
	$po .= '"' . $line . '\n"' . "\n";
}
$po .= "\n";

$esc = static function ( $s ) {
	return str_replace( array( '\\', '"', "\n" ), array( '\\\\', '\"', '\n' ), $s );
};

foreach ( $T as $src => $dst ) {
	$po .= 'msgid "' . $esc( $src ) . "\"\n";
	$po .= 'msgstr "' . $esc( $dst ) . "\"\n\n";
}

if ( ! is_dir( $lang_dir ) ) {
	mkdir( $lang_dir, 0777, true );
}
file_put_contents( $lang_dir . '/en_US.po', $po );

/* --------------------------------------------------------------------- MO */

$entries = array( '' => str_replace( '\\n', "\n", $header ) );
foreach ( $T as $src => $dst ) {
	$entries[ $src ] = $dst;
}
ksort( $entries, SORT_STRING );

$count       = count( $entries );
$offsets_o   = array();
$offsets_t   = array();
$data        = '';
$origin_size = 28 + 16 * $count;

foreach ( $entries as $src => $dst ) {
	$offsets_o[] = array( strlen( $src ), $origin_size + strlen( $data ) );
	$data       .= $src . "\0";
}
foreach ( $entries as $src => $dst ) {
	$offsets_t[] = array( strlen( $dst ), $origin_size + strlen( $data ) );
	$data       .= $dst . "\0";
}

$mo  = pack( 'V*', 0x950412de, 0, $count, 28, 28 + 8 * $count, 0, 28 + 16 * $count );
foreach ( $offsets_o as $o ) {
	$mo .= pack( 'VV', $o[0], $o[1] );
}
foreach ( $offsets_t as $o ) {
	$mo .= pack( 'VV', $o[0], $o[1] );
}
$mo .= $data;

file_put_contents( $lang_dir . '/en_US.mo', $mo );

echo 'po/mo written: ' . $count . " entries\n";
