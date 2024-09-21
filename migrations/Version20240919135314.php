<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919135314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE grocery_list (id INT AUTO_INCREMENT NOT NULL, list JSON DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE grocery_list_user (grocery_list_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_1CDDBA95D059BDAB (grocery_list_id), INDEX IDX_1CDDBA95A76ED395 (user_id), PRIMARY KEY(grocery_list_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, date DATE DEFAULT NULL, is_locked TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE menu_day (id INT AUTO_INCREMENT NOT NULL, menu_id INT NOT NULL, recipe_id INT DEFAULT NULL, meal_number SMALLINT NOT NULL, meal VARCHAR(255) DEFAULT NULL, INDEX IDX_7D01E7A2CCD7E912 (menu_id), INDEX IDX_7D01E7A259D8A214 (recipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, instructions LONGTEXT DEFAULT NULL, ingredients LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_DA88B137A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_menu (user_id INT NOT NULL, menu_id INT NOT NULL, INDEX IDX_784765AA76ED395 (user_id), INDEX IDX_784765ACCD7E912 (menu_id), PRIMARY KEY(user_id, menu_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE grocery_list_user ADD CONSTRAINT FK_1CDDBA95D059BDAB FOREIGN KEY (grocery_list_id) REFERENCES grocery_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE grocery_list_user ADD CONSTRAINT FK_1CDDBA95A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_day ADD CONSTRAINT FK_7D01E7A2CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE menu_day ADD CONSTRAINT FK_7D01E7A259D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('ALTER TABLE recipe ADD CONSTRAINT FK_DA88B137A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_menu ADD CONSTRAINT FK_784765AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_menu ADD CONSTRAINT FK_784765ACCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grocery_list_user DROP FOREIGN KEY FK_1CDDBA95D059BDAB');
        $this->addSql('ALTER TABLE grocery_list_user DROP FOREIGN KEY FK_1CDDBA95A76ED395');
        $this->addSql('ALTER TABLE menu_day DROP FOREIGN KEY FK_7D01E7A2CCD7E912');
        $this->addSql('ALTER TABLE menu_day DROP FOREIGN KEY FK_7D01E7A259D8A214');
        $this->addSql('ALTER TABLE recipe DROP FOREIGN KEY FK_DA88B137A76ED395');
        $this->addSql('ALTER TABLE user_menu DROP FOREIGN KEY FK_784765AA76ED395');
        $this->addSql('ALTER TABLE user_menu DROP FOREIGN KEY FK_784765ACCD7E912');
        $this->addSql('DROP TABLE grocery_list');
        $this->addSql('DROP TABLE grocery_list_user');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE menu_day');
        $this->addSql('DROP TABLE recipe');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_menu');
    }
}
