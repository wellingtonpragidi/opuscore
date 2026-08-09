# Instalação

## O Opus Core ainda não possui um instalador por interface visual. 

De qualquer forma, a criação do banco de dados ainda precisaria ser manual e o restante da instalação é simples.

### Etapas:
1. Crie um banco de dados.
2. Importe as tabelas usando o arquivo `scheme.sql`.
3. Envie os arquivos para a hospedagem via FTP ou coloque no diretório do seu servidor local.
4. Defina as credenciais de conexão com o banco de dados no arquivo `config.php` localizado na raiz do sistema.
5. Existem blocos separados para ambientes de desenvolvimento e produção (local e prod).
6. Após isso o site já estará funcionando com o template básico enviado junto ao pacote e ativo por padrão.
7. Login provisório do painel:
    - URL: http://seusite.ext/dashboard
    - Nome: Administrator do Sistema
    - E-mail: admin@localhost.ext
    - Senha: Admin123
8. No Opus Core, Administradores e Usuários são totalmente separados, incluindo tabelas e regras próprias.
9. Vá até Configurações → E-mail e defina as credenciais de e-mail do sistema.
10. Após isso, crie um novo administrador com um e-mail válido.
11. Após acessar o painel com a nova conta, exclua o administrador provisório.
12. O administrador provisório pode ser usado em ambiente de desenvolvimento. Apenas não utilize essa conta em produção.
13. Agora o restante é configuração: título do site, preferências do sistema, template, SEO etc. 