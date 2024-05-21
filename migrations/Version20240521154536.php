<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240521154536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE grocery_list_user (grocery_list_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_1CDDBA95D059BDAB (grocery_list_id), INDEX IDX_1CDDBA95A76ED395 (user_id), PRIMARY KEY(grocery_list_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE grocery_list_user ADD CONSTRAINT FK_1CDDBA95D059BDAB FOREIGN KEY (grocery_list_id) REFERENCES grocery_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE grocery_list_user ADD CONSTRAINT FK_1CDDBA95A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE grocery_list_user DROP FOREIGN KEY FK_1CDDBA95D059BDAB');
        $this->addSql('ALTER TABLE grocery_list_user DROP FOREIGN KEY FK_1CDDBA95A76ED395');
        $this->addSql('DROP TABLE grocery_list_user');
    }
}
