# AluandTech Auto Shorts

Painel administrativo PHP MVC do MVP. A geração de vídeos e a publicação externa ainda não fazem parte desta etapa.

## Instalação local

1. Crie o banco `aluandtech_autoshorts` usando o schema SQL fornecido.
2. Copie `.env.example` para `.env` e informe os dados do MySQL.
3. Execute `composer install`.
4. Inicie com `php -S localhost:8000 -t public`.
5. Acesse `http://localhost:8000/login`.

O primeiro administrador deve ser criado de forma controlada no banco usando um hash produzido por `password_hash()`; o sistema não inclui senha padrão.

## cPanel / HostGator

Envie o projeto sem `.env`, `vendor/` opcionalmente, e configure o document root do domínio para `public/`. Crie o `.env` no diretório privado do projeto, execute `composer install --no-dev` via SSH ou envie `vendor/`, e configure o banco com o prefixo fornecido pelo cPanel. Não publique o arquivo `.env` nem a pasta `storage` diretamente.

## Esquema analisado

A autenticação usa somente as colunas existentes em `usuarios`: `id`, `nome`, `email`, `senha_hash`, `perfil`, `ativo` e `ultimo_login_em`. Nenhuma migração foi criada ou executada.
