<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GuideController extends AbstractController
{
    public function index(): Response
    {
        $citiesJson = json_encode(
            array_values($this->getCities()),
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );

        return $this->render('guide/index.html.twig', array(
            'citiesJson' => $citiesJson,
        ));
    }

    public function apiCities(): JsonResponse
    {
        return new JsonResponse(array_values($this->getCities()));
    }

    public function apiCity(string $slug): JsonResponse
    {
        $city = $this->findCity($slug);

        if (!$city) {
            return new JsonResponse(
                array('error' => 'Stadt nicht gefunden'),
                Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse($city);
    }

    private function findCity(string $slug): ?array
    {
        $normalizedSlug = $this->normalize($slug);

        foreach ($this->getCities() as $city) {
            if ($city['slug'] === $normalizedSlug) {
                return $city;
            }

            $aliases = array_merge(array($city['name']), $city['aliases']);

            foreach ($aliases as $alias) {
                if ($this->normalize($alias) === $normalizedSlug) {
                    return $city;
                }
            }
        }

        return null;
    }

    private function normalize(string $value): string
    {
        $map = array(
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'ß' => 'ss',
            'Ä' => 'ae',
            'Ö' => 'oe',
            'Ü' => 'ue',
            'à' => 'a',
            'è' => 'e',
            'é' => 'e',
            'ì' => 'i',
            'ò' => 'o',
            'ù' => 'u',
        );
        $value = trim($value);
        $value = strtr($value, $map);
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);

        return trim($value, '-');
    }

    private function getCities(): array
    {
        return array(
            array(
                'slug' => 'berlin',
                'name' => 'Berlin',
                'displayName' => 'Berlin',
                'aliases' => array(),
                'region' => 'Kreative Hauptstadt',
                'headline' => 'Straßenkunst, mutige Museen und lange Nächte an der Spree.',
                'summary' => 'Berlin funktioniert am besten, wenn du kulturelle Ikonen, industrielle Höfe, Märkte und Viertel mit starkem lokalem Leben kombinierst.',
                'bestFor' => 'Urbane Kunst, Nachtleben, unabhängige Kultur',
                'duration' => '2-4 Tage',
                'accent' => '#f9735b',
                'neighborhoods' => array('Mitte', 'Kreuzberg', 'Friedrichshain', 'Neukölln'),
                'spots' => array(
                    array('type' => 'Straßenkunst', 'name' => 'East Side Gallery', 'area' => 'Friedrichshain', 'note' => 'Ein ikonischer Abschnitt der Mauer als offene Galerie, perfekt für einen energiegeladenen Einstieg.', 'time' => '60-90 Min.'),
                    array('type' => 'Kultur', 'name' => 'Sammlung Boros', 'area' => 'Mitte', 'note' => 'Zeitgenössische Kunst in einem umgebauten Bunker, rau und sehr berlinisch.', 'time' => 'Reservierung'),
                    array('type' => 'Essen', 'name' => 'Markthalle Neun', 'area' => 'Kreuzberg', 'note' => 'Markthalle mit gemeinsamen Tischen, Straßenküche und lokalen Produzenten.', 'time' => 'Mittagessen'),
                    array('type' => 'Ausgehen', 'name' => 'Klunkerkranich', 'area' => 'Neukölln', 'note' => 'Ungezwungene Dachterrasse über der Stadt, besonders schön bei Sonnenuntergang.', 'time' => 'Abend'),
                    array('type' => 'Einzigartig', 'name' => 'Tempelhofer Feld', 'area' => 'Tempelhof', 'note' => 'Der ehemalige Flughafen ist heute ein Stadtpark: Fahrräder, Picknick und viel Horizont mitten in Berlin.', 'time' => '2 Std.'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Mitte und Gegenwartskunst', 'detail' => 'Kaffee, Galerien und ein reservierter Besuch in der Sammlung Boros.'),
                    array('step' => 'Nachmittag', 'title' => 'Kreuzberg langsam erleben', 'detail' => 'Mittagessen in der Markthalle Neun und ein Spaziergang entlang von Kanälen, unabhängigen Läden und Straßenkunst.'),
                    array('step' => 'Abend', 'title' => 'Dachterrasse und Kiez', 'detail' => 'Sonnenuntergang im Klunkerkranich, danach ein unkompliziertes Abendessen rund um Neukölln.'),
                ),
            ),
            array(
                'slug' => 'hamburg',
                'name' => 'Hamburg',
                'displayName' => 'Hamburg',
                'aliases' => array(),
                'region' => 'Hafen und Design',
                'headline' => 'Kanäle, rote Speicherhäuser, Musik und Tische am Hafen.',
                'summary' => 'Hamburg verbindet Wasser, Architektur und kreative Viertel: Der beste Rhythmus führt durch Speicherstadt, Sternschanze und ans Ufer.',
                'bestFor' => 'Architektur, Konzerte, Spaziergänge am Wasser',
                'duration' => '2-3 Tage',
                'accent' => '#0f766e',
                'neighborhoods' => array('Speicherstadt', 'HafenCity', 'Sternschanze', 'St. Pauli'),
                'spots' => array(
                    array('type' => 'Wahrzeichen', 'name' => 'Speicherstadt', 'area' => 'HafenCity', 'note' => 'Historische Speicher und Kanäle: die perfekte visuelle Grundlage, um die Stadt zu verstehen.', 'time' => '90 Min.'),
                    array('type' => 'Aussicht', 'name' => 'Elbphilharmonie Plaza', 'area' => 'HafenCity', 'note' => 'Eine öffentliche Terrasse mit Hafenblick und sehr wiedererkennbarem Architekturprofil.', 'time' => '45 Min.'),
                    array('type' => 'Viertel', 'name' => 'Sternschanze', 'area' => 'Schanze', 'note' => 'Cafés, unabhängige Läden und unkomplizierte Orte für einen Abend ohne festen Plan.', 'time' => 'Nachmittag'),
                    array('type' => 'Essen', 'name' => 'Fischmarkt', 'area' => 'Altona', 'note' => 'Ein lokaler Klassiker, lebendig und sehr früh: ideal, um eine andere Seite der Stadt zu sehen.', 'time' => 'Sonntag'),
                    array('type' => 'Entspannen', 'name' => 'Strandperle', 'area' => 'Othmarschen', 'note' => 'Tische im Sand an der Elbe, perfekt bei weichem Licht und langsamem Tempo.', 'time' => 'Sonnenuntergang'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Speicherstadt zu Fuß', 'detail' => 'Kanäle, Brücken und Backsteinarchitektur, bevor es voller wird.'),
                    array('step' => 'Nachmittag', 'title' => 'HafenCity und Schanze', 'detail' => 'Blick von der Plaza, danach Läden und Cafés rund um die Sternschanze.'),
                    array('step' => 'Abend', 'title' => 'Elbe oder St. Pauli', 'detail' => 'Unkompliziertes Abendessen am Fluss oder Live-Musik auf der nächtlicheren Seite der Stadt.'),
                ),
            ),
            array(
                'slug' => 'munich',
                'name' => 'München',
                'displayName' => 'München',
                'aliases' => array('Muenchen'),
                'region' => 'Klassisch und draußen',
                'headline' => 'Museen, Märkte, Surfen in der Stadt und Biergärten unter Bäumen.',
                'summary' => 'München ist geordnet, aber nicht steif: Große Museen, Parks, historische Märkte und Viertel mit starken kulinarischen Adressen wechseln sich ab.',
                'bestFor' => 'Museen, Märkte, Parks, bayerische Tradition',
                'duration' => '2-3 Tage',
                'accent' => '#2563eb',
                'neighborhoods' => array('Altstadt', 'Maxvorstadt', 'Glockenbach', 'Schwabing'),
                'spots' => array(
                    array('type' => 'Museen', 'name' => 'Kunstareal', 'area' => 'Maxvorstadt', 'note' => 'Ein dichtes Kulturviertel, ideal für einen Tag zwischen Kunst und Design.', 'time' => 'Halber Tag'),
                    array('type' => 'Essen', 'name' => 'Viktualienmarkt', 'area' => 'Altstadt', 'note' => 'Historischer Markt für schnelle Kostproben, lokale Produkte und eine Pause im Freien.', 'time' => 'Mittagessen'),
                    array('type' => 'Draußen', 'name' => 'Eisbachwelle', 'area' => 'Englischer Garten', 'note' => 'Die berühmte urbane Surfwelle: kurz, überraschend und sehr fotogen.', 'time' => '30 Min.'),
                    array('type' => 'Viertel', 'name' => 'Glockenbachviertel', 'area' => 'Glockenbach', 'note' => 'Boutiquen, Bars und zeitgemäße Restaurants in einem kompakten, lebendigen Viertel.', 'time' => 'Abend'),
                    array('type' => 'Park', 'name' => 'Olympiapark', 'area' => 'Milbertshofen', 'note' => 'Siebzigerjahre-Architektur, Hügel und weite Blicke über die Stadt.', 'time' => '2 Std.'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Kunst in der Maxvorstadt', 'detail' => 'Wähle eine Pinakothek und lass Platz für ein langes Frühstück.'),
                    array('step' => 'Nachmittag', 'title' => 'Markt und Park', 'detail' => 'Viktualienmarkt, Altstadt und ein Abstecher zur Eisbachwelle.'),
                    array('step' => 'Abend', 'title' => 'Glockenbach', 'detail' => 'Zeitgemäßes Abendessen und Bars in einem Viertel, das sich leicht zu Fuß erkunden lässt.'),
                ),
            ),
            array(
                'slug' => 'cologne',
                'name' => 'Köln',
                'displayName' => 'Köln',
                'aliases' => array('Koeln'),
                'region' => 'Rhein und Popkultur',
                'headline' => 'Dom, moderne Kunst, Brauhäuser und kreative Viertel.',
                'summary' => 'Köln ist direkt und gesellig: Das Zentrum ist kompakt, doch die umliegenden Viertel geben der Reise den spannendsten Ton.',
                'bestFor' => 'Moderne Kunst, Brauhäuser, unkomplizierte Wochenenden',
                'duration' => '1-2 Tage',
                'accent' => '#db2777',
                'neighborhoods' => array('Altstadt', 'Belgisches Viertel', 'Ehrenfeld', 'Rheinauhafen'),
                'spots' => array(
                    array('type' => 'Wahrzeichen', 'name' => 'Kölner Dom', 'area' => 'Altstadt', 'note' => 'Ein szenischer Einstieg in die Stadt, besonders wenn du am Hauptbahnhof ankommst.', 'time' => '45 Min.'),
                    array('type' => 'Kultur', 'name' => 'Museum Ludwig', 'area' => 'Altstadt', 'note' => 'Moderne Kunst und Pop-Art in perfekter Lage zwischen Dom und Rhein.', 'time' => '2 Std.'),
                    array('type' => 'Viertel', 'name' => 'Belgisches Viertel', 'area' => 'Innenstadt', 'note' => 'Cafés, Konzeptläden und Bars: die einfachste Seite, um das heutige Köln zu verstehen.', 'time' => 'Nachmittag'),
                    array('type' => 'Essen', 'name' => 'Brauhaus-Tour', 'area' => 'Altstadt', 'note' => 'Traditionelle Brauhäuser und schnell serviertes Kölsch, am besten mit leichter Stimmung.', 'time' => 'Abend'),
                    array('type' => 'Einzigartig', 'name' => 'Odonien', 'area' => 'Ehrenfeld', 'note' => 'Künstlerischer Industrieort mit Veranstaltungen, Installationen und alternativer Atmosphäre.', 'time' => 'Veranstaltung'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Dom und Museum Ludwig', 'detail' => 'Klassisch und zeitgenössisch in wenigen Schritten, ohne Zeit mit Wegen zu verlieren.'),
                    array('step' => 'Nachmittag', 'title' => 'Belgisches Viertel', 'detail' => 'Unabhängiges Einkaufen, Kaffee und eine langsame Pause auf den Plätzen des Viertels.'),
                    array('step' => 'Abend', 'title' => 'Kölsch oder Ehrenfeld', 'detail' => 'Traditionelles Brauhaus im Zentrum oder ein alternativer Abend Richtung Odonien.'),
                ),
            ),
            array(
                'slug' => 'frankfurt',
                'name' => 'Frankfurt',
                'displayName' => 'Frankfurt',
                'aliases' => array('Frankfurt am Main'),
                'region' => 'Skyline und Museen',
                'headline' => 'Hochhäuser, Apfelwein, Markthallen und Museen am Main.',
                'summary' => 'Frankfurt ist am interessantesten, wenn du die Stadt als Ort der Gegensätze liest: Finanzen, grüne Ufer, Markthallen und sehr unterschiedliche Viertel.',
                'bestFor' => 'Museen, Skyline, internationale Gastronomieszene',
                'duration' => '1-2 Tage',
                'accent' => '#ea580c',
                'neighborhoods' => array('Innenstadt', 'Sachsenhausen', 'Bahnhofsviertel', 'Westend'),
                'spots' => array(
                    array('type' => 'Aussicht', 'name' => 'Main Tower', 'area' => 'Innenstadt', 'note' => 'Die einfachste Terrasse, um Skyline und Fluss von oben zu lesen.', 'time' => '45 Min.'),
                    array('type' => 'Essen', 'name' => 'Kleinmarkthalle', 'area' => 'Innenstadt', 'note' => 'Überdachter Markt mit historischen Ständen, Snacks und Produkten zum Probieren.', 'time' => 'Mittagessen'),
                    array('type' => 'Museen', 'name' => 'Museumsufer', 'area' => 'Sachsenhausen', 'note' => 'Ein ganzes Ufer voller Museen, perfekt, wenn du je nach Stimmung wählen möchtest.', 'time' => 'Halber Tag'),
                    array('type' => 'Viertel', 'name' => 'Bahnhofsviertel', 'area' => 'Zentrum', 'note' => 'Internationale Küchen, Bars und eine rauere, dynamische urbane Szene.', 'time' => 'Abend'),
                    array('type' => 'Ausgehen', 'name' => 'Apfelweinwirtschaft', 'area' => 'Sachsenhausen', 'note' => 'Ein traditionelles Abendessen mit lokalem Apfelwein als sehr frankfurter Abschluss.', 'time' => 'Abendessen'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Zentrum und Markt', 'detail' => 'Spaziergang zwischen Römerberg und Kleinmarkthalle mit schnellen Kostproben.'),
                    array('step' => 'Nachmittag', 'title' => 'Museumsufer', 'detail' => 'Wähle ein Museum und geh zum Sonnenuntergang am Main entlang.'),
                    array('step' => 'Abend', 'title' => 'Sachsenhausen', 'detail' => 'Abendessen mit Apfelwein oder ein urbanerer Abstecher ins Bahnhofsviertel.'),
                ),
            ),
            array(
                'slug' => 'dresden',
                'name' => 'Dresden',
                'displayName' => 'Dresden',
                'aliases' => array(),
                'region' => 'Barock und Neustadt',
                'headline' => 'Bühnenhafte Paläste, künstlerische Höfe und alternative Abende.',
                'summary' => 'Dresden verbindet Monumentalität und kreative Viertel: Am schönsten ist der Wechsel von der barocken Altstadt in die Neustadt, ohne daraus zwei getrennte Reisen zu machen.',
                'bestFor' => 'Architektur, Galerien, romantische, aber nicht offensichtliche Wochenenden',
                'duration' => '2 Tage',
                'accent' => '#7c3aed',
                'neighborhoods' => array('Altstadt', 'Innere Neustadt', 'Äußere Neustadt', 'Elbufer'),
                'spots' => array(
                    array('type' => 'Wahrzeichen', 'name' => 'Zwinger', 'area' => 'Altstadt', 'note' => 'Theatralische Barockarchitektur, perfekt als Auftakt im historischen Zentrum.', 'time' => '90 Min.'),
                    array('type' => 'Kultur', 'name' => 'Albertinum', 'area' => 'Altstadt', 'note' => 'Moderne und zeitgenössische Kunst in einem eleganten Gebäude nahe der Elbe.', 'time' => '2 Std.'),
                    array('type' => 'Einzigartig', 'name' => 'Kunsthofpassage', 'area' => 'Äußere Neustadt', 'note' => 'Farbige Höfe, Installationen und kleine unabhängige Adressen.', 'time' => '60 Min.'),
                    array('type' => 'Essen', 'name' => 'Pfunds Molkerei', 'area' => 'Neustadt', 'note' => 'Eine historische Molkerei mit dekorierten Innenräumen und sehr besonderer Atmosphäre.', 'time' => '30 Min.'),
                    array('type' => 'Entspannen', 'name' => 'Elbufer', 'area' => 'Elbe', 'note' => 'Weite Ufer für eine langsame Pause mit Blick auf das Profil der Stadt.', 'time' => 'Sonnenuntergang'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Szenische Altstadt', 'detail' => 'Zwinger, Frauenkirche und ein kurzer Spaziergang über monumentale Plätze.'),
                    array('step' => 'Nachmittag', 'title' => 'Kreative Neustadt', 'detail' => 'Kunsthofpassage, Kaffee und kleine Galerien ohne starre Route.'),
                    array('step' => 'Abend', 'title' => 'Elbe bei Sonnenuntergang', 'detail' => 'Zurück am Fluss entlang und ein unkompliziertes Abendessen in der Neustadt.'),
                ),
            ),
        );
    }
}
