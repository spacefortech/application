<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260611193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create places table for guide card and place-detail data.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on mysql.'
        );

        $this->addSql('CREATE TABLE places (
            id INT AUTO_INCREMENT NOT NULL,
            city_slug VARCHAR(120) NOT NULL,
            city_name VARCHAR(180) DEFAULT NULL,
            city_display_name VARCHAR(180) NOT NULL,
            city_aliases LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\',
            city_region VARCHAR(180) DEFAULT NULL,
            city_headline LONGTEXT DEFAULT NULL,
            city_summary LONGTEXT DEFAULT NULL,
            city_best_for VARCHAR(255) DEFAULT NULL,
            city_duration VARCHAR(80) DEFAULT NULL,
            city_accent VARCHAR(20) DEFAULT NULL,
            city_image_index INT DEFAULT NULL,
            city_neighborhoods LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\',
            city_route LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\',
            slug VARCHAR(180) NOT NULL,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(120) DEFAULT NULL,
            area VARCHAR(180) DEFAULT NULL,
            note LONGTEXT DEFAULT NULL,
            time VARCHAR(80) DEFAULT NULL,
            duration VARCHAR(80) DEFAULT NULL,
            best_time VARCHAR(180) DEFAULT NULL,
            address VARCHAR(255) DEFAULT NULL,
            intro LONGTEXT DEFAULT NULL,
            why LONGTEXT DEFAULT NULL,
            tip LONGTEXT DEFAULT NULL,
            facts LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\',
            photos LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\',
            feedbacks LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\',
            is_city_spot TINYINT(1) DEFAULT 1 NOT NULL,
            is_cool_place TINYINT(1) DEFAULT 0 NOT NULL,
            cool_place_order INT DEFAULT NULL,
            sort_order INT DEFAULT 0 NOT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_PLACES_CITY_SLUG (city_slug, slug),
            INDEX IDX_PLACES_CITY_SLUG (city_slug),
            INDEX IDX_PLACES_TYPE (type),
            INDEX IDX_PLACES_CITY_SPOT (is_city_spot),
            INDEX IDX_PLACES_COOL_PLACE (is_cool_place, cool_place_order),
            INDEX IDX_PLACES_SORT_ORDER (sort_order)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on mysql.'
        );

        $this->addSql('DROP TABLE places');
    }
}
