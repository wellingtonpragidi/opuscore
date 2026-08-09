# Registro de alterações

## [1.1.1] - ?/?/2026

### Adicionado
Bloqueios contra possíveis registros duplicados para imagem de destaque na tabela `medias`  

Adicionada a propriedade `version` à estrutura JSON da coluna `attachment`, permitindo o gerenciamento e a invalidação de cache de arquivos de forma precisa.

› Contexts agora possuem identificador numérico único (ID) global.
› Suporte para relacionamentos de Contexts baseados em `ID`, e já implementado com inserção de Mídias.

Método `OpusCore.debug()` para depuração condicional em JavaScript no painel de controle, com identificação automática da origem: 
- Nome do método, 
- Caminho e nome do arquivo após; 
- Número da linha; 
- Removido o numero da coluna;
Exemplo: `[ loadData: /js/routes/editor/media.js Linha: ]`  
_A exibição pode variar um pouco dependendo do navegador_

Roteador para controladores assíncronos (`class RouterAsync`) do painel, e mais:
- Resolução automática de rotas por estrutura de diretórios.
- Bloqueio de acesso direto aos controladores assíncronos, permitindo execução apenas pelo roteador.

Adicionado botão gerar senha nos formulários de ativação e redefinição de senha no ambiente de acesso público.

**Usuário e acesso público**:
- Classe `Access` para gerenciamento de permissões e acessos de usuários.
- Página pública de perfil de usuário (`templates/*/user.php`).
- Atualização assíncrona da imagem de perfil.
- Atualização assíncrona de nome e nome de usuário.
- Sistema de redirecionamento após autenticação para retornar à página de origem.
- Novos helpers para exibição de informações do perfil do usuário.
- Opção de itens dinâmicos de autenticação (`Entrar`, `Registrar`, `Perfil` e `Sair`) na renderização de menus.

**Comentários**:
- Adicionado o campo `updated` à tabela `comments`.
- Adicionado carregamento condicional dos recursos de comentários por meio do registro de funcionalidades (`append_feature()`/`has_feature()`).



### Corrigido
- Exclusão de arquivos físicos associados aos registros de mídia.
- Correção da atualização de segment ao alterar o slug de posts.
- Remoção de atributos temporários e limpeza de elementos `<br>` utilizados e gerados durante a inserção de incoporados no editor
- Adições e correções em registro e atualização de administradores dentro do painel.
- Correção do status `404` em rotas de acesso público.
- Correção da renderização dos itens de autenticação em menus armazenados em cache.
- Correção do fluxo de retorno (`redirect`) após login.
- Correção da higienização do conteúdo de comentários.



### Atualizado
Gerenciamento de mídias enviadas pela biblioteca de arquivos do editor rich-text padrão:
- Melhorada a consistência entre seleção, exclusão e carregamento de mídias em interfaces assíncronas.
- Ajustes de estabilidade e manutenção no fluxo de upload, seleção e exclusão de mídias.
- Refinado o tratamento de anexos (`attachment`) para diferentes tipos de arquivos e imagens.



### Modificado
- O fluxo de criação de Contexts foi reformulado.
    - Passam a ser criados inicialmente com informações mínimas, seguindo o mesmo modelo utilizado por Pages e Posts.
    - Ajustes na interface da tela de inserção de Contexts.
    - Melhorado o fluxo de redirecionamento após criação de Contexts.

Constantes `DISPLAY_ERRORS` e `ERROR_LOG` para controle centralizado de exibição e registro de erros.

Simplificação da estrutura de entrada do painel.

Simplificação da obtenção da URL atual (método `URL::current(string:append_query=true)`).

**Reorganização das classes `Auth`, `User` e `Access`**:
- Ambiente público `web/access/` realocado e reorganizado
- Unificação das funcionalidades de perfil na classe `User`, removendo a necessidade da antiga `UserProfile`.
- Reestruturação do fluxo de atualização do perfil utilizando o usuário autenticado em vez do usuário da rota.
- Ajustes no processamento de imagens para reutilizar o diretório original de armazenamento durante atualizações, substituindo a imagem anterior.

Simplificação da API do `Router`, permitindo obter diretamente a `case()` da rota.

Melhoria na renderização dos menus com cache e substituição dinâmica dos itens de autenticação.

Refatoração do manipulador de imagens com melhor organização dos caminhos relativos e absolutos (`relative_path` e `abs_path`).

**Comentários**:
- Refatorado o módulo para a nova estrutura do sistema.
- Tabela agora utilizam `user_id` em vez de `email` para relacionamento com usuários.
- Atualizadas consultas SQL e renderização para a nova estrutura de dados.
- Melhorada a organização interna do módulo para facilitar futuras expansões.



### Otimizado
Implementação de cache interno que impede consultas repetidas aos dados durante a mesma requisição.

Redução de consultas e reutilização de dados autenticados.



### Segurança
- Validação segura da URL de redirecionamento de autenticação, restringindo o retorno ao painel administrativo.
- Bloqueio do acesso direto aos controladores assíncronos, permitindo execução apenas por um roteador do sistema.
- Regeneração do identificador de sessão durante o processo de autenticação.



### Melhorias
Reorganização dos ativos JavaScript do painel:
- Novo sistema de carregamento condicional de scripts;

Revisão da arquitetura do editor Punk:
- Correções no sistema de seleção e inserção de mídias;
- Correções relacionadas à edição de conteúdo e sincronização do editor;
- Remoção de elementos HTML temporários gerados durante a edição;
- Ajustes no modo código-fonte;
- Correções de interface relacionadas ao cursor e comportamento do editor.

Organização da listagem de pages no painel com estrutura hierárquica (parent → child), melhorando a leitura da tabela.

Melhorado o tratamento de atualização de segmentos (URLs) de posts após alteração de slug.

Melhorado o retorno de métodos responsáveis pela inserção de itens de menu.

Centralização das validações de autenticação e permissões.

Melhor organização das rotas e controladores de acesso na parte pública.

Reescrita do JavaScript responsável pelas atualizações assíncronas do perfil.

Separação do formulário de imagem do formulário de dados do usuário, simplificando a interface e o código.  
Revisão do fluxo de upload de imagens e da validação dos arquivos enviados.

Remoção da alteração automática de qualidade das imagens como comportamento padrão, preservando ao máximo a imagem enviada pelos administradores do site.

Consultas e operações de atualização do módulo revisadas.



### Obsoleto
A chave `['title']` no array do hook `dashboard_menu`, usada para definir o texto dos itens do menu do painel, passa a ser `['label']`. `['title']` permanece suportada, até a próxima versão com breaking changes.
