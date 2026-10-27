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

Envie o projeto sem `.env`, `vendor/` opcionalmente, e configure o document root do domínio para `public/`. O arquivo `public/.htaccess` encaminha as rotas ao front controller. Crie o `.env` no diretório privado do projeto, execute `composer install --no-dev` via SSH ou envie `vendor/`, e configure o banco com o prefixo fornecido pelo cPanel. Não publique o arquivo `.env` nem a pasta `storage` diretamente.

### Apache local

O projeto não deve ser acessado como `http://localhost/video_publisher` enquanto a pasta estiver fora do document root do Apache. Aponte um VirtualHost ou Alias para `C:/projetos_andre/video_publisher/public`, habilite `AllowOverride All` e reinicie o Apache. Exemplo de VirtualHost:

```apache
<VirtualHost *:80>
    ServerName autoshorts.local
    DocumentRoot "C:/projetos_andre/video_publisher/public"
    <Directory "C:/projetos_andre/video_publisher/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Alternativamente, use o servidor PHP local: `php -S localhost:8000 -t public` e acesse `http://localhost:8000/login`.

## API inicial

`GET /api/v1/health` verifica a aplicação e a conexão com o banco, sem autenticação. `GET /api/v1/dashboard` retorna os indicadores do administrador autenticado e exige a sessão do painel. Autenticação própria para GitHub Actions será definida em etapa futura, sem armazenar tokens até existir uma decisão de segurança e uma estrutura de banco aprovada.

As consultas autenticadas `GET /api/v1/assuntos` e `GET /api/v1/canais` retornam os cadastros do administrador conectado. Nesta etapa a API não altera registros nem executa processamento externo.

Também estão disponíveis as consultas autenticadas `GET /api/v1/configuracoes`, `GET /api/v1/rotinas` e `GET /api/v1/historico`.

Com Bearer Token e os escopos correspondentes, a API também oferece `GET /api/v1/fila/assuntos`, `POST /api/v1/execucoes`, `POST /api/v1/execucoes/logs` e `POST /api/v1/execucoes/encerrar`. Esses endpoints apenas controlam a fila e os registros de execução; não geram vídeos nem publicam conteúdo.

A autenticação Bearer para integrações externas está documentada em `docs/api-auth.md` e implementada para o endpoint `/api/v1/me`. A tabela `api_tokens` foi criada pela migração aprovada.

## Esquema analisado

A autenticação usa somente as colunas existentes em `usuarios`: `id`, `nome`, `email`, `senha_hash`, `perfil`, `ativo` e `ultimo_login_em`. Nenhuma migração foi criada ou executada.
