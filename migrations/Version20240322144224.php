<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240322144224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe DROP FOREIGN KEY FK_DA88B1373EC4DCE');
        $this->addSql('DROP INDEX IDX_DA88B1373EC4DCE ON recipe');
        $this->addSql('ALTER TABLE recipe DROP ingredients_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe ADD ingredients_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recipe ADD CONSTRAINT FK_DA88B1373EC4DCE FOREIGN KEY (ingredients_id) REFERENCES ingredient (id)');
        $this->addSql('CREATE INDEX IDX_DA88B1373EC4DCE ON recipe (ingredients_id)');
    }
}
