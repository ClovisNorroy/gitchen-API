<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240120153841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_day DROP FOREIGN KEY FK_7D01E7A2CCD7E912');
        $this->addSql('DROP INDEX IDX_7D01E7A2CCD7E912 ON menu_day');
        $this->addSql('ALTER TABLE menu_day DROP menu_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_day ADD menu_id INT NOT NULL');
        $this->addSql('ALTER TABLE menu_day ADD CONSTRAINT FK_7D01E7A2CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('CREATE INDEX IDX_7D01E7A2CCD7E912 ON menu_day (menu_id)');
    }
}
