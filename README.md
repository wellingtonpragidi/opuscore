<div align="center">

<h1><img src="./dist/assets/img/opuscore-access-logo.svg" width="180" alt="Opus Core"></h1>

<p>
  Sistema gerenciador web/CMS desenvolvido em PHP puro, com foco em simplicidade, clareza estrutural, baixo acoplamento e controle explícito da aplicação.
</p>

<br>

[![Release](https://img.shields.io/github/v/release/wellingtonpragidi/opuscore)]()
[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)]()
[![License](https://img.shields.io/badge/license-MPL--2.0%20%2B%20Commons%20Clause-yellow)]()
[![Status](https://img.shields.io/badge/status-active-2ea44f)]()

<p>
  <a href="https://opuscore.dev/docs">
    Explorar documentação »
  </a>
</p>

</div>

<hr>
<br>

## Sobre

Opus Core é um sistema gerenciador web/CMS independente construído em PHP puro.

Desenvolvido por uma única pessoa, nasceu como ferramenta de trabalho, não como produto.
Foi utilizado, reconstruído e refinado durante anos em projetos reais antes de ser disponibilizado publicamente.

O objetivo é fornecer estrutura e direção, sem controlar completamente como o sistema deve ser utilizado.

O Opus não transforma soluções simples em arquiteturas infladas, priorizando decisões diretas e visíveis no código.

Funcionalidades e características:
- Separação entre ambientes
- Painel de controle limpo e intuitivo
- Views com PHP no HTML
- SQL visível
- URLs com importância estrutural
- Container de instância
- Hooks de ação e filtro
- SEO integrado
- Carregamento assíncrono de listagens
- CSS por blocos `<style>` e `<link>` com carregamento condicional
- Geração de favicons e manifest.json

O sistema utiliza MySQL na maior parte da estrutura e armazenamento em arquivos quando isso faz sentido.
Sem extremismos: cada abordagem existe por utilidade prática. 


## Requisitos
- PHP 8.1+ (Testado com PHP 8.3)
- MySQL 8.0+ ou MariaDB 10.5+
Apache/Nginx:
- O Opus Core é utilizado atualmente em ambiente Apache com HTTPS habilitado.
  - Compatibilidade com Nginx ainda não foi oficialmente testada.
- Em **produção**, HTTPS é considerado requisito devido à segurança e compatibilidade com recursos modernos da web.

Extensões PHP necessárias:
- PDO
- mbstring
- Manipulação de Imagens: GD - de preferencia Imagick
- ZIP
- DOM
- Zend OPcache: É altamente recomendado que o PHP OPCache esteja habilitado para manter a alta performance que o Opus proporciona

O Opus Core foi projetado para uso com HTTPS habilitado.


## Instalação
Ainda não existe um instalador por interface visual. De qualquer forma, a criação do banco de dados ainda precisaria ser manual e o restante da instalação é simples.

1. Crie um banco de dados.
2. Importe as tabelas usando o arquivo `scheme.sql`.
3. Envie os arquivos para a hospedagem via FTP ou coloque no diretório do seu servidor local.
4. Defina as credenciais de conexão com o banco de dados no arquivo `config.php`.
5. Existem blocos separados para ambientes de desenvolvimento e produção.
6. Os arquivos `scheme.sql` e `config.php` ficam na raiz do arquivo `opuscore-{version}.zip`.
7. Após isso o site já estará funcionando com o Old Begin enviado junto ao pacote e ativo por padrão.
8. Login provisório do painel:
  1. URL: http://seusite.ext/dashboard
  2. Nome: Administrator do Sistema
  3. E-mail: admin@localhost.ext
  4. Senha: Admin123
9. No Opus Core, Administradores e Usuários são totalmente separados, incluindo tabelas e regras próprias.
10. Vá até Configurações → E-mail e defina as credenciais de e-mail do sistema.
11. Depois disso, crie um novo administrador com um e-mail válido. Será necessário acessar a URL enviada por e-mail para definir a senha da conta.
12. Após acessar o painel com a nova conta, exclua o administrador provisório.
O administrador provisório pode ser usado em ambiente de desenvolvimento. Apenas não utilize essa conta em produção.


## Estrutura do sistema
`/dashboard` → painel de administração
`/dist` → núcleo compartilhado
`/storage` → dados armazenados em arquivos e cachê
`/templates` → diretório base de templates
`/uploads` → arquivos carregados por administradores e usuários
`/web`  → gerador para a saída pública

Os ambientes `/dashboard` e `/web` são separados por constantes


## Templates
O template padrão é o Old Begin, que tem como objetivo oferecer poucos recursos e estruturas descomplicadas, para servir como uma base leve, organizada e funcional para diferentes tipos de conteúdo e projetos, facilitando entendimento, edição e adaptação mesmo para quem ainda não conhece o sistema.


## Filosofia

✓ Disciplina interna para quem constrói; liberdade para quem utiliza  
✓ Pragmatismo técnico inspirado na mentalidade inicial de Rasmus Lerdorf  
✓ Nomenclatura própria e consistente, evitando padrões apenas por tendência  
✓ Voltado para desenvolvedores que preferem controle explícito  
✓ Eficiência por arquitetura, não por infraestrutura externa  
✓ Preferência por comportamento visível em vez de abstrações ocultas  

Nada nasce do vazio:
nem linguagens,
nem frameworks,
nem sistemas,
nem mesmo a inteligência artificial.

Tudo nasce da necessidade.

O Opus Core segue o mesmo princípio.

Não se trata de ser “inovador” no sentido superficial, mas de um sistema onde cada decisão técnica existe por um motivo prático.

O objetivo não é competir com outros CMSs, mas existir como alternativa baseada em simplicidade e controle.

Nenhuma arquitetura substitui critério técnico.

Simplificar é evoluir quando reduz complexidade acidental sem remover complexidade necessária.


## Versionamento
Software também acumula coisas desnecessárias, crescer não é apenas adicionar funcionalidades.
Também é preciso remover excessos antes que a estrutura comece a apodrecer por dentro.

Versões anteriores continuarão disponíveis.
Nem toda melhoria deve carregar peso morto eternamente. 


## Licença
Este projeto está licenciado sob a [Mozilla Public License 2.0](https://mozilla.org/MPL/2.0/) ***com Commons Clause***, o que significa:

- **Você pode** usar, modificar e distribuir este código, inclusive criar projetos derivados.
- **Não é permitido** o uso comercial direto, como vender, revender ou oferecer serviços pagos baseados neste software, conforme definido pela [Commons Clause](https://commonsclause.com/).
- Modificações feitas nos arquivos originais do projeto devem ser disponibilizadas sob a mesma licença.

Para detalhes legais completos, consulte o arquivo [`LICENSE`](./LICENSE) na raiz do repositório.
