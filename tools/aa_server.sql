-- MySQL Script generated by MySQL Workbench
-- Thu Dec  3 16:16:15 2020
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema aa_server
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `aa_server` ;

-- -----------------------------------------------------
-- Schema aa_server
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `aa_server` DEFAULT CHARACTER SET utf8mb4 ;
USE `aa_server` ;

-- -----------------------------------------------------
-- Table `aa_server`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `aa_server`.`users` ;

CREATE TABLE IF NOT EXISTS `aa_server`.`users` (
  `id` INT NOT NULL,
  `username` VARCHAR(45) NOT NULL,
  `password` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `aa_server`.`authentication_tokens`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `aa_server`.`authentication_tokens` ;

CREATE TABLE IF NOT EXISTS `aa_server`.`authentication_tokens` (
  `jti` VARCHAR(80) NOT NULL,
  `token` MEDIUMTEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `users_id` INT NOT NULL,
  PRIMARY KEY (`jti`, `users_id`),
  CONSTRAINT `fk_tokens_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `aa_server`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_tokens_users_idx` ON `aa_server`.`authentication_tokens` (`users_id` ASC);


-- -----------------------------------------------------
-- Table `aa_server`.`tokens_blacklist`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `aa_server`.`tokens_blacklist` ;

CREATE TABLE IF NOT EXISTS `aa_server`.`tokens_blacklist` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `jti` VARCHAR(80) NOT NULL,
  `token` MEDIUMTEXT NOT NULL,
  `token_type` VARCHAR(20) NOT NULL,
  `users_id` INT NOT NULL,
  PRIMARY KEY (`id`, `users_id`),
  CONSTRAINT `fk_tokens_blacklist_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `aa_server`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_tokens_blacklist_users_idx` ON `aa_server`.`tokens_blacklist` (`users_id` ASC);


-- -----------------------------------------------------
-- Table `aa_server`.`authorization_tokens`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `aa_server`.`authorization_tokens` ;

CREATE TABLE IF NOT EXISTS `aa_server`.`authorization_tokens` (
  `jti` VARCHAR(80) NOT NULL,
  `token` MEDIUMTEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `users_id` INT NOT NULL,
  PRIMARY KEY (`jti`, `users_id`),
  CONSTRAINT `fk_authorization_tokens_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `aa_server`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_authorization_tokens_users_idx` ON `aa_server`.`authorization_tokens` (`users_id` ASC);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
