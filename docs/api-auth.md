# Autenticação da API externa

Os endpoints REST atuais usam a sessão administrativa do painel e são adequados apenas para consultas internas autenticadas.

Para o GitHub Actions, não é seguro reutilizar a sessão do navegador. O schema atual não possui uma tabela para tokens de integração. A proposta é adicionar `api_tokens` por migração separada, armazenando somente o hash do token, nunca o token em claro.

A migração abaixo ainda não foi executada. Depois da aprovação, será necessário implementar emissão, revogação, expiração, escopos e o middleware Bearer.
