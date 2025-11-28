# Todo PHP (sem CSS)
Projeto simples de Lista de Tarefas em PHP, com autenticação, CRUD de tarefas e dashboard com gráfico (Chart.js).

## Como usar
1. Coloque a pasta no `htdocs` / diretório público do seu servidor (ex: XAMPP, LAMP).
2. Ajuste `config.php` com credenciais do MySQL.
3. Importe o arquivo `database.sql` no MySQL (via phpMyAdmin ou CLI).
4. Acesse `register.php` para criar um usuário de teste, ou insira manualmente.
5. Faça login em `login.php`.

## Observações
- Não há CSS por pedido — o layout é simples e funcional.
- Senhas são armazenadas com `password_hash()`.
- Use HTTPS em produção e configure variáveis de ambiente para credenciais.
