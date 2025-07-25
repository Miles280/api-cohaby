<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250725132813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment CHANGE icon_url icon VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE listing ADD adress_id INT NOT NULL');
        $this->addSql('ALTER TABLE listing ADD CONSTRAINT FK_CB0048D48486F9AC FOREIGN KEY (adress_id) REFERENCES adress (id)');
        $this->addSql('CREATE INDEX IDX_CB0048D48486F9AC ON listing (adress_id)');
        $this->addSql('ALTER TABLE service CHANGE icon_url icon VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment CHANGE icon icon_url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE listing DROP FOREIGN KEY FK_CB0048D48486F9AC');
        $this->addSql('DROP INDEX IDX_CB0048D48486F9AC ON listing');
        $this->addSql('ALTER TABLE listing DROP adress_id');
        $this->addSql('ALTER TABLE service CHANGE icon icon_url VARCHAR(255) NOT NULL');
    }
}
