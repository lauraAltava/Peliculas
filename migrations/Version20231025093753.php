<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231025093753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pelicula ADD libro_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pelicula ADD CONSTRAINT FK_73BC7095C0238522 FOREIGN KEY (libro_id) REFERENCES libro (id)');
        $this->addSql('CREATE INDEX IDX_73BC7095C0238522 ON pelicula (libro_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pelicula DROP FOREIGN KEY FK_73BC7095C0238522');
        $this->addSql('DROP INDEX IDX_73BC7095C0238522 ON pelicula');
        $this->addSql('ALTER TABLE pelicula DROP libro_id');
    }
}
