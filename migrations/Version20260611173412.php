<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260611173412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align JSON place columns when places already exists.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on mysql.'
        );

        if (!$schema->hasTable('places')) {
            return;
        }

        $this->addSql('ALTER TABLE places CHANGE city_aliases city_aliases LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE city_neighborhoods city_neighborhoods LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE city_route city_route LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE facts facts LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE photos photos LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', CHANGE feedbacks feedbacks LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on mysql.'
        );

        if (!$schema->hasTable('places')) {
            return;
        }

        $this->addSql('ALTER TABLE places CHANGE city_aliases city_aliases LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE city_neighborhoods city_neighborhoods LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE city_route city_route LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE facts facts LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE photos photos LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE feedbacks feedbacks LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_bin`');
    }
}
