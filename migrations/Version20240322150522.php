<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240322150522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_day ADD recipe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE menu_day ADD CONSTRAINT FK_7D01E7A259D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('CREATE INDEX IDX_7D01E7A259D8A214 ON menu_day (recipe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_day DROP FOREIGN KEY FK_7D01E7A259D8A214');
        $this->addSql('DROP INDEX IDX_7D01E7A259D8A214 ON menu_day');
        $this->addSql('ALTER TABLE menu_day DROP recipe_id');
    }
}
