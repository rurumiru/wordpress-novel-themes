<div align="center">

<h1>XI&nbsp;Novels</h1>

<h3>Dois temas WordPress para um site onde se lê.</h3>

<p>
Catálogo de títulos, página da obra, leitor em tela cheia, biblioteca do leitor,<br>
estúdio do autor, download de livros, discussões, glossário do projeto, avisos de capítulo —<br>
<b>tudo isso mora no tema</b>. Sem plugins obrigatórios, sem requisições externas, sem build.<br>
<b>E em nenhuma tela isso parece WordPress.</b>
</p>

[![Demonstração](https://img.shields.io/badge/Demonstração-xi.community-b45309?style=for-the-badge)](https://xi.community)
[![Instalação](https://img.shields.io/badge/Instalação-dois_minutos-2ea44f?style=for-the-badge&logo=wordpress&logoColor=white)](#instalação)
[![Documentação](https://img.shields.io/badge/Documentação-ler-21759b?style=for-the-badge)](docs/)
[![Mudanças](https://img.shields.io/badge/Mudanças-versão_2.0-6366f1?style=for-the-badge)](CHANGELOG.md)

<br>

![Versão](https://img.shields.io/badge/versão-2.0.0-b45309?style=flat-square)
[![Licença](https://img.shields.io/badge/licença-GPL--2.0--or--later-e1173f?style=flat-square)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-6.4%20%E2%86%92%207.x-21759b?style=flat-square&logo=wordpress&logoColor=white)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)

![Build](https://img.shields.io/badge/etapa%20de%20build-nenhuma-2ea44f?style=flat-square)
![Dependências npm](https://img.shields.io/badge/dependências%20npm-0-2ea44f?style=flat-square)
![Requisições externas](https://img.shields.io/badge/requisições%20externas-0-2ea44f?style=flat-square)
[![Idiomas](https://img.shields.io/badge/interface-RU%20%2F%20EN%20%2F%20PT--BR-3b82f6?style=flat-square)](#idiomas)

[English](README.md) · [Русский](README.ru.md) · **Português&nbsp;(BR)**

[📢 Canal](https://t.me/licht_re) · [💬 Chat da comunidade](https://t.me/xicommunity)

</div>

![Página inicial do XIN-V2](screenshots/xin-v2/01-home.jpg)

---

## Versão 2 — o que mudou

O repositório agora traz **dois temas**, e eles compartilham um banco: trocar de um para o outro
é um clique no painel, sem migrar nada.

| | |
|:--|:--|
| **XIN-V2** — o novo | Papel quente, serifada nos títulos, fios finos no lugar de sombras. Leitor com rolagem infinita e leitura em voz alta, biblioteca do leitor, cantinho da comunidade, entrada no próprio site. Escrito do zero para bibliotecas grandes. |
| **XIN-Com** — o clássico | A mesma plataforma num tom mais denso, de revista. Seção de quadrinhos, banners, painel de gerência, rankings por período. Continua no repositório e continua recebendo correções. |

Chegaram **dois plugins novos**: o `XIN Turbo` acelera qualquer WordPress e o `XIN Setup` deixa o
site e o conteúdo já existente no formato que o tema espera — tudo numa tela.

A lista completa está em [CHANGELOG.md](CHANGELOG.md).

---

## Por que este

Uma afirmação alta vale exatamente o que há de medição e de código embaixo dela. Aqui estão.

| | |
|:--|:--|
| 📚 **Tudo no tema, não em cinco plugins** | Catálogo, capítulos, leitor, rankings, notas, biblioteca, estúdio do autor, exportação de livros, discussões, glossário, avisos — um código, um modelo de dados, um conjunto de opções. |
| 🚀 **Aguenta bibliotecas grandes** | A ordem dos capítulos vive como um índice próprio na meta do título. O sumário de um título com quatro mil capítulos custa **6 consultas**, o salto "próximo capítulo" também custa 6, e um catálogo de 24 cartões com toda a meta e as capas custa **9**. Sem o aquecimento do cache, esses mesmos 24 cartões custam 91. |
| 📖 **Um leitor a que se volta** | Rolagem infinita: terminou o capítulo e o próximo se acrescenta sozinho, e a barra de endereço passa a ser a dele. Tamanho, largura, papel, tema; marcadores e citações por parágrafo; notas do glossário; leitura em voz alta; barra de progresso. |
| 🔌 **Nenhuma requisição externa** | Sem CDN, sem Google Fonts, sem rastreadores. Quatro famílias tipográficas ficam dentro do tema — 22 arquivos, só cirílico e latino. A página carrega inteira do seu próprio domínio. |
| 🛠 **Nenhuma etapa de build** | Sem npm, sem composer, sem compilação. Baixe, descompacte em `wp-content/themes`, ative. |
| ✍️ **O autor publica pelo site** | Estúdio, editor de capítulo, cronograma de lançamento, acesso antecipado, coautores, glossário — sem abrir o `/wp-admin` uma única vez. |
| 🌍 **Três idiomas de fábrica** | 601 strings no tema: fonte em russo, `en_US` e `pt_BR` compilados, um seletor RU / EN / PT no cabeçalho. |
| 🔍 **Código que dá para ler** | ~13.000 linhas de PHP com comentários que explicam *por que é assim*, não *o que a linha faz*. Nenhum JSON ofuscado de construtor de páginas. |

> [!TIP]
> **Demonstração — [xi.community](https://xi.community).** Um site de verdade rodando este tema:
> navegue pelo catálogo, abra um título, experimente o leitor e seus ajustes.

---

## XIN-V2

<table>
<tr>
<td width="50%"><img src="screenshots/xin-v2/02-catalog.jpg" alt="Catálogo"></td>
<td width="50%"><img src="screenshots/xin-v2/03-novel.jpg" alt="Página do título"></td>
</tr>
<tr>
<td><b>Catálogo.</b> Filtros por situação, ano, gênero e tag; quatro ordenações. Cada mudança de filtro é um link comum, então a seleção pode ser compartilhada e aberta de um favorito.</td>
<td><b>Página do título.</b> Sumário, nota, extensão, títulos parecidos, equipe do projeto, download em livro e um botão para avisos de capítulo.</td>
</tr>
<tr>
<td><img src="screenshots/xin-v2/04-reader.jpg" alt="Leitor"></td>
<td><img src="screenshots/xin-v2/05-reader-dark.jpg" alt="Leitor, tema escuro"></td>
</tr>
<tr>
<td><b>Leitor.</b> Sumário à esquerda como uma janela em volta do capítulo atual, barra vertical à direita, barra de progresso no alto. O sublinhado pontilhado é uma nota do glossário.</td>
<td><b>Tema escuro.</b> A escolha do leitor é aplicada antes da primeira pintura — sem o clarão branco ao carregar.</td>
</tr>
<tr>
<td><img src="screenshots/xin-v2/06-library.jpg" alt="Biblioteca"></td>
<td><img src="screenshots/xin-v2/07-hub.jpg" alt="Cantinho do leitor"></td>
</tr>
<tr>
<td><b>Biblioteca.</b> Onde o leitor parou, o que guardou, o que leu há pouco. Tudo na conta, não no navegador: a estante viaja com ele para o celular.</td>
<td><b>Cantinho do leitor.</b> O que foi atualizado, sobre o que estão falando, quem mais lê. Quem está conectado ganha também a própria sequência e suas marcas.</td>
</tr>
<tr>
<td><img src="screenshots/xin-v2/08-studio.jpg" alt="Estúdio do autor"></td>
<td><img src="screenshots/xin-v2/09-manage.jpg" alt="Painel de gerência"></td>
</tr>
<tr>
<td><b>Estúdio do autor.</b> Títulos, capítulos, cronograma, acesso antecipado, capa, gêneros, coautores, glossário — sem abrir o painel nenhuma vez.</td>
<td><b>Painel de gerência.</b> A fila de comentários, os pedidos para virar autor e os papéis — no site, não nas tabelas do <code>/wp-admin</code>.</td>
</tr>
</table>

<details>
<summary><b>O que o leitor faz, exatamente</b></summary>

<br>

| | |
|:--|:--|
| **Rolagem infinita** | O próximo capítulo entra duas telas antes do fim do atual. A barra de endereço passa a ser a do capítulo que está diante do leitor: a aba fecha onde ele estava lendo e abre no mesmo ponto. |
| **Ajustes de leitura** | Oito tamanhos de fonte, quatro larguras de coluna, papel comum e sépia, tema claro e escuro, um modo sem o sumário. Tudo vive no navegador do leitor e sobrevive ao salto para o próximo capítulo. |
| **Marcadores e citações** | Um clique no parágrafo. O marcador assinala o ponto com um fio na margem; a citação é copiada junto com um link direto para aquele parágrafo. |
| **Glossário do projeto** | Nomes, títulos e nomes de técnicas ganham sublinhado pontilhado, e a nota aparece ao passar o cursor e ao receber foco pelo teclado. Um termo é marcado uma vez por parágrafo. |
| **Leitura em voz alta** | A síntese de fala do próprio navegador, parágrafo a parágrafo, destacando o que está sendo lido. Começa pelo parágrafo à vista, não pelo alto do capítulo. |
| **Sumário em janela** | Num título com milhares de capítulos, só uma centena em volta do atual chega à marcação; o resto carrega pelos botões "acima" e "abaixo". |
| **Teclas** | As setas viram capítulos, `+` e `−` mudam o tamanho, `Esc` fecha os botões do parágrafo. |

</details>

<details>
<summary><b>Acesso antecipado, pagamento e equipe do projeto</b></summary>

<br>

Um capítulo trancado entende duas trancas ao mesmo tempo:

* **uma data** — `_xin_unlock_at`: o capítulo abre para todos no momento marcado e, até lá, é
  visível para o autor, para os editores e para a equipe do projeto;
* **uma compra** — `_xin_product`: um produto WooCommerce. A chave é a mesma do XIN-Com, então um
  capítulo comprado lá abre aqui também.

Uma verificação cobre tudo: a página do capítulo, o sumário, a rolagem infinita e a exportação do
livro respondem igual. Um capítulo trancado não chega nem ao EPUB nem ao FB2.

A equipe do projeto é `_xin_team` no título. Um coautor edita o título e seus capítulos e lê o
acesso antecipado; quem muda a composição é só o dono do título e os editores.

</details>

---

## Velocidade

Os números abaixo foram medidos neste repositório, com o cache de objetos frio. Para repetir: ligue
o `SAVEQUERIES` e conte as consultas em volta da chamada.

| O quê | Consultas | Tempo |
|:--|--:|--:|
| Catálogo, 24 cartões com toda a meta e capas | **9** | 11,6 ms |
| Sumário, janela de 100 capítulos | **6** | 6,1 ms |
| Salto "anterior / próximo" | **6** | 3,8 ms |
| Uma página de título inteira | 15 | 11,3 ms |
| Resumo da página inicial | 10 | 8,4 ms |

Os mesmos 24 cartões sem o aquecimento da meta custam **91 consultas**. A diferença é que o tema
puxa a meta, os termos e as capas da lista inteira numa consulta, em vez de uma por cartão.

A ordem dos capítulos não é remontada por consulta a cada página: ela fica como um vetor de
identificadores na meta do título e se remonta sozinha quando um capítulo aparece, muda de número,
muda de situação ou some. É por isso que o sumário de um título com quatro mil capítulos custa o
mesmo que o de quatro.

O contador de visualizações não grava uma linha por visitante: as visualizações se acumulam no
cache de objetos e caem como um `UPDATE` por lote. A soma acontece no próprio banco — dois
visitantes no mesmo segundo contam como dois, não como um.

Com o plugin `XIN Turbo` a página de um visitante é servida sem sequer iniciar o WordPress:

| | Sem cache | Com cache |
|:--|--:|--:|
| Página do título | 0,41 s | **0,015 s** |
| Visita repetida com ETag | — | **304, 0 bytes** |

---

## Plugins

O tema funciona sem eles. Cada um cobre a sua tarefa e sai sozinho.

| Plugin | Para quê |
|:--|:--|
| **XIN Turbo** | Velocidade para todo o WordPress, não só para este tema. Cache de páginas prontas que atende visitantes sem iniciar o núcleo, cabeçalho e fila de arquivos enxutos, imagens e incorporações preguiçosas, heartbeat segurado, faxina noturna no banco. Cada chave diz o que desliga e qual é o preço. |
| **XIN Setup** | Uma tela mostrando o que no site está ajustado para o tema e o que não está: links, página inicial, discussões, tamanhos de capa, gêneros, páginas de seção, menus. À parte, ele conserta o conteúdo que já existe: capítulos sem título vinculado, capítulos sem número, títulos sem capa e sem resumo. Cada conserto mostra um número antes, aplica depois, e sempre em lotes. |
| **XIN-V2 Kit** | Mantém tipos de post, taxonomias e meta fora do tema, para que títulos e capítulos sobrevivam a uma troca de aparência. Mais a tela de Diagnóstico: divergências nos dados e seu conserto. |
| [**XI Studio**](plugins/xi-studio) | A aparência do tema sem uma linha de CSS. |
| [**XI Novel Import**](plugins/xi-novel-import) | Importação em massa de capítulos, de arquivos e de outros sites. |
| [**XI Novel Manager**](plugins/xi-novel-manager) | Edição em massa de títulos e capítulos. |
| **XI from Fictioneer** | Trazer uma biblioteca do tema Fictioneer. |

<table>
<tr>
<td width="50%"><img src="screenshots/xin-v2/15-turbo.jpg" alt="XIN Turbo"></td>
<td width="50%"><img src="screenshots/xin-v2/16-setup.jpg" alt="XIN Setup"></td>
</tr>
<tr>
<td><b>XIN Turbo.</b> Nenhum botão de "otimizar tudo": um botão desses um dia quebra o site, e o dono não faz ideia de qual das trinta opções foi.</td>
<td><b>XIN Setup.</b> Uma lista que se lê de cima para baixo. Ao lado de cada item: como está, o que vai virar e por que importa.</td>
</tr>
</table>

---

## Instalação

**O tema.**

1. Baixe o `xin-v2` nos [releases](../../releases) ou copie a pasta `themes/xin-v2` para
   `wp-content/themes`.
2. Ative: **Aparência → Temas**.
3. Ative o `XIN-V2 Kit` em `plugins/xin-v2-kit` — é ele que mantém os tipos de post fora do tema.
4. Ative o `XIN Setup` e abra **Ferramentas → Configuração do tema**. Ele mostra o que falta e cria
   as páginas de seção, os menus e as opções.

É isso. Sem build, sem dependências.

**Vindo do XIN-Com.** Troque o tema — os dados ficam onde estão, porque o modelo `_xin_*` é o
mesmo. O XIN-Com nomeia as páginas de seção de outro jeito, então depois da troca abra
**Ferramentas → Configuração do tema**: ele cria as que faltam e não toca nas existentes.

**Cache de páginas.** Liga-se à parte, em **Configurações → Velocidade** → "Instalar o
interceptador". O plugin escreve `wp-content/advanced-cache.php` e define `WP_CACHE` no
`wp-config.php`, e desfaz as duas coisas ao ser desligado.

---

## Idiomas

A interface é construída em três idiomas: o russo é a fonte, `en_US` e `pt_BR` são compilados.
O seletor RU / EN / PT fica no cabeçalho; a escolha vive num cookie, então os links que as pessoas
compartilham continuam comuns.

Para reconstruir depois de mexer nas strings:

```
php tools/build-translations.php
```

O montador pega as strings direto do código, confere com os mapas em `tools/i18n/`, informa o que
falta e o que não se usa mais, e escreve `.po` e `.mo` sem `msgfmt`. Uma string faltando devolve
código diferente de zero — ou seja, ele também é uma verificação.

![Página inicial em inglês](screenshots/xin-v2/14-home-en.jpg)

---

## No celular

<table>
<tr>
<td width="50%"><img src="screenshots/xin-v2/12-mobile.jpg" alt="Página inicial no celular"></td>
<td width="50%"><img src="screenshots/xin-v2/13-mobile-reader.jpg" alt="Leitor no celular"></td>
</tr>
</table>

Na tela estreita a barra do leitor desce para baixo, embaixo do polegar, e o sumário se esconde
numa tela própria.

---

## Documentação

| | |
|:--|:--|
| [Instalação](docs/install.md) | Em detalhe, incluindo a vinda de outros temas |
| [Publicação](docs/authoring.md) | Estúdio do autor, cronograma, acesso antecipado, glossário |
| [Importação](docs/import.md) | Carga de capítulos em massa |
| [Aparência](docs/customizing.md) | Opções de visual |
| [Desenvolvimento](docs/development.md) | Hooks, filtros, estrutura |

---

## Compatibilidade

* WordPress 6.4 → 7.x
* PHP 8.0+
* MySQL / MariaDB e SQLite (via `sqlite-database-integration`)
* WooCommerce — só para capítulos pagos, não é obrigatório

---

## Contribuindo

Contribuições são bem-vindas. Antes de enviar:

* strings de interface passam por `__()` com o text domain do tema;
* o comentário explica **por que** é assim, não o que a linha faz;
* depois de mexer nas strings, rode `php tools/build-translations.php`, que também é a verificação;
* um arquivo novo do tema é incluído no `functions.php` junto com os outros.

---

## Licença

[GPL-2.0-or-later](LICENSE). Um link de volta para o repositório não é exigido pela licença, mas
deixa os autores felizes.
