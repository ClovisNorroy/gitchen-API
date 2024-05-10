<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240119154649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE menu_day (id INT AUTO_INCREMENT NOT NULL, menu_id INT NOT NULL, day_number SMALLINT NOT NULL, meal VARCHAR(255) DEFAULT NULL, INDEX IDX_7D01E7A2CCD7E912 (menu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE menu_day ADD CONSTRAINT FK_7D01E7A2CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_day DROP FOREIGN KEY FK_7D01E7A2CCD7E912');
        $this->addSql('DROP TABLE menu_day');
    }
}
