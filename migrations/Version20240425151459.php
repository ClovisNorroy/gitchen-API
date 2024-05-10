<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240425151459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_menu (user_id INT NOT NULL, menu_id INT NOT NULL, INDEX IDX_784765AA76ED395 (user_id), INDEX IDX_784765ACCD7E912 (menu_id), PRIMARY KEY(user_id, menu_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_menu ADD CONSTRAINT FK_784765AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_menu ADD CONSTRAINT FK_784765ACCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_menu DROP FOREIGN KEY FK_784765AA76ED395');
        $this->addSql('ALTER TABLE user_menu DROP FOREIGN KEY FK_784765ACCD7E912');
        $this->addSql('DROP TABLE user_menu');
    }
}
