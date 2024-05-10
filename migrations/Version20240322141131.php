<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240322141131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ingredient (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe (id INT AUTO_INCREMENT NOT NULL, ingredients_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, instructions LONGTEXT DEFAULT NULL, unverified_ingredients LONGTEXT DEFAULT NULL, INDEX IDX_DA88B1373EC4DCE (ingredients_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recipe ADD CONSTRAINT FK_DA88B1373EC4DCE FOREIGN KEY (ingredients_id) REFERENCES ingredient (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe DROP FOREIGN KEY FK_DA88B1373EC4DCE');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('DROP TABLE recipe');
    }
}
