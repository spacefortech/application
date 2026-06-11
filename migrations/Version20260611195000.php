<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260611195000 extends AbstractMigration
{
    private const JSON_COLUMNS = array(
        'city_aliases',
        'city_neighborhoods',
        'city_route',
        'facts',
        'photos',
        'feedbacks',
    );

    private const COLUMNS = array(
        'city_slug',
        'city_name',
        'city_display_name',
        'city_aliases',
        'city_region',
        'city_headline',
        'city_summary',
        'city_best_for',
        'city_duration',
        'city_accent',
        'city_image_index',
        'city_neighborhoods',
        'city_route',
        'slug',
        'name',
        'type',
        'area',
        'note',
        'time',
        'duration',
        'best_time',
        'address',
        'intro',
        'why',
        'tip',
        'facts',
        'photos',
        'feedbacks',
        'is_city_spot',
        'is_cool_place',
        'cool_place_order',
        'sort_order',
    );

    public function getDescription(): string
    {
        return 'Seed places table from GuideController data.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on mysql.'
        );

        $this->abortIf(!$schema->hasTable('places'), 'The places table must exist before seeding guide data.');

        $table = $schema->getTable('places');

        if (!$table->hasColumn('is_city_spot')) {
            $this->addSql('ALTER TABLE places ADD is_city_spot TINYINT(1) DEFAULT 1 NOT NULL, ADD is_cool_place TINYINT(1) DEFAULT 0 NOT NULL, ADD cool_place_order INT DEFAULT NULL');
        }

        if (!$table->hasIndex('IDX_PLACES_CITY_SPOT')) {
            $this->addSql('CREATE INDEX IDX_PLACES_CITY_SPOT ON places (is_city_spot)');
        }

        if (!$table->hasIndex('IDX_PLACES_COOL_PLACE')) {
            $this->addSql('CREATE INDEX IDX_PLACES_COOL_PLACE ON places (is_cool_place, cool_place_order)');
        }

        foreach ($this->getRows() as $row) {
            $this->addSql($this->getUpsertSql(), $this->buildParams($row));
        }
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

        foreach ($this->getRows() as $row) {
            $this->addSql('DELETE FROM places WHERE city_slug = ? AND slug = ?', array($row['city_slug'], $row['slug']));
        }
    }

    private function getUpsertSql(): string
    {
        $quotedColumns = array_map(static function (string $column): string {
            return '`' . $column . '`';
        }, self::COLUMNS);

        $updates = array();
        foreach (self::COLUMNS as $column) {
            if ($column === 'city_slug' || $column === 'slug') {
                continue;
            }

            $updates[] = '`' . $column . '` = VALUES(`' . $column . '`)';
        }

        return 'INSERT INTO places (' . implode(', ', $quotedColumns) . ') VALUES (' . implode(', ', array_fill(0, count(self::COLUMNS), '?')) . ') ON DUPLICATE KEY UPDATE ' . implode(', ', $updates);
    }

    private function buildParams(array $row): array
    {
        $params = array();

        foreach (self::COLUMNS as $column) {
            $value = $row[$column] ?? null;

            if (in_array($column, self::JSON_COLUMNS, true)) {
                $value = $this->encodeJson($value);
            }

            if ($column === 'is_city_spot' || $column === 'is_cool_place') {
                $value = $value ? 1 : 0;
            }

            $params[] = $value;
        }

        return $params;
    }

    private function encodeJson($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $json === false ? null : $json;
    }

    private function getRows(): array
    {
        return array (
  0 => 
  array (
    'city_slug' => 'berlin',
    'city_name' => 'Berlin',
    'city_display_name' => 'Berlin',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Kreative Hauptstadt',
    'city_headline' => 'Straßenkunst, mutige Museen und lange Nächte an der Spree.',
    'city_summary' => 'Berlin funktioniert am besten, wenn du kulturelle Ikonen, industrielle Höfe, Märkte und Viertel mit starkem lokalem Leben kombinierst.',
    'city_best_for' => 'Urbane Kunst, Nachtleben, unabhängige Kultur',
    'city_duration' => '2-4 Tage',
    'city_accent' => '#f9735b',
    'city_image_index' => 0,
    'city_neighborhoods' => 
    array (
      0 => 'Mitte',
      1 => 'Kreuzberg',
      2 => 'Friedrichshain',
      3 => 'Neukölln',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Mitte und Gegenwartskunst',
        'detail' => 'Kaffee, Galerien und ein reservierter Besuch in der Sammlung Boros.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Kreuzberg langsam erleben',
        'detail' => 'Mittagessen in der Markthalle Neun und ein Spaziergang entlang von Kanälen, unabhängigen Läden und Straßenkunst.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Dachterrasse und Kiez',
        'detail' => 'Sonnenuntergang im Klunkerkranich, danach ein unkompliziertes Abendessen rund um Neukölln.',
      ),
    ),
    'slug' => 'spot-0-east-side-gallery',
    'name' => 'East Side Gallery',
    'type' => 'Straßenkunst',
    'area' => 'Friedrichshain',
    'note' => 'Ein ikonischer Abschnitt der Mauer als offene Galerie, perfekt für einen energiegeladenen Einstieg.',
    'time' => '60-90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 0,
  ),
  1 => 
  array (
    'city_slug' => 'berlin',
    'city_name' => 'Berlin',
    'city_display_name' => 'Berlin',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Kreative Hauptstadt',
    'city_headline' => 'Straßenkunst, mutige Museen und lange Nächte an der Spree.',
    'city_summary' => 'Berlin funktioniert am besten, wenn du kulturelle Ikonen, industrielle Höfe, Märkte und Viertel mit starkem lokalem Leben kombinierst.',
    'city_best_for' => 'Urbane Kunst, Nachtleben, unabhängige Kultur',
    'city_duration' => '2-4 Tage',
    'city_accent' => '#f9735b',
    'city_image_index' => 0,
    'city_neighborhoods' => 
    array (
      0 => 'Mitte',
      1 => 'Kreuzberg',
      2 => 'Friedrichshain',
      3 => 'Neukölln',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Mitte und Gegenwartskunst',
        'detail' => 'Kaffee, Galerien und ein reservierter Besuch in der Sammlung Boros.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Kreuzberg langsam erleben',
        'detail' => 'Mittagessen in der Markthalle Neun und ein Spaziergang entlang von Kanälen, unabhängigen Läden und Straßenkunst.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Dachterrasse und Kiez',
        'detail' => 'Sonnenuntergang im Klunkerkranich, danach ein unkompliziertes Abendessen rund um Neukölln.',
      ),
    ),
    'slug' => 'spot-1-sammlung-boros',
    'name' => 'Sammlung Boros',
    'type' => 'Kultur',
    'area' => 'Mitte',
    'note' => 'Zeitgenössische Kunst in einem umgebauten Bunker, rau und sehr berlinisch.',
    'time' => 'Reservierung',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1,
  ),
  2 => 
  array (
    'city_slug' => 'berlin',
    'city_name' => 'Berlin',
    'city_display_name' => 'Berlin',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Kreative Hauptstadt',
    'city_headline' => 'Straßenkunst, mutige Museen und lange Nächte an der Spree.',
    'city_summary' => 'Berlin funktioniert am besten, wenn du kulturelle Ikonen, industrielle Höfe, Märkte und Viertel mit starkem lokalem Leben kombinierst.',
    'city_best_for' => 'Urbane Kunst, Nachtleben, unabhängige Kultur',
    'city_duration' => '2-4 Tage',
    'city_accent' => '#f9735b',
    'city_image_index' => 0,
    'city_neighborhoods' => 
    array (
      0 => 'Mitte',
      1 => 'Kreuzberg',
      2 => 'Friedrichshain',
      3 => 'Neukölln',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Mitte und Gegenwartskunst',
        'detail' => 'Kaffee, Galerien und ein reservierter Besuch in der Sammlung Boros.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Kreuzberg langsam erleben',
        'detail' => 'Mittagessen in der Markthalle Neun und ein Spaziergang entlang von Kanälen, unabhängigen Läden und Straßenkunst.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Dachterrasse und Kiez',
        'detail' => 'Sonnenuntergang im Klunkerkranich, danach ein unkompliziertes Abendessen rund um Neukölln.',
      ),
    ),
    'slug' => 'spot-2-markthalle-neun',
    'name' => 'Markthalle Neun',
    'type' => 'Essen',
    'area' => 'Kreuzberg',
    'note' => 'Markthalle mit gemeinsamen Tischen, Straßenküche und lokalen Produzenten.',
    'time' => 'Mittagessen',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 2,
  ),
  3 => 
  array (
    'city_slug' => 'berlin',
    'city_name' => 'Berlin',
    'city_display_name' => 'Berlin',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Kreative Hauptstadt',
    'city_headline' => 'Straßenkunst, mutige Museen und lange Nächte an der Spree.',
    'city_summary' => 'Berlin funktioniert am besten, wenn du kulturelle Ikonen, industrielle Höfe, Märkte und Viertel mit starkem lokalem Leben kombinierst.',
    'city_best_for' => 'Urbane Kunst, Nachtleben, unabhängige Kultur',
    'city_duration' => '2-4 Tage',
    'city_accent' => '#f9735b',
    'city_image_index' => 0,
    'city_neighborhoods' => 
    array (
      0 => 'Mitte',
      1 => 'Kreuzberg',
      2 => 'Friedrichshain',
      3 => 'Neukölln',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Mitte und Gegenwartskunst',
        'detail' => 'Kaffee, Galerien und ein reservierter Besuch in der Sammlung Boros.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Kreuzberg langsam erleben',
        'detail' => 'Mittagessen in der Markthalle Neun und ein Spaziergang entlang von Kanälen, unabhängigen Läden und Straßenkunst.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Dachterrasse und Kiez',
        'detail' => 'Sonnenuntergang im Klunkerkranich, danach ein unkompliziertes Abendessen rund um Neukölln.',
      ),
    ),
    'slug' => 'spot-3-klunkerkranich',
    'name' => 'Klunkerkranich',
    'type' => 'Ausgehen',
    'area' => 'Neukölln',
    'note' => 'Ungezwungene Dachterrasse über der Stadt, besonders schön bei Sonnenuntergang.',
    'time' => 'Abend',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 3,
  ),
  4 => 
  array (
    'city_slug' => 'berlin',
    'city_name' => 'Berlin',
    'city_display_name' => 'Berlin',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Kreative Hauptstadt',
    'city_headline' => 'Straßenkunst, mutige Museen und lange Nächte an der Spree.',
    'city_summary' => 'Berlin funktioniert am besten, wenn du kulturelle Ikonen, industrielle Höfe, Märkte und Viertel mit starkem lokalem Leben kombinierst.',
    'city_best_for' => 'Urbane Kunst, Nachtleben, unabhängige Kultur',
    'city_duration' => '2-4 Tage',
    'city_accent' => '#f9735b',
    'city_image_index' => 0,
    'city_neighborhoods' => 
    array (
      0 => 'Mitte',
      1 => 'Kreuzberg',
      2 => 'Friedrichshain',
      3 => 'Neukölln',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Mitte und Gegenwartskunst',
        'detail' => 'Kaffee, Galerien und ein reservierter Besuch in der Sammlung Boros.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Kreuzberg langsam erleben',
        'detail' => 'Mittagessen in der Markthalle Neun und ein Spaziergang entlang von Kanälen, unabhängigen Läden und Straßenkunst.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Dachterrasse und Kiez',
        'detail' => 'Sonnenuntergang im Klunkerkranich, danach ein unkompliziertes Abendessen rund um Neukölln.',
      ),
    ),
    'slug' => 'spot-4-tempelhofer-feld',
    'name' => 'Tempelhofer Feld',
    'type' => 'Einzigartig',
    'area' => 'Tempelhof',
    'note' => 'Der ehemalige Flughafen ist heute ein Stadtpark: Fahrräder, Picknick und viel Horizont mitten in Berlin.',
    'time' => '2 Std.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 4,
  ),
  5 => 
  array (
    'city_slug' => 'hamburg',
    'city_name' => 'Hamburg',
    'city_display_name' => 'Hamburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hafen und Design',
    'city_headline' => 'Kanäle, rote Speicherhäuser, Musik und Tische am Hafen.',
    'city_summary' => 'Hamburg verbindet Wasser, Architektur und kreative Viertel: Der beste Rhythmus führt durch Speicherstadt, Sternschanze und ans Ufer.',
    'city_best_for' => 'Architektur, Konzerte, Spaziergänge am Wasser',
    'city_duration' => '2-3 Tage',
    'city_accent' => '#0f766e',
    'city_image_index' => 1,
    'city_neighborhoods' => 
    array (
      0 => 'Speicherstadt',
      1 => 'HafenCity',
      2 => 'Sternschanze',
      3 => 'St. Pauli',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Speicherstadt zu Fuß',
        'detail' => 'Kanäle, Brücken und Backsteinarchitektur, bevor es voller wird.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'HafenCity und Schanze',
        'detail' => 'Blick von der Plaza, danach Läden und Cafés rund um die Sternschanze.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Elbe oder St. Pauli',
        'detail' => 'Unkompliziertes Abendessen am Fluss oder Live-Musik auf der nächtlicheren Seite der Stadt.',
      ),
    ),
    'slug' => 'spot-0-speicherstadt',
    'name' => 'Speicherstadt',
    'type' => 'Wahrzeichen',
    'area' => 'HafenCity',
    'note' => 'Historische Speicher und Kanäle: die perfekte visuelle Grundlage, um die Stadt zu verstehen.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 100,
  ),
  6 => 
  array (
    'city_slug' => 'hamburg',
    'city_name' => 'Hamburg',
    'city_display_name' => 'Hamburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hafen und Design',
    'city_headline' => 'Kanäle, rote Speicherhäuser, Musik und Tische am Hafen.',
    'city_summary' => 'Hamburg verbindet Wasser, Architektur und kreative Viertel: Der beste Rhythmus führt durch Speicherstadt, Sternschanze und ans Ufer.',
    'city_best_for' => 'Architektur, Konzerte, Spaziergänge am Wasser',
    'city_duration' => '2-3 Tage',
    'city_accent' => '#0f766e',
    'city_image_index' => 1,
    'city_neighborhoods' => 
    array (
      0 => 'Speicherstadt',
      1 => 'HafenCity',
      2 => 'Sternschanze',
      3 => 'St. Pauli',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Speicherstadt zu Fuß',
        'detail' => 'Kanäle, Brücken und Backsteinarchitektur, bevor es voller wird.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'HafenCity und Schanze',
        'detail' => 'Blick von der Plaza, danach Läden und Cafés rund um die Sternschanze.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Elbe oder St. Pauli',
        'detail' => 'Unkompliziertes Abendessen am Fluss oder Live-Musik auf der nächtlicheren Seite der Stadt.',
      ),
    ),
    'slug' => 'spot-1-elbphilharmonie-plaza',
    'name' => 'Elbphilharmonie Plaza',
    'type' => 'Aussicht',
    'area' => 'HafenCity',
    'note' => 'Eine öffentliche Terrasse mit Hafenblick und sehr wiedererkennbarem Architekturprofil.',
    'time' => '45 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 101,
  ),
  7 => 
  array (
    'city_slug' => 'hamburg',
    'city_name' => 'Hamburg',
    'city_display_name' => 'Hamburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hafen und Design',
    'city_headline' => 'Kanäle, rote Speicherhäuser, Musik und Tische am Hafen.',
    'city_summary' => 'Hamburg verbindet Wasser, Architektur und kreative Viertel: Der beste Rhythmus führt durch Speicherstadt, Sternschanze und ans Ufer.',
    'city_best_for' => 'Architektur, Konzerte, Spaziergänge am Wasser',
    'city_duration' => '2-3 Tage',
    'city_accent' => '#0f766e',
    'city_image_index' => 1,
    'city_neighborhoods' => 
    array (
      0 => 'Speicherstadt',
      1 => 'HafenCity',
      2 => 'Sternschanze',
      3 => 'St. Pauli',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Speicherstadt zu Fuß',
        'detail' => 'Kanäle, Brücken und Backsteinarchitektur, bevor es voller wird.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'HafenCity und Schanze',
        'detail' => 'Blick von der Plaza, danach Läden und Cafés rund um die Sternschanze.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Elbe oder St. Pauli',
        'detail' => 'Unkompliziertes Abendessen am Fluss oder Live-Musik auf der nächtlicheren Seite der Stadt.',
      ),
    ),
    'slug' => 'spot-2-sternschanze',
    'name' => 'Sternschanze',
    'type' => 'Viertel',
    'area' => 'Schanze',
    'note' => 'Cafés, unabhängige Läden und unkomplizierte Orte für einen Abend ohne festen Plan.',
    'time' => 'Nachmittag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 102,
  ),
  8 => 
  array (
    'city_slug' => 'hamburg',
    'city_name' => 'Hamburg',
    'city_display_name' => 'Hamburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hafen und Design',
    'city_headline' => 'Kanäle, rote Speicherhäuser, Musik und Tische am Hafen.',
    'city_summary' => 'Hamburg verbindet Wasser, Architektur und kreative Viertel: Der beste Rhythmus führt durch Speicherstadt, Sternschanze und ans Ufer.',
    'city_best_for' => 'Architektur, Konzerte, Spaziergänge am Wasser',
    'city_duration' => '2-3 Tage',
    'city_accent' => '#0f766e',
    'city_image_index' => 1,
    'city_neighborhoods' => 
    array (
      0 => 'Speicherstadt',
      1 => 'HafenCity',
      2 => 'Sternschanze',
      3 => 'St. Pauli',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Speicherstadt zu Fuß',
        'detail' => 'Kanäle, Brücken und Backsteinarchitektur, bevor es voller wird.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'HafenCity und Schanze',
        'detail' => 'Blick von der Plaza, danach Läden und Cafés rund um die Sternschanze.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Elbe oder St. Pauli',
        'detail' => 'Unkompliziertes Abendessen am Fluss oder Live-Musik auf der nächtlicheren Seite der Stadt.',
      ),
    ),
    'slug' => 'spot-3-fischmarkt',
    'name' => 'Fischmarkt',
    'type' => 'Essen',
    'area' => 'Altona',
    'note' => 'Ein lokaler Klassiker, lebendig und sehr früh: ideal, um eine andere Seite der Stadt zu sehen.',
    'time' => 'Sonntag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 103,
  ),
  9 => 
  array (
    'city_slug' => 'hamburg',
    'city_name' => 'Hamburg',
    'city_display_name' => 'Hamburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hafen und Design',
    'city_headline' => 'Kanäle, rote Speicherhäuser, Musik und Tische am Hafen.',
    'city_summary' => 'Hamburg verbindet Wasser, Architektur und kreative Viertel: Der beste Rhythmus führt durch Speicherstadt, Sternschanze und ans Ufer.',
    'city_best_for' => 'Architektur, Konzerte, Spaziergänge am Wasser',
    'city_duration' => '2-3 Tage',
    'city_accent' => '#0f766e',
    'city_image_index' => 1,
    'city_neighborhoods' => 
    array (
      0 => 'Speicherstadt',
      1 => 'HafenCity',
      2 => 'Sternschanze',
      3 => 'St. Pauli',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Speicherstadt zu Fuß',
        'detail' => 'Kanäle, Brücken und Backsteinarchitektur, bevor es voller wird.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'HafenCity und Schanze',
        'detail' => 'Blick von der Plaza, danach Läden und Cafés rund um die Sternschanze.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Elbe oder St. Pauli',
        'detail' => 'Unkompliziertes Abendessen am Fluss oder Live-Musik auf der nächtlicheren Seite der Stadt.',
      ),
    ),
    'slug' => 'spot-4-strandperle',
    'name' => 'Strandperle',
    'type' => 'Entspannen',
    'area' => 'Othmarschen',
    'note' => 'Tische im Sand an der Elbe, perfekt bei weichem Licht und langsamem Tempo.',
    'time' => 'Sonnenuntergang',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 104,
  ),
  10 => 
  array (
    'city_slug' => 'munich',
    'city_name' => 'München',
    'city_display_name' => 'München',
    'city_aliases' => 
    array (
      0 => 'Muenchen',
    ),
    'city_region' => 'Klassisch und draußen',
    'city_headline' => 'Museen, Märkte, Surfen in der Stadt und Biergärten unter Bäumen.',
    'city_summary' => 'München ist geordnet, aber nicht steif: Große Museen, Parks, historische Märkte und Viertel mit starken kulinarischen Adressen wechseln sich ab.',
    'city_best_for' => 'Museen, Märkte, Parks, bayerische Tradition',
    'city_duration' => '2-3 Tage',
    'city_accent' => '#2563eb',
    'city_image_index' => 2,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Maxvorstadt',
      2 => 'Glockenbach',
      3 => 'Schwabing',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Kunst in der Maxvorstadt',
        'detail' => 'Wähle eine Pinakothek und lass Platz für ein langes Frühstück.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Markt und Park',
        'detail' => 'Viktualienmarkt, Altstadt und ein Abstecher zur Eisbachwelle.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Glockenbach',
        'detail' => 'Zeitgemäßes Abendessen und Bars in einem Viertel, das sich leicht zu Fuß erkunden lässt.',
      ),
    ),
    'slug' => 'spot-0-kunstareal',
    'name' => 'Kunstareal',
    'type' => 'Museen',
    'area' => 'Maxvorstadt',
    'note' => 'Ein dichtes Kulturviertel, ideal für einen Tag zwischen Kunst und Design.',
    'time' => 'Halber Tag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 200,
  ),
  11 => 
  array (
    'city_slug' => 'munich',
    'city_name' => 'München',
    'city_display_name' => 'München',
    'city_aliases' => 
    array (
      0 => 'Muenchen',
    ),
    'city_region' => 'Klassisch und draußen',
    'city_headline' => 'Museen, Märkte, Surfen in der Stadt und Biergärten unter Bäumen.',
    'city_summary' => 'München ist geordnet, aber nicht steif: Große Museen, Parks, historische Märkte und Viertel mit starken kulinarischen Adressen wechseln sich ab.',
    'city_best_for' => 'Museen, Märkte, Parks, bayerische Tradition',
    'city_duration' => '2-3 Tage',
    'city_accent' => '#2563eb',
    'city_image_index' => 2,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Maxvorstadt',
      2 => 'Glockenbach',
      3 => 'Schwabing',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Kunst in der Maxvorstadt',
        'detail' => 'Wähle eine Pinakothek und lass Platz für ein langes Frühstück.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Markt und Park',
        'detail' => 'Viktualienmarkt, Altstadt und ein Abstecher zur Eisbachwelle.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Glockenbach',
        'detail' => 'Zeitgemäßes Abendessen und Bars in einem Viertel, das sich leicht zu Fuß erkunden lässt.',
      ),
    ),
    'slug' => 'spot-1-viktualienmarkt',
    'name' => 'Viktualienmarkt',
    'type' => 'Essen',
    'area' => 'Altstadt',
    'note' => 'Historischer Markt für schnelle Kostproben, lokale Produkte und eine Pause im Freien.',
    'time' => 'Mittagessen',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 201,
  ),
  12 => 
  array (
    'city_slug' => 'munich',
    'city_name' => 'München',
    'city_display_name' => 'München',
    'city_aliases' => 
    array (
      0 => 'Muenchen',
    ),
    'city_region' => 'Klassisch und draußen',
    'city_headline' => 'Museen, Märkte, Surfen in der Stadt und Biergärten unter Bäumen.',
    'city_summary' => 'München ist geordnet, aber nicht steif: Große Museen, Parks, historische Märkte und Viertel mit starken kulinarischen Adressen wechseln sich ab.',
    'city_best_for' => 'Museen, Märkte, Parks, bayerische Tradition',
    'city_duration' => '2-3 Tage',
    'city_accent' => '#2563eb',
    'city_image_index' => 2,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Maxvorstadt',
      2 => 'Glockenbach',
      3 => 'Schwabing',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Kunst in der Maxvorstadt',
        'detail' => 'Wähle eine Pinakothek und lass Platz für ein langes Frühstück.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Markt und Park',
        'detail' => 'Viktualienmarkt, Altstadt und ein Abstecher zur Eisbachwelle.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Glockenbach',
        'detail' => 'Zeitgemäßes Abendessen und Bars in einem Viertel, das sich leicht zu Fuß erkunden lässt.',
      ),
    ),
    'slug' => 'spot-2-eisbachwelle',
    'name' => 'Eisbachwelle',
    'type' => 'Draußen',
    'area' => 'Englischer Garten',
    'note' => 'Die berühmte urbane Surfwelle: kurz, überraschend und sehr fotogen.',
    'time' => '30 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 202,
  ),
  13 => 
  array (
    'city_slug' => 'munich',
    'city_name' => 'München',
    'city_display_name' => 'München',
    'city_aliases' => 
    array (
      0 => 'Muenchen',
    ),
    'city_region' => 'Klassisch und draußen',
    'city_headline' => 'Museen, Märkte, Surfen in der Stadt und Biergärten unter Bäumen.',
    'city_summary' => 'München ist geordnet, aber nicht steif: Große Museen, Parks, historische Märkte und Viertel mit starken kulinarischen Adressen wechseln sich ab.',
    'city_best_for' => 'Museen, Märkte, Parks, bayerische Tradition',
    'city_duration' => '2-3 Tage',
    'city_accent' => '#2563eb',
    'city_image_index' => 2,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Maxvorstadt',
      2 => 'Glockenbach',
      3 => 'Schwabing',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Kunst in der Maxvorstadt',
        'detail' => 'Wähle eine Pinakothek und lass Platz für ein langes Frühstück.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Markt und Park',
        'detail' => 'Viktualienmarkt, Altstadt und ein Abstecher zur Eisbachwelle.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Glockenbach',
        'detail' => 'Zeitgemäßes Abendessen und Bars in einem Viertel, das sich leicht zu Fuß erkunden lässt.',
      ),
    ),
    'slug' => 'spot-3-glockenbachviertel',
    'name' => 'Glockenbachviertel',
    'type' => 'Viertel',
    'area' => 'Glockenbach',
    'note' => 'Boutiquen, Bars und zeitgemäße Restaurants in einem kompakten, lebendigen Viertel.',
    'time' => 'Abend',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 203,
  ),
  14 => 
  array (
    'city_slug' => 'munich',
    'city_name' => 'München',
    'city_display_name' => 'München',
    'city_aliases' => 
    array (
      0 => 'Muenchen',
    ),
    'city_region' => 'Klassisch und draußen',
    'city_headline' => 'Museen, Märkte, Surfen in der Stadt und Biergärten unter Bäumen.',
    'city_summary' => 'München ist geordnet, aber nicht steif: Große Museen, Parks, historische Märkte und Viertel mit starken kulinarischen Adressen wechseln sich ab.',
    'city_best_for' => 'Museen, Märkte, Parks, bayerische Tradition',
    'city_duration' => '2-3 Tage',
    'city_accent' => '#2563eb',
    'city_image_index' => 2,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Maxvorstadt',
      2 => 'Glockenbach',
      3 => 'Schwabing',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Kunst in der Maxvorstadt',
        'detail' => 'Wähle eine Pinakothek und lass Platz für ein langes Frühstück.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Markt und Park',
        'detail' => 'Viktualienmarkt, Altstadt und ein Abstecher zur Eisbachwelle.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Glockenbach',
        'detail' => 'Zeitgemäßes Abendessen und Bars in einem Viertel, das sich leicht zu Fuß erkunden lässt.',
      ),
    ),
    'slug' => 'spot-4-olympiapark',
    'name' => 'Olympiapark',
    'type' => 'Park',
    'area' => 'Milbertshofen',
    'note' => 'Siebzigerjahre-Architektur, Hügel und weite Blicke über die Stadt.',
    'time' => '2 Std.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 204,
  ),
  15 => 
  array (
    'city_slug' => 'cologne',
    'city_name' => 'Köln',
    'city_display_name' => 'Köln',
    'city_aliases' => 
    array (
      0 => 'Koeln',
    ),
    'city_region' => 'Rhein und Popkultur',
    'city_headline' => 'Dom, moderne Kunst, Brauhäuser und kreative Viertel.',
    'city_summary' => 'Köln ist direkt und gesellig: Das Zentrum ist kompakt, doch die umliegenden Viertel geben der Reise den spannendsten Ton.',
    'city_best_for' => 'Moderne Kunst, Brauhäuser, unkomplizierte Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#db2777',
    'city_image_index' => 3,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Belgisches Viertel',
      2 => 'Ehrenfeld',
      3 => 'Rheinauhafen',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Dom und Museum Ludwig',
        'detail' => 'Klassisch und zeitgenössisch in wenigen Schritten, ohne Zeit mit Wegen zu verlieren.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Belgisches Viertel',
        'detail' => 'Unabhängiges Einkaufen, Kaffee und eine langsame Pause auf den Plätzen des Viertels.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Kölsch oder Ehrenfeld',
        'detail' => 'Traditionelles Brauhaus im Zentrum oder ein alternativer Abend Richtung Odonien.',
      ),
    ),
    'slug' => 'spot-0-koelner-dom',
    'name' => 'Kölner Dom',
    'type' => 'Wahrzeichen',
    'area' => 'Altstadt',
    'note' => 'Ein szenischer Einstieg in die Stadt, besonders wenn du am Hauptbahnhof ankommst.',
    'time' => '45 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 300,
  ),
  16 => 
  array (
    'city_slug' => 'cologne',
    'city_name' => 'Köln',
    'city_display_name' => 'Köln',
    'city_aliases' => 
    array (
      0 => 'Koeln',
    ),
    'city_region' => 'Rhein und Popkultur',
    'city_headline' => 'Dom, moderne Kunst, Brauhäuser und kreative Viertel.',
    'city_summary' => 'Köln ist direkt und gesellig: Das Zentrum ist kompakt, doch die umliegenden Viertel geben der Reise den spannendsten Ton.',
    'city_best_for' => 'Moderne Kunst, Brauhäuser, unkomplizierte Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#db2777',
    'city_image_index' => 3,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Belgisches Viertel',
      2 => 'Ehrenfeld',
      3 => 'Rheinauhafen',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Dom und Museum Ludwig',
        'detail' => 'Klassisch und zeitgenössisch in wenigen Schritten, ohne Zeit mit Wegen zu verlieren.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Belgisches Viertel',
        'detail' => 'Unabhängiges Einkaufen, Kaffee und eine langsame Pause auf den Plätzen des Viertels.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Kölsch oder Ehrenfeld',
        'detail' => 'Traditionelles Brauhaus im Zentrum oder ein alternativer Abend Richtung Odonien.',
      ),
    ),
    'slug' => 'spot-1-museum-ludwig',
    'name' => 'Museum Ludwig',
    'type' => 'Kultur',
    'area' => 'Altstadt',
    'note' => 'Moderne Kunst und Pop-Art in perfekter Lage zwischen Dom und Rhein.',
    'time' => '2 Std.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 301,
  ),
  17 => 
  array (
    'city_slug' => 'cologne',
    'city_name' => 'Köln',
    'city_display_name' => 'Köln',
    'city_aliases' => 
    array (
      0 => 'Koeln',
    ),
    'city_region' => 'Rhein und Popkultur',
    'city_headline' => 'Dom, moderne Kunst, Brauhäuser und kreative Viertel.',
    'city_summary' => 'Köln ist direkt und gesellig: Das Zentrum ist kompakt, doch die umliegenden Viertel geben der Reise den spannendsten Ton.',
    'city_best_for' => 'Moderne Kunst, Brauhäuser, unkomplizierte Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#db2777',
    'city_image_index' => 3,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Belgisches Viertel',
      2 => 'Ehrenfeld',
      3 => 'Rheinauhafen',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Dom und Museum Ludwig',
        'detail' => 'Klassisch und zeitgenössisch in wenigen Schritten, ohne Zeit mit Wegen zu verlieren.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Belgisches Viertel',
        'detail' => 'Unabhängiges Einkaufen, Kaffee und eine langsame Pause auf den Plätzen des Viertels.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Kölsch oder Ehrenfeld',
        'detail' => 'Traditionelles Brauhaus im Zentrum oder ein alternativer Abend Richtung Odonien.',
      ),
    ),
    'slug' => 'spot-2-belgisches-viertel',
    'name' => 'Belgisches Viertel',
    'type' => 'Viertel',
    'area' => 'Innenstadt',
    'note' => 'Cafés, Konzeptläden und Bars: die einfachste Seite, um das heutige Köln zu verstehen.',
    'time' => 'Nachmittag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 302,
  ),
  18 => 
  array (
    'city_slug' => 'cologne',
    'city_name' => 'Köln',
    'city_display_name' => 'Köln',
    'city_aliases' => 
    array (
      0 => 'Koeln',
    ),
    'city_region' => 'Rhein und Popkultur',
    'city_headline' => 'Dom, moderne Kunst, Brauhäuser und kreative Viertel.',
    'city_summary' => 'Köln ist direkt und gesellig: Das Zentrum ist kompakt, doch die umliegenden Viertel geben der Reise den spannendsten Ton.',
    'city_best_for' => 'Moderne Kunst, Brauhäuser, unkomplizierte Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#db2777',
    'city_image_index' => 3,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Belgisches Viertel',
      2 => 'Ehrenfeld',
      3 => 'Rheinauhafen',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Dom und Museum Ludwig',
        'detail' => 'Klassisch und zeitgenössisch in wenigen Schritten, ohne Zeit mit Wegen zu verlieren.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Belgisches Viertel',
        'detail' => 'Unabhängiges Einkaufen, Kaffee und eine langsame Pause auf den Plätzen des Viertels.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Kölsch oder Ehrenfeld',
        'detail' => 'Traditionelles Brauhaus im Zentrum oder ein alternativer Abend Richtung Odonien.',
      ),
    ),
    'slug' => 'spot-3-brauhaus-tour',
    'name' => 'Brauhaus-Tour',
    'type' => 'Essen',
    'area' => 'Altstadt',
    'note' => 'Traditionelle Brauhäuser und schnell serviertes Kölsch, am besten mit leichter Stimmung.',
    'time' => 'Abend',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 303,
  ),
  19 => 
  array (
    'city_slug' => 'cologne',
    'city_name' => 'Köln',
    'city_display_name' => 'Köln',
    'city_aliases' => 
    array (
      0 => 'Koeln',
    ),
    'city_region' => 'Rhein und Popkultur',
    'city_headline' => 'Dom, moderne Kunst, Brauhäuser und kreative Viertel.',
    'city_summary' => 'Köln ist direkt und gesellig: Das Zentrum ist kompakt, doch die umliegenden Viertel geben der Reise den spannendsten Ton.',
    'city_best_for' => 'Moderne Kunst, Brauhäuser, unkomplizierte Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#db2777',
    'city_image_index' => 3,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Belgisches Viertel',
      2 => 'Ehrenfeld',
      3 => 'Rheinauhafen',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Dom und Museum Ludwig',
        'detail' => 'Klassisch und zeitgenössisch in wenigen Schritten, ohne Zeit mit Wegen zu verlieren.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Belgisches Viertel',
        'detail' => 'Unabhängiges Einkaufen, Kaffee und eine langsame Pause auf den Plätzen des Viertels.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Kölsch oder Ehrenfeld',
        'detail' => 'Traditionelles Brauhaus im Zentrum oder ein alternativer Abend Richtung Odonien.',
      ),
    ),
    'slug' => 'spot-4-odonien',
    'name' => 'Odonien',
    'type' => 'Einzigartig',
    'area' => 'Ehrenfeld',
    'note' => 'Künstlerischer Industrieort mit Veranstaltungen, Installationen und alternativer Atmosphäre.',
    'time' => 'Veranstaltung',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 304,
  ),
  20 => 
  array (
    'city_slug' => 'frankfurt',
    'city_name' => 'Frankfurt',
    'city_display_name' => 'Frankfurt',
    'city_aliases' => 
    array (
      0 => 'Frankfurt am Main',
    ),
    'city_region' => 'Skyline und Museen',
    'city_headline' => 'Hochhäuser, Apfelwein, Markthallen und Museen am Main.',
    'city_summary' => 'Frankfurt ist am interessantesten, wenn du die Stadt als Ort der Gegensätze liest: Finanzen, grüne Ufer, Markthallen und sehr unterschiedliche Viertel.',
    'city_best_for' => 'Museen, Skyline, internationale Gastronomieszene',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#ea580c',
    'city_image_index' => 4,
    'city_neighborhoods' => 
    array (
      0 => 'Innenstadt',
      1 => 'Sachsenhausen',
      2 => 'Bahnhofsviertel',
      3 => 'Westend',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Zentrum und Markt',
        'detail' => 'Spaziergang zwischen Römerberg und Kleinmarkthalle mit schnellen Kostproben.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Museumsufer',
        'detail' => 'Wähle ein Museum und geh zum Sonnenuntergang am Main entlang.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Sachsenhausen',
        'detail' => 'Abendessen mit Apfelwein oder ein urbanerer Abstecher ins Bahnhofsviertel.',
      ),
    ),
    'slug' => 'spot-0-main-tower',
    'name' => 'Main Tower',
    'type' => 'Aussicht',
    'area' => 'Innenstadt',
    'note' => 'Die einfachste Terrasse, um Skyline und Fluss von oben zu lesen.',
    'time' => '45 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 400,
  ),
  21 => 
  array (
    'city_slug' => 'frankfurt',
    'city_name' => 'Frankfurt',
    'city_display_name' => 'Frankfurt',
    'city_aliases' => 
    array (
      0 => 'Frankfurt am Main',
    ),
    'city_region' => 'Skyline und Museen',
    'city_headline' => 'Hochhäuser, Apfelwein, Markthallen und Museen am Main.',
    'city_summary' => 'Frankfurt ist am interessantesten, wenn du die Stadt als Ort der Gegensätze liest: Finanzen, grüne Ufer, Markthallen und sehr unterschiedliche Viertel.',
    'city_best_for' => 'Museen, Skyline, internationale Gastronomieszene',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#ea580c',
    'city_image_index' => 4,
    'city_neighborhoods' => 
    array (
      0 => 'Innenstadt',
      1 => 'Sachsenhausen',
      2 => 'Bahnhofsviertel',
      3 => 'Westend',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Zentrum und Markt',
        'detail' => 'Spaziergang zwischen Römerberg und Kleinmarkthalle mit schnellen Kostproben.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Museumsufer',
        'detail' => 'Wähle ein Museum und geh zum Sonnenuntergang am Main entlang.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Sachsenhausen',
        'detail' => 'Abendessen mit Apfelwein oder ein urbanerer Abstecher ins Bahnhofsviertel.',
      ),
    ),
    'slug' => 'spot-1-kleinmarkthalle',
    'name' => 'Kleinmarkthalle',
    'type' => 'Essen',
    'area' => 'Innenstadt',
    'note' => 'Überdachter Markt mit historischen Ständen, Snacks und Produkten zum Probieren.',
    'time' => 'Mittagessen',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 401,
  ),
  22 => 
  array (
    'city_slug' => 'frankfurt',
    'city_name' => 'Frankfurt',
    'city_display_name' => 'Frankfurt',
    'city_aliases' => 
    array (
      0 => 'Frankfurt am Main',
    ),
    'city_region' => 'Skyline und Museen',
    'city_headline' => 'Hochhäuser, Apfelwein, Markthallen und Museen am Main.',
    'city_summary' => 'Frankfurt ist am interessantesten, wenn du die Stadt als Ort der Gegensätze liest: Finanzen, grüne Ufer, Markthallen und sehr unterschiedliche Viertel.',
    'city_best_for' => 'Museen, Skyline, internationale Gastronomieszene',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#ea580c',
    'city_image_index' => 4,
    'city_neighborhoods' => 
    array (
      0 => 'Innenstadt',
      1 => 'Sachsenhausen',
      2 => 'Bahnhofsviertel',
      3 => 'Westend',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Zentrum und Markt',
        'detail' => 'Spaziergang zwischen Römerberg und Kleinmarkthalle mit schnellen Kostproben.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Museumsufer',
        'detail' => 'Wähle ein Museum und geh zum Sonnenuntergang am Main entlang.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Sachsenhausen',
        'detail' => 'Abendessen mit Apfelwein oder ein urbanerer Abstecher ins Bahnhofsviertel.',
      ),
    ),
    'slug' => 'spot-2-museumsufer',
    'name' => 'Museumsufer',
    'type' => 'Museen',
    'area' => 'Sachsenhausen',
    'note' => 'Ein ganzes Ufer voller Museen, perfekt, wenn du je nach Stimmung wählen möchtest.',
    'time' => 'Halber Tag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 402,
  ),
  23 => 
  array (
    'city_slug' => 'frankfurt',
    'city_name' => 'Frankfurt',
    'city_display_name' => 'Frankfurt',
    'city_aliases' => 
    array (
      0 => 'Frankfurt am Main',
    ),
    'city_region' => 'Skyline und Museen',
    'city_headline' => 'Hochhäuser, Apfelwein, Markthallen und Museen am Main.',
    'city_summary' => 'Frankfurt ist am interessantesten, wenn du die Stadt als Ort der Gegensätze liest: Finanzen, grüne Ufer, Markthallen und sehr unterschiedliche Viertel.',
    'city_best_for' => 'Museen, Skyline, internationale Gastronomieszene',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#ea580c',
    'city_image_index' => 4,
    'city_neighborhoods' => 
    array (
      0 => 'Innenstadt',
      1 => 'Sachsenhausen',
      2 => 'Bahnhofsviertel',
      3 => 'Westend',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Zentrum und Markt',
        'detail' => 'Spaziergang zwischen Römerberg und Kleinmarkthalle mit schnellen Kostproben.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Museumsufer',
        'detail' => 'Wähle ein Museum und geh zum Sonnenuntergang am Main entlang.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Sachsenhausen',
        'detail' => 'Abendessen mit Apfelwein oder ein urbanerer Abstecher ins Bahnhofsviertel.',
      ),
    ),
    'slug' => 'spot-3-bahnhofsviertel',
    'name' => 'Bahnhofsviertel',
    'type' => 'Viertel',
    'area' => 'Zentrum',
    'note' => 'Internationale Küchen, Bars und eine rauere, dynamische urbane Szene.',
    'time' => 'Abend',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 403,
  ),
  24 => 
  array (
    'city_slug' => 'frankfurt',
    'city_name' => 'Frankfurt',
    'city_display_name' => 'Frankfurt',
    'city_aliases' => 
    array (
      0 => 'Frankfurt am Main',
    ),
    'city_region' => 'Skyline und Museen',
    'city_headline' => 'Hochhäuser, Apfelwein, Markthallen und Museen am Main.',
    'city_summary' => 'Frankfurt ist am interessantesten, wenn du die Stadt als Ort der Gegensätze liest: Finanzen, grüne Ufer, Markthallen und sehr unterschiedliche Viertel.',
    'city_best_for' => 'Museen, Skyline, internationale Gastronomieszene',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#ea580c',
    'city_image_index' => 4,
    'city_neighborhoods' => 
    array (
      0 => 'Innenstadt',
      1 => 'Sachsenhausen',
      2 => 'Bahnhofsviertel',
      3 => 'Westend',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Zentrum und Markt',
        'detail' => 'Spaziergang zwischen Römerberg und Kleinmarkthalle mit schnellen Kostproben.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Museumsufer',
        'detail' => 'Wähle ein Museum und geh zum Sonnenuntergang am Main entlang.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Sachsenhausen',
        'detail' => 'Abendessen mit Apfelwein oder ein urbanerer Abstecher ins Bahnhofsviertel.',
      ),
    ),
    'slug' => 'spot-4-apfelweinwirtschaft',
    'name' => 'Apfelweinwirtschaft',
    'type' => 'Ausgehen',
    'area' => 'Sachsenhausen',
    'note' => 'Ein traditionelles Abendessen mit lokalem Apfelwein als sehr frankfurter Abschluss.',
    'time' => 'Abendessen',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 404,
  ),
  25 => 
  array (
    'city_slug' => 'dresden',
    'city_name' => 'Dresden',
    'city_display_name' => 'Dresden',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Barock und Neustadt',
    'city_headline' => 'Bühnenhafte Paläste, künstlerische Höfe und alternative Abende.',
    'city_summary' => 'Dresden verbindet Monumentalität und kreative Viertel: Am schönsten ist der Wechsel von der barocken Altstadt in die Neustadt, ohne daraus zwei getrennte Reisen zu machen.',
    'city_best_for' => 'Architektur, Galerien, romantische, aber nicht offensichtliche Wochenenden',
    'city_duration' => '2 Tage',
    'city_accent' => '#7c3aed',
    'city_image_index' => 5,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Innere Neustadt',
      2 => 'Äußere Neustadt',
      3 => 'Elbufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Szenische Altstadt',
        'detail' => 'Zwinger, Frauenkirche und ein kurzer Spaziergang über monumentale Plätze.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Kreative Neustadt',
        'detail' => 'Kunsthofpassage, Kaffee und kleine Galerien ohne starre Route.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Elbe bei Sonnenuntergang',
        'detail' => 'Zurück am Fluss entlang und ein unkompliziertes Abendessen in der Neustadt.',
      ),
    ),
    'slug' => 'spot-0-zwinger',
    'name' => 'Zwinger',
    'type' => 'Wahrzeichen',
    'area' => 'Altstadt',
    'note' => 'Theatralische Barockarchitektur, perfekt als Auftakt im historischen Zentrum.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 500,
  ),
  26 => 
  array (
    'city_slug' => 'dresden',
    'city_name' => 'Dresden',
    'city_display_name' => 'Dresden',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Barock und Neustadt',
    'city_headline' => 'Bühnenhafte Paläste, künstlerische Höfe und alternative Abende.',
    'city_summary' => 'Dresden verbindet Monumentalität und kreative Viertel: Am schönsten ist der Wechsel von der barocken Altstadt in die Neustadt, ohne daraus zwei getrennte Reisen zu machen.',
    'city_best_for' => 'Architektur, Galerien, romantische, aber nicht offensichtliche Wochenenden',
    'city_duration' => '2 Tage',
    'city_accent' => '#7c3aed',
    'city_image_index' => 5,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Innere Neustadt',
      2 => 'Äußere Neustadt',
      3 => 'Elbufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Szenische Altstadt',
        'detail' => 'Zwinger, Frauenkirche und ein kurzer Spaziergang über monumentale Plätze.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Kreative Neustadt',
        'detail' => 'Kunsthofpassage, Kaffee und kleine Galerien ohne starre Route.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Elbe bei Sonnenuntergang',
        'detail' => 'Zurück am Fluss entlang und ein unkompliziertes Abendessen in der Neustadt.',
      ),
    ),
    'slug' => 'spot-1-albertinum',
    'name' => 'Albertinum',
    'type' => 'Kultur',
    'area' => 'Altstadt',
    'note' => 'Moderne und zeitgenössische Kunst in einem eleganten Gebäude nahe der Elbe.',
    'time' => '2 Std.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 501,
  ),
  27 => 
  array (
    'city_slug' => 'dresden',
    'city_name' => 'Dresden',
    'city_display_name' => 'Dresden',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Barock und Neustadt',
    'city_headline' => 'Bühnenhafte Paläste, künstlerische Höfe und alternative Abende.',
    'city_summary' => 'Dresden verbindet Monumentalität und kreative Viertel: Am schönsten ist der Wechsel von der barocken Altstadt in die Neustadt, ohne daraus zwei getrennte Reisen zu machen.',
    'city_best_for' => 'Architektur, Galerien, romantische, aber nicht offensichtliche Wochenenden',
    'city_duration' => '2 Tage',
    'city_accent' => '#7c3aed',
    'city_image_index' => 5,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Innere Neustadt',
      2 => 'Äußere Neustadt',
      3 => 'Elbufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Szenische Altstadt',
        'detail' => 'Zwinger, Frauenkirche und ein kurzer Spaziergang über monumentale Plätze.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Kreative Neustadt',
        'detail' => 'Kunsthofpassage, Kaffee und kleine Galerien ohne starre Route.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Elbe bei Sonnenuntergang',
        'detail' => 'Zurück am Fluss entlang und ein unkompliziertes Abendessen in der Neustadt.',
      ),
    ),
    'slug' => 'spot-2-kunsthofpassage',
    'name' => 'Kunsthofpassage',
    'type' => 'Einzigartig',
    'area' => 'Äußere Neustadt',
    'note' => 'Farbige Höfe, Installationen und kleine unabhängige Adressen.',
    'time' => '60 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 502,
  ),
  28 => 
  array (
    'city_slug' => 'dresden',
    'city_name' => 'Dresden',
    'city_display_name' => 'Dresden',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Barock und Neustadt',
    'city_headline' => 'Bühnenhafte Paläste, künstlerische Höfe und alternative Abende.',
    'city_summary' => 'Dresden verbindet Monumentalität und kreative Viertel: Am schönsten ist der Wechsel von der barocken Altstadt in die Neustadt, ohne daraus zwei getrennte Reisen zu machen.',
    'city_best_for' => 'Architektur, Galerien, romantische, aber nicht offensichtliche Wochenenden',
    'city_duration' => '2 Tage',
    'city_accent' => '#7c3aed',
    'city_image_index' => 5,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Innere Neustadt',
      2 => 'Äußere Neustadt',
      3 => 'Elbufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Szenische Altstadt',
        'detail' => 'Zwinger, Frauenkirche und ein kurzer Spaziergang über monumentale Plätze.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Kreative Neustadt',
        'detail' => 'Kunsthofpassage, Kaffee und kleine Galerien ohne starre Route.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Elbe bei Sonnenuntergang',
        'detail' => 'Zurück am Fluss entlang und ein unkompliziertes Abendessen in der Neustadt.',
      ),
    ),
    'slug' => 'spot-3-pfunds-molkerei',
    'name' => 'Pfunds Molkerei',
    'type' => 'Essen',
    'area' => 'Neustadt',
    'note' => 'Eine historische Molkerei mit dekorierten Innenräumen und sehr besonderer Atmosphäre.',
    'time' => '30 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 503,
  ),
  29 => 
  array (
    'city_slug' => 'dresden',
    'city_name' => 'Dresden',
    'city_display_name' => 'Dresden',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Barock und Neustadt',
    'city_headline' => 'Bühnenhafte Paläste, künstlerische Höfe und alternative Abende.',
    'city_summary' => 'Dresden verbindet Monumentalität und kreative Viertel: Am schönsten ist der Wechsel von der barocken Altstadt in die Neustadt, ohne daraus zwei getrennte Reisen zu machen.',
    'city_best_for' => 'Architektur, Galerien, romantische, aber nicht offensichtliche Wochenenden',
    'city_duration' => '2 Tage',
    'city_accent' => '#7c3aed',
    'city_image_index' => 5,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Innere Neustadt',
      2 => 'Äußere Neustadt',
      3 => 'Elbufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Szenische Altstadt',
        'detail' => 'Zwinger, Frauenkirche und ein kurzer Spaziergang über monumentale Plätze.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Kreative Neustadt',
        'detail' => 'Kunsthofpassage, Kaffee und kleine Galerien ohne starre Route.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Elbe bei Sonnenuntergang',
        'detail' => 'Zurück am Fluss entlang und ein unkompliziertes Abendessen in der Neustadt.',
      ),
    ),
    'slug' => 'spot-4-elbufer',
    'name' => 'Elbufer',
    'type' => 'Entspannen',
    'area' => 'Elbe',
    'note' => 'Weite Ufer für eine langsame Pause mit Blick auf das Profil der Stadt.',
    'time' => 'Sonnenuntergang',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 504,
  ),
  30 => 
  array (
    'city_slug' => 'leipzig',
    'city_name' => 'Leipzig',
    'city_display_name' => 'Leipzig',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Kreative Passagen',
    'city_headline' => 'Historische Passagen, Seen, Musikgeschichte und junge Viertel.',
    'city_summary' => 'Leipzig fühlt sich leicht und kreativ an: kompakte Innenstadt, starke Musikgeschichte und Viertel, in denen Ateliers, Cafés und Wasserwege nah beieinanderliegen.',
    'city_best_for' => 'Musik, Passagen, kreative Viertel, Seen',
    'city_duration' => '2 Tage',
    'city_accent' => '#16a34a',
    'city_image_index' => 6,
    'city_neighborhoods' => 
    array (
      0 => 'Zentrum',
      1 => 'Plagwitz',
      2 => 'Südvorstadt',
      3 => 'Neuseenland',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenstadt und Passagen',
        'detail' => 'Starte kompakt zwischen Markt, Mädlerpassage und kleinen Cafés.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Plagwitz am Wasser',
        'detail' => 'Industriearchitektur, Kanäle und kreative Adressen im Westen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Musik oder Südvorstadt',
        'detail' => 'Konzertabend oder unkomplizierte Bars und Restaurants südlich des Zentrums.',
      ),
    ),
    'slug' => 'spot-0-maedlerpassage',
    'name' => 'Mädlerpassage',
    'type' => 'Architektur',
    'area' => 'Zentrum',
    'note' => 'Elegante Passagenarchitektur, ideal für einen ersten Eindruck der Innenstadt.',
    'time' => '45 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 600,
  ),
  31 => 
  array (
    'city_slug' => 'leipzig',
    'city_name' => 'Leipzig',
    'city_display_name' => 'Leipzig',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Kreative Passagen',
    'city_headline' => 'Historische Passagen, Seen, Musikgeschichte und junge Viertel.',
    'city_summary' => 'Leipzig fühlt sich leicht und kreativ an: kompakte Innenstadt, starke Musikgeschichte und Viertel, in denen Ateliers, Cafés und Wasserwege nah beieinanderliegen.',
    'city_best_for' => 'Musik, Passagen, kreative Viertel, Seen',
    'city_duration' => '2 Tage',
    'city_accent' => '#16a34a',
    'city_image_index' => 6,
    'city_neighborhoods' => 
    array (
      0 => 'Zentrum',
      1 => 'Plagwitz',
      2 => 'Südvorstadt',
      3 => 'Neuseenland',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenstadt und Passagen',
        'detail' => 'Starte kompakt zwischen Markt, Mädlerpassage und kleinen Cafés.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Plagwitz am Wasser',
        'detail' => 'Industriearchitektur, Kanäle und kreative Adressen im Westen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Musik oder Südvorstadt',
        'detail' => 'Konzertabend oder unkomplizierte Bars und Restaurants südlich des Zentrums.',
      ),
    ),
    'slug' => 'spot-1-gewandhaus',
    'name' => 'Gewandhaus',
    'type' => 'Kultur',
    'area' => 'Zentrum',
    'note' => 'Musikgeschichte und Gegenwart an einem Ort mit internationalem Klang.',
    'time' => 'Konzert',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 601,
  ),
  32 => 
  array (
    'city_slug' => 'leipzig',
    'city_name' => 'Leipzig',
    'city_display_name' => 'Leipzig',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Kreative Passagen',
    'city_headline' => 'Historische Passagen, Seen, Musikgeschichte und junge Viertel.',
    'city_summary' => 'Leipzig fühlt sich leicht und kreativ an: kompakte Innenstadt, starke Musikgeschichte und Viertel, in denen Ateliers, Cafés und Wasserwege nah beieinanderliegen.',
    'city_best_for' => 'Musik, Passagen, kreative Viertel, Seen',
    'city_duration' => '2 Tage',
    'city_accent' => '#16a34a',
    'city_image_index' => 6,
    'city_neighborhoods' => 
    array (
      0 => 'Zentrum',
      1 => 'Plagwitz',
      2 => 'Südvorstadt',
      3 => 'Neuseenland',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenstadt und Passagen',
        'detail' => 'Starte kompakt zwischen Markt, Mädlerpassage und kleinen Cafés.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Plagwitz am Wasser',
        'detail' => 'Industriearchitektur, Kanäle und kreative Adressen im Westen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Musik oder Südvorstadt',
        'detail' => 'Konzertabend oder unkomplizierte Bars und Restaurants südlich des Zentrums.',
      ),
    ),
    'slug' => 'spot-2-plagwitz',
    'name' => 'Plagwitz',
    'type' => 'Viertel',
    'area' => 'Westen',
    'note' => 'Kanäle, Ateliers, Cafés und Industriebauten mit sehr lebendigem Wochenendgefühl.',
    'time' => 'Nachmittag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 602,
  ),
  33 => 
  array (
    'city_slug' => 'leipzig',
    'city_name' => 'Leipzig',
    'city_display_name' => 'Leipzig',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Kreative Passagen',
    'city_headline' => 'Historische Passagen, Seen, Musikgeschichte und junge Viertel.',
    'city_summary' => 'Leipzig fühlt sich leicht und kreativ an: kompakte Innenstadt, starke Musikgeschichte und Viertel, in denen Ateliers, Cafés und Wasserwege nah beieinanderliegen.',
    'city_best_for' => 'Musik, Passagen, kreative Viertel, Seen',
    'city_duration' => '2 Tage',
    'city_accent' => '#16a34a',
    'city_image_index' => 6,
    'city_neighborhoods' => 
    array (
      0 => 'Zentrum',
      1 => 'Plagwitz',
      2 => 'Südvorstadt',
      3 => 'Neuseenland',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenstadt und Passagen',
        'detail' => 'Starte kompakt zwischen Markt, Mädlerpassage und kleinen Cafés.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Plagwitz am Wasser',
        'detail' => 'Industriearchitektur, Kanäle und kreative Adressen im Westen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Musik oder Südvorstadt',
        'detail' => 'Konzertabend oder unkomplizierte Bars und Restaurants südlich des Zentrums.',
      ),
    ),
    'slug' => 'spot-3-cospudener-see',
    'name' => 'Cospudener See',
    'type' => 'Draußen',
    'area' => 'Neuseenland',
    'note' => 'Ein schneller Wechsel vom Stadtrhythmus ans Wasser.',
    'time' => 'Halber Tag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 603,
  ),
  34 => 
  array (
    'city_slug' => 'stuttgart',
    'city_name' => 'Stuttgart',
    'city_display_name' => 'Stuttgart',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Design und Weinberge',
    'city_headline' => 'Architektur, Automobilkultur, Hügelblicke und Wein am Stadtrand.',
    'city_summary' => 'Stuttgart ist spannender, wenn du Höhe und Tiefe kombinierst: Museen, moderne Architektur, Talkesselblicke und Weinberge direkt neben der Stadt.',
    'city_best_for' => 'Design, Museen, Weinberge, Aussichtspunkte',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#0891b2',
    'city_image_index' => 7,
    'city_neighborhoods' => 
    array (
      0 => 'Mitte',
      1 => 'Bad Cannstatt',
      2 => 'Stuttgart-West',
      3 => 'Rotenberg',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Museum und Architektur',
        'detail' => 'Starte mit Designgeschichte und klarer Formensprache.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Höhen und Weinberge',
        'detail' => 'Plane eine Aussicht ein, damit die Lage der Stadt sichtbar wird.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'West oder Mitte',
        'detail' => 'Essen in einem urbanen Viertel mit kurzem Heimweg.',
      ),
    ),
    'slug' => 'spot-0-mercedes-benz-museum',
    'name' => 'Mercedes-Benz Museum',
    'type' => 'Design',
    'area' => 'Bad Cannstatt',
    'note' => 'Ein architektonisch starkes Museum für Design, Technik und Stadtidentität.',
    'time' => '2 Std.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 700,
  ),
  35 => 
  array (
    'city_slug' => 'stuttgart',
    'city_name' => 'Stuttgart',
    'city_display_name' => 'Stuttgart',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Design und Weinberge',
    'city_headline' => 'Architektur, Automobilkultur, Hügelblicke und Wein am Stadtrand.',
    'city_summary' => 'Stuttgart ist spannender, wenn du Höhe und Tiefe kombinierst: Museen, moderne Architektur, Talkesselblicke und Weinberge direkt neben der Stadt.',
    'city_best_for' => 'Design, Museen, Weinberge, Aussichtspunkte',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#0891b2',
    'city_image_index' => 7,
    'city_neighborhoods' => 
    array (
      0 => 'Mitte',
      1 => 'Bad Cannstatt',
      2 => 'Stuttgart-West',
      3 => 'Rotenberg',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Museum und Architektur',
        'detail' => 'Starte mit Designgeschichte und klarer Formensprache.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Höhen und Weinberge',
        'detail' => 'Plane eine Aussicht ein, damit die Lage der Stadt sichtbar wird.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'West oder Mitte',
        'detail' => 'Essen in einem urbanen Viertel mit kurzem Heimweg.',
      ),
    ),
    'slug' => 'spot-1-karlshoehe',
    'name' => 'Karlshöhe',
    'type' => 'Aussicht',
    'area' => 'West',
    'note' => 'Ein kurzer Aufstieg mit Blick über den Talkessel.',
    'time' => '60 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 701,
  ),
  36 => 
  array (
    'city_slug' => 'stuttgart',
    'city_name' => 'Stuttgart',
    'city_display_name' => 'Stuttgart',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Design und Weinberge',
    'city_headline' => 'Architektur, Automobilkultur, Hügelblicke und Wein am Stadtrand.',
    'city_summary' => 'Stuttgart ist spannender, wenn du Höhe und Tiefe kombinierst: Museen, moderne Architektur, Talkesselblicke und Weinberge direkt neben der Stadt.',
    'city_best_for' => 'Design, Museen, Weinberge, Aussichtspunkte',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#0891b2',
    'city_image_index' => 7,
    'city_neighborhoods' => 
    array (
      0 => 'Mitte',
      1 => 'Bad Cannstatt',
      2 => 'Stuttgart-West',
      3 => 'Rotenberg',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Museum und Architektur',
        'detail' => 'Starte mit Designgeschichte und klarer Formensprache.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Höhen und Weinberge',
        'detail' => 'Plane eine Aussicht ein, damit die Lage der Stadt sichtbar wird.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'West oder Mitte',
        'detail' => 'Essen in einem urbanen Viertel mit kurzem Heimweg.',
      ),
    ),
    'slug' => 'spot-2-grabkapelle-rotenberg',
    'name' => 'Grabkapelle Rotenberg',
    'type' => 'Wein',
    'area' => 'Rotenberg',
    'note' => 'Weinberge, Panorama und ein ruhiger Kontrast zum Zentrum.',
    'time' => 'Nachmittag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 702,
  ),
  37 => 
  array (
    'city_slug' => 'stuttgart',
    'city_name' => 'Stuttgart',
    'city_display_name' => 'Stuttgart',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Design und Weinberge',
    'city_headline' => 'Architektur, Automobilkultur, Hügelblicke und Wein am Stadtrand.',
    'city_summary' => 'Stuttgart ist spannender, wenn du Höhe und Tiefe kombinierst: Museen, moderne Architektur, Talkesselblicke und Weinberge direkt neben der Stadt.',
    'city_best_for' => 'Design, Museen, Weinberge, Aussichtspunkte',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#0891b2',
    'city_image_index' => 7,
    'city_neighborhoods' => 
    array (
      0 => 'Mitte',
      1 => 'Bad Cannstatt',
      2 => 'Stuttgart-West',
      3 => 'Rotenberg',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Museum und Architektur',
        'detail' => 'Starte mit Designgeschichte und klarer Formensprache.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Höhen und Weinberge',
        'detail' => 'Plane eine Aussicht ein, damit die Lage der Stadt sichtbar wird.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'West oder Mitte',
        'detail' => 'Essen in einem urbanen Viertel mit kurzem Heimweg.',
      ),
    ),
    'slug' => 'spot-3-staatsgalerie',
    'name' => 'Staatsgalerie',
    'type' => 'Kultur',
    'area' => 'Mitte',
    'note' => 'Kunst und postmoderne Architektur in zentraler Lage.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 703,
  ),
  38 => 
  array (
    'city_slug' => 'nuremberg',
    'city_name' => 'Nürnberg',
    'city_display_name' => 'Nürnberg',
    'city_aliases' => 
    array (
      0 => 'Nuernberg',
      1 => 'Nuremberg',
    ),
    'city_region' => 'Mittelalter und Märkte',
    'city_headline' => 'Burgblicke, Fachwerk, Geschichte und eine kompakte Altstadt.',
    'city_summary' => 'Nürnberg ist ideal für kurze Reisen: Die Altstadt ist gut lesbar, die Burg setzt den Rahmen und Museen geben der Stadt Tiefe.',
    'city_best_for' => 'Altstadt, Geschichte, Märkte, kurze Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#b45309',
    'city_image_index' => 8,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Gostenhof',
      2 => 'Sebald',
      3 => 'Lorenzer Seite',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Burg und Altstadt',
        'detail' => 'Von oben starten und langsam durch die historischen Straßen zurückgehen.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Museum oder Gostenhof',
        'detail' => 'Je nach Wetter Kultur oder ein lokales Viertel mit Cafés.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Klassisch essen',
        'detail' => 'Ein kompakter Abend in der Altstadt ohne lange Wege.',
      ),
    ),
    'slug' => 'spot-0-kaiserburg',
    'name' => 'Kaiserburg',
    'type' => 'Wahrzeichen',
    'area' => 'Altstadt',
    'note' => 'Der klassische Blick über Dächer und Stadtmauern.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 800,
  ),
  39 => 
  array (
    'city_slug' => 'nuremberg',
    'city_name' => 'Nürnberg',
    'city_display_name' => 'Nürnberg',
    'city_aliases' => 
    array (
      0 => 'Nuernberg',
      1 => 'Nuremberg',
    ),
    'city_region' => 'Mittelalter und Märkte',
    'city_headline' => 'Burgblicke, Fachwerk, Geschichte und eine kompakte Altstadt.',
    'city_summary' => 'Nürnberg ist ideal für kurze Reisen: Die Altstadt ist gut lesbar, die Burg setzt den Rahmen und Museen geben der Stadt Tiefe.',
    'city_best_for' => 'Altstadt, Geschichte, Märkte, kurze Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#b45309',
    'city_image_index' => 8,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Gostenhof',
      2 => 'Sebald',
      3 => 'Lorenzer Seite',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Burg und Altstadt',
        'detail' => 'Von oben starten und langsam durch die historischen Straßen zurückgehen.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Museum oder Gostenhof',
        'detail' => 'Je nach Wetter Kultur oder ein lokales Viertel mit Cafés.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Klassisch essen',
        'detail' => 'Ein kompakter Abend in der Altstadt ohne lange Wege.',
      ),
    ),
    'slug' => 'spot-1-germanisches-nationalmuseum',
    'name' => 'Germanisches Nationalmuseum',
    'type' => 'Museum',
    'area' => 'Altstadt',
    'note' => 'Ein tiefes Kulturmuseum, wenn du mehr als Fassaden sehen möchtest.',
    'time' => '2 Std.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 801,
  ),
  40 => 
  array (
    'city_slug' => 'nuremberg',
    'city_name' => 'Nürnberg',
    'city_display_name' => 'Nürnberg',
    'city_aliases' => 
    array (
      0 => 'Nuernberg',
      1 => 'Nuremberg',
    ),
    'city_region' => 'Mittelalter und Märkte',
    'city_headline' => 'Burgblicke, Fachwerk, Geschichte und eine kompakte Altstadt.',
    'city_summary' => 'Nürnberg ist ideal für kurze Reisen: Die Altstadt ist gut lesbar, die Burg setzt den Rahmen und Museen geben der Stadt Tiefe.',
    'city_best_for' => 'Altstadt, Geschichte, Märkte, kurze Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#b45309',
    'city_image_index' => 8,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Gostenhof',
      2 => 'Sebald',
      3 => 'Lorenzer Seite',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Burg und Altstadt',
        'detail' => 'Von oben starten und langsam durch die historischen Straßen zurückgehen.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Museum oder Gostenhof',
        'detail' => 'Je nach Wetter Kultur oder ein lokales Viertel mit Cafés.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Klassisch essen',
        'detail' => 'Ein kompakter Abend in der Altstadt ohne lange Wege.',
      ),
    ),
    'slug' => 'spot-2-gostenhof',
    'name' => 'Gostenhof',
    'type' => 'Viertel',
    'area' => 'Westen',
    'note' => 'Unabhängige Läden, Cafés und ein etwas rauerer lokaler Ton.',
    'time' => 'Nachmittag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 802,
  ),
  41 => 
  array (
    'city_slug' => 'nuremberg',
    'city_name' => 'Nürnberg',
    'city_display_name' => 'Nürnberg',
    'city_aliases' => 
    array (
      0 => 'Nuernberg',
      1 => 'Nuremberg',
    ),
    'city_region' => 'Mittelalter und Märkte',
    'city_headline' => 'Burgblicke, Fachwerk, Geschichte und eine kompakte Altstadt.',
    'city_summary' => 'Nürnberg ist ideal für kurze Reisen: Die Altstadt ist gut lesbar, die Burg setzt den Rahmen und Museen geben der Stadt Tiefe.',
    'city_best_for' => 'Altstadt, Geschichte, Märkte, kurze Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#b45309',
    'city_image_index' => 8,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Gostenhof',
      2 => 'Sebald',
      3 => 'Lorenzer Seite',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Burg und Altstadt',
        'detail' => 'Von oben starten und langsam durch die historischen Straßen zurückgehen.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Museum oder Gostenhof',
        'detail' => 'Je nach Wetter Kultur oder ein lokales Viertel mit Cafés.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Klassisch essen',
        'detail' => 'Ein kompakter Abend in der Altstadt ohne lange Wege.',
      ),
    ),
    'slug' => 'spot-3-altstadt-kueche',
    'name' => 'Altstadt-Küche',
    'type' => 'Essen',
    'area' => 'Zentrum',
    'note' => 'Regionale Klassiker in einem sehr fußläufigen Zentrum.',
    'time' => 'Abend',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 803,
  ),
  42 => 
  array (
    'city_slug' => 'heidelberg',
    'city_name' => 'Heidelberg',
    'city_display_name' => 'Heidelberg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Romantik am Neckar',
    'city_headline' => 'Schloss, Alte Brücke, Flussblick und eine der schönsten Altstädte.',
    'city_summary' => 'Heidelberg funktioniert, wenn du es langsam angehst: Fluss, Schloss, Altstadt und Aussichtspunkte ergeben eine sehr runde Kurzreise.',
    'city_best_for' => 'Romantische Wochenenden, Altstadt, Aussicht, Flusswege',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#dc2626',
    'city_image_index' => 9,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Neuenheim',
      2 => 'Königstuhl',
      3 => 'Neckarufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Schloss und Altstadt',
        'detail' => 'Beginne oben, bevor die Hauptwege voller werden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Neckar und Brücke',
        'detail' => 'Wechsle ans Wasser und nimm den Blick vom anderen Ufer mit.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Langsam ausklingen',
        'detail' => 'Ein frühes Abendessen in der Altstadt oder ein letzter Spaziergang am Fluss.',
      ),
    ),
    'slug' => 'spot-0-schloss-heidelberg',
    'name' => 'Schloss Heidelberg',
    'type' => 'Wahrzeichen',
    'area' => 'Altstadt',
    'note' => 'Der große Blick über Stadt, Fluss und Hügel.',
    'time' => '2 Std.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 900,
  ),
  43 => 
  array (
    'city_slug' => 'heidelberg',
    'city_name' => 'Heidelberg',
    'city_display_name' => 'Heidelberg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Romantik am Neckar',
    'city_headline' => 'Schloss, Alte Brücke, Flussblick und eine der schönsten Altstädte.',
    'city_summary' => 'Heidelberg funktioniert, wenn du es langsam angehst: Fluss, Schloss, Altstadt und Aussichtspunkte ergeben eine sehr runde Kurzreise.',
    'city_best_for' => 'Romantische Wochenenden, Altstadt, Aussicht, Flusswege',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#dc2626',
    'city_image_index' => 9,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Neuenheim',
      2 => 'Königstuhl',
      3 => 'Neckarufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Schloss und Altstadt',
        'detail' => 'Beginne oben, bevor die Hauptwege voller werden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Neckar und Brücke',
        'detail' => 'Wechsle ans Wasser und nimm den Blick vom anderen Ufer mit.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Langsam ausklingen',
        'detail' => 'Ein frühes Abendessen in der Altstadt oder ein letzter Spaziergang am Fluss.',
      ),
    ),
    'slug' => 'spot-1-alte-bruecke',
    'name' => 'Alte Brücke',
    'type' => 'Spaziergang',
    'area' => 'Neckar',
    'note' => 'Der einfachste Übergang zwischen Postkartenblick und Stadtleben.',
    'time' => '30 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 901,
  ),
  44 => 
  array (
    'city_slug' => 'heidelberg',
    'city_name' => 'Heidelberg',
    'city_display_name' => 'Heidelberg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Romantik am Neckar',
    'city_headline' => 'Schloss, Alte Brücke, Flussblick und eine der schönsten Altstädte.',
    'city_summary' => 'Heidelberg funktioniert, wenn du es langsam angehst: Fluss, Schloss, Altstadt und Aussichtspunkte ergeben eine sehr runde Kurzreise.',
    'city_best_for' => 'Romantische Wochenenden, Altstadt, Aussicht, Flusswege',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#dc2626',
    'city_image_index' => 9,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Neuenheim',
      2 => 'Königstuhl',
      3 => 'Neckarufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Schloss und Altstadt',
        'detail' => 'Beginne oben, bevor die Hauptwege voller werden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Neckar und Brücke',
        'detail' => 'Wechsle ans Wasser und nimm den Blick vom anderen Ufer mit.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Langsam ausklingen',
        'detail' => 'Ein frühes Abendessen in der Altstadt oder ein letzter Spaziergang am Fluss.',
      ),
    ),
    'slug' => 'spot-2-philosophenweg',
    'name' => 'Philosophenweg',
    'type' => 'Aussicht',
    'area' => 'Neuenheim',
    'note' => 'Ein ruhiger Weg mit weitem Blick auf Schloss und Altstadt.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 902,
  ),
  45 => 
  array (
    'city_slug' => 'heidelberg',
    'city_name' => 'Heidelberg',
    'city_display_name' => 'Heidelberg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Romantik am Neckar',
    'city_headline' => 'Schloss, Alte Brücke, Flussblick und eine der schönsten Altstädte.',
    'city_summary' => 'Heidelberg funktioniert, wenn du es langsam angehst: Fluss, Schloss, Altstadt und Aussichtspunkte ergeben eine sehr runde Kurzreise.',
    'city_best_for' => 'Romantische Wochenenden, Altstadt, Aussicht, Flusswege',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#dc2626',
    'city_image_index' => 9,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Neuenheim',
      2 => 'Königstuhl',
      3 => 'Neckarufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Schloss und Altstadt',
        'detail' => 'Beginne oben, bevor die Hauptwege voller werden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Neckar und Brücke',
        'detail' => 'Wechsle ans Wasser und nimm den Blick vom anderen Ufer mit.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Langsam ausklingen',
        'detail' => 'Ein frühes Abendessen in der Altstadt oder ein letzter Spaziergang am Fluss.',
      ),
    ),
    'slug' => 'spot-3-hauptstrasse',
    'name' => 'Hauptstraße',
    'type' => 'Altstadt',
    'area' => 'Zentrum',
    'note' => 'Läden, Cafés und historische Fassaden in sehr dichter Folge.',
    'time' => 'Nachmittag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 903,
  ),
  46 => 
  array (
    'city_slug' => 'bremen',
    'city_name' => 'Bremen',
    'city_display_name' => 'Bremen',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hanse und Weser',
    'city_headline' => 'Marktplatz, Schnoor, Backstein, Weser und hanseatische Ruhe.',
    'city_summary' => 'Bremen ist eine ruhige, gut portionierbare Städtereise: historisches Zentrum, kleine Gassen und Wege am Wasser liegen nah beieinander.',
    'city_best_for' => 'Hanseflair, kurze Wege, Altstadt, Wasser',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#0f766e',
    'city_image_index' => 10,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Schnoor',
      2 => 'Viertel',
      3 => 'Weserufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Marktplatz und Schnoor',
        'detail' => 'Historisches Zentrum zuerst, dann durch die kleinen Gassen weiterziehen.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Viertel und Cafés',
        'detail' => 'Ein lokalerer Ton mit kurzer Distanz zur Altstadt.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Weserufer',
        'detail' => 'Abschluss an der Schlachte oder in einer Bar im Viertel.',
      ),
    ),
    'slug' => 'spot-0-marktplatz',
    'name' => 'Marktplatz',
    'type' => 'Wahrzeichen',
    'area' => 'Altstadt',
    'note' => 'Rathaus, Roland und hanseatische Fassaden als starkes Zentrum.',
    'time' => '60 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1000,
  ),
  47 => 
  array (
    'city_slug' => 'bremen',
    'city_name' => 'Bremen',
    'city_display_name' => 'Bremen',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hanse und Weser',
    'city_headline' => 'Marktplatz, Schnoor, Backstein, Weser und hanseatische Ruhe.',
    'city_summary' => 'Bremen ist eine ruhige, gut portionierbare Städtereise: historisches Zentrum, kleine Gassen und Wege am Wasser liegen nah beieinander.',
    'city_best_for' => 'Hanseflair, kurze Wege, Altstadt, Wasser',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#0f766e',
    'city_image_index' => 10,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Schnoor',
      2 => 'Viertel',
      3 => 'Weserufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Marktplatz und Schnoor',
        'detail' => 'Historisches Zentrum zuerst, dann durch die kleinen Gassen weiterziehen.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Viertel und Cafés',
        'detail' => 'Ein lokalerer Ton mit kurzer Distanz zur Altstadt.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Weserufer',
        'detail' => 'Abschluss an der Schlachte oder in einer Bar im Viertel.',
      ),
    ),
    'slug' => 'spot-1-schnoorviertel',
    'name' => 'Schnoorviertel',
    'type' => 'Gassen',
    'area' => 'Altstadt',
    'note' => 'Kleine Häuser, enge Wege und ein sehr eigener Rhythmus.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1001,
  ),
  48 => 
  array (
    'city_slug' => 'bremen',
    'city_name' => 'Bremen',
    'city_display_name' => 'Bremen',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hanse und Weser',
    'city_headline' => 'Marktplatz, Schnoor, Backstein, Weser und hanseatische Ruhe.',
    'city_summary' => 'Bremen ist eine ruhige, gut portionierbare Städtereise: historisches Zentrum, kleine Gassen und Wege am Wasser liegen nah beieinander.',
    'city_best_for' => 'Hanseflair, kurze Wege, Altstadt, Wasser',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#0f766e',
    'city_image_index' => 10,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Schnoor',
      2 => 'Viertel',
      3 => 'Weserufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Marktplatz und Schnoor',
        'detail' => 'Historisches Zentrum zuerst, dann durch die kleinen Gassen weiterziehen.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Viertel und Cafés',
        'detail' => 'Ein lokalerer Ton mit kurzer Distanz zur Altstadt.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Weserufer',
        'detail' => 'Abschluss an der Schlachte oder in einer Bar im Viertel.',
      ),
    ),
    'slug' => 'spot-2-schlachte',
    'name' => 'Schlachte',
    'type' => 'Wasser',
    'area' => 'Weser',
    'note' => 'Uferpromenade für eine unkomplizierte Pause am Fluss.',
    'time' => 'Abend',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1002,
  ),
  49 => 
  array (
    'city_slug' => 'bremen',
    'city_name' => 'Bremen',
    'city_display_name' => 'Bremen',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hanse und Weser',
    'city_headline' => 'Marktplatz, Schnoor, Backstein, Weser und hanseatische Ruhe.',
    'city_summary' => 'Bremen ist eine ruhige, gut portionierbare Städtereise: historisches Zentrum, kleine Gassen und Wege am Wasser liegen nah beieinander.',
    'city_best_for' => 'Hanseflair, kurze Wege, Altstadt, Wasser',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#0f766e',
    'city_image_index' => 10,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Schnoor',
      2 => 'Viertel',
      3 => 'Weserufer',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Marktplatz und Schnoor',
        'detail' => 'Historisches Zentrum zuerst, dann durch die kleinen Gassen weiterziehen.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Viertel und Cafés',
        'detail' => 'Ein lokalerer Ton mit kurzer Distanz zur Altstadt.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Weserufer',
        'detail' => 'Abschluss an der Schlachte oder in einer Bar im Viertel.',
      ),
    ),
    'slug' => 'spot-3-das-viertel',
    'name' => 'Das Viertel',
    'type' => 'Viertel',
    'area' => 'Ostertor',
    'note' => 'Kinos, Bars, Cafés und ein lebendiger lokaler Alltag.',
    'time' => 'Nachmittag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1003,
  ),
  50 => 
  array (
    'city_slug' => 'duesseldorf',
    'city_name' => 'Düsseldorf',
    'city_display_name' => 'Düsseldorf',
    'city_aliases' => 
    array (
      0 => 'Duesseldorf',
      1 => 'Dusseldorf',
    ),
    'city_region' => 'Rhein und Avantgarde',
    'city_headline' => 'Rheinpromenade, Kunst, Mode und moderne Architektur im MedienHafen.',
    'city_summary' => 'Düsseldorf ist elegant, aber nicht steif: Kunstsammlungen, Rheinwege, japanische Küche und der MedienHafen ergeben einen klaren urbanen Wochenendtrip.',
    'city_best_for' => 'Kunst, Mode, Rheinpromenade, moderne Architektur',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#4f46e5',
    'city_image_index' => 11,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'MedienHafen',
      2 => 'Pempelfort',
      3 => 'Little Tokyo',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Kunst und Altstadt',
        'detail' => 'Museum oder Galerie zuerst, danach kurzer Weg Richtung Rhein.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Little Tokyo und Hafen',
        'detail' => 'Essen in der Stadtmitte, Architekturspaziergang im MedienHafen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Rheinpromenade',
        'detail' => 'Spaziergang am Wasser und ein klarer Blick auf die moderne Stadt.',
      ),
    ),
    'slug' => 'spot-0-medienhafen',
    'name' => 'MedienHafen',
    'type' => 'Architektur',
    'area' => 'Hafen',
    'note' => 'Moderne Architektur und klare Rheinblicke in einem kompakten Spaziergang.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1100,
  ),
  51 => 
  array (
    'city_slug' => 'duesseldorf',
    'city_name' => 'Düsseldorf',
    'city_display_name' => 'Düsseldorf',
    'city_aliases' => 
    array (
      0 => 'Duesseldorf',
      1 => 'Dusseldorf',
    ),
    'city_region' => 'Rhein und Avantgarde',
    'city_headline' => 'Rheinpromenade, Kunst, Mode und moderne Architektur im MedienHafen.',
    'city_summary' => 'Düsseldorf ist elegant, aber nicht steif: Kunstsammlungen, Rheinwege, japanische Küche und der MedienHafen ergeben einen klaren urbanen Wochenendtrip.',
    'city_best_for' => 'Kunst, Mode, Rheinpromenade, moderne Architektur',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#4f46e5',
    'city_image_index' => 11,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'MedienHafen',
      2 => 'Pempelfort',
      3 => 'Little Tokyo',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Kunst und Altstadt',
        'detail' => 'Museum oder Galerie zuerst, danach kurzer Weg Richtung Rhein.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Little Tokyo und Hafen',
        'detail' => 'Essen in der Stadtmitte, Architekturspaziergang im MedienHafen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Rheinpromenade',
        'detail' => 'Spaziergang am Wasser und ein klarer Blick auf die moderne Stadt.',
      ),
    ),
    'slug' => 'spot-1-k20',
    'name' => 'K20',
    'type' => 'Kunst',
    'area' => 'Altstadt',
    'note' => 'Eine starke Sammlung für moderne Kunst direkt am Zentrum.',
    'time' => '2 Std.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1101,
  ),
  52 => 
  array (
    'city_slug' => 'duesseldorf',
    'city_name' => 'Düsseldorf',
    'city_display_name' => 'Düsseldorf',
    'city_aliases' => 
    array (
      0 => 'Duesseldorf',
      1 => 'Dusseldorf',
    ),
    'city_region' => 'Rhein und Avantgarde',
    'city_headline' => 'Rheinpromenade, Kunst, Mode und moderne Architektur im MedienHafen.',
    'city_summary' => 'Düsseldorf ist elegant, aber nicht steif: Kunstsammlungen, Rheinwege, japanische Küche und der MedienHafen ergeben einen klaren urbanen Wochenendtrip.',
    'city_best_for' => 'Kunst, Mode, Rheinpromenade, moderne Architektur',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#4f46e5',
    'city_image_index' => 11,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'MedienHafen',
      2 => 'Pempelfort',
      3 => 'Little Tokyo',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Kunst und Altstadt',
        'detail' => 'Museum oder Galerie zuerst, danach kurzer Weg Richtung Rhein.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Little Tokyo und Hafen',
        'detail' => 'Essen in der Stadtmitte, Architekturspaziergang im MedienHafen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Rheinpromenade',
        'detail' => 'Spaziergang am Wasser und ein klarer Blick auf die moderne Stadt.',
      ),
    ),
    'slug' => 'spot-2-little-tokyo',
    'name' => 'Little Tokyo',
    'type' => 'Essen',
    'area' => 'Stadtmitte',
    'note' => 'Japanische Küche und Läden als sehr eigenständige Düsseldorfer Seite.',
    'time' => 'Mittagessen',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1102,
  ),
  53 => 
  array (
    'city_slug' => 'duesseldorf',
    'city_name' => 'Düsseldorf',
    'city_display_name' => 'Düsseldorf',
    'city_aliases' => 
    array (
      0 => 'Duesseldorf',
      1 => 'Dusseldorf',
    ),
    'city_region' => 'Rhein und Avantgarde',
    'city_headline' => 'Rheinpromenade, Kunst, Mode und moderne Architektur im MedienHafen.',
    'city_summary' => 'Düsseldorf ist elegant, aber nicht steif: Kunstsammlungen, Rheinwege, japanische Küche und der MedienHafen ergeben einen klaren urbanen Wochenendtrip.',
    'city_best_for' => 'Kunst, Mode, Rheinpromenade, moderne Architektur',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#4f46e5',
    'city_image_index' => 11,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'MedienHafen',
      2 => 'Pempelfort',
      3 => 'Little Tokyo',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Kunst und Altstadt',
        'detail' => 'Museum oder Galerie zuerst, danach kurzer Weg Richtung Rhein.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Little Tokyo und Hafen',
        'detail' => 'Essen in der Stadtmitte, Architekturspaziergang im MedienHafen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Rheinpromenade',
        'detail' => 'Spaziergang am Wasser und ein klarer Blick auf die moderne Stadt.',
      ),
    ),
    'slug' => 'spot-3-rheinpromenade',
    'name' => 'Rheinpromenade',
    'type' => 'Spaziergang',
    'area' => 'Rhein',
    'note' => 'Der beste Weg, Stadt, Wasser und Abendlicht zusammenzubringen.',
    'time' => 'Sonnenuntergang',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1103,
  ),
  54 => 
  array (
    'city_slug' => 'duisburg',
    'city_name' => 'Duisburg',
    'city_display_name' => 'Duisburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Industriekultur am Rhein',
    'city_headline' => 'Hafen, Stahlarchitektur, Rheinwege und raues Ruhrgebietsgefühl.',
    'city_summary' => 'Duisburg ist besonders, wenn du Industriekultur nicht als Kulisse, sondern als Stadterlebnis liest: Hafen, Parks, Wasser und alte Stahlstrukturen ergeben einen starken Kontrast.',
    'city_best_for' => 'Industriekultur, Hafen, Rhein, urbane Fotospots',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#475569',
    'city_image_index' => 12,
    'city_neighborhoods' => 
    array (
      0 => 'Innenhafen',
      1 => 'Meiderich',
      2 => 'Ruhrort',
      3 => 'Rheinpark',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenhafen und Ruhrort',
        'detail' => 'Starte am Wasser und nähere dich der Hafenstadt langsam.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Landschaftspark',
        'detail' => 'Industriekultur zu Fuß erleben, mit viel Raum für Fotos und Pausen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Licht und Aussicht',
        'detail' => 'Tiger & Turtle oder der beleuchtete Landschaftspark als starker Abschluss.',
      ),
    ),
    'slug' => 'spot-0-landschaftspark-duisburg-nord',
    'name' => 'Landschaftspark Duisburg-Nord',
    'type' => 'Industriekultur',
    'area' => 'Meiderich',
    'note' => 'Ehemalige Hochöfen, Lichtinstallationen und weite Wege durch eine sehr eigene Parklandschaft.',
    'time' => '2 Std.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1200,
  ),
  55 => 
  array (
    'city_slug' => 'duisburg',
    'city_name' => 'Duisburg',
    'city_display_name' => 'Duisburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Industriekultur am Rhein',
    'city_headline' => 'Hafen, Stahlarchitektur, Rheinwege und raues Ruhrgebietsgefühl.',
    'city_summary' => 'Duisburg ist besonders, wenn du Industriekultur nicht als Kulisse, sondern als Stadterlebnis liest: Hafen, Parks, Wasser und alte Stahlstrukturen ergeben einen starken Kontrast.',
    'city_best_for' => 'Industriekultur, Hafen, Rhein, urbane Fotospots',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#475569',
    'city_image_index' => 12,
    'city_neighborhoods' => 
    array (
      0 => 'Innenhafen',
      1 => 'Meiderich',
      2 => 'Ruhrort',
      3 => 'Rheinpark',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenhafen und Ruhrort',
        'detail' => 'Starte am Wasser und nähere dich der Hafenstadt langsam.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Landschaftspark',
        'detail' => 'Industriekultur zu Fuß erleben, mit viel Raum für Fotos und Pausen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Licht und Aussicht',
        'detail' => 'Tiger & Turtle oder der beleuchtete Landschaftspark als starker Abschluss.',
      ),
    ),
    'slug' => 'spot-1-innenhafen',
    'name' => 'Innenhafen',
    'type' => 'Wasser',
    'area' => 'Zentrum',
    'note' => 'Speicher, Restaurants und Wasserblicke als ruhiger Einstieg in die Stadt.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1201,
  ),
  56 => 
  array (
    'city_slug' => 'duisburg',
    'city_name' => 'Duisburg',
    'city_display_name' => 'Duisburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Industriekultur am Rhein',
    'city_headline' => 'Hafen, Stahlarchitektur, Rheinwege und raues Ruhrgebietsgefühl.',
    'city_summary' => 'Duisburg ist besonders, wenn du Industriekultur nicht als Kulisse, sondern als Stadterlebnis liest: Hafen, Parks, Wasser und alte Stahlstrukturen ergeben einen starken Kontrast.',
    'city_best_for' => 'Industriekultur, Hafen, Rhein, urbane Fotospots',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#475569',
    'city_image_index' => 12,
    'city_neighborhoods' => 
    array (
      0 => 'Innenhafen',
      1 => 'Meiderich',
      2 => 'Ruhrort',
      3 => 'Rheinpark',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenhafen und Ruhrort',
        'detail' => 'Starte am Wasser und nähere dich der Hafenstadt langsam.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Landschaftspark',
        'detail' => 'Industriekultur zu Fuß erleben, mit viel Raum für Fotos und Pausen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Licht und Aussicht',
        'detail' => 'Tiger & Turtle oder der beleuchtete Landschaftspark als starker Abschluss.',
      ),
    ),
    'slug' => 'spot-2-ruhrort',
    'name' => 'Ruhrort',
    'type' => 'Hafen',
    'area' => 'Ruhrort',
    'note' => 'Hafenstadtgefühl, Schiffe und ein Blick auf die industrielle Seite des Rheins.',
    'time' => 'Nachmittag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1202,
  ),
  57 => 
  array (
    'city_slug' => 'duisburg',
    'city_name' => 'Duisburg',
    'city_display_name' => 'Duisburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Industriekultur am Rhein',
    'city_headline' => 'Hafen, Stahlarchitektur, Rheinwege und raues Ruhrgebietsgefühl.',
    'city_summary' => 'Duisburg ist besonders, wenn du Industriekultur nicht als Kulisse, sondern als Stadterlebnis liest: Hafen, Parks, Wasser und alte Stahlstrukturen ergeben einen starken Kontrast.',
    'city_best_for' => 'Industriekultur, Hafen, Rhein, urbane Fotospots',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#475569',
    'city_image_index' => 12,
    'city_neighborhoods' => 
    array (
      0 => 'Innenhafen',
      1 => 'Meiderich',
      2 => 'Ruhrort',
      3 => 'Rheinpark',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenhafen und Ruhrort',
        'detail' => 'Starte am Wasser und nähere dich der Hafenstadt langsam.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Landschaftspark',
        'detail' => 'Industriekultur zu Fuß erleben, mit viel Raum für Fotos und Pausen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Licht und Aussicht',
        'detail' => 'Tiger & Turtle oder der beleuchtete Landschaftspark als starker Abschluss.',
      ),
    ),
    'slug' => 'spot-3-tiger-turtle',
    'name' => 'Tiger & Turtle',
    'type' => 'Draußen',
    'area' => 'Angerhausen',
    'note' => 'Begehbare Skulptur mit weitem Blick über Rhein und Ruhrgebiet.',
    'time' => '60 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1203,
  ),
  58 => 
  array (
    'city_slug' => 'bonn',
    'city_name' => 'Bonn',
    'city_display_name' => 'Bonn',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Rhein und Geschichte',
    'city_headline' => 'Beethovenstadt, Rheinwege, Museen und elegante Gründerzeitstraßen.',
    'city_summary' => 'Bonn ist ruhig, grün und überraschend vielschichtig: Musikgeschichte, Museen, Rheinpromenaden und Viertel mit schönen Fassaden liegen angenehm nah beieinander.',
    'city_best_for' => 'Rheinspaziergänge, Museen, Geschichte, entspannte Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#db2777',
    'city_image_index' => 13,
    'city_neighborhoods' => 
    array (
      0 => 'Zentrum',
      1 => 'Südstadt',
      2 => 'Rheinaue',
      3 => 'Poppelsdorf',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Zentrum und Beethoven',
        'detail' => 'Historischer Auftakt mit kurzer Distanz zum Rhein.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Südstadt oder Museumsmeile',
        'detail' => 'Je nach Stimmung Fassaden, Cafés oder eine größere Ausstellung.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Rheinblick',
        'detail' => 'Ein ruhiger Abschluss am Wasser oder in Poppelsdorf.',
      ),
    ),
    'slug' => 'spot-0-beethoven-haus',
    'name' => 'Beethoven-Haus',
    'type' => 'Kultur',
    'area' => 'Zentrum',
    'note' => 'Ein kompakter kultureller Einstieg in die Identität der Stadt.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1300,
  ),
  59 => 
  array (
    'city_slug' => 'bonn',
    'city_name' => 'Bonn',
    'city_display_name' => 'Bonn',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Rhein und Geschichte',
    'city_headline' => 'Beethovenstadt, Rheinwege, Museen und elegante Gründerzeitstraßen.',
    'city_summary' => 'Bonn ist ruhig, grün und überraschend vielschichtig: Musikgeschichte, Museen, Rheinpromenaden und Viertel mit schönen Fassaden liegen angenehm nah beieinander.',
    'city_best_for' => 'Rheinspaziergänge, Museen, Geschichte, entspannte Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#db2777',
    'city_image_index' => 13,
    'city_neighborhoods' => 
    array (
      0 => 'Zentrum',
      1 => 'Südstadt',
      2 => 'Rheinaue',
      3 => 'Poppelsdorf',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Zentrum und Beethoven',
        'detail' => 'Historischer Auftakt mit kurzer Distanz zum Rhein.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Südstadt oder Museumsmeile',
        'detail' => 'Je nach Stimmung Fassaden, Cafés oder eine größere Ausstellung.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Rheinblick',
        'detail' => 'Ein ruhiger Abschluss am Wasser oder in Poppelsdorf.',
      ),
    ),
    'slug' => 'spot-1-rheinpromenade',
    'name' => 'Rheinpromenade',
    'type' => 'Spaziergang',
    'area' => 'Rhein',
    'note' => 'Weite Wege am Wasser mit Blick Richtung Siebengebirge.',
    'time' => '60 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1301,
  ),
  60 => 
  array (
    'city_slug' => 'bonn',
    'city_name' => 'Bonn',
    'city_display_name' => 'Bonn',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Rhein und Geschichte',
    'city_headline' => 'Beethovenstadt, Rheinwege, Museen und elegante Gründerzeitstraßen.',
    'city_summary' => 'Bonn ist ruhig, grün und überraschend vielschichtig: Musikgeschichte, Museen, Rheinpromenaden und Viertel mit schönen Fassaden liegen angenehm nah beieinander.',
    'city_best_for' => 'Rheinspaziergänge, Museen, Geschichte, entspannte Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#db2777',
    'city_image_index' => 13,
    'city_neighborhoods' => 
    array (
      0 => 'Zentrum',
      1 => 'Südstadt',
      2 => 'Rheinaue',
      3 => 'Poppelsdorf',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Zentrum und Beethoven',
        'detail' => 'Historischer Auftakt mit kurzer Distanz zum Rhein.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Südstadt oder Museumsmeile',
        'detail' => 'Je nach Stimmung Fassaden, Cafés oder eine größere Ausstellung.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Rheinblick',
        'detail' => 'Ein ruhiger Abschluss am Wasser oder in Poppelsdorf.',
      ),
    ),
    'slug' => 'spot-2-suedstadt',
    'name' => 'Südstadt',
    'type' => 'Viertel',
    'area' => 'Südstadt',
    'note' => 'Gründerzeitstraßen, Cafés und ruhige Wohnstadtatmosphäre.',
    'time' => 'Nachmittag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1302,
  ),
  61 => 
  array (
    'city_slug' => 'bonn',
    'city_name' => 'Bonn',
    'city_display_name' => 'Bonn',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Rhein und Geschichte',
    'city_headline' => 'Beethovenstadt, Rheinwege, Museen und elegante Gründerzeitstraßen.',
    'city_summary' => 'Bonn ist ruhig, grün und überraschend vielschichtig: Musikgeschichte, Museen, Rheinpromenaden und Viertel mit schönen Fassaden liegen angenehm nah beieinander.',
    'city_best_for' => 'Rheinspaziergänge, Museen, Geschichte, entspannte Wochenenden',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#db2777',
    'city_image_index' => 13,
    'city_neighborhoods' => 
    array (
      0 => 'Zentrum',
      1 => 'Südstadt',
      2 => 'Rheinaue',
      3 => 'Poppelsdorf',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Zentrum und Beethoven',
        'detail' => 'Historischer Auftakt mit kurzer Distanz zum Rhein.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Südstadt oder Museumsmeile',
        'detail' => 'Je nach Stimmung Fassaden, Cafés oder eine größere Ausstellung.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Rheinblick',
        'detail' => 'Ein ruhiger Abschluss am Wasser oder in Poppelsdorf.',
      ),
    ),
    'slug' => 'spot-3-museumsmeile',
    'name' => 'Museumsmeile',
    'type' => 'Museen',
    'area' => 'Gronau',
    'note' => 'Starke Auswahl für Kunst, Geschichte und Gegenwart.',
    'time' => 'Halber Tag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1303,
  ),
  62 => 
  array (
    'city_slug' => 'muenster',
    'city_name' => 'Münster',
    'city_display_name' => 'Münster',
    'city_aliases' => 
    array (
      0 => 'Muenster',
      1 => 'Munster',
    ),
    'city_region' => 'Altstadt und Fahrräder',
    'city_headline' => 'Giebelhäuser, Prinzipalmarkt, Aasee und sehr entspannter Stadtrhythmus.',
    'city_summary' => 'Münster ist ideal für ein leichtes Wochenende: historische Fassaden, kurze Wege, Fahrräder und Wasser geben der Stadt einen ruhigen, sehr lebenswerten Charakter.',
    'city_best_for' => 'Altstadt, Fahrräder, Cafés, entspannte Kurztrips',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#15803d',
    'city_image_index' => 14,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Aasee',
      2 => 'Kreuzviertel',
      3 => 'Hafen',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Altstadt und Markt',
        'detail' => 'Prinzipalmarkt, Domplatz und kurze Wege durch das Zentrum.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Aasee und Viertel',
        'detail' => 'Langsam ans Wasser und dann durch ein ruhiges Wohnviertel.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Hafen',
        'detail' => 'Essen oder Drinks am Wasser, ohne den Tagesrhythmus zu verlieren.',
      ),
    ),
    'slug' => 'spot-0-prinzipalmarkt',
    'name' => 'Prinzipalmarkt',
    'type' => 'Altstadt',
    'area' => 'Zentrum',
    'note' => 'Giebelhäuser, Arkaden und der klarste visuelle Einstieg in Münster.',
    'time' => '60 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1400,
  ),
  63 => 
  array (
    'city_slug' => 'muenster',
    'city_name' => 'Münster',
    'city_display_name' => 'Münster',
    'city_aliases' => 
    array (
      0 => 'Muenster',
      1 => 'Munster',
    ),
    'city_region' => 'Altstadt und Fahrräder',
    'city_headline' => 'Giebelhäuser, Prinzipalmarkt, Aasee und sehr entspannter Stadtrhythmus.',
    'city_summary' => 'Münster ist ideal für ein leichtes Wochenende: historische Fassaden, kurze Wege, Fahrräder und Wasser geben der Stadt einen ruhigen, sehr lebenswerten Charakter.',
    'city_best_for' => 'Altstadt, Fahrräder, Cafés, entspannte Kurztrips',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#15803d',
    'city_image_index' => 14,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Aasee',
      2 => 'Kreuzviertel',
      3 => 'Hafen',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Altstadt und Markt',
        'detail' => 'Prinzipalmarkt, Domplatz und kurze Wege durch das Zentrum.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Aasee und Viertel',
        'detail' => 'Langsam ans Wasser und dann durch ein ruhiges Wohnviertel.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Hafen',
        'detail' => 'Essen oder Drinks am Wasser, ohne den Tagesrhythmus zu verlieren.',
      ),
    ),
    'slug' => 'spot-1-aasee',
    'name' => 'Aasee',
    'type' => 'Wasser',
    'area' => 'Innenstadt',
    'note' => 'Spaziergang, Picknick oder Bootsblick direkt nahe der Altstadt.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1401,
  ),
  64 => 
  array (
    'city_slug' => 'muenster',
    'city_name' => 'Münster',
    'city_display_name' => 'Münster',
    'city_aliases' => 
    array (
      0 => 'Muenster',
      1 => 'Munster',
    ),
    'city_region' => 'Altstadt und Fahrräder',
    'city_headline' => 'Giebelhäuser, Prinzipalmarkt, Aasee und sehr entspannter Stadtrhythmus.',
    'city_summary' => 'Münster ist ideal für ein leichtes Wochenende: historische Fassaden, kurze Wege, Fahrräder und Wasser geben der Stadt einen ruhigen, sehr lebenswerten Charakter.',
    'city_best_for' => 'Altstadt, Fahrräder, Cafés, entspannte Kurztrips',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#15803d',
    'city_image_index' => 14,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Aasee',
      2 => 'Kreuzviertel',
      3 => 'Hafen',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Altstadt und Markt',
        'detail' => 'Prinzipalmarkt, Domplatz und kurze Wege durch das Zentrum.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Aasee und Viertel',
        'detail' => 'Langsam ans Wasser und dann durch ein ruhiges Wohnviertel.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Hafen',
        'detail' => 'Essen oder Drinks am Wasser, ohne den Tagesrhythmus zu verlieren.',
      ),
    ),
    'slug' => 'spot-2-kreuzviertel',
    'name' => 'Kreuzviertel',
    'type' => 'Viertel',
    'area' => 'Nordwesten',
    'note' => 'Schöne Straßen, Cafés und ein sehr lokaler Wochenendton.',
    'time' => 'Nachmittag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1402,
  ),
  65 => 
  array (
    'city_slug' => 'muenster',
    'city_name' => 'Münster',
    'city_display_name' => 'Münster',
    'city_aliases' => 
    array (
      0 => 'Muenster',
      1 => 'Munster',
    ),
    'city_region' => 'Altstadt und Fahrräder',
    'city_headline' => 'Giebelhäuser, Prinzipalmarkt, Aasee und sehr entspannter Stadtrhythmus.',
    'city_summary' => 'Münster ist ideal für ein leichtes Wochenende: historische Fassaden, kurze Wege, Fahrräder und Wasser geben der Stadt einen ruhigen, sehr lebenswerten Charakter.',
    'city_best_for' => 'Altstadt, Fahrräder, Cafés, entspannte Kurztrips',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#15803d',
    'city_image_index' => 14,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Aasee',
      2 => 'Kreuzviertel',
      3 => 'Hafen',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Altstadt und Markt',
        'detail' => 'Prinzipalmarkt, Domplatz und kurze Wege durch das Zentrum.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Aasee und Viertel',
        'detail' => 'Langsam ans Wasser und dann durch ein ruhiges Wohnviertel.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Hafen',
        'detail' => 'Essen oder Drinks am Wasser, ohne den Tagesrhythmus zu verlieren.',
      ),
    ),
    'slug' => 'spot-3-hafen',
    'name' => 'Hafen',
    'type' => 'Ausgehen',
    'area' => 'Hafen',
    'note' => 'Restaurants und Bars am Wasser als unkomplizierter Abend.',
    'time' => 'Abend',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1403,
  ),
  66 => 
  array (
    'city_slug' => 'rostock',
    'city_name' => 'Rostock',
    'city_display_name' => 'Rostock',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hanse und Ostsee',
    'city_headline' => 'Backstein, Hafenluft, Ostsee und ein schneller Wechsel nach Warnemünde.',
    'city_summary' => 'Rostock verbindet Stadt und Küste: Hanseatische Backsteinarchitektur, Hafenwege und die Nähe zur Ostsee machen den Trip klar und erholsam.',
    'city_best_for' => 'Ostsee, Hafen, Backsteinarchitektur, entspannte Küstentage',
    'city_duration' => '2 Tage',
    'city_accent' => '#0284c7',
    'city_image_index' => 15,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Stadthafen',
      2 => 'Kröpeliner-Tor-Vorstadt',
      3 => 'Warnemünde',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Altstadt und Backstein',
        'detail' => 'Starte zwischen Markt, Toren und hanseatischen Fassaden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Hafen oder Warnemünde',
        'detail' => 'Je nach Wetter Promenade in der Stadt oder direkt an die Ostsee.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'KTV oder Stadthafen',
        'detail' => 'Ein entspannter Abschluss zwischen Bars, Wasser und Küstenlicht.',
      ),
    ),
    'slug' => 'spot-0-neuer-markt',
    'name' => 'Neuer Markt',
    'type' => 'Altstadt',
    'area' => 'Zentrum',
    'note' => 'Hanseatische Fassaden und Backstein als kompakter Stadteinstieg.',
    'time' => '60 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1500,
  ),
  67 => 
  array (
    'city_slug' => 'rostock',
    'city_name' => 'Rostock',
    'city_display_name' => 'Rostock',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hanse und Ostsee',
    'city_headline' => 'Backstein, Hafenluft, Ostsee und ein schneller Wechsel nach Warnemünde.',
    'city_summary' => 'Rostock verbindet Stadt und Küste: Hanseatische Backsteinarchitektur, Hafenwege und die Nähe zur Ostsee machen den Trip klar und erholsam.',
    'city_best_for' => 'Ostsee, Hafen, Backsteinarchitektur, entspannte Küstentage',
    'city_duration' => '2 Tage',
    'city_accent' => '#0284c7',
    'city_image_index' => 15,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Stadthafen',
      2 => 'Kröpeliner-Tor-Vorstadt',
      3 => 'Warnemünde',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Altstadt und Backstein',
        'detail' => 'Starte zwischen Markt, Toren und hanseatischen Fassaden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Hafen oder Warnemünde',
        'detail' => 'Je nach Wetter Promenade in der Stadt oder direkt an die Ostsee.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'KTV oder Stadthafen',
        'detail' => 'Ein entspannter Abschluss zwischen Bars, Wasser und Küstenlicht.',
      ),
    ),
    'slug' => 'spot-1-stadthafen',
    'name' => 'Stadthafen',
    'type' => 'Hafen',
    'area' => 'Warnow',
    'note' => 'Promenade, Schiffe und Abendlicht direkt an der Warnow.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1501,
  ),
  68 => 
  array (
    'city_slug' => 'rostock',
    'city_name' => 'Rostock',
    'city_display_name' => 'Rostock',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hanse und Ostsee',
    'city_headline' => 'Backstein, Hafenluft, Ostsee und ein schneller Wechsel nach Warnemünde.',
    'city_summary' => 'Rostock verbindet Stadt und Küste: Hanseatische Backsteinarchitektur, Hafenwege und die Nähe zur Ostsee machen den Trip klar und erholsam.',
    'city_best_for' => 'Ostsee, Hafen, Backsteinarchitektur, entspannte Küstentage',
    'city_duration' => '2 Tage',
    'city_accent' => '#0284c7',
    'city_image_index' => 15,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Stadthafen',
      2 => 'Kröpeliner-Tor-Vorstadt',
      3 => 'Warnemünde',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Altstadt und Backstein',
        'detail' => 'Starte zwischen Markt, Toren und hanseatischen Fassaden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Hafen oder Warnemünde',
        'detail' => 'Je nach Wetter Promenade in der Stadt oder direkt an die Ostsee.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'KTV oder Stadthafen',
        'detail' => 'Ein entspannter Abschluss zwischen Bars, Wasser und Küstenlicht.',
      ),
    ),
    'slug' => 'spot-2-warnemuende',
    'name' => 'Warnemünde',
    'type' => 'Küste',
    'area' => 'Ostsee',
    'note' => 'Strand, Leuchtturm und Ostseeluft als natürlicher zweiter Teil der Reise.',
    'time' => 'Halber Tag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1502,
  ),
  69 => 
  array (
    'city_slug' => 'rostock',
    'city_name' => 'Rostock',
    'city_display_name' => 'Rostock',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Hanse und Ostsee',
    'city_headline' => 'Backstein, Hafenluft, Ostsee und ein schneller Wechsel nach Warnemünde.',
    'city_summary' => 'Rostock verbindet Stadt und Küste: Hanseatische Backsteinarchitektur, Hafenwege und die Nähe zur Ostsee machen den Trip klar und erholsam.',
    'city_best_for' => 'Ostsee, Hafen, Backsteinarchitektur, entspannte Küstentage',
    'city_duration' => '2 Tage',
    'city_accent' => '#0284c7',
    'city_image_index' => 15,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Stadthafen',
      2 => 'Kröpeliner-Tor-Vorstadt',
      3 => 'Warnemünde',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Altstadt und Backstein',
        'detail' => 'Starte zwischen Markt, Toren und hanseatischen Fassaden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Hafen oder Warnemünde',
        'detail' => 'Je nach Wetter Promenade in der Stadt oder direkt an die Ostsee.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'KTV oder Stadthafen',
        'detail' => 'Ein entspannter Abschluss zwischen Bars, Wasser und Küstenlicht.',
      ),
    ),
    'slug' => 'spot-3-ktv',
    'name' => 'KTV',
    'type' => 'Viertel',
    'area' => 'Kröpeliner-Tor-Vorstadt',
    'note' => 'Cafés, Bars und studentisches Leben nahe dem Zentrum.',
    'time' => 'Abend',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1503,
  ),
  70 => 
  array (
    'city_slug' => 'aachen',
    'city_name' => 'Aachen',
    'city_display_name' => 'Aachen',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Dom und Thermalquellen',
    'city_headline' => 'Kaiserliche Geschichte, heiße Quellen, Altstadtgassen und kurze Wege.',
    'city_summary' => 'Aachen ist kompakt und geschichtsstark: Dom, Rathaus, Thermalwasser und studentische Cafés ergeben eine ruhige, gut portionierbare Städtereise.',
    'city_best_for' => 'Dom, Geschichte, Cafés, entspannte Grenzstadt-Atmosphäre',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#ca8a04',
    'city_image_index' => 0,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Pontviertel',
      2 => 'Burtscheid',
      3 => 'Lousberg',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Dom und Katschhof',
        'detail' => 'Starte im historischen Zentrum und nimm dir Zeit für die Innenräume.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Cafés oder Thermalwasser',
        'detail' => 'Pontviertel für Alltag und Kaffee oder ein ruhiger Thermenbesuch.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Altstadt langsam',
        'detail' => 'Kurzer Spaziergang durch beleuchtete Gassen und ein unkompliziertes Abendessen.',
      ),
    ),
    'slug' => 'spot-0-aachener-dom',
    'name' => 'Aachener Dom',
    'type' => 'Wahrzeichen',
    'area' => 'Altstadt',
    'note' => 'UNESCO-Welterbe, Mosaike und Kaiserpfalzgefühl in sehr kompakter Form.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1600,
  ),
  71 => 
  array (
    'city_slug' => 'aachen',
    'city_name' => 'Aachen',
    'city_display_name' => 'Aachen',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Dom und Thermalquellen',
    'city_headline' => 'Kaiserliche Geschichte, heiße Quellen, Altstadtgassen und kurze Wege.',
    'city_summary' => 'Aachen ist kompakt und geschichtsstark: Dom, Rathaus, Thermalwasser und studentische Cafés ergeben eine ruhige, gut portionierbare Städtereise.',
    'city_best_for' => 'Dom, Geschichte, Cafés, entspannte Grenzstadt-Atmosphäre',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#ca8a04',
    'city_image_index' => 0,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Pontviertel',
      2 => 'Burtscheid',
      3 => 'Lousberg',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Dom und Katschhof',
        'detail' => 'Starte im historischen Zentrum und nimm dir Zeit für die Innenräume.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Cafés oder Thermalwasser',
        'detail' => 'Pontviertel für Alltag und Kaffee oder ein ruhiger Thermenbesuch.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Altstadt langsam',
        'detail' => 'Kurzer Spaziergang durch beleuchtete Gassen und ein unkompliziertes Abendessen.',
      ),
    ),
    'slug' => 'spot-1-rathaus-und-katschhof',
    'name' => 'Rathaus und Katschhof',
    'type' => 'Altstadt',
    'area' => 'Zentrum',
    'note' => 'Der Platz zwischen Dom und Rathaus ist der klarste Einstieg in die Stadt.',
    'time' => '45 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1601,
  ),
  72 => 
  array (
    'city_slug' => 'aachen',
    'city_name' => 'Aachen',
    'city_display_name' => 'Aachen',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Dom und Thermalquellen',
    'city_headline' => 'Kaiserliche Geschichte, heiße Quellen, Altstadtgassen und kurze Wege.',
    'city_summary' => 'Aachen ist kompakt und geschichtsstark: Dom, Rathaus, Thermalwasser und studentische Cafés ergeben eine ruhige, gut portionierbare Städtereise.',
    'city_best_for' => 'Dom, Geschichte, Cafés, entspannte Grenzstadt-Atmosphäre',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#ca8a04',
    'city_image_index' => 0,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Pontviertel',
      2 => 'Burtscheid',
      3 => 'Lousberg',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Dom und Katschhof',
        'detail' => 'Starte im historischen Zentrum und nimm dir Zeit für die Innenräume.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Cafés oder Thermalwasser',
        'detail' => 'Pontviertel für Alltag und Kaffee oder ein ruhiger Thermenbesuch.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Altstadt langsam',
        'detail' => 'Kurzer Spaziergang durch beleuchtete Gassen und ein unkompliziertes Abendessen.',
      ),
    ),
    'slug' => 'spot-2-carolus-thermen',
    'name' => 'Carolus Thermen',
    'type' => 'Thermal',
    'area' => 'Monheimsallee',
    'note' => 'Ein entspannter Kontrast zur Altstadt, besonders bei kühlerem Wetter.',
    'time' => '2 Std.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1602,
  ),
  73 => 
  array (
    'city_slug' => 'aachen',
    'city_name' => 'Aachen',
    'city_display_name' => 'Aachen',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Dom und Thermalquellen',
    'city_headline' => 'Kaiserliche Geschichte, heiße Quellen, Altstadtgassen und kurze Wege.',
    'city_summary' => 'Aachen ist kompakt und geschichtsstark: Dom, Rathaus, Thermalwasser und studentische Cafés ergeben eine ruhige, gut portionierbare Städtereise.',
    'city_best_for' => 'Dom, Geschichte, Cafés, entspannte Grenzstadt-Atmosphäre',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#ca8a04',
    'city_image_index' => 0,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Pontviertel',
      2 => 'Burtscheid',
      3 => 'Lousberg',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Dom und Katschhof',
        'detail' => 'Starte im historischen Zentrum und nimm dir Zeit für die Innenräume.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Cafés oder Thermalwasser',
        'detail' => 'Pontviertel für Alltag und Kaffee oder ein ruhiger Thermenbesuch.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Altstadt langsam',
        'detail' => 'Kurzer Spaziergang durch beleuchtete Gassen und ein unkompliziertes Abendessen.',
      ),
    ),
    'slug' => 'spot-3-lousberg',
    'name' => 'Lousberg',
    'type' => 'Aussicht',
    'area' => 'Nordstadt',
    'note' => 'Kurzer Aufstieg mit Blick über Aachen und die hügelige Umgebung.',
    'time' => '60 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1603,
  ),
  74 => 
  array (
    'city_slug' => 'trier',
    'city_name' => 'Trier',
    'city_display_name' => 'Trier',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Römerstadt an der Mosel',
    'city_headline' => 'Porta Nigra, römische Spuren, Moselwege und Wein in der Altstadt.',
    'city_summary' => 'Trier ist klein genug für ein Wochenende und alt genug für viel Tiefe: Römische Bauten, Moselblick und Weinlokale liegen angenehm nah beieinander.',
    'city_best_for' => 'Römische Geschichte, Mosel, Wein, kurze Kulturtrips',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#7c2d12',
    'city_image_index' => 1,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Moselufer',
      2 => 'Südallee',
      3 => 'Petrisberg',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Römischer Einstieg',
        'detail' => 'Porta Nigra, Hauptmarkt und die ersten historischen Spuren zu Fuß verbinden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Thermen und Mosel',
        'detail' => 'Kultur vertiefen und danach ans Wasser wechseln.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Wein und Altstadt',
        'detail' => 'Ein langsamer Abend mit regionalem Wein und kurzen Wegen.',
      ),
    ),
    'slug' => 'spot-0-porta-nigra',
    'name' => 'Porta Nigra',
    'type' => 'Wahrzeichen',
    'area' => 'Altstadt',
    'note' => 'Der stärkste Auftakt in die römische Geschichte der Stadt.',
    'time' => '60 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1700,
  ),
  75 => 
  array (
    'city_slug' => 'trier',
    'city_name' => 'Trier',
    'city_display_name' => 'Trier',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Römerstadt an der Mosel',
    'city_headline' => 'Porta Nigra, römische Spuren, Moselwege und Wein in der Altstadt.',
    'city_summary' => 'Trier ist klein genug für ein Wochenende und alt genug für viel Tiefe: Römische Bauten, Moselblick und Weinlokale liegen angenehm nah beieinander.',
    'city_best_for' => 'Römische Geschichte, Mosel, Wein, kurze Kulturtrips',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#7c2d12',
    'city_image_index' => 1,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Moselufer',
      2 => 'Südallee',
      3 => 'Petrisberg',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Römischer Einstieg',
        'detail' => 'Porta Nigra, Hauptmarkt und die ersten historischen Spuren zu Fuß verbinden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Thermen und Mosel',
        'detail' => 'Kultur vertiefen und danach ans Wasser wechseln.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Wein und Altstadt',
        'detail' => 'Ein langsamer Abend mit regionalem Wein und kurzen Wegen.',
      ),
    ),
    'slug' => 'spot-1-kaiserthermen',
    'name' => 'Kaiserthermen',
    'type' => 'Geschichte',
    'area' => 'Zentrum',
    'note' => 'Römische Ruinen, die gut zeigen, wie groß Trier einmal gedacht war.',
    'time' => '75 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1701,
  ),
  76 => 
  array (
    'city_slug' => 'trier',
    'city_name' => 'Trier',
    'city_display_name' => 'Trier',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Römerstadt an der Mosel',
    'city_headline' => 'Porta Nigra, römische Spuren, Moselwege und Wein in der Altstadt.',
    'city_summary' => 'Trier ist klein genug für ein Wochenende und alt genug für viel Tiefe: Römische Bauten, Moselblick und Weinlokale liegen angenehm nah beieinander.',
    'city_best_for' => 'Römische Geschichte, Mosel, Wein, kurze Kulturtrips',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#7c2d12',
    'city_image_index' => 1,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Moselufer',
      2 => 'Südallee',
      3 => 'Petrisberg',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Römischer Einstieg',
        'detail' => 'Porta Nigra, Hauptmarkt und die ersten historischen Spuren zu Fuß verbinden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Thermen und Mosel',
        'detail' => 'Kultur vertiefen und danach ans Wasser wechseln.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Wein und Altstadt',
        'detail' => 'Ein langsamer Abend mit regionalem Wein und kurzen Wegen.',
      ),
    ),
    'slug' => 'spot-2-hauptmarkt',
    'name' => 'Hauptmarkt',
    'type' => 'Platz',
    'area' => 'Altstadt',
    'note' => 'Bunte Fassaden, kurze Wege und ein guter Ort für eine Pause.',
    'time' => '45 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1702,
  ),
  77 => 
  array (
    'city_slug' => 'trier',
    'city_name' => 'Trier',
    'city_display_name' => 'Trier',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Römerstadt an der Mosel',
    'city_headline' => 'Porta Nigra, römische Spuren, Moselwege und Wein in der Altstadt.',
    'city_summary' => 'Trier ist klein genug für ein Wochenende und alt genug für viel Tiefe: Römische Bauten, Moselblick und Weinlokale liegen angenehm nah beieinander.',
    'city_best_for' => 'Römische Geschichte, Mosel, Wein, kurze Kulturtrips',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#7c2d12',
    'city_image_index' => 1,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Moselufer',
      2 => 'Südallee',
      3 => 'Petrisberg',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Römischer Einstieg',
        'detail' => 'Porta Nigra, Hauptmarkt und die ersten historischen Spuren zu Fuß verbinden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Thermen und Mosel',
        'detail' => 'Kultur vertiefen und danach ans Wasser wechseln.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Wein und Altstadt',
        'detail' => 'Ein langsamer Abend mit regionalem Wein und kurzen Wegen.',
      ),
    ),
    'slug' => 'spot-3-moselufer',
    'name' => 'Moselufer',
    'type' => 'Wasser',
    'area' => 'Mosel',
    'note' => 'Ein ruhiger Spaziergang mit Weinregion-Gefühl direkt neben der Stadt.',
    'time' => 'Sonnenuntergang',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1703,
  ),
  78 => 
  array (
    'city_slug' => 'luebeck',
    'city_name' => 'Lübeck',
    'city_display_name' => 'Lübeck',
    'city_aliases' => 
    array (
      0 => 'Luebeck',
      1 => 'Lubeck',
    ),
    'city_region' => 'Backstein und Wasser',
    'city_headline' => 'Holstentor, Inselaltstadt, Gänge, Marzipan und ein schneller Weg zur Ostsee.',
    'city_summary' => 'Lübeck fühlt sich wie eine konzentrierte Hansestadt an: Backstein, Wasserarme, versteckte Höfe und die Nähe nach Travemünde machen den Trip sehr rund.',
    'city_best_for' => 'Backsteinarchitektur, Wasser, Altstadtgassen, Ostsee-Ausflug',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#be123c',
    'city_image_index' => 2,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadtinsel',
      1 => 'Museumshafen',
      2 => 'St. Lorenz',
      3 => 'Travemünde',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Holstentor und Altstadtinsel',
        'detail' => 'Am Wasser starten und langsam durch die Backsteinstadt gehen.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Höfe oder Hanse',
        'detail' => 'Versteckte Gänge suchen oder die Geschichte im Museum vertiefen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Wasser und Marzipan',
        'detail' => 'Ein ruhiger Abschluss am Museumshafen oder in der Altstadt.',
      ),
    ),
    'slug' => 'spot-0-holstentor',
    'name' => 'Holstentor',
    'type' => 'Wahrzeichen',
    'area' => 'Altstadt',
    'note' => 'Das klare Eingangsmotiv der Stadt mit Wasser und Backstein im Blick.',
    'time' => '45 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1800,
  ),
  79 => 
  array (
    'city_slug' => 'luebeck',
    'city_name' => 'Lübeck',
    'city_display_name' => 'Lübeck',
    'city_aliases' => 
    array (
      0 => 'Luebeck',
      1 => 'Lubeck',
    ),
    'city_region' => 'Backstein und Wasser',
    'city_headline' => 'Holstentor, Inselaltstadt, Gänge, Marzipan und ein schneller Weg zur Ostsee.',
    'city_summary' => 'Lübeck fühlt sich wie eine konzentrierte Hansestadt an: Backstein, Wasserarme, versteckte Höfe und die Nähe nach Travemünde machen den Trip sehr rund.',
    'city_best_for' => 'Backsteinarchitektur, Wasser, Altstadtgassen, Ostsee-Ausflug',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#be123c',
    'city_image_index' => 2,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadtinsel',
      1 => 'Museumshafen',
      2 => 'St. Lorenz',
      3 => 'Travemünde',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Holstentor und Altstadtinsel',
        'detail' => 'Am Wasser starten und langsam durch die Backsteinstadt gehen.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Höfe oder Hanse',
        'detail' => 'Versteckte Gänge suchen oder die Geschichte im Museum vertiefen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Wasser und Marzipan',
        'detail' => 'Ein ruhiger Abschluss am Museumshafen oder in der Altstadt.',
      ),
    ),
    'slug' => 'spot-1-gaenge-und-hoefe',
    'name' => 'Gänge und Höfe',
    'type' => 'Gassen',
    'area' => 'Altstadtinsel',
    'note' => 'Kleine Durchgänge und ruhige Innenhöfe zeigen Lübeck von innen.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1801,
  ),
  80 => 
  array (
    'city_slug' => 'luebeck',
    'city_name' => 'Lübeck',
    'city_display_name' => 'Lübeck',
    'city_aliases' => 
    array (
      0 => 'Luebeck',
      1 => 'Lubeck',
    ),
    'city_region' => 'Backstein und Wasser',
    'city_headline' => 'Holstentor, Inselaltstadt, Gänge, Marzipan und ein schneller Weg zur Ostsee.',
    'city_summary' => 'Lübeck fühlt sich wie eine konzentrierte Hansestadt an: Backstein, Wasserarme, versteckte Höfe und die Nähe nach Travemünde machen den Trip sehr rund.',
    'city_best_for' => 'Backsteinarchitektur, Wasser, Altstadtgassen, Ostsee-Ausflug',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#be123c',
    'city_image_index' => 2,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadtinsel',
      1 => 'Museumshafen',
      2 => 'St. Lorenz',
      3 => 'Travemünde',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Holstentor und Altstadtinsel',
        'detail' => 'Am Wasser starten und langsam durch die Backsteinstadt gehen.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Höfe oder Hanse',
        'detail' => 'Versteckte Gänge suchen oder die Geschichte im Museum vertiefen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Wasser und Marzipan',
        'detail' => 'Ein ruhiger Abschluss am Museumshafen oder in der Altstadt.',
      ),
    ),
    'slug' => 'spot-2-europaeisches-hansemuseum',
    'name' => 'Europäisches Hansemuseum',
    'type' => 'Kultur',
    'area' => 'Burgtor',
    'note' => 'Ein moderner Einstieg in die Geschichte der Hanse.',
    'time' => '2 Std.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1802,
  ),
  81 => 
  array (
    'city_slug' => 'luebeck',
    'city_name' => 'Lübeck',
    'city_display_name' => 'Lübeck',
    'city_aliases' => 
    array (
      0 => 'Luebeck',
      1 => 'Lubeck',
    ),
    'city_region' => 'Backstein und Wasser',
    'city_headline' => 'Holstentor, Inselaltstadt, Gänge, Marzipan und ein schneller Weg zur Ostsee.',
    'city_summary' => 'Lübeck fühlt sich wie eine konzentrierte Hansestadt an: Backstein, Wasserarme, versteckte Höfe und die Nähe nach Travemünde machen den Trip sehr rund.',
    'city_best_for' => 'Backsteinarchitektur, Wasser, Altstadtgassen, Ostsee-Ausflug',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#be123c',
    'city_image_index' => 2,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadtinsel',
      1 => 'Museumshafen',
      2 => 'St. Lorenz',
      3 => 'Travemünde',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Holstentor und Altstadtinsel',
        'detail' => 'Am Wasser starten und langsam durch die Backsteinstadt gehen.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Höfe oder Hanse',
        'detail' => 'Versteckte Gänge suchen oder die Geschichte im Museum vertiefen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Wasser und Marzipan',
        'detail' => 'Ein ruhiger Abschluss am Museumshafen oder in der Altstadt.',
      ),
    ),
    'slug' => 'spot-3-travemuende',
    'name' => 'Travemünde',
    'type' => 'Küste',
    'area' => 'Ostsee',
    'note' => 'Strand, Promenade und Hafenluft als leichter zweiter Teil des Trips.',
    'time' => 'Halber Tag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1803,
  ),
  82 => 
  array (
    'city_slug' => 'freiburg',
    'city_name' => 'Freiburg',
    'city_display_name' => 'Freiburg',
    'city_aliases' => 
    array (
      0 => 'Freiburg im Breisgau',
    ),
    'city_region' => 'Altstadt und Schwarzwald',
    'city_headline' => 'Bächle, Münster, sonnige Plätze und schnelle Wege in die Hügel.',
    'city_summary' => 'Freiburg ist leicht, sonnig und sehr gut zu Fuß: Altstadt, Markt, kleine Wasserläufe und Schwarzwaldnähe ergeben einen entspannten Kurztrip.',
    'city_best_for' => 'Altstadt, Markt, Schwarzwald, sonnige Wochenenden',
    'city_duration' => '2 Tage',
    'city_accent' => '#16a34a',
    'city_image_index' => 3,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Wiehre',
      2 => 'Schlossberg',
      3 => 'Vauban',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Münster und Markt',
        'detail' => 'Altstadt, Marktstände und Bächle ohne Eile verbinden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Schlossberg oder Wiehre',
        'detail' => 'Aussicht suchen oder ein ruhiges Viertel mit Cafés wählen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Sonnenstadt ausklingen lassen',
        'detail' => 'Essen in der Altstadt und ein letzter Weg über die Plätze.',
      ),
    ),
    'slug' => 'spot-0-freiburger-muenster',
    'name' => 'Freiburger Münster',
    'type' => 'Wahrzeichen',
    'area' => 'Altstadt',
    'note' => 'Der zentrale Orientierungspunkt mit Markt und viel Leben rundherum.',
    'time' => '90 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1900,
  ),
  83 => 
  array (
    'city_slug' => 'freiburg',
    'city_name' => 'Freiburg',
    'city_display_name' => 'Freiburg',
    'city_aliases' => 
    array (
      0 => 'Freiburg im Breisgau',
    ),
    'city_region' => 'Altstadt und Schwarzwald',
    'city_headline' => 'Bächle, Münster, sonnige Plätze und schnelle Wege in die Hügel.',
    'city_summary' => 'Freiburg ist leicht, sonnig und sehr gut zu Fuß: Altstadt, Markt, kleine Wasserläufe und Schwarzwaldnähe ergeben einen entspannten Kurztrip.',
    'city_best_for' => 'Altstadt, Markt, Schwarzwald, sonnige Wochenenden',
    'city_duration' => '2 Tage',
    'city_accent' => '#16a34a',
    'city_image_index' => 3,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Wiehre',
      2 => 'Schlossberg',
      3 => 'Vauban',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Münster und Markt',
        'detail' => 'Altstadt, Marktstände und Bächle ohne Eile verbinden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Schlossberg oder Wiehre',
        'detail' => 'Aussicht suchen oder ein ruhiges Viertel mit Cafés wählen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Sonnenstadt ausklingen lassen',
        'detail' => 'Essen in der Altstadt und ein letzter Weg über die Plätze.',
      ),
    ),
    'slug' => 'spot-1-schlossberg',
    'name' => 'Schlossberg',
    'type' => 'Aussicht',
    'area' => 'Osten',
    'note' => 'Kurzer Aufstieg für Blick über Dächer, Münster und Schwarzwaldkante.',
    'time' => '75 Min.',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1901,
  ),
  84 => 
  array (
    'city_slug' => 'freiburg',
    'city_name' => 'Freiburg',
    'city_display_name' => 'Freiburg',
    'city_aliases' => 
    array (
      0 => 'Freiburg im Breisgau',
    ),
    'city_region' => 'Altstadt und Schwarzwald',
    'city_headline' => 'Bächle, Münster, sonnige Plätze und schnelle Wege in die Hügel.',
    'city_summary' => 'Freiburg ist leicht, sonnig und sehr gut zu Fuß: Altstadt, Markt, kleine Wasserläufe und Schwarzwaldnähe ergeben einen entspannten Kurztrip.',
    'city_best_for' => 'Altstadt, Markt, Schwarzwald, sonnige Wochenenden',
    'city_duration' => '2 Tage',
    'city_accent' => '#16a34a',
    'city_image_index' => 3,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Wiehre',
      2 => 'Schlossberg',
      3 => 'Vauban',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Münster und Markt',
        'detail' => 'Altstadt, Marktstände und Bächle ohne Eile verbinden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Schlossberg oder Wiehre',
        'detail' => 'Aussicht suchen oder ein ruhiges Viertel mit Cafés wählen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Sonnenstadt ausklingen lassen',
        'detail' => 'Essen in der Altstadt und ein letzter Weg über die Plätze.',
      ),
    ),
    'slug' => 'spot-2-wiehre',
    'name' => 'Wiehre',
    'type' => 'Viertel',
    'area' => 'Südosten',
    'note' => 'Ruhige Straßen, Cafés und ein weicherer lokaler Ton.',
    'time' => 'Nachmittag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1902,
  ),
  85 => 
  array (
    'city_slug' => 'freiburg',
    'city_name' => 'Freiburg',
    'city_display_name' => 'Freiburg',
    'city_aliases' => 
    array (
      0 => 'Freiburg im Breisgau',
    ),
    'city_region' => 'Altstadt und Schwarzwald',
    'city_headline' => 'Bächle, Münster, sonnige Plätze und schnelle Wege in die Hügel.',
    'city_summary' => 'Freiburg ist leicht, sonnig und sehr gut zu Fuß: Altstadt, Markt, kleine Wasserläufe und Schwarzwaldnähe ergeben einen entspannten Kurztrip.',
    'city_best_for' => 'Altstadt, Markt, Schwarzwald, sonnige Wochenenden',
    'city_duration' => '2 Tage',
    'city_accent' => '#16a34a',
    'city_image_index' => 3,
    'city_neighborhoods' => 
    array (
      0 => 'Altstadt',
      1 => 'Wiehre',
      2 => 'Schlossberg',
      3 => 'Vauban',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Münster und Markt',
        'detail' => 'Altstadt, Marktstände und Bächle ohne Eile verbinden.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Schlossberg oder Wiehre',
        'detail' => 'Aussicht suchen oder ein ruhiges Viertel mit Cafés wählen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Sonnenstadt ausklingen lassen',
        'detail' => 'Essen in der Altstadt und ein letzter Weg über die Plätze.',
      ),
    ),
    'slug' => 'spot-3-schauinsland',
    'name' => 'Schauinsland',
    'type' => 'Draußen',
    'area' => 'Schwarzwald',
    'note' => 'Ein schneller Naturwechsel, wenn der Trip mehr Luft bekommen soll.',
    'time' => 'Halber Tag',
    'duration' => NULL,
    'best_time' => NULL,
    'address' => NULL,
    'intro' => NULL,
    'why' => NULL,
    'tip' => NULL,
    'facts' => NULL,
    'photos' => NULL,
    'feedbacks' => NULL,
    'is_city_spot' => true,
    'is_cool_place' => false,
    'cool_place_order' => NULL,
    'sort_order' => 1903,
  ),
  86 => 
  array (
    'city_slug' => 'duisburg',
    'city_name' => 'Duisburg',
    'city_display_name' => 'Duisburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Industriekultur am Rhein',
    'city_headline' => 'Hafen, Stahlarchitektur, Rheinwege und raues Ruhrgebietsgefühl.',
    'city_summary' => 'Duisburg ist besonders, wenn du Industriekultur nicht als Kulisse, sondern als Stadterlebnis liest: Hafen, Parks, Wasser und alte Stahlstrukturen ergeben einen starken Kontrast.',
    'city_best_for' => 'Industriekultur, Hafen, Rhein, urbane Fotospots',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#475569',
    'city_image_index' => 12,
    'city_neighborhoods' => 
    array (
      0 => 'Innenhafen',
      1 => 'Meiderich',
      2 => 'Ruhrort',
      3 => 'Rheinpark',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenhafen und Ruhrort',
        'detail' => 'Starte am Wasser und nähere dich der Hafenstadt langsam.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Landschaftspark',
        'detail' => 'Industriekultur zu Fuß erleben, mit viel Raum für Fotos und Pausen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Licht und Aussicht',
        'detail' => 'Tiger & Turtle oder der beleuchtete Landschaftspark als starker Abschluss.',
      ),
    ),
    'slug' => 'landschaftspark-duisburg-nord',
    'name' => 'Landschaftspark Duisburg-Nord',
    'type' => 'Industriekultur',
    'area' => 'Meiderich',
    'note' => NULL,
    'time' => NULL,
    'duration' => '2-3 Std.',
    'best_time' => 'Später Nachmittag bis Abend',
    'address' => 'Emscherstraße 71, 47137 Duisburg',
    'intro' => 'Ein stillgelegtes Hüttenwerk, das heute als Park, Aussichtsort, Kletter- und Lichtkulisse funktioniert.',
    'why' => 'Der Ort zeigt Duisburgs Wandel sehr direkt: alte Hochöfen, grüne Wege, Wasserflächen und abends farbiges Licht.',
    'tip' => 'Plane genug Zeit ein und bleib bis zur Dämmerung, wenn die Lichtinstallation den Park stark verändert.',
    'facts' => 
    array (
      0 => 
      array (
        'label' => 'Kategorie',
        'value' => 'Industriekultur',
      ),
      1 => 
      array (
        'label' => 'Ideal für',
        'value' => 'Fotos, Spaziergang, Abendlicht',
      ),
      2 => 
      array (
        'label' => 'Zeit',
        'value' => '2-3 Std.',
      ),
      3 => 
      array (
        'label' => 'Mood',
        'value' => 'Rau, weit, filmisch',
      ),
    ),
    'photos' => 
    array (
      0 => 
      array (
        'visual' => 'industrial',
        'label' => 'Hochofen',
        'caption' => 'Stahlstrukturen und weite Wege durch das alte Werk.',
      ),
      1 => 
      array (
        'visual' => 'industrial-light',
        'label' => 'Licht',
        'caption' => 'Abends wirkt der Park wie eine offene Bühne.',
      ),
      2 => 
      array (
        'visual' => 'industrial-water',
        'label' => 'Wasserpark',
        'caption' => 'Natur und Industrie liegen hier direkt nebeneinander.',
      ),
      3 => 
      array (
        'visual' => 'industrial-view',
        'label' => 'Aussicht',
        'caption' => 'Von oben liest du das Ruhrgebiet als Landschaft.',
      ),
    ),
    'feedbacks' => 
    array (
      0 => 
      array (
        'name' => 'Mara',
        'quote' => 'Bei Lichtwechsel wird der Park richtig stark. Man bleibt automatisch länger.',
      ),
      1 => 
      array (
        'name' => 'Toni',
        'quote' => 'Mein Lieblingsort in Duisburg für Fotos und einen ruhigen Abendspaziergang.',
      ),
    ),
    'is_city_spot' => false,
    'is_cool_place' => true,
    'cool_place_order' => 0,
    'sort_order' => 12000,
  ),
  87 => 
  array (
    'city_slug' => 'duisburg',
    'city_name' => 'Duisburg',
    'city_display_name' => 'Duisburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Industriekultur am Rhein',
    'city_headline' => 'Hafen, Stahlarchitektur, Rheinwege und raues Ruhrgebietsgefühl.',
    'city_summary' => 'Duisburg ist besonders, wenn du Industriekultur nicht als Kulisse, sondern als Stadterlebnis liest: Hafen, Parks, Wasser und alte Stahlstrukturen ergeben einen starken Kontrast.',
    'city_best_for' => 'Industriekultur, Hafen, Rhein, urbane Fotospots',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#475569',
    'city_image_index' => 12,
    'city_neighborhoods' => 
    array (
      0 => 'Innenhafen',
      1 => 'Meiderich',
      2 => 'Ruhrort',
      3 => 'Rheinpark',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenhafen und Ruhrort',
        'detail' => 'Starte am Wasser und nähere dich der Hafenstadt langsam.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Landschaftspark',
        'detail' => 'Industriekultur zu Fuß erleben, mit viel Raum für Fotos und Pausen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Licht und Aussicht',
        'detail' => 'Tiger & Turtle oder der beleuchtete Landschaftspark als starker Abschluss.',
      ),
    ),
    'slug' => 'tiger-and-turtle',
    'name' => 'Tiger & Turtle',
    'type' => 'Landmarke',
    'area' => 'Angerhausen',
    'note' => NULL,
    'time' => NULL,
    'duration' => '45-60 Min.',
    'best_time' => 'Dämmerung',
    'address' => 'Ehinger Straße, 47249 Duisburg',
    'intro' => 'Eine begehbare Achterbahn-Skulptur auf einer Halde mit weitem Blick über Duisburg, Rhein und Ruhrgebiet.',
    'why' => 'Der Ort ist kurz, ikonisch und sehr fotografisch. Besonders abends zeichnen Lichter die Stahlkurven in den Himmel.',
    'tip' => 'Festes Schuhwerk mitnehmen und bei Wind oder schlechtem Wetter vorher prüfen, ob der Zugang möglich ist.',
    'facts' => 
    array (
      0 => 
      array (
        'label' => 'Kategorie',
        'value' => 'Aussicht & Kunst',
      ),
      1 => 
      array (
        'label' => 'Ideal für',
        'value' => 'Sonnenuntergang, Fotos',
      ),
      2 => 
      array (
        'label' => 'Zeit',
        'value' => '45-60 Min.',
      ),
      3 => 
      array (
        'label' => 'Mood',
        'value' => 'Skulptural, luftig',
      ),
    ),
    'photos' => 
    array (
      0 => 
      array (
        'visual' => 'tiger',
        'label' => 'Skulptur',
        'caption' => 'Die geschwungene Stahlform ist das Motiv.',
      ),
      1 => 
      array (
        'visual' => 'tiger-dusk',
        'label' => 'Dämmerung',
        'caption' => 'Kurz vor dunkel wirkt der Ort am stärksten.',
      ),
      2 => 
      array (
        'visual' => 'tiger-view',
        'label' => 'Panorama',
        'caption' => 'Der Blick reicht über Industrie, Rhein und Stadt.',
      ),
      3 => 
      array (
        'visual' => 'tiger-lines',
        'label' => 'Linien',
        'caption' => 'Treppen, Kurven und Stahl ergeben klare Fotos.',
      ),
    ),
    'feedbacks' => 
    array (
      0 => 
      array (
        'name' => 'Jonas',
        'quote' => 'Kurz, aber bleibt hängen. Bei Abendlicht sieht alles viel größer aus.',
      ),
      1 => 
      array (
        'name' => 'Nora',
        'quote' => 'Perfekter Spot, wenn man Duisburg von oben sehen will.',
      ),
    ),
    'is_city_spot' => false,
    'is_cool_place' => true,
    'cool_place_order' => 1,
    'sort_order' => 12001,
  ),
  88 => 
  array (
    'city_slug' => 'duisburg',
    'city_name' => 'Duisburg',
    'city_display_name' => 'Duisburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Industriekultur am Rhein',
    'city_headline' => 'Hafen, Stahlarchitektur, Rheinwege und raues Ruhrgebietsgefühl.',
    'city_summary' => 'Duisburg ist besonders, wenn du Industriekultur nicht als Kulisse, sondern als Stadterlebnis liest: Hafen, Parks, Wasser und alte Stahlstrukturen ergeben einen starken Kontrast.',
    'city_best_for' => 'Industriekultur, Hafen, Rhein, urbane Fotospots',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#475569',
    'city_image_index' => 12,
    'city_neighborhoods' => 
    array (
      0 => 'Innenhafen',
      1 => 'Meiderich',
      2 => 'Ruhrort',
      3 => 'Rheinpark',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenhafen und Ruhrort',
        'detail' => 'Starte am Wasser und nähere dich der Hafenstadt langsam.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Landschaftspark',
        'detail' => 'Industriekultur zu Fuß erleben, mit viel Raum für Fotos und Pausen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Licht und Aussicht',
        'detail' => 'Tiger & Turtle oder der beleuchtete Landschaftspark als starker Abschluss.',
      ),
    ),
    'slug' => 'duisburger-innenhafen',
    'name' => 'Duisburger Innenhafen',
    'type' => 'Wasser & Architektur',
    'area' => 'Zentrum',
    'note' => NULL,
    'time' => NULL,
    'duration' => '90 Min.',
    'best_time' => 'Nachmittag bis Abend',
    'address' => 'Innenhafen, 47051 Duisburg',
    'intro' => 'Marina, Speicherarchitektur, Restaurants und Museen machen den Innenhafen zum einfachsten Einstieg in Duisburg.',
    'why' => 'Hier ist Duisburg weicher und urbaner: Wasser, Backstein, neue Architektur und kurze Wege passen gut zusammen.',
    'tip' => 'Mit dem Museum Küppersmühle kombinieren und danach am Wasser sitzen bleiben.',
    'facts' => 
    array (
      0 => 
      array (
        'label' => 'Kategorie',
        'value' => 'Hafen & Essen',
      ),
      1 => 
      array (
        'label' => 'Ideal für',
        'value' => 'Ankommen, Spaziergang',
      ),
      2 => 
      array (
        'label' => 'Zeit',
        'value' => '90 Min.',
      ),
      3 => 
      array (
        'label' => 'Mood',
        'value' => 'Urban, ruhig',
      ),
    ),
    'photos' => 
    array (
      0 => 
      array (
        'visual' => 'harbor',
        'label' => 'Marina',
        'caption' => 'Wasser und Architektur im Zentrum.',
      ),
      1 => 
      array (
        'visual' => 'harbor-brick',
        'label' => 'Speicher',
        'caption' => 'Alte Speicher geben dem Hafen Charakter.',
      ),
      2 => 
      array (
        'visual' => 'harbor-night',
        'label' => 'Abend',
        'caption' => 'Restaurants und Licht am Wasser.',
      ),
      3 => 
      array (
        'visual' => 'harbor-walk',
        'label' => 'Promenade',
        'caption' => 'Kurze Wege entlang der Hafenkante.',
      ),
    ),
    'feedbacks' => 
    array (
      0 => 
      array (
        'name' => 'Sofia',
        'quote' => 'Innenhafen fühlt sich entspannt an und ist ein guter Startpunkt.',
      ),
      1 => 
      array (
        'name' => 'Luca',
        'quote' => 'Erst Kunst, dann Wasserblick. Genau mein Duisburg-Einstieg.',
      ),
    ),
    'is_city_spot' => false,
    'is_cool_place' => true,
    'cool_place_order' => 2,
    'sort_order' => 12002,
  ),
  89 => 
  array (
    'city_slug' => 'duisburg',
    'city_name' => 'Duisburg',
    'city_display_name' => 'Duisburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Industriekultur am Rhein',
    'city_headline' => 'Hafen, Stahlarchitektur, Rheinwege und raues Ruhrgebietsgefühl.',
    'city_summary' => 'Duisburg ist besonders, wenn du Industriekultur nicht als Kulisse, sondern als Stadterlebnis liest: Hafen, Parks, Wasser und alte Stahlstrukturen ergeben einen starken Kontrast.',
    'city_best_for' => 'Industriekultur, Hafen, Rhein, urbane Fotospots',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#475569',
    'city_image_index' => 12,
    'city_neighborhoods' => 
    array (
      0 => 'Innenhafen',
      1 => 'Meiderich',
      2 => 'Ruhrort',
      3 => 'Rheinpark',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenhafen und Ruhrort',
        'detail' => 'Starte am Wasser und nähere dich der Hafenstadt langsam.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Landschaftspark',
        'detail' => 'Industriekultur zu Fuß erleben, mit viel Raum für Fotos und Pausen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Licht und Aussicht',
        'detail' => 'Tiger & Turtle oder der beleuchtete Landschaftspark als starker Abschluss.',
      ),
    ),
    'slug' => 'mkm-museum-kueppersmuehle',
    'name' => 'MKM Museum Küppersmühle',
    'type' => 'Kunst',
    'area' => 'Innenhafen',
    'note' => NULL,
    'time' => NULL,
    'duration' => '2 Std.',
    'best_time' => 'Schlechtwetter oder ruhiger Nachmittag',
    'address' => 'Philosophenweg 55, 47051 Duisburg',
    'intro' => 'Moderne Kunst in einer ehemaligen Mühle, stark geprägt von Industriebau, White Cube und klarer Architektur.',
    'why' => 'Das Museum macht den Wandel des Innenhafens sichtbar und ist auch architektonisch ein Highlight.',
    'tip' => 'Vorher Öffnungszeiten prüfen und den Besuch mit einem Spaziergang am Innenhafen verbinden.',
    'facts' => 
    array (
      0 => 
      array (
        'label' => 'Kategorie',
        'value' => 'Museum',
      ),
      1 => 
      array (
        'label' => 'Ideal für',
        'value' => 'Kunst, Architektur',
      ),
      2 => 
      array (
        'label' => 'Zeit',
        'value' => '2 Std.',
      ),
      3 => 
      array (
        'label' => 'Mood',
        'value' => 'Klar, kulturell',
      ),
    ),
    'photos' => 
    array (
      0 => 
      array (
        'visual' => 'museum',
        'label' => 'Fassade',
        'caption' => 'Industriebau trifft moderne Museumsarchitektur.',
      ),
      1 => 
      array (
        'visual' => 'museum-stairs',
        'label' => 'Treppen',
        'caption' => 'Klare Linien und starke Innenräume.',
      ),
      2 => 
      array (
        'visual' => 'museum-gallery',
        'label' => 'Galerie',
        'caption' => 'Ruhige Räume für moderne Kunst.',
      ),
      3 => 
      array (
        'visual' => 'museum-harbor',
        'label' => 'Hafen',
        'caption' => 'Das Museum liegt direkt am Innenhafen.',
      ),
    ),
    'feedbacks' => 
    array (
      0 => 
      array (
        'name' => 'Elena',
        'quote' => 'Das Gebäude allein lohnt sich schon, die Sammlung macht es rund.',
      ),
    ),
    'is_city_spot' => false,
    'is_cool_place' => true,
    'cool_place_order' => 3,
    'sort_order' => 12003,
  ),
  90 => 
  array (
    'city_slug' => 'duisburg',
    'city_name' => 'Duisburg',
    'city_display_name' => 'Duisburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Industriekultur am Rhein',
    'city_headline' => 'Hafen, Stahlarchitektur, Rheinwege und raues Ruhrgebietsgefühl.',
    'city_summary' => 'Duisburg ist besonders, wenn du Industriekultur nicht als Kulisse, sondern als Stadterlebnis liest: Hafen, Parks, Wasser und alte Stahlstrukturen ergeben einen starken Kontrast.',
    'city_best_for' => 'Industriekultur, Hafen, Rhein, urbane Fotospots',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#475569',
    'city_image_index' => 12,
    'city_neighborhoods' => 
    array (
      0 => 'Innenhafen',
      1 => 'Meiderich',
      2 => 'Ruhrort',
      3 => 'Rheinpark',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenhafen und Ruhrort',
        'detail' => 'Starte am Wasser und nähere dich der Hafenstadt langsam.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Landschaftspark',
        'detail' => 'Industriekultur zu Fuß erleben, mit viel Raum für Fotos und Pausen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Licht und Aussicht',
        'detail' => 'Tiger & Turtle oder der beleuchtete Landschaftspark als starker Abschluss.',
      ),
    ),
    'slug' => 'sechs-seen-platte',
    'name' => 'Sechs-Seen-Platte',
    'type' => 'Natur',
    'area' => 'Duisburg-Süd',
    'note' => NULL,
    'time' => NULL,
    'duration' => 'Halber Tag',
    'best_time' => 'Sommer, Vormittag',
    'address' => 'Duisburg-Wedau / Buchholz',
    'intro' => 'Seen, Spazierwege, Wasserflächen und Aussichtsturm bilden den entspannten Gegenpol zur Industriekultur.',
    'why' => 'Die Sechs-Seen-Platte zeigt, dass Duisburg nicht nur Stahl und Hafen ist, sondern auch viel Grün und Wasser hat.',
    'tip' => 'Für Picknick oder längere Pause einplanen, besonders wenn du einen langsameren Reisetag willst.',
    'facts' => 
    array (
      0 => 
      array (
        'label' => 'Kategorie',
        'value' => 'Natur & Wasser',
      ),
      1 => 
      array (
        'label' => 'Ideal für',
        'value' => 'Picknick, Spaziergang',
      ),
      2 => 
      array (
        'label' => 'Zeit',
        'value' => 'Halber Tag',
      ),
      3 => 
      array (
        'label' => 'Mood',
        'value' => 'Grün, leicht',
      ),
    ),
    'photos' => 
    array (
      0 => 
      array (
        'visual' => 'lake',
        'label' => 'Seeufer',
        'caption' => 'Viel Wasser und lange Wege.',
      ),
      1 => 
      array (
        'visual' => 'lake-summer',
        'label' => 'Sommer',
        'caption' => 'Ideal für einen warmen Reisetag.',
      ),
      2 => 
      array (
        'visual' => 'lake-path',
        'label' => 'Wege',
        'caption' => 'Spaziergänge zwischen den Seen.',
      ),
      3 => 
      array (
        'visual' => 'lake-view',
        'label' => 'Aussicht',
        'caption' => 'Vom Turm sieht Duisburg überraschend grün aus.',
      ),
    ),
    'feedbacks' => 
    array (
      0 => 
      array (
        'name' => 'Amir',
        'quote' => 'Schöner Wechsel nach Hafen und Industrie. Ruhiger als erwartet.',
      ),
    ),
    'is_city_spot' => false,
    'is_cool_place' => true,
    'cool_place_order' => 4,
    'sort_order' => 12004,
  ),
  91 => 
  array (
    'city_slug' => 'duisburg',
    'city_name' => 'Duisburg',
    'city_display_name' => 'Duisburg',
    'city_aliases' => 
    array (
    ),
    'city_region' => 'Industriekultur am Rhein',
    'city_headline' => 'Hafen, Stahlarchitektur, Rheinwege und raues Ruhrgebietsgefühl.',
    'city_summary' => 'Duisburg ist besonders, wenn du Industriekultur nicht als Kulisse, sondern als Stadterlebnis liest: Hafen, Parks, Wasser und alte Stahlstrukturen ergeben einen starken Kontrast.',
    'city_best_for' => 'Industriekultur, Hafen, Rhein, urbane Fotospots',
    'city_duration' => '1-2 Tage',
    'city_accent' => '#475569',
    'city_image_index' => 12,
    'city_neighborhoods' => 
    array (
      0 => 'Innenhafen',
      1 => 'Meiderich',
      2 => 'Ruhrort',
      3 => 'Rheinpark',
    ),
    'city_route' => 
    array (
      0 => 
      array (
        'step' => 'Morgen',
        'title' => 'Innenhafen und Ruhrort',
        'detail' => 'Starte am Wasser und nähere dich der Hafenstadt langsam.',
      ),
      1 => 
      array (
        'step' => 'Nachmittag',
        'title' => 'Landschaftspark',
        'detail' => 'Industriekultur zu Fuß erleben, mit viel Raum für Fotos und Pausen.',
      ),
      2 => 
      array (
        'step' => 'Abend',
        'title' => 'Licht und Aussicht',
        'detail' => 'Tiger & Turtle oder der beleuchtete Landschaftspark als starker Abschluss.',
      ),
    ),
    'slug' => 'lehmbruck-museum-kantpark',
    'name' => 'Lehmbruck Museum & Kantpark',
    'type' => 'Skulptur',
    'area' => 'Dellviertel',
    'note' => NULL,
    'time' => NULL,
    'duration' => '90 Min.',
    'best_time' => 'Mittag oder ruhiger Nachmittag',
    'address' => 'Friedrich-Wilhelm-Straße 40, 47051 Duisburg',
    'intro' => 'Ein Museum für moderne Skulptur mit klarer Architektur und Skulpturenpark direkt im Kantpark.',
    'why' => 'Der Ort verbindet Kunst, Ruhe und Stadtmitte. Gut, wenn der Trip nicht nur draußen stattfinden soll.',
    'tip' => 'Erst Museum, danach eine kleine Runde durch den frei zugänglichen Skulpturenpark.',
    'facts' => 
    array (
      0 => 
      array (
        'label' => 'Kategorie',
        'value' => 'Kunst & Park',
      ),
      1 => 
      array (
        'label' => 'Ideal für',
        'value' => 'Skulptur, Pause',
      ),
      2 => 
      array (
        'label' => 'Zeit',
        'value' => '90 Min.',
      ),
      3 => 
      array (
        'label' => 'Mood',
        'value' => 'Ruhig, konzentriert',
      ),
    ),
    'photos' => 
    array (
      0 => 
      array (
        'visual' => 'art',
        'label' => 'Museum',
        'caption' => 'Moderne Skulptur in klaren Räumen.',
      ),
      1 => 
      array (
        'visual' => 'art-park',
        'label' => 'Kantpark',
        'caption' => 'Kunst geht hier nach draußen weiter.',
      ),
      2 => 
      array (
        'visual' => 'art-glass',
        'label' => 'Architektur',
        'caption' => 'Glas, Beton und ruhige Linien.',
      ),
      3 => 
      array (
        'visual' => 'art-detail',
        'label' => 'Details',
        'caption' => 'Ein guter Ort zum genauen Hinschauen.',
      ),
    ),
    'feedbacks' => 
    array (
      0 => 
      array (
        'name' => 'Mila',
        'quote' => 'Sehr ruhig und genau richtig zwischen zwei größeren Outdoor-Spots.',
      ),
    ),
    'is_city_spot' => false,
    'is_cool_place' => true,
    'cool_place_order' => 5,
    'sort_order' => 12005,
  ),
);
    }
}