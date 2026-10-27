-- Proposta não executada. Revisar e aprovar antes de aplicar.
CREATE TABLE api_tokens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 usuario_id BIGINT UNSIGNED NOT NULL,
 nome VARCHAR(120) NOT NULL,
 token_hash CHAR(64) NOT NULL,
 escopos VARCHAR(500) NOT NULL DEFAULT '',
 expira_em DATETIME NULL,
 ultimo_uso_em DATETIME NULL,
 revogado_em DATETIME NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_api_tokens_hash (token_hash),
 KEY idx_api_tokens_usuario (usuario_id),
 CONSTRAINT fk_api_tokens_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
