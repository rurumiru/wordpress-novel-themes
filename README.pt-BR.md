<div align="center">

# WordPress Novel Themes

### Pare de manter um blog. Coloque uma plataforma de novels no ar.

**Um tema WordPress gratuito, GPL e sem nenhuma dependência que transforma uma instalação limpa em um site completo de light novels, web novels e traduções — catálogo, capítulos, leitor em tela cheia, rankings, painel do autor, biblioteca e espaço do leitor.**
**E em nenhum momento parece WordPress.**

[![Versão](https://img.shields.io/badge/version-beta%200.5.0-f59e0b)](CHANGELOG.md)
[![Licença](https://img.shields.io/badge/license-GPL--2.0--or--later-e1173f)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-6.4%20%E2%86%92%207.x-21759b)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)](https://www.php.net/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.3-7952b3)](https://getbootstrap.com/)
[![Build](https://img.shields.io/badge/build%20step-none-2ea44f)](#stack-t%C3%A9cnica)
[![Dependências npm](https://img.shields.io/badge/npm%20dependencies-0-2ea44f)](#stack-t%C3%A9cnica)
[![i18n](https://img.shields.io/badge/i18n-RU%20%2F%20EN%20%2F%20PT--BR-blue)](#idiomas)
[![PRs bem-vindos](https://img.shields.io/badge/PRs-welcome-brightgreen)](#contribuindo)

**[🌐 Demo ao vivo — xi.community](https://xi.community)**

[English README →](README.md) · [Русская версия →](README.ru.md) · [Instalação](#instalação-em-dois-minutos) · [Documentação](docs/) · [Capturas](#capturas-de-tela) · [FAQ](#faq)

**Telegram:** [📢 Canal](https://t.me/licht_re) · [💬 Chat da comunidade](https://t.me/xicommunity)

</div>

![Página inicial](screenshots/01-home.jpg)

---

> ### 🚧 Status: **beta 0.5.0**
>
> A plataforma funciona de ponta a ponta — dá para instalar hoje e publicar obras e capítulos. O que ainda não está fechado é a **camada de apresentação**. O Bootstrap 5 é a base atual, não o destino: o plano é medir as alternativas e ficar com a que vencer em números reais.
>
> **O que é medido antes de um framework ficar:** peso depois do gzip, bytes que bloqueiam a renderização, Largest Contentful Paint em um celular mediano, deslocamento de layout na grade do catálogo e como o leitor se comporta em uma sessão de 40 minutos — ritmo das linhas, contraste à noite, rapidez com que as configurações se aplicam.
>
> **Candidatos na bancada:** Bootstrap 5 (agora) · Tailwind em um subconjunto sem build e sem CDN · UnoCSS · Bulma · Pico.css · CSS puro apenas com os tokens do próprio tema, sem framework nenhum.
>
> Espere que a marcação dos componentes compartilhados (navbar, offcanvas, modal, formulários, abas) mude entre as betas. O modelo de dados, a hierarquia de templates e os hooks já estão estáveis — o que for construído sobre eles sobrevive à troca.
>
> Os números e a decisão vão para o [CHANGELOG.md](CHANGELOG.md). Opiniões e medições feitas nas suas próprias instalações são bem-vindas nas issues.

> ### 🌐 Demo ao vivo: [xi.community](https://xi.community)
>
> Um site de verdade rodando este tema — navegue pelo catálogo, abra uma obra, experimente o leitor e suas configurações.
>
> **Aviso:** esse demo roda na versão WordPress até a plataforma própria da equipe, em **Elixir**, ficar pronta. Quando ela entrar no ar, o xi.community migra para lá e este tema continua aqui como a implementação em WordPress — grátis, GPL e mantida em sua própria trilha.

## 🆕 O que chegou recentemente

| | |
|---|---|
| **0.5.0** | **Espaço do leitor** em `/hub/` — uma página que mostra a plataforma por dentro: quem fala, sobre o que discutem, o que está sendo lido agora. Seis contadores do site no cabeçalho, as duas métricas de uma vez no quadro de líderes, um cartão de perfil com a barra até o próximo nível. Desenhado como um terminal — grade e varredura no fundo, cantos cortados, leituras monoespaçadas, barras segmentadas — e não coleta nada novo sobre ninguém além de um anel de 40 leituras recentes |
| **0.4.0** | **Ferramentas de parágrafo e leitura em voz alta com vozes do aparelho.** Clique em um parágrafo: marque-o em uma de quatro cores, link direto para ele, cite-o na discussão, sugira uma correção com comparação ao vivo ou ouça o capítulo a partir dali. **Capítulos na fila dizem quando saem**, e a agenda de publicação passou a morar nas configurações do projeto, com o autor |
| **0.3.3** | **Os rankings viraram uma página própria** em `/ranking/`: três quadros, três janelas de tempo, filtro por gênero e uma nota ponderada — um voto cinco estrelas não passa à frente de quatrocentos votos honestos. A **gestão em massa de obras** chegou como plugin |
| **0.3.0** | **Um editor de capítulos do próprio tema** no lugar do TinyMCE, um **glossário de projeto** mantido pelo tradutor e o **XI Studio** — o estúdio do tema com o site ao vivo ao lado dos controles |

Todas as versões, na íntegra: **[CHANGELOG.md](CHANGELOG.md)**.

## ⭐ Por que você está lendo isto

Sites de novels são um gênero de site à parte. Uma obra tem capítulos, capítulos têm ordem, leitores voltam para o próximo, autores publicam várias vezes por semana e todo mundo lê de madrugada no celular. O WordPress, recém-instalado, oferece posts e categorias — o que não atende a nada disso.

As outras respostas são um **tema de marketplace de US$ 59 a US$ 99** soldado a um construtor de páginas, ou um **SaaS que fica dono dos seus leitores**. Este repositório é a terceira opção: a plataforma inteira como um tema que você pode ler, bifurcar, renomear e publicar — de graça, para sempre.

> Sem construtor de páginas. Sem “versão pro”. Sem assinatura. Sem npm. Sem CDN. Sem telemetria.

## O que você recebe

### Para leitores

| | |
|---|---|
| 📚 **Catálogo** | Capas, gêneros, tags, status de lançamento, ordenação por visualizações / nota / novidade, filtros que sobrevivem à paginação |
| 📖 **Leitor em tela cheia** | Sem cabeçalho, sem rodapé, sem barra lateral. Barra superior que se esconde sozinha, gaveta do índice, doca de progresso, navegação com `←` / `→` |
| 🎨 **Configurações de leitura** | Corpo do texto, entrelinha, largura da coluna, com ou sem serifa, quatro papéis (site / branco / sépia / noite) — salvos no navegador e aplicados a todos os capítulos |
| 🔤 **Glossário no leitor** | Renomeie o que quiser durante a leitura: selecione uma palavra, escreva como ela deve aparecer, e todos os capítulos passam a seguir a regra — qualquer caixa ou caixa exata, palavra inteira ou parte dela, para uma obra só ou para o site inteiro. Fica no navegador e sai em arquivo, então uma tradução de máquina se conserta uma vez e é passada adiante |
| 🔖 **Biblioteca sem conta** | Marcadores, histórico de leitura e “continuar lendo” vivem no `localStorage` |
| 🕒 **Feed de atualizações** | Todo capítulo novo do site, agrupado em uma linha do tempo Hoje / Ontem / data |
| 🏆 **Rankings** | Uma página própria em `/ranking/`: três quadros — nota, visualizações e número de capítulos —, três janelas de tempo e filtro por gênero. Os três primeiros sobem ao pódio, o resto vem em linhas com uma barra em relação ao líder |
| 🛰️ **Espaço do leitor** | `/hub/` mostra a plataforma por dentro: quem fala, sobre o que discutem, o que está sendo lido agora, seis contadores e um quadro de líderes. No estilo terminal, e todo o movimento para sob `prefers-reduced-motion` |
| 🗣️ **Ferramentas de parágrafo e leitura em voz alta** | Clique em um parágrafo: marque-o em uma de quatro cores, gere um link para ele, cite-o, sugira uma correção com comparação ao vivo ou ouça o capítulo a partir dali, com uma voz já instalada no aparelho — velocidade, tom, volume e prévia inclusos |
| 🌙 **Claro, escuro ou o do sistema** | Claro por padrão, escuro e “seguir o sistema” a um toque no cabeçalho — e sem estouro branco ao carregar |
| 📥 **EPUB e FB2** | Qualquer obra baixa como um e-book de verdade — capa, sumário, capítulos. Capítulos trancados entram no arquivo apenas para quem pode lê-los |
| 🏅 **Sequências e conquistas** | Dias seguidos, capítulos lidos, dez conquistas discretas no perfil — sem pontos, sem placares |
| 🔑 **Entrada no próprio site** | Entrar, cadastrar e recuperar a senha em uma página centralizada com o seu design — o leitor nunca vê `/wp-login.php` |
| 🌍 **Interface RU / EN / PT-BR** | Troca de idioma no cabeçalho, lembrada em um cookie |

### Para autores

| | |
|---|---|
| ✍️ **Painel do autor no site** | Crie projetos e capítulos sem nunca abrir `/wp-admin` |
| 🧰 **Um editor feito para capítulos** | O editor do próprio tema, não o TinyMCE: colar do Word chega limpo, quebra de cena é um botão, «ajeitar» conserta aspas, travessões e espaços sobrando, localizar e substituir varre o capítulo inteiro, e o modo foco tira tudo da frente |
| 🔤 **Glossário do projeto** | Os nomes da obra ficam em uma lista só e chegam a todos os leitores sozinhos — ou são gravados nos capítulos de uma vez, com uma contagem prévia das ocorrências |
| 💾 **Rascunhos que sobrevivem** | O texto do capítulo salva sozinho no navegador enquanto você escreve; contagem de palavras ao vivo |
| 🔢 **Numeração de capítulos** | O próximo número vem preenchido; números quebrados (`12.5`) para extras |
| 👑 **Acesso antecipado** | Marque capítulos como PLUS — trancados para visitantes, com selo no índice |
| 🗓️ **Agenda e fila de publicação** | Dias da semana e horário de publicação ficam nas configurações do projeto, com um resumo de quantos capítulos esperam e quando sai o próximo. Um capítulo na fila leva o selo “Na fila”, a data de saída e quanto falta |
| 🧑‍🎤 **Perfis públicos** | Página do autor com estatísticas e abas: projetos / capítulos / artigos |

### Para quem administra

| | |
|---|---|
| 🕵️ **Nada grita WordPress** | Barra de admin desligada; generator, RSD, wlwmanifest, shortlink, oEmbed, emoji, X-Pingback e as versões nos assets removidos; REST movida de `/wp-json/` para `/api/`; página de login repaginada na sua marca |
| 🛠️ **Painel de controle no site** | `/manage/`: usuários e papéis, acesso PLUS com data de validade, fila de revisão dos envios dos colaboradores, todas as obras e as configurações do site — sem precisar de `/wp-admin` |
| 🎨 **Estúdio do tema** | Um plugin que vem junto: uma tela com os controles à esquerda e o site ao vivo à direita. Cor, arredondamento, sombras, largura, fontes e os padrões de leitura — tudo visível antes de salvar, cinco conjuntos prontos, exportação em JSON |
| 🎛️ **Customizer** | Os mesmos controles sem o plugin, mais doze blocos da página inicial que podem ser desligados um a um, texto do rodapé, links sociais |
| 👥 **Coautores** | Um projeto pode carregar vários tradutores; cada um adiciona e edita os capítulos dele, e a equipe aparece na página da obra |
| 🛒 **Capítulos pagos** | Uma ponte para o WooCommerce: associe um produto a um capítulo e ele abre depois da compra, ao lado do PLUS |
| 💬 **Discussões (opcional)** | Desligadas por padrão. Ligadas: marcação própria, respostas em um nível, spoilers, curtidas, selos de autor e de equipe — nada com cara de comentário do WordPress |
| 👑 **Acesso PLUS** | Dê acesso antecipado a um leitor por 30 / 90 / 365 dias ou sem prazo; os capítulos marcados com PLUS abrem para ele automaticamente |
| 🗂️ **Gestão de obras em massa** | Um plugin que vem junto: filtre por dono, gênero, status, capa ou 18+, selecione com Shift — ou pegue tudo o que o filtro encontrou — e então publique, troque gêneros e tags, mude o dono ou a equipe, aplique uma capa ao lote, libere PLUS em todos os capítulos de uma obra, exporte CSV ou apague. Cada id é conferido de novo com `current_user_can()`, então um formulário adulterado não toca na obra de outra pessoa |
| 🧩 **Widgets próprios** | “Seleção de novels” (visualizações / nota / novidades / atualizações) e “Últimos capítulos” |
| 🚫 **Sem comentários em lugar nenhum** | Entregue de propósito sem discussões — front-end, templates e admin todos limpos |
| 👥 **Contas nas suas regras** | O interruptor do cadastro e o papel que uma conta nova recebe (autor / colaborador / leitor) ficam no customizer; tentativas repetidas são freadas e um campo escondido pega robôs |
| 🌐 **Pronto para tradução** | 982 strings no tema e mais 257 nos três plugins que vêm junto — original em russo, `.mo` compilados em inglês e português do Brasil, além do script de build |

![Leitor](screenshots/04-reader.jpg)

## Como se compara

| | Este repositório | Temas pagos de marketplace | Plataformas SaaS de novels |
|---|---|---|---|
| Preço | **Grátis, GPL** | US$ 59–99 + renovações | Divisão de receita / mensalidade |
| Código que dá para ler | **Sim, ~19 mil linhas, API comentada** | JSON ofuscado de construtor | Nenhum |
| Exige construtor de páginas | **Não** | Em geral sim | n/d |
| npm / composer / build | **Nenhum** | Muitas vezes | n/d |
| Chamadas externas em execução | **Zero** | Fontes em CDN, rastreadores | Tudo |
| Painel do autor no front-end | **Sim** | Raro | Sim |
| Leitor em tela cheia com configurações | **Sim** | Raro | Sim |
| Os leitores e os dados são seus | **Sim** | Sim | **Não** |
| Parece WordPress | **Não** | Sim | n/d |

## Stack técnica

Deliberadamente sem graça e com poucas dependências — dá para ler tudo em uma noite.

| Camada | O que é usado | Por quê |
|---|---|---|
| CMS | **WordPress 6.4+** (testado até 7.0), tema clássico, sem FSE | O editor de site não expressa um leitor, um painel de autor ou um ranking sem dez plugins |
| PHP | **7.4+**, API do WordPress em PHP procedural | Sem composer, sem autoloader, sem framework — entra em qualquer hospedagem |
| Framework CSS | **Bootstrap 5.3.3**, empacotado localmente em `assets/vendor/` | Grade, navbar, offcanvas, modal, dropdown, abas, formulários, paginação — acessíveis e testados no mundo real |
| Camada de design | **CSS próprio com tokens em HSL** (`style.css`, `skin.css`, `pages.css`, `parts.css`) | O Bootstrap é repaginado por variáveis CSS; as escalas clara e escura vêm de contraste medido |
| JS | **ES5 puro**, ~3,2 mil linhas + o bundle do Bootstrap | Sem etapa de build, sem npm, sem framework |
| Modelo de dados | Dois tipos de post (`novel`, `chapter`), três taxonomias (`genre`, `novel_tag`, `novel_status`), post meta | WordPress padrão — o seu conteúdo continua portátil |
| Editor | Editor próprio em `contenteditable`, ~600 linhas | Um capítulo precisa de limpeza de colagem, quebra de cena e foco — não de um construtor de páginas |
| Armazenamento no cliente | `localStorage` para biblioteca, histórico, configurações de leitura, glossário e rascunhos | O leitor guarda o lugar dele sem criar conta |
| REST | Três rotas sob `/api/xin/v1/` — `rate`, `like`, `skin` | Avaliação anônima, curtidas nas discussões e a prévia ao vivo do estúdio, sem plugin |
| i18n | Gettext `.po` / `.mo` + script de build | Nenhum plugin de tradução necessário |
| Ícones | Sprite SVG inline em PHP | Sem fonte de ícones, sem requisição externa |
| Fontes | Pilha do sistema | Nada carregado do Google |
| Plugins que vêm junto | **XI Studio**, **XI Novels Import**, **XI Novels Manager** — ~3,7 mil linhas de PHP | Todos opcionais: o tema roda sozinho, e os plugins acrescentam o estúdio, a importação em massa e a gestão em massa |
| Ambiente de desenvolvimento | Servidor embutido do PHP + drop-in **SQLite** | Prévia sem instalar MySQL |

## Instalação em dois minutos

```bash
git clone https://github.com/rurumiru/wordpress-novel-themes.git
cp -r wordpress-novel-themes/themes/xi-novels /caminho/para/wordpress/wp-content/themes/
```

**Aparência → Temas → XI Novels → Ativar.** Na ativação o tema cria as páginas “Painel do autor” e “Minha biblioteca” e cadastra os status de lançamento (Em andamento / Concluído / Congelado / Anunciado). Deixe os links permanentes em *Nome do post* e adicione a primeira obra em **Novels → Adicionar**.

### Experimente sem banco de dados

Sem MySQL? O repositório traz uma receita de sandbox: o servidor embutido do PHP mais o drop-in oficial de SQLite, já com obras e capítulos de demonstração.

```bash
php -S localhost:8080 -t wordpress tools/dev-router.php
```

Passo a passo: **[docs/install.md](docs/install.md)** ([RU](docs/install.ru.md)).

## Estrutura do repositório

```
themes/xi-novels/      o tema — tudo que foi descrito acima vive aqui
  inc/                 tipos de post, metaboxes, template tags, customizer,
                       widgets, painel do autor, i18n, nav walkers, limpeza
  template-parts/      seções da página inicial, catálogo, telas do painel
  assets/              css (7 arquivos), js (5 arquivos), vendor/bootstrap
  languages/           en_US e pt_BR, .po + .mo
demo/                  conteúdo de demonstração: um plugin com dois botões e scripts de CLI
plugins/               xi-studio (estúdio do tema), xi-novel-import (importação
                       em massa), xi-novel-manager (gestão de obras em massa) e notas
                       sobre quais plugins de terceiros o projeto usa e por quê
tools/                 roteador do servidor de dev, importador em massa, gerador de traduções,
                       i18n/ com um mapa RU -> idioma por língua
docs/                  instalação, publicação, importação, customização, desenvolvimento
screenshots/           como isso se parece
```

## Documentação

* **[Instalação](docs/install.md)** — instalação em produção, sandbox de desenvolvimento em SQLite, links permanentes, primeira obra ([RU](docs/install.ru.md))
* **[Publicação](docs/authoring.md)** — o painel, numeração de capítulos, acesso antecipado ([RU](docs/authoring.ru.md))
* **[Importação e uploads pesados](docs/import.md)** — o importador incluído: manifestos JSON/CSV **e pastas ou arquivos ZIP com capítulos em `.txt` / `.html` / `.md`**, mais receitas de WP All Import e WP-CLI, e cada limite de PHP / nginx / LiteSpeed / Cloudflare que precisa ser levantado antes que capas grandes subam ([RU](docs/import.ru.md))
* **[Customização](docs/customizing.md)** — tokens de design, opções do customizer, tema-filho, hooks
* **[Desenvolvimento](docs/development.md)** — mapa dos arquivos, modelo de dados, hierarquia de templates, traduções, estilo de código
* **[Conteúdo de demonstração](demo/README.md)** — um plugin que enche o site com 12 obras, 48 capítulos, posts de blog e banners a partir de **Ferramentas → Conteúdo de demonstração**, e remove tudo com um botão; scripts de CLI para servidores com SSH

## Capturas de tela

Cada imagem abaixo é o próprio tema, rodando com o conteúdo de demonstração deste repositório. Nada é maquete.

### Leitura

**Página inicial** — banner dentro do contêiner do site, atalhos e o bloco de tendências.

![Página inicial: banner, atalhos, obra em alta](screenshots/01-home.jpg)

**Catálogo** — chips de gênero, filtro de status, cinco ordens, seis capas por linha.

![Catálogo: chips de gênero, filtros e uma grade de capas](screenshots/02-catalog.jpg)

**Página da obra** — um cabeçalho compacto e depois seções planas: descrição, índice com busca e uma barra lateral com fatos, nota e obras parecidas.

![Página da obra: cabeçalho com capa, descrição, índice e barra lateral](screenshots/03-novel.jpg)

**Leitor** — sem cabeçalho do site, sem rodapé, sem barra lateral. A barra some enquanto você lê.

![Leitor em tela cheia com o texto do capítulo](screenshots/04-reader.jpg)

### Contas e administração

| | |
|---|---|
| ![Página de entrada e cadastro](screenshots/14-account.jpg) | ![Painel de controle com a lista de usuários](screenshots/13-manage.jpg) |
| **Entrar e cadastrar** no próprio site — uma página centralizada para login, cadastro e recuperação de senha | **Painel de controle** em `/manage/` — papéis, acesso PLUS com prazo, fila de revisão, obras e configurações |
| ![Perfil do autor com abas e estatísticas](screenshots/07-profile.jpg) | ![Painel de configurações de leitura](screenshots/06-reader-settings.jpg) |
| **Perfil do autor** — capa, estatísticas, pódio das obras mais lidas, abas | **Configurações de leitura** — corpo, entrelinha, largura da coluna, com ou sem serifa, quatro papéis |

### Todo o resto

| | |
|---|---|
| ![Feed de atualizações agrupado por dia](screenshots/08-updates.jpg) | ![Blog com uma matéria de capa](screenshots/09-blog.jpg) |
| **Atualizações** — cada capítulo novo, agrupado em Hoje / Ontem / data | **Blog** — matéria principal, pílulas de categoria, barra lateral |
| ![Leitor no esquema escuro](screenshots/05-reader-alt.jpg) | ![Página da biblioteca](screenshots/10-library.jpg) |
| **Esquema escuro** — grafite neutro, trocado pelo cabeçalho | **Biblioteca** — marcadores e histórico, guardados no navegador |
| ![A mesma página inicial em inglês](screenshots/11-home-en.jpg) | ![Layout móvel com navegação inferior](screenshots/12-mobile.jpg) |
| **Inglês** — o mesmo site, `?lang=en` ou o seletor do cabeçalho | **Celular** — navegação inferior, layout de uma coluna |

## Idiomas

A interface vem em **russo** (as strings de origem), **inglês** (`languages/en_US.mo`) e **português do Brasil** (`languages/pt_BR.mo`) — 982 strings cada, mais 257 nos plugins que vêm junto (estúdio 36, importação 127, gestão 94). O visitante troca pelo controle RU / EN / PT no cabeçalho e a escolha fica em um cookie; **Customizer → Marca → Idioma principal** decide com o que o site abre para quem chega pela primeira vez.

Um quarto idioma é um arquivo só — um mapa PHP de string em russo para tradução:

```bash
cp tools/i18n/en_US.php tools/i18n/de_DE.php
# traduza o lado direito de cada linha e depois
php tools/build-translations.php
```

O script relê cada string traduzível do tema, aponta o que falta e o que sobra em um mapa e escreve `.po` e `.mo` para cada mapa em `tools/i18n/`. Registre o idioma em `xin_languages()` (`inc/i18n.php`) e ele entra no seletor do cabeçalho. As strings do próprio WordPress e os formatos de data vêm do pacote de idioma do site — instale-o em **Configurações → Geral** se o admin também precisar falar essa língua.

## Roadmap

Ideias que cabem na regra do “sem dependências”. Vote com 👍 nas issues ou mande um PR.

### Pronto

- [x] **Espaço do leitor** — uma página que mostra a plataforma por dentro: quem fala, sobre o que discutem, o que estão lendo agora. No estilo terminal: grade e varredura no fundo, cantos cortados, leituras monoespaçadas, barras segmentadas. Nada de novo é coletado sobre as pessoas além de um anel de 40 leituras recentes
- [x] **Rankings como página própria** — três quadros, três janelas de tempo, filtro por gênero e nota ponderada
- [x] **Gestão de obras em massa** — filtrar, selecionar com Shift e agir sobre centenas de obras de uma vez, com saída em CSV
- [x] Exportação EPUB / FB2 de uma obra inteira
- [x] Sequências de leitura e conquistas simples
- [x] Equipes de tradução (vários autores por projeto)
- [x] Capítulos pagos opcionais por uma ponte com o WooCommerce
- [x] Importação em massa de capítulos — `.txt`, `.md`, `.html`, `.docx` e lotes ZIP, processados dez arquivos por vez para aguentar hospedagem compartilhada. Exporte um Google Doc como `.docx` e ele segue o mesmo caminho
- [x] Módulo opcional de discussões (opt-in, desligado por padrão)
- [x] Ferramentas de parágrafo no leitor e leitura em voz alta com vozes do aparelho
- [x] Fila de capítulos agendados com contagem regressiva até a publicação
- [x] Três idiomas de ponta a ponta: russo, inglês e português do Brasil — tema, estúdio e os dois plugins

### A seguir

- [ ] **Comparativo de frameworks** — Bootstrap contra um subconjunto de Tailwind, UnoCSS, Bulma, Pico e nenhum framework, julgados por tamanho após gzip, LCP em celular mediano, deslocamento de layout e conforto de leitura
- [ ] **Build sem framework CSS** como final provável: o tema já carrega o próprio sistema de tokens, então um framework pode acabar sendo peso morto
- [ ] Revisão tipográfica do leitor: comprimento de linha medido, margens ópticas, ritmo de linha por idioma
- [ ] Mais idiomas: DE, ES, ID, VI

## FAQ

**Funciona em hospedagem compartilhada?**
Sim. É um tema clássico: arquivos PHP, CSS, JS. Sem composer, sem node, sem cron.

**Preciso de algum plugin?**
Não. Cache e antispam são opcionais — veja [plugins/](plugins/README.md).

**Posso vender um site feito com ele?**
Pode. É GPL. Renomeie, refaça a marca, cobre por isso — sem exigência de atribuição.

**Dá para usar o design sem WordPress?**
O CSS e o JS são oferecidos também sob MIT, então sim.

**Funciona com o editor de blocos?**
Capítulos e obras usam o editor clássico de propósito (autores escrevem textos longos). Páginas e posts de blog funcionam normalmente com blocos.

**Quantos capítulos ele aguenta?**
As listas de capítulos são cacheadas e ordenadas por meta numérica; sites com milhares de capítulos por obra são o alvo do projeto.

**Existe um demo?**
Sim — [xi.community](https://xi.community) é um site de verdade rodando este tema. Você também pode clonar o repositório e rodar o sandbox: ele semeia um catálogo de demonstração com um comando, e todas as capturas acima vêm dele.

## Usando o tema? Um link de volta cai bem

A licença não pede nada além da GPL: use o tema comercialmente, bifurque, mude a marca, venda serviços em cima dele. Mas se o seu site roda com ele e você menciona isso em algum lugar — no rodapé, em uma página sobre, em um post — isso ajuda de verdade o projeto a seguir vivo.

O tema traz uma linha pronta: **Customizer → rodapé → “Feito com XI Novels”**, desligada por padrão. Ou cole a sua:

```html
<a href="https://github.com/rurumiru/wordpress-novel-themes">Feito com o tema XI Novels</a>
```

**Quer apoiar o trabalho?** Apareça no Telegram — [📢 canal](https://t.me/licht_re) e [💬 chat da comunidade](https://t.me/xicommunity). Relatos de bug, capturas do seu site, ideias de recursos e um simples obrigado são todos bem-vindos; cada versão é discutida lá primeiro.

## Contribuindo

Issues e pull requests são bem-vindos. Duas regras: **nenhuma etapa de build** (o tema continua editável em um editor de texto) e **nenhuma dependência externa em execução**.

O trabalho aceito é creditado pelo nome no [CHANGELOG.md](CHANGELOG.md) — foi assim que chegaram as ferramentas de parágrafo do leitor e o estúdio de vozes, de [@HeavenlyCatCodes](https://github.com/HeavenlyCatCodes).

## Licença

**GPL-2.0-or-later** para o tema como um todo — a única licença correta para um trabalho derivado do WordPress.

As partes que não derivam do WordPress — o CSS em `assets/css/` e o JavaScript em `assets/js/` — são oferecidas também sob **MIT**, para que o sistema de design possa viajar para projetos fora do WordPress. O Bootstrap 5.3.3 vem sob a licença MIT dele. As capturas de tela são ilustrativas e não fazem parte do código licenciado.

---

<div align="center">

**Se isto te poupou cem dólares e um fim de semana — deixe uma estrela.** ⭐

*Palavras-chave: tema wordpress para light novel · tema para ranobe · web novel wordpress · plataforma de webnovel · site de novels · tema leitor de capítulos · tema wordpress de ficção · tema de novels grátis · plataforma de novels GPL · tema para tradução de novels · дизайн для ранобэ · тема WordPress для новелл*

</div>
