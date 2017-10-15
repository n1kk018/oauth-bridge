# Dump of table scopes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `scopes`;

CREATE TABLE `scopes` (
  `id` VARCHAR(40) NOT NULL,
  `description` varchar(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table grants
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grants`;

CREATE TABLE `grants` (
  `id` VARCHAR(40) NOT NULL,
  `description` varchar(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table grant_scopes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `grant_scopes`;

CREATE TABLE `grant_scopes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `grant_id` VARCHAR(40) NOT NULL,
  `scope_id` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grant_id` (`grant_id`),
  KEY `scope_id` (`scope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table clients
# ------------------------------------------------------------

DROP TABLE IF EXISTS `clients`;

CREATE TABLE `clients` (
  `id` VARCHAR(100) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `secret` VARCHAR(80) NOT NULL,
  `redirect_uri` VARCHAR(255) NOT NULL,
  `is_confidential` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '3',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `secret` (`secret`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table client_scopes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `client_scopes`;

CREATE TABLE `client_scopes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` VARCHAR(100) NOT NULL,
  `scope_id` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `scope_id` (`scope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table user_grants
# ------------------------------------------------------------

DROP TABLE IF EXISTS `client_grants`;

CREATE TABLE `user_grants` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` VARCHAR(100) NOT NULL,
  `grant_id` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `grant_id` (`grant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(72) NOT NULL,
  `email` VARCHAR(72) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `email` (`email`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table user_scopes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_scopes`;

CREATE TABLE `user_scopes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `scope_id` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `scope_id` (`scope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table access_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `access_tokens`;

CREATE TABLE `access_tokens` (
  `id` VARCHAR(100) NOT NULL,
  `client_id` VARCHAR(100) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `expiration` TIMESTAMP NULL DEFAULT NULL,
  `revoked` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table access_token_scopes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `access_token_scopes`;

CREATE TABLE `access_token_scopes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `access_token_id` VARCHAR(100) NOT NULL,
  `scope_id` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `access_token_id` (`access_token_id`),
  KEY `scope_id` (`scope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table user_grants
# ------------------------------------------------------------

DROP TABLE IF EXISTS `user_grants`;

CREATE TABLE `user_grants` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `grant_id` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `grant_id` (`grant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table refresh_tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `refresh_tokens`;

CREATE TABLE `refresh_tokens` (
  `id` VARCHAR(100) NOT NULL,
  `access_token_id` VARCHAR(100) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `expiration` TIMESTAMP NULL DEFAULT NULL,
  `revoked` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `access_token_id` (`access_token_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table auth_codes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `auth_codes`;

CREATE TABLE `auth_codes` (
  `id` VARCHAR(100) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `client_id` VARCHAR(100) NOT NULL,
  `expiration` TIMESTAMP NULL DEFAULT NULL,
  `redirect_uri` VARCHAR(255) NOT NULL,
  `revoked` TINYINT(1) NOT NULL DEFAULT '0',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table auth_code_scopes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `auth_code_scopes`;

CREATE TABLE `auth_code_scopes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `auth_code_id` VARCHAR(100) NOT NULL,
  `scope_id` VARCHAR(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `auth_code_id` (`auth_code_id`),
  KEY `scope_id` (`scope_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
