-- MySQL Script generated by MySQL Workbench
-- Tue Nov 24 15:51:51 2020
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
-- Table `aa_server`.`tokens`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `aa_server`.`tokens` ;

CREATE TABLE IF NOT EXISTS `aa_server`.`tokens` (
  `jti` VARCHAR(100) NOT NULL,
  `jwt` MEDIUMTEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `users_id` INT NOT NULL,
  PRIMARY KEY (`jti`, `users_id`),
  CONSTRAINT `fk_tokens_users`
    FOREIGN KEY (`users_id`)
    REFERENCES `aa_server`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_tokens_users_idx` ON `aa_server`.`tokens` (`users_id` ASC);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
