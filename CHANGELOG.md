# Registro de alterações

## [1.2.0] - 08/10/2026

### Adicionado (Added)
- Adicionado dois novos campos nas configurações de e-mail SMTP  
- Duas novas funções de e-mail em `dist/settings.php`: `email_dest()` e `email_reply_to()`  
- Arquivos `components.php`, `presentations.php` e  `resource.php` em `/web/iterators/` a fim de organizar declaração de funções  
- **Interface de debug em modo escuro:** Telas em HTML/CSS com tema escuro para exceções não capturadas (`set_exception_handler`) e avisos de execução (`set_error_handler`), melhorando a leitura de depuração de exceções.
- **Suporte a `'ALL'` em `DISPLAY_ERRORS`:** Nova opção via string para permitir a exibição de todos os avisos do PHP, incluindo alertas de depreciação (`E_DEPRECATED & E_USER_DEPRECATED`).
- **Nova constante `ERROR_REPORTING`:** Garante um valor booleano confiável (`true`/`false`) para verificações condicionais internas do framework sem quebrar a retrocompatibilidade.

- Adicionada a possibilidade de editar o slug de uma categoria independentemente do título.
- Ao alterar o slug de uma categoria, os segmentos das publicações relacionadas são reconstruídos automaticamente.


### Modificado (Changed/Modified)
Nomes de funções de e-mail **SMTP**: prefixos `email_` passam a ser `smtp_` em `dist/settings.php`  


### Correções (Fixed)
Corrigida a inclusão dos arquivos que compõem partes da view de **menus** no painel, que não estavam sendo carregados corretamente em instalações distribuídas pelo pacote de download.


### Removido (Removed)
Método `Selection::display()`


### Melhorias
- **Compatibilidade com PHP 8.5+:** O modo `DISPLAY_ERRORS = true` agora filtra `E_DEPRECATED` e `E_USER_DEPRECATED` por padrão. Isso evita telas poluídas ao rodar em versões mais recentes do PHP, mantendo o suporte a partir do PHP 8.1+.
- **Limpeza do Buffer de Saída (`Output Buffer`):** Garantia de descarte de buffer (`ob_end_clean`) antes de renderizar erros fatais, impedindo layouts quebrados na exibição de exceções.
- **Isolamento em Ambiente de Produção:** Com `DISPLAY_ERRORS = false`, o sistema emite o cabeçalho HTTP 500 correto e oculta qualquer caminho ou dado sensível do servidor.
- **Avisos Inline Legíveis:** Avisos leves (`E_WARNING`, `E_NOTICE`) agora são formatados em blocos escuros diretamente no ponto onde ocorreram, sem interromper o fluxo do código.

Adicionada a possibilidade de filtrar artigos por categoria na tabela de listagem do painel.  
A contagem de registros e a paginação passam a considerar a categoria selecionada.

Adicionada proteção contra perda de alterações não salvas em formulários de edição e configuração.  
Mantidos os dados preenchidos no formulário quando ocorre um erro no envio.



### Notas
Observações finais ou alterações que não se enquadram nos títulos acima.
