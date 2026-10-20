# XI Novels 3 — beta

**Versão beta:** a [3.0.4-beta.1](https://github.com/rurumiru/wordpress-novel-themes/releases/tag/v3.0.4-beta.1) é a versão atual. O desenvolvimento continua; esta ainda não é uma versão estável.

**XIN-V2 é o tema principal para WordPress:** um portal de novels, catálogo visual, leitor de um capítulo por página e estúdio para autores e tradutores.

[English](README.md) · [Русский](README.ru.md) · [Apresentação visual](docs/design-v3.md) · [Instalação](docs/install.md)

![Novo início do XI Novels](screenshots/xin-v2-3/home-light.jpg)

## Novo design

- Logo XI Novels e navegação com pesquisa, conta, recursos e acesso ao estúdio.
- Página inicial com destaques, rankings, carrossel ilustrado, gêneros, histórico e atualizações.
- Catálogo de capas com pesquisa, filtros e ordenação.
- Página da obra com créditos, estatísticas, avaliações de 1 a 5, favoritos, capítulos, comentários e exportação EPUB/FB2.
- **Um capítulo por página**, navegação fixa e preferências de leitura persistentes.
- Tipografia Manrope, cartões de comentários, identificação de funções, respostas e reações.
- Layout responsivo, menu móvel, navegação inferior, temas claro/escuro e interface RU/EN/PT-BR.
- Estúdio no frontend com edição de obras e capítulos, agendamento, equipes e glossário, respeitando as permissões do WordPress.

Wuxiaworld, KakaoPage e Novelpia são referências de estrutura. O código, logo e ilustrações de demonstração são recursos do projeto. Veja a [apresentação completa](docs/design-v3.md).

| Catálogo | Obra |
|---|---|
| ![Catálogo](screenshots/xin-v2-3/catalog-desktop.jpg) | ![Obra](screenshots/xin-v2-3/title-desktop.jpg) |

## Instalação

Requer WordPress 6.4+ e PHP 8.0+. `mbstring`, processamento de imagens e `ZipArchive` são necessários para os recursos correspondentes. Não há etapa de build do frontend; fontes e scripts são locais.

1. Copie `themes/xin-v2` para `wp-content/themes` e ative **XI Community Library**.
2. Copie `plugins/xin-setup` para `wp-content/plugins` e ative **XIN Setup**.
3. Abra o painel depois da ativação/importação. O módulo configura páginas, templates, menus, permalinks, taxonomias e contadores. O relatório fica em **Ferramentas → Configuração do tema**.
4. Importe obras, capítulos e capas; verifique o catálogo e a leitura.

[Instalação detalhada](docs/install.md) · [Módulo de configuração](plugins/xin-setup/README.md)

Apache precisa de `.htaccess` gravável. Nginx precisa do fallback WordPress na configuração do servidor. O plugin preserva conteúdo editorial existente e menus já atribuídos.

O tema alternativo `themes/xin-com` foi mantido. Capturas atuais: `screenshots/xin-v2-3`. Capas geradas e prompts: `docs/media/covers`. Banco de dados, uploads, contas e senhas locais não estão incluídos.

Consulte o [relatório de validação local](docs/validation-v3.md). Compatibilidade com todas as hospedagens e plugins externos não foi verificada.

Licença GPL-2.0-or-later: [LICENSE](LICENSE).
