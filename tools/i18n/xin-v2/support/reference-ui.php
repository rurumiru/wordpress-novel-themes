<?php
/** UI strings introduced by the local reference redesign. */
$rows=<<<'STRINGS'
Редактор|Editor|Editor
Читатель|Reader|Leitor
Ваш комментарий|Your comment|Seu comentário
Поделитесь впечатлениями…|Share your thoughts…|Compartilhe suas impressões…
Ваша оценка|Your rating|Sua avaliação
Оценка сохранена|Rating saved|Avaliação salva
Не удалось сохранить оценку. Попробуйте ещё раз.|Could not save your rating. Try again.|Não foi possível salvar sua avaliação. Tente novamente.
Оценить произведение|Rate this title|Avaliar esta obra
%d из 5|%d out of 5|%d de 5
Войдите, чтобы оценить произведение|Sign in to rate this title|Entre para avaliar esta obra
Оценка должна быть от 1 до 5.|Rating must be between 1 and 5.|A avaliação deve ser entre 1 e 5.
В центре внимания|In the spotlight|Em destaque
Предыдущая история|Previous story|História anterior
Следующая история|Next story|Próxima história
Карусель произведений|Story carousel|Carrossel de histórias
Открыть произведение|Explore this title|Conhecer esta obra
Главная|Home|Início
Главная навигация|Main navigation|Navegação principal
Найти историю, автора…|Find a story, author…|Buscar história, autor…
Сменить тему|Switch theme|Alterar tema
Моя библиотека|My library|Minha biblioteca
Студия|Studio|Estúdio
%1$s тайтлов · %2$s жанров|%1$s titles · %2$s genres|%1$s obras · %2$s gêneros
АВТОРАМ И ПЕРЕВОДЧИКАМ|FOR AUTHORS AND TRANSLATORS|PARA AUTORES E TRADUTORES
Автор:|Author:|Autor:
Без меню|Hide menu|Ocultar menu
Ваши произведения|Your works|Suas obras
Ваши произведения и переводы. Всё для следующей главы.|Your works and translations. Everything for your next chapter.|Suas obras e traduções. Tudo para o próximo capítulo.
Веб-новелла|Web novel|Webnovel
Выбор редакции|Featured titles|Destaques
Высокая оценка|Top rated|Mais bem avaliadas
Глоссарий перевода|Translation glossary|Glossário da tradução
Здесь начинается ваша история|Your story starts here|Sua história começa aqui
Истории, которые остаются с вами|Stories that stay with you|Histórias que ficam com você
История чтения|Reading history|Histórico de leitura
Каталог историй|Story catalog|Catálogo de histórias
Комментарии|Comments|Comentários
Лучшие истории по жанрам|Discover stories by genre|Descubra histórias por gênero
Мои произведения|My works|Minhas obras
На странице книги|Book navigation|Navegação da obra
Навигация студии|Studio navigation|Navegação do estúdio
Навигация чтения|Reader navigation|Navegação da leitura
Найдите следующую историю в любимом жанре|Find your next story in your favorite genre|Encontre sua próxima história no seu gênero favorito
Найти произведение…|Find a work…|Encontrar uma obra…
Новое произведение|New work|Nova obra
Новые произведения|New releases|Novas obras
О произведении|About|Sobre a obra
Объявлений пока нет.|No announcements yet.|Ainda não há anúncios.
Объявления|Announcements|Anúncios
Отзывы|Reviews|Resenhas
Откройте новую историю|Discover a new story|Descubra uma nova história
Открыть рейтинг|View rankings|Ver classificação
Открыть студию|Open studio|Abrir estúdio
ПРОСТРАНСТВО АВТОРА И ПЕРЕВОДЧИКА|AUTHOR AND TRANSLATOR WORKSPACE|ESPAÇO DO AUTOR E TRADUTOR
Переводчик:|Translator:|Tradutor:
Подборки каталога|Catalog collections|Coleções do catálogo
Поиск по своим произведениям|Search your works|Buscar nas suas obras
Помощь|Help|Ajuda
Популярные произведения|Popular titles|Obras populares
Порядок рейтинга|Ranking order|Ordem da classificação
Похожие произведения|You may also like|Obras semelhantes
Предпросмотр|Preview|Prévia
Предыдущая подборка|Previous feature|Destaque anterior
Рейтинг читателей|Reader rankings|Classificação dos leitores
Ресурсы|Resources|Recursos
СОЗДАВАЙТЕ · ПЕРЕВОДИТЕ · ПУБЛИКУЙТЕ|WRITE · TRANSLATE · PUBLISH|ESCREVA · TRADUZA · PUBLIQUE
Скачать книгу|Download book|Baixar obra
Скрыть объявление|Dismiss announcement|Ocultar anúncio
Следующая подборка|Next feature|Próximo destaque
Смотреть все|View all|Ver todas
Создавайте главы, сохраняйте черновики и планируйте публикации прямо здесь.|Write chapters, save drafts and schedule releases here.|Escreva capítulos, salve rascunhos e agende publicações aqui.
Сообщество|Community|Comunidade
Студия|Studio|Estúdio
Студия автора и переводчика|Author and translator studio|Estúdio do autor e tradutor
Творческая студия|Creative studio|Estúdio criativo
Текст → глава|Text → chapter|Texto → capítulo
Тема|Theme|Tema
Читать запись|Read post|Ler publicação
Ширина|Width|Largura
Фильтры и поиск|Filters and search|Filtros e busca
STRINGS;
$result=array('en_US'=>array(),'pt_BR'=>array());
foreach(explode("\n",$rows) as $row){$parts=explode('|',trim($row));if(count($parts)===3){$result['en_US'][$parts[0]]=$parts[1];$result['pt_BR'][$parts[0]]=$parts[2];}}
return $result;
