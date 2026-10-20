# XI Novels 3 — beta

[English](README.md) · [Русский](README.ru.md) · **Português**

<picture>
  <source media="(prefers-color-scheme: dark)" srcset="screenshots/xin-v2-3/home-dark.jpg">
  <img alt="XI Novels 3" src="screenshots/xin-v2-3/home-light.jpg">
</picture>

<sub>A imagem acima acompanha o seu tema do GitHub, claro ou escuro.</sub>

XI Novels é um tema WordPress para sites de novels e traduções: página inicial, catálogo, página do título, leitor, discussões e um estúdio para autores e tradutores. Na versão 3, o **XIN-V2** passou a ser o tema principal. O tema anterior, **XIN-Com**, continua no repositório como alternativa, e os dois usam o mesmo banco de dados.

> **Versão beta.** O modelo de dados `_xin_*`, a hierarquia de templates e os hooks são estáveis; a marcação dos componentes compartilhados pode mudar entre as betas. Faça backup do banco de dados e da pasta `uploads` antes de instalar num site em produção.
>
> **Baixar:** [a versão mais recente](https://github.com/rurumiru/wordpress-novel-themes/releases/latest) — o tema, o XIN Setup e a demonstração.

## Versão mais recente: 3.0.4-beta.1 — perfis de membros

Cada membro agora tem uma página pública: banner, avatar, função, ano de cadastro, bio, links e obras publicadas. As obras incluem os títulos do próprio membro e os projetos em que ele faz parte da equipe. As estatísticas consideram a lista inteira; as capas são paginadas de doze em doze.

Rascunhos, projetos de outras pessoas, e-mails e histórico de leitura nunca aparecem na página pública. Os links para a biblioteca pessoal e para o estúdio só aparecem para o dono do perfil. Qualquer membro conectado, inclusive assinantes, pode alterar bio, pseudônimo, subtítulo, links e avatar pelo mesmo formulário de perfil do site.

<img width="720" src="screenshots/xin-v2-3/profile-desktop.jpg" alt="Perfil de membro">

## O caminho: da primeira beta à versão 3

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

## Front-end

- **Sem etapa de build.** Sem npm, sem bundler, sem framework CSS. Os estilos usam o sistema de tokens do próprio tema. Os scripts são JavaScript puro, divididos em módulos (interface, leitor, discussões, glossário, notificações) e carregados no fim da página.
- **Nenhuma requisição para outro domínio.** As fontes vêm com o tema — Manrope na interface e mais três famílias, só cirílico e latim. Os ícones são SVG embutido.
- **Leitor.** Um capítulo por página e uma barra fixa: capítulo anterior e próximo, sumário, histórico e comentários. Tamanho do texto, largura da coluna, papel e tema ficam salvos.
- **Discussões próprias.** `comment_form()` e `wp_list_comments()` não são usados, porque trazem marcação alheia e recarregam a página. Respostas, reações e selos de função — leitor, editor, autor.
- **Esquemas claro e escuro** e seletor RU / EN / PT. A escolha de idioma fica num cookie, então os links são compartilhados normalmente.
- **Celular.** Menu em gaveta e navegação inferior; o layout foi verificado em 320, 390 e 768 px. O carrossel funciona com setas, pontos, teclado e rolagem comum, e respeita `prefers-reduced-motion`. A tecla <kbd>/</kbd> abre a busca.
- **Web Push** para novos capítulos via service worker.

## Back-end

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

## Por que tudo acontece fora do painel

O WordPress parte do princípio de que o trabalho é feito no `/wp-admin`. Num site de leitura isso atrapalha quase todo mundo que trabalha nele, por isso leitores, autores e moderadores têm cada um a sua tela no próprio site. Permissões, dados e verificações continuam com o WordPress.

- **Leitores.** O `wp-login.php` parece a página de outro site, e aparece justamente no momento em que a pessoa confia pela primeira vez seu e-mail e sua senha ao site. Por isso login, cadastro e recuperação de senha ficam numa página comum, com o visual do site. Usuário e senha são verificados pelo núcleo (`wp_signon()`, `register_new_user()`); o tema não tem criptografia própria. Depois de oito tentativas erradas do mesmo endereço, o formulário fecha por 15 minutos. A barra de ferramentas do WordPress fica oculta nas páginas do site.
- **Autores e tradutores.** Quem publica capítulos precisa de uma ferramenta de escrita, não de um CMS. O estúdio no site cuida de títulos, capítulos, rascunhos, agendamento, acesso antecipado, equipe do projeto, glossário e perfil. Não existe uma função especial: é usada a função padrão de Autor do WordPress. Coautores editam os títulos de cuja equipe fazem parte.
- **Moderadores.** O moderador precisa de três coisas: a fila de comentários, as funções e os pedidos para virar autor. Não faz sentido mandá-lo para as tabelas do painel por causa disso, onde ele também veria todo o resto, que não precisa ver. O painel do site oferece exatamente essas três coisas. Por ele não dá para promover nem rebaixar um administrador, e ninguém pode mudar a própria função.
- **O que continua com o WordPress.** Toda ação no site passa por verificação de nonce e por `current_user_can()`. Os dados são posts, metadados e a tabela de comentários comuns. Por isso moderação, antispam, importadores e backups continuam funcionando, e o `/wp-admin` segue disponível para o administrador fazer todo o resto.

## Instalação

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

## O que foi verificado e o que ainda não foi

Verificado numa instalação local: PHP 8.3, WordPress e banco SQLite. Foram cobertos configuração, avaliações, estúdio, discussões, biblioteca, exportação EPUB e FB2, troca de idioma e de esquema, e layouts em 320, 390 e 768 px. O tema não foi testado em aparelhos iOS ou Android físicos. Entrega de e-mail e Web Push, pagamentos via WooCommerce e combinações com plugins de terceiros precisam ser testados no seu próprio ambiente.

## Repositório

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

## Rode as verificações você mesmo

```bash
php tools/verify-release.php /path/to/test-wordpress/wp-load.php
```

O script altera configurações do site e cria registros temporários, que depois vão para a lixeira. Rode-o só numa instalação de teste.

Licença GPL-2.0-or-later, veja [LICENSE](LICENSE).

[Registro de alterações](CHANGELOG.md) · [Apresentação do design](docs/design-v3.md) · [Instalação](docs/install.md) · [Relatório de verificação](docs/validation-v3.md)

## Como fica

<table>
<tr>
<td width="33%"><img src="screenshots/xin-v2-3/catalog-desktop.jpg" alt="Catálogo"><br><sub>Catálogo</sub></td>
<td width="33%"><img src="screenshots/xin-v2-3/title-desktop.jpg" alt="Título"><br><sub>Título</sub></td>
<td width="33%"><img src="screenshots/xin-v2-3/home-carousel.jpg" alt="Carrossel"><br><sub>Carrossel</sub></td>
</tr>
<tr>
<td><img src="screenshots/xin-v2-3/reader-desktop.jpg" alt="Leitor"><br><sub>Leitor</sub></td>
<td><img src="screenshots/xin-v2-3/studio-editor.jpg" alt="Estúdio"><br><sub>Estúdio</sub></td>
<td><img src="screenshots/xin-v2-3/comments-desktop.jpg" alt="Discussões"><br><sub>Discussões</sub></td>
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

<sub>Celular, 390 px; o ranking aparece em português do Brasil.</sub>
