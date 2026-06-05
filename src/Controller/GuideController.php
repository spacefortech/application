<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GuideController extends AbstractController
{
    public function index(): Response
    {
        $citiesJson = $this->encodeJson(array_values($this->getCities()));

        return $this->render('guide/index.html.twig', array(
            'citiesJson' => $citiesJson,
        ));
    }

    public function coolPlaces(): Response
    {
        return $this->render('guide/cool_places.html.twig', array(
            'coolPlacesJson' => $this->encodeJson($this->getCoolPlacesData()),
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

    public function einzelattraktion(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = max(6, (int) $request->query->get('perPage', 24));

        $items = array();

        foreach ($this->getCities() as $city) {
            $spots = isset($city['spots']) && is_array($city['spots']) ? $city['spots'] : array();

            foreach ($spots as $index => $spot) {
                $items[] = array(
                    'id' => $city['slug'] . '-' . $index,
                    'slug' => $this->normalize($city['slug'] . '-' . ($spot['name'] ?? (string) $index)),
                    'city' => array(
                        'slug' => $city['slug'],
                        'displayName' => $city['displayName'],
                        'region' => $city['region'] ?? '',
                        'accent' => $city['accent'] ?? '#0b0c3f',
                    ),
                    'spot' => array(
                        'type' => $spot['type'] ?? '',
                        'name' => $spot['name'] ?? '',
                        'area' => $spot['area'] ?? '',
                        'note' => $spot['note'] ?? '',
                        'time' => $spot['time'] ?? '',
                    ),
                );
            }
        }

        $totalItems = count($items);
        $totalPages = (int) ceil($totalItems / max(1, $perPage));
        $totalPages = max(1, $totalPages);
        $page = min($page, $totalPages);

        $offset = ($page - 1) * $perPage;
        $pageItems = array_slice($items, $offset, $perPage);

        return $this->render('guide/einzelattraktion.html.twig', array(
            'items' => $pageItems,
            'page' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
        ));
    }

    public function highlightsPaket(): Response
    {
        return $this->render('guide/highlights_paket.html.twig');
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

    private function encodeJson(array $data): string
    {
        $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        return $json === false ? '{}' : $json;
    }

    private function getCoolPlacesData(): array
    {
        $duisburg = $this->findCity('duisburg');
        if (!$duisburg) {
            $duisburg = array(
                'displayName' => 'Duisburg',
                'region' => 'Industriekultur am Rhein',
                'accent' => '#475569',
                'summary' => 'Hafen, Stahlarchitektur, Rheinwege und raues Ruhrgebietsgefühl.',
            );
        }

        return array(
            'city' => array(
                'slug' => 'duisburg',
                'displayName' => $duisburg['displayName'],
                'region' => $duisburg['region'],
                'accent' => $duisburg['accent'],
                'summary' => $duisburg['summary'],
            ),
            'places' => array(
                array(
                    'slug' => 'landschaftspark-duisburg-nord',
                    'name' => 'Landschaftspark Duisburg-Nord',
                    'type' => 'Industriekultur',
                    'area' => 'Meiderich',
                    'duration' => '2-3 Std.',
                    'bestTime' => 'Später Nachmittag bis Abend',
                    'address' => 'Emscherstraße 71, 47137 Duisburg',
                    'intro' => 'Ein stillgelegtes Hüttenwerk, das heute als Park, Aussichtsort, Kletter- und Lichtkulisse funktioniert.',
                    'why' => 'Der Ort zeigt Duisburgs Wandel sehr direkt: alte Hochöfen, grüne Wege, Wasserflächen und abends farbiges Licht.',
                    'tip' => 'Plane genug Zeit ein und bleib bis zur Dämmerung, wenn die Lichtinstallation den Park stark verändert.',
                    'facts' => array(
                        array('label' => 'Kategorie', 'value' => 'Industriekultur'),
                        array('label' => 'Ideal für', 'value' => 'Fotos, Spaziergang, Abendlicht'),
                        array('label' => 'Zeit', 'value' => '2-3 Std.'),
                        array('label' => 'Mood', 'value' => 'Rau, weit, filmisch'),
                    ),
                    'photos' => array(
                        array('visual' => 'industrial', 'label' => 'Hochofen', 'caption' => 'Stahlstrukturen und weite Wege durch das alte Werk.'),
                        array('visual' => 'industrial-light', 'label' => 'Licht', 'caption' => 'Abends wirkt der Park wie eine offene Bühne.'),
                        array('visual' => 'industrial-water', 'label' => 'Wasserpark', 'caption' => 'Natur und Industrie liegen hier direkt nebeneinander.'),
                        array('visual' => 'industrial-view', 'label' => 'Aussicht', 'caption' => 'Von oben liest du das Ruhrgebiet als Landschaft.'),
                    ),
                    'feedbacks' => array(
                        array('name' => 'Mara', 'quote' => 'Bei Lichtwechsel wird der Park richtig stark. Man bleibt automatisch länger.'),
                        array('name' => 'Toni', 'quote' => 'Mein Lieblingsort in Duisburg für Fotos und einen ruhigen Abendspaziergang.'),
                    ),
                ),
                array(
                    'slug' => 'tiger-and-turtle',
                    'name' => 'Tiger & Turtle',
                    'type' => 'Landmarke',
                    'area' => 'Angerhausen',
                    'duration' => '45-60 Min.',
                    'bestTime' => 'Dämmerung',
                    'address' => 'Ehinger Straße, 47249 Duisburg',
                    'intro' => 'Eine begehbare Achterbahn-Skulptur auf einer Halde mit weitem Blick über Duisburg, Rhein und Ruhrgebiet.',
                    'why' => 'Der Ort ist kurz, ikonisch und sehr fotografisch. Besonders abends zeichnen Lichter die Stahlkurven in den Himmel.',
                    'tip' => 'Festes Schuhwerk mitnehmen und bei Wind oder schlechtem Wetter vorher prüfen, ob der Zugang möglich ist.',
                    'facts' => array(
                        array('label' => 'Kategorie', 'value' => 'Aussicht & Kunst'),
                        array('label' => 'Ideal für', 'value' => 'Sonnenuntergang, Fotos'),
                        array('label' => 'Zeit', 'value' => '45-60 Min.'),
                        array('label' => 'Mood', 'value' => 'Skulptural, luftig'),
                    ),
                    'photos' => array(
                        array('visual' => 'tiger', 'label' => 'Skulptur', 'caption' => 'Die geschwungene Stahlform ist das Motiv.'),
                        array('visual' => 'tiger-dusk', 'label' => 'Dämmerung', 'caption' => 'Kurz vor dunkel wirkt der Ort am stärksten.'),
                        array('visual' => 'tiger-view', 'label' => 'Panorama', 'caption' => 'Der Blick reicht über Industrie, Rhein und Stadt.'),
                        array('visual' => 'tiger-lines', 'label' => 'Linien', 'caption' => 'Treppen, Kurven und Stahl ergeben klare Fotos.'),
                    ),
                    'feedbacks' => array(
                        array('name' => 'Jonas', 'quote' => 'Kurz, aber bleibt hängen. Bei Abendlicht sieht alles viel größer aus.'),
                        array('name' => 'Nora', 'quote' => 'Perfekter Spot, wenn man Duisburg von oben sehen will.'),
                    ),
                ),
                array(
                    'slug' => 'duisburger-innenhafen',
                    'name' => 'Duisburger Innenhafen',
                    'type' => 'Wasser & Architektur',
                    'area' => 'Zentrum',
                    'duration' => '90 Min.',
                    'bestTime' => 'Nachmittag bis Abend',
                    'address' => 'Innenhafen, 47051 Duisburg',
                    'intro' => 'Marina, Speicherarchitektur, Restaurants und Museen machen den Innenhafen zum einfachsten Einstieg in Duisburg.',
                    'why' => 'Hier ist Duisburg weicher und urbaner: Wasser, Backstein, neue Architektur und kurze Wege passen gut zusammen.',
                    'tip' => 'Mit dem Museum Küppersmühle kombinieren und danach am Wasser sitzen bleiben.',
                    'facts' => array(
                        array('label' => 'Kategorie', 'value' => 'Hafen & Essen'),
                        array('label' => 'Ideal für', 'value' => 'Ankommen, Spaziergang'),
                        array('label' => 'Zeit', 'value' => '90 Min.'),
                        array('label' => 'Mood', 'value' => 'Urban, ruhig'),
                    ),
                    'photos' => array(
                        array('visual' => 'harbor', 'label' => 'Marina', 'caption' => 'Wasser und Architektur im Zentrum.'),
                        array('visual' => 'harbor-brick', 'label' => 'Speicher', 'caption' => 'Alte Speicher geben dem Hafen Charakter.'),
                        array('visual' => 'harbor-night', 'label' => 'Abend', 'caption' => 'Restaurants und Licht am Wasser.'),
                        array('visual' => 'harbor-walk', 'label' => 'Promenade', 'caption' => 'Kurze Wege entlang der Hafenkante.'),
                    ),
                    'feedbacks' => array(
                        array('name' => 'Sofia', 'quote' => 'Innenhafen fühlt sich entspannt an und ist ein guter Startpunkt.'),
                        array('name' => 'Luca', 'quote' => 'Erst Kunst, dann Wasserblick. Genau mein Duisburg-Einstieg.'),
                    ),
                ),
                array(
                    'slug' => 'mkm-museum-kueppersmuehle',
                    'name' => 'MKM Museum Küppersmühle',
                    'type' => 'Kunst',
                    'area' => 'Innenhafen',
                    'duration' => '2 Std.',
                    'bestTime' => 'Schlechtwetter oder ruhiger Nachmittag',
                    'address' => 'Philosophenweg 55, 47051 Duisburg',
                    'intro' => 'Moderne Kunst in einer ehemaligen Mühle, stark geprägt von Industriebau, White Cube und klarer Architektur.',
                    'why' => 'Das Museum macht den Wandel des Innenhafens sichtbar und ist auch architektonisch ein Highlight.',
                    'tip' => 'Vorher Öffnungszeiten prüfen und den Besuch mit einem Spaziergang am Innenhafen verbinden.',
                    'facts' => array(
                        array('label' => 'Kategorie', 'value' => 'Museum'),
                        array('label' => 'Ideal für', 'value' => 'Kunst, Architektur'),
                        array('label' => 'Zeit', 'value' => '2 Std.'),
                        array('label' => 'Mood', 'value' => 'Klar, kulturell'),
                    ),
                    'photos' => array(
                        array('visual' => 'museum', 'label' => 'Fassade', 'caption' => 'Industriebau trifft moderne Museumsarchitektur.'),
                        array('visual' => 'museum-stairs', 'label' => 'Treppen', 'caption' => 'Klare Linien und starke Innenräume.'),
                        array('visual' => 'museum-gallery', 'label' => 'Galerie', 'caption' => 'Ruhige Räume für moderne Kunst.'),
                        array('visual' => 'museum-harbor', 'label' => 'Hafen', 'caption' => 'Das Museum liegt direkt am Innenhafen.'),
                    ),
                    'feedbacks' => array(
                        array('name' => 'Elena', 'quote' => 'Das Gebäude allein lohnt sich schon, die Sammlung macht es rund.'),
                    ),
                ),
                array(
                    'slug' => 'sechs-seen-platte',
                    'name' => 'Sechs-Seen-Platte',
                    'type' => 'Natur',
                    'area' => 'Duisburg-Süd',
                    'duration' => 'Halber Tag',
                    'bestTime' => 'Sommer, Vormittag',
                    'address' => 'Duisburg-Wedau / Buchholz',
                    'intro' => 'Seen, Spazierwege, Wasserflächen und Aussichtsturm bilden den entspannten Gegenpol zur Industriekultur.',
                    'why' => 'Die Sechs-Seen-Platte zeigt, dass Duisburg nicht nur Stahl und Hafen ist, sondern auch viel Grün und Wasser hat.',
                    'tip' => 'Für Picknick oder längere Pause einplanen, besonders wenn du einen langsameren Reisetag willst.',
                    'facts' => array(
                        array('label' => 'Kategorie', 'value' => 'Natur & Wasser'),
                        array('label' => 'Ideal für', 'value' => 'Picknick, Spaziergang'),
                        array('label' => 'Zeit', 'value' => 'Halber Tag'),
                        array('label' => 'Mood', 'value' => 'Grün, leicht'),
                    ),
                    'photos' => array(
                        array('visual' => 'lake', 'label' => 'Seeufer', 'caption' => 'Viel Wasser und lange Wege.'),
                        array('visual' => 'lake-summer', 'label' => 'Sommer', 'caption' => 'Ideal für einen warmen Reisetag.'),
                        array('visual' => 'lake-path', 'label' => 'Wege', 'caption' => 'Spaziergänge zwischen den Seen.'),
                        array('visual' => 'lake-view', 'label' => 'Aussicht', 'caption' => 'Vom Turm sieht Duisburg überraschend grün aus.'),
                    ),
                    'feedbacks' => array(
                        array('name' => 'Amir', 'quote' => 'Schöner Wechsel nach Hafen und Industrie. Ruhiger als erwartet.'),
                    ),
                ),
                array(
                    'slug' => 'lehmbruck-museum-kantpark',
                    'name' => 'Lehmbruck Museum & Kantpark',
                    'type' => 'Skulptur',
                    'area' => 'Dellviertel',
                    'duration' => '90 Min.',
                    'bestTime' => 'Mittag oder ruhiger Nachmittag',
                    'address' => 'Friedrich-Wilhelm-Straße 40, 47051 Duisburg',
                    'intro' => 'Ein Museum für moderne Skulptur mit klarer Architektur und Skulpturenpark direkt im Kantpark.',
                    'why' => 'Der Ort verbindet Kunst, Ruhe und Stadtmitte. Gut, wenn der Trip nicht nur draußen stattfinden soll.',
                    'tip' => 'Erst Museum, danach eine kleine Runde durch den frei zugänglichen Skulpturenpark.',
                    'facts' => array(
                        array('label' => 'Kategorie', 'value' => 'Kunst & Park'),
                        array('label' => 'Ideal für', 'value' => 'Skulptur, Pause'),
                        array('label' => 'Zeit', 'value' => '90 Min.'),
                        array('label' => 'Mood', 'value' => 'Ruhig, konzentriert'),
                    ),
                    'photos' => array(
                        array('visual' => 'art', 'label' => 'Museum', 'caption' => 'Moderne Skulptur in klaren Räumen.'),
                        array('visual' => 'art-park', 'label' => 'Kantpark', 'caption' => 'Kunst geht hier nach draußen weiter.'),
                        array('visual' => 'art-glass', 'label' => 'Architektur', 'caption' => 'Glas, Beton und ruhige Linien.'),
                        array('visual' => 'art-detail', 'label' => 'Details', 'caption' => 'Ein guter Ort zum genauen Hinschauen.'),
                    ),
                    'feedbacks' => array(
                        array('name' => 'Mila', 'quote' => 'Sehr ruhig und genau richtig zwischen zwei größeren Outdoor-Spots.'),
                    ),
                ),
            ),
        );
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
                'imageIndex' => 0,
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
                'imageIndex' => 1,
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
                'imageIndex' => 2,
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
                'imageIndex' => 3,
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
                'imageIndex' => 4,
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
                'imageIndex' => 5,
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
            array(
                'slug' => 'leipzig',
                'name' => 'Leipzig',
                'displayName' => 'Leipzig',
                'aliases' => array(),
                'region' => 'Kreative Passagen',
                'headline' => 'Historische Passagen, Seen, Musikgeschichte und junge Viertel.',
                'summary' => 'Leipzig fühlt sich leicht und kreativ an: kompakte Innenstadt, starke Musikgeschichte und Viertel, in denen Ateliers, Cafés und Wasserwege nah beieinanderliegen.',
                'bestFor' => 'Musik, Passagen, kreative Viertel, Seen',
                'duration' => '2 Tage',
                'accent' => '#16a34a',
                'imageIndex' => 6,
                'neighborhoods' => array('Zentrum', 'Plagwitz', 'Südvorstadt', 'Neuseenland'),
                'spots' => array(
                    array('type' => 'Architektur', 'name' => 'Mädlerpassage', 'area' => 'Zentrum', 'note' => 'Elegante Passagenarchitektur, ideal für einen ersten Eindruck der Innenstadt.', 'time' => '45 Min.'),
                    array('type' => 'Kultur', 'name' => 'Gewandhaus', 'area' => 'Zentrum', 'note' => 'Musikgeschichte und Gegenwart an einem Ort mit internationalem Klang.', 'time' => 'Konzert'),
                    array('type' => 'Viertel', 'name' => 'Plagwitz', 'area' => 'Westen', 'note' => 'Kanäle, Ateliers, Cafés und Industriebauten mit sehr lebendigem Wochenendgefühl.', 'time' => 'Nachmittag'),
                    array('type' => 'Draußen', 'name' => 'Cospudener See', 'area' => 'Neuseenland', 'note' => 'Ein schneller Wechsel vom Stadtrhythmus ans Wasser.', 'time' => 'Halber Tag'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Innenstadt und Passagen', 'detail' => 'Starte kompakt zwischen Markt, Mädlerpassage und kleinen Cafés.'),
                    array('step' => 'Nachmittag', 'title' => 'Plagwitz am Wasser', 'detail' => 'Industriearchitektur, Kanäle und kreative Adressen im Westen.'),
                    array('step' => 'Abend', 'title' => 'Musik oder Südvorstadt', 'detail' => 'Konzertabend oder unkomplizierte Bars und Restaurants südlich des Zentrums.'),
                ),
            ),
            array(
                'slug' => 'stuttgart',
                'name' => 'Stuttgart',
                'displayName' => 'Stuttgart',
                'aliases' => array(),
                'region' => 'Design und Weinberge',
                'headline' => 'Architektur, Automobilkultur, Hügelblicke und Wein am Stadtrand.',
                'summary' => 'Stuttgart ist spannender, wenn du Höhe und Tiefe kombinierst: Museen, moderne Architektur, Talkesselblicke und Weinberge direkt neben der Stadt.',
                'bestFor' => 'Design, Museen, Weinberge, Aussichtspunkte',
                'duration' => '1-2 Tage',
                'accent' => '#0891b2',
                'imageIndex' => 7,
                'neighborhoods' => array('Mitte', 'Bad Cannstatt', 'Stuttgart-West', 'Rotenberg'),
                'spots' => array(
                    array('type' => 'Design', 'name' => 'Mercedes-Benz Museum', 'area' => 'Bad Cannstatt', 'note' => 'Ein architektonisch starkes Museum für Design, Technik und Stadtidentität.', 'time' => '2 Std.'),
                    array('type' => 'Aussicht', 'name' => 'Karlshöhe', 'area' => 'West', 'note' => 'Ein kurzer Aufstieg mit Blick über den Talkessel.', 'time' => '60 Min.'),
                    array('type' => 'Wein', 'name' => 'Grabkapelle Rotenberg', 'area' => 'Rotenberg', 'note' => 'Weinberge, Panorama und ein ruhiger Kontrast zum Zentrum.', 'time' => 'Nachmittag'),
                    array('type' => 'Kultur', 'name' => 'Staatsgalerie', 'area' => 'Mitte', 'note' => 'Kunst und postmoderne Architektur in zentraler Lage.', 'time' => '90 Min.'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Museum und Architektur', 'detail' => 'Starte mit Designgeschichte und klarer Formensprache.'),
                    array('step' => 'Nachmittag', 'title' => 'Höhen und Weinberge', 'detail' => 'Plane eine Aussicht ein, damit die Lage der Stadt sichtbar wird.'),
                    array('step' => 'Abend', 'title' => 'West oder Mitte', 'detail' => 'Essen in einem urbanen Viertel mit kurzem Heimweg.'),
                ),
            ),
            array(
                'slug' => 'nuremberg',
                'name' => 'Nürnberg',
                'displayName' => 'Nürnberg',
                'aliases' => array('Nuernberg', 'Nuremberg'),
                'region' => 'Mittelalter und Märkte',
                'headline' => 'Burgblicke, Fachwerk, Geschichte und eine kompakte Altstadt.',
                'summary' => 'Nürnberg ist ideal für kurze Reisen: Die Altstadt ist gut lesbar, die Burg setzt den Rahmen und Museen geben der Stadt Tiefe.',
                'bestFor' => 'Altstadt, Geschichte, Märkte, kurze Wochenenden',
                'duration' => '1-2 Tage',
                'accent' => '#b45309',
                'imageIndex' => 8,
                'neighborhoods' => array('Altstadt', 'Gostenhof', 'Sebald', 'Lorenzer Seite'),
                'spots' => array(
                    array('type' => 'Wahrzeichen', 'name' => 'Kaiserburg', 'area' => 'Altstadt', 'note' => 'Der klassische Blick über Dächer und Stadtmauern.', 'time' => '90 Min.'),
                    array('type' => 'Museum', 'name' => 'Germanisches Nationalmuseum', 'area' => 'Altstadt', 'note' => 'Ein tiefes Kulturmuseum, wenn du mehr als Fassaden sehen möchtest.', 'time' => '2 Std.'),
                    array('type' => 'Viertel', 'name' => 'Gostenhof', 'area' => 'Westen', 'note' => 'Unabhängige Läden, Cafés und ein etwas rauerer lokaler Ton.', 'time' => 'Nachmittag'),
                    array('type' => 'Essen', 'name' => 'Altstadt-Küche', 'area' => 'Zentrum', 'note' => 'Regionale Klassiker in einem sehr fußläufigen Zentrum.', 'time' => 'Abend'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Burg und Altstadt', 'detail' => 'Von oben starten und langsam durch die historischen Straßen zurückgehen.'),
                    array('step' => 'Nachmittag', 'title' => 'Museum oder Gostenhof', 'detail' => 'Je nach Wetter Kultur oder ein lokales Viertel mit Cafés.'),
                    array('step' => 'Abend', 'title' => 'Klassisch essen', 'detail' => 'Ein kompakter Abend in der Altstadt ohne lange Wege.'),
                ),
            ),
            array(
                'slug' => 'heidelberg',
                'name' => 'Heidelberg',
                'displayName' => 'Heidelberg',
                'aliases' => array(),
                'region' => 'Romantik am Neckar',
                'headline' => 'Schloss, Alte Brücke, Flussblick und eine der schönsten Altstädte.',
                'summary' => 'Heidelberg funktioniert, wenn du es langsam angehst: Fluss, Schloss, Altstadt und Aussichtspunkte ergeben eine sehr runde Kurzreise.',
                'bestFor' => 'Romantische Wochenenden, Altstadt, Aussicht, Flusswege',
                'duration' => '1-2 Tage',
                'accent' => '#dc2626',
                'imageIndex' => 9,
                'neighborhoods' => array('Altstadt', 'Neuenheim', 'Königstuhl', 'Neckarufer'),
                'spots' => array(
                    array('type' => 'Wahrzeichen', 'name' => 'Schloss Heidelberg', 'area' => 'Altstadt', 'note' => 'Der große Blick über Stadt, Fluss und Hügel.', 'time' => '2 Std.'),
                    array('type' => 'Spaziergang', 'name' => 'Alte Brücke', 'area' => 'Neckar', 'note' => 'Der einfachste Übergang zwischen Postkartenblick und Stadtleben.', 'time' => '30 Min.'),
                    array('type' => 'Aussicht', 'name' => 'Philosophenweg', 'area' => 'Neuenheim', 'note' => 'Ein ruhiger Weg mit weitem Blick auf Schloss und Altstadt.', 'time' => '90 Min.'),
                    array('type' => 'Altstadt', 'name' => 'Hauptstraße', 'area' => 'Zentrum', 'note' => 'Läden, Cafés und historische Fassaden in sehr dichter Folge.', 'time' => 'Nachmittag'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Schloss und Altstadt', 'detail' => 'Beginne oben, bevor die Hauptwege voller werden.'),
                    array('step' => 'Nachmittag', 'title' => 'Neckar und Brücke', 'detail' => 'Wechsle ans Wasser und nimm den Blick vom anderen Ufer mit.'),
                    array('step' => 'Abend', 'title' => 'Langsam ausklingen', 'detail' => 'Ein frühes Abendessen in der Altstadt oder ein letzter Spaziergang am Fluss.'),
                ),
            ),
            array(
                'slug' => 'bremen',
                'name' => 'Bremen',
                'displayName' => 'Bremen',
                'aliases' => array(),
                'region' => 'Hanse und Weser',
                'headline' => 'Marktplatz, Schnoor, Backstein, Weser und hanseatische Ruhe.',
                'summary' => 'Bremen ist eine ruhige, gut portionierbare Städtereise: historisches Zentrum, kleine Gassen und Wege am Wasser liegen nah beieinander.',
                'bestFor' => 'Hanseflair, kurze Wege, Altstadt, Wasser',
                'duration' => '1-2 Tage',
                'accent' => '#0f766e',
                'imageIndex' => 10,
                'neighborhoods' => array('Altstadt', 'Schnoor', 'Viertel', 'Weserufer'),
                'spots' => array(
                    array('type' => 'Wahrzeichen', 'name' => 'Marktplatz', 'area' => 'Altstadt', 'note' => 'Rathaus, Roland und hanseatische Fassaden als starkes Zentrum.', 'time' => '60 Min.'),
                    array('type' => 'Gassen', 'name' => 'Schnoorviertel', 'area' => 'Altstadt', 'note' => 'Kleine Häuser, enge Wege und ein sehr eigener Rhythmus.', 'time' => '90 Min.'),
                    array('type' => 'Wasser', 'name' => 'Schlachte', 'area' => 'Weser', 'note' => 'Uferpromenade für eine unkomplizierte Pause am Fluss.', 'time' => 'Abend'),
                    array('type' => 'Viertel', 'name' => 'Das Viertel', 'area' => 'Ostertor', 'note' => 'Kinos, Bars, Cafés und ein lebendiger lokaler Alltag.', 'time' => 'Nachmittag'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Marktplatz und Schnoor', 'detail' => 'Historisches Zentrum zuerst, dann durch die kleinen Gassen weiterziehen.'),
                    array('step' => 'Nachmittag', 'title' => 'Viertel und Cafés', 'detail' => 'Ein lokalerer Ton mit kurzer Distanz zur Altstadt.'),
                    array('step' => 'Abend', 'title' => 'Weserufer', 'detail' => 'Abschluss an der Schlachte oder in einer Bar im Viertel.'),
                ),
            ),
            array(
                'slug' => 'duesseldorf',
                'name' => 'Düsseldorf',
                'displayName' => 'Düsseldorf',
                'aliases' => array('Duesseldorf', 'Dusseldorf'),
                'region' => 'Rhein und Avantgarde',
                'headline' => 'Rheinpromenade, Kunst, Mode und moderne Architektur im MedienHafen.',
                'summary' => 'Düsseldorf ist elegant, aber nicht steif: Kunstsammlungen, Rheinwege, japanische Küche und der MedienHafen ergeben einen klaren urbanen Wochenendtrip.',
                'bestFor' => 'Kunst, Mode, Rheinpromenade, moderne Architektur',
                'duration' => '1-2 Tage',
                'accent' => '#4f46e5',
                'imageIndex' => 11,
                'neighborhoods' => array('Altstadt', 'MedienHafen', 'Pempelfort', 'Little Tokyo'),
                'spots' => array(
                    array('type' => 'Architektur', 'name' => 'MedienHafen', 'area' => 'Hafen', 'note' => 'Moderne Architektur und klare Rheinblicke in einem kompakten Spaziergang.', 'time' => '90 Min.'),
                    array('type' => 'Kunst', 'name' => 'K20', 'area' => 'Altstadt', 'note' => 'Eine starke Sammlung für moderne Kunst direkt am Zentrum.', 'time' => '2 Std.'),
                    array('type' => 'Essen', 'name' => 'Little Tokyo', 'area' => 'Stadtmitte', 'note' => 'Japanische Küche und Läden als sehr eigenständige Düsseldorfer Seite.', 'time' => 'Mittagessen'),
                    array('type' => 'Spaziergang', 'name' => 'Rheinpromenade', 'area' => 'Rhein', 'note' => 'Der beste Weg, Stadt, Wasser und Abendlicht zusammenzubringen.', 'time' => 'Sonnenuntergang'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Kunst und Altstadt', 'detail' => 'Museum oder Galerie zuerst, danach kurzer Weg Richtung Rhein.'),
                    array('step' => 'Nachmittag', 'title' => 'Little Tokyo und Hafen', 'detail' => 'Essen in der Stadtmitte, Architekturspaziergang im MedienHafen.'),
                    array('step' => 'Abend', 'title' => 'Rheinpromenade', 'detail' => 'Spaziergang am Wasser und ein klarer Blick auf die moderne Stadt.'),
                ),
            ),
            array(
                'slug' => 'duisburg',
                'name' => 'Duisburg',
                'displayName' => 'Duisburg',
                'aliases' => array(),
                'region' => 'Industriekultur am Rhein',
                'headline' => 'Hafen, Stahlarchitektur, Rheinwege und raues Ruhrgebietsgefühl.',
                'summary' => 'Duisburg ist besonders, wenn du Industriekultur nicht als Kulisse, sondern als Stadterlebnis liest: Hafen, Parks, Wasser und alte Stahlstrukturen ergeben einen starken Kontrast.',
                'bestFor' => 'Industriekultur, Hafen, Rhein, urbane Fotospots',
                'duration' => '1-2 Tage',
                'accent' => '#475569',
                'imageIndex' => 12,
                'neighborhoods' => array('Innenhafen', 'Meiderich', 'Ruhrort', 'Rheinpark'),
                'spots' => array(
                    array('type' => 'Industriekultur', 'name' => 'Landschaftspark Duisburg-Nord', 'area' => 'Meiderich', 'note' => 'Ehemalige Hochöfen, Lichtinstallationen und weite Wege durch eine sehr eigene Parklandschaft.', 'time' => '2 Std.'),
                    array('type' => 'Wasser', 'name' => 'Innenhafen', 'area' => 'Zentrum', 'note' => 'Speicher, Restaurants und Wasserblicke als ruhiger Einstieg in die Stadt.', 'time' => '90 Min.'),
                    array('type' => 'Hafen', 'name' => 'Ruhrort', 'area' => 'Ruhrort', 'note' => 'Hafenstadtgefühl, Schiffe und ein Blick auf die industrielle Seite des Rheins.', 'time' => 'Nachmittag'),
                    array('type' => 'Draußen', 'name' => 'Tiger & Turtle', 'area' => 'Angerhausen', 'note' => 'Begehbare Skulptur mit weitem Blick über Rhein und Ruhrgebiet.', 'time' => '60 Min.'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Innenhafen und Ruhrort', 'detail' => 'Starte am Wasser und nähere dich der Hafenstadt langsam.'),
                    array('step' => 'Nachmittag', 'title' => 'Landschaftspark', 'detail' => 'Industriekultur zu Fuß erleben, mit viel Raum für Fotos und Pausen.'),
                    array('step' => 'Abend', 'title' => 'Licht und Aussicht', 'detail' => 'Tiger & Turtle oder der beleuchtete Landschaftspark als starker Abschluss.'),
                ),
            ),
            array(
                'slug' => 'bonn',
                'name' => 'Bonn',
                'displayName' => 'Bonn',
                'aliases' => array(),
                'region' => 'Rhein und Geschichte',
                'headline' => 'Beethovenstadt, Rheinwege, Museen und elegante Gründerzeitstraßen.',
                'summary' => 'Bonn ist ruhig, grün und überraschend vielschichtig: Musikgeschichte, Museen, Rheinpromenaden und Viertel mit schönen Fassaden liegen angenehm nah beieinander.',
                'bestFor' => 'Rheinspaziergänge, Museen, Geschichte, entspannte Wochenenden',
                'duration' => '1-2 Tage',
                'accent' => '#db2777',
                'imageIndex' => 13,
                'neighborhoods' => array('Zentrum', 'Südstadt', 'Rheinaue', 'Poppelsdorf'),
                'spots' => array(
                    array('type' => 'Kultur', 'name' => 'Beethoven-Haus', 'area' => 'Zentrum', 'note' => 'Ein kompakter kultureller Einstieg in die Identität der Stadt.', 'time' => '90 Min.'),
                    array('type' => 'Spaziergang', 'name' => 'Rheinpromenade', 'area' => 'Rhein', 'note' => 'Weite Wege am Wasser mit Blick Richtung Siebengebirge.', 'time' => '60 Min.'),
                    array('type' => 'Viertel', 'name' => 'Südstadt', 'area' => 'Südstadt', 'note' => 'Gründerzeitstraßen, Cafés und ruhige Wohnstadtatmosphäre.', 'time' => 'Nachmittag'),
                    array('type' => 'Museen', 'name' => 'Museumsmeile', 'area' => 'Gronau', 'note' => 'Starke Auswahl für Kunst, Geschichte und Gegenwart.', 'time' => 'Halber Tag'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Zentrum und Beethoven', 'detail' => 'Historischer Auftakt mit kurzer Distanz zum Rhein.'),
                    array('step' => 'Nachmittag', 'title' => 'Südstadt oder Museumsmeile', 'detail' => 'Je nach Stimmung Fassaden, Cafés oder eine größere Ausstellung.'),
                    array('step' => 'Abend', 'title' => 'Rheinblick', 'detail' => 'Ein ruhiger Abschluss am Wasser oder in Poppelsdorf.'),
                ),
            ),
            array(
                'slug' => 'muenster',
                'name' => 'Münster',
                'displayName' => 'Münster',
                'aliases' => array('Muenster', 'Munster'),
                'region' => 'Altstadt und Fahrräder',
                'headline' => 'Giebelhäuser, Prinzipalmarkt, Aasee und sehr entspannter Stadtrhythmus.',
                'summary' => 'Münster ist ideal für ein leichtes Wochenende: historische Fassaden, kurze Wege, Fahrräder und Wasser geben der Stadt einen ruhigen, sehr lebenswerten Charakter.',
                'bestFor' => 'Altstadt, Fahrräder, Cafés, entspannte Kurztrips',
                'duration' => '1-2 Tage',
                'accent' => '#15803d',
                'imageIndex' => 14,
                'neighborhoods' => array('Altstadt', 'Aasee', 'Kreuzviertel', 'Hafen'),
                'spots' => array(
                    array('type' => 'Altstadt', 'name' => 'Prinzipalmarkt', 'area' => 'Zentrum', 'note' => 'Giebelhäuser, Arkaden und der klarste visuelle Einstieg in Münster.', 'time' => '60 Min.'),
                    array('type' => 'Wasser', 'name' => 'Aasee', 'area' => 'Innenstadt', 'note' => 'Spaziergang, Picknick oder Bootsblick direkt nahe der Altstadt.', 'time' => '90 Min.'),
                    array('type' => 'Viertel', 'name' => 'Kreuzviertel', 'area' => 'Nordwesten', 'note' => 'Schöne Straßen, Cafés und ein sehr lokaler Wochenendton.', 'time' => 'Nachmittag'),
                    array('type' => 'Ausgehen', 'name' => 'Hafen', 'area' => 'Hafen', 'note' => 'Restaurants und Bars am Wasser als unkomplizierter Abend.', 'time' => 'Abend'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Altstadt und Markt', 'detail' => 'Prinzipalmarkt, Domplatz und kurze Wege durch das Zentrum.'),
                    array('step' => 'Nachmittag', 'title' => 'Aasee und Viertel', 'detail' => 'Langsam ans Wasser und dann durch ein ruhiges Wohnviertel.'),
                    array('step' => 'Abend', 'title' => 'Hafen', 'detail' => 'Essen oder Drinks am Wasser, ohne den Tagesrhythmus zu verlieren.'),
                ),
            ),
            array(
                'slug' => 'rostock',
                'name' => 'Rostock',
                'displayName' => 'Rostock',
                'aliases' => array(),
                'region' => 'Hanse und Ostsee',
                'headline' => 'Backstein, Hafenluft, Ostsee und ein schneller Wechsel nach Warnemünde.',
                'summary' => 'Rostock verbindet Stadt und Küste: Hanseatische Backsteinarchitektur, Hafenwege und die Nähe zur Ostsee machen den Trip klar und erholsam.',
                'bestFor' => 'Ostsee, Hafen, Backsteinarchitektur, entspannte Küstentage',
                'duration' => '2 Tage',
                'accent' => '#0284c7',
                'imageIndex' => 15,
                'neighborhoods' => array('Altstadt', 'Stadthafen', 'Kröpeliner-Tor-Vorstadt', 'Warnemünde'),
                'spots' => array(
                    array('type' => 'Altstadt', 'name' => 'Neuer Markt', 'area' => 'Zentrum', 'note' => 'Hanseatische Fassaden und Backstein als kompakter Stadteinstieg.', 'time' => '60 Min.'),
                    array('type' => 'Hafen', 'name' => 'Stadthafen', 'area' => 'Warnow', 'note' => 'Promenade, Schiffe und Abendlicht direkt an der Warnow.', 'time' => '90 Min.'),
                    array('type' => 'Küste', 'name' => 'Warnemünde', 'area' => 'Ostsee', 'note' => 'Strand, Leuchtturm und Ostseeluft als natürlicher zweiter Teil der Reise.', 'time' => 'Halber Tag'),
                    array('type' => 'Viertel', 'name' => 'KTV', 'area' => 'Kröpeliner-Tor-Vorstadt', 'note' => 'Cafés, Bars und studentisches Leben nahe dem Zentrum.', 'time' => 'Abend'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Altstadt und Backstein', 'detail' => 'Starte zwischen Markt, Toren und hanseatischen Fassaden.'),
                    array('step' => 'Nachmittag', 'title' => 'Hafen oder Warnemünde', 'detail' => 'Je nach Wetter Promenade in der Stadt oder direkt an die Ostsee.'),
                    array('step' => 'Abend', 'title' => 'KTV oder Stadthafen', 'detail' => 'Ein entspannter Abschluss zwischen Bars, Wasser und Küstenlicht.'),
                ),
            ),
            array(
                'slug' => 'aachen',
                'name' => 'Aachen',
                'displayName' => 'Aachen',
                'aliases' => array(),
                'region' => 'Dom und Thermalquellen',
                'headline' => 'Kaiserliche Geschichte, heiße Quellen, Altstadtgassen und kurze Wege.',
                'summary' => 'Aachen ist kompakt und geschichtsstark: Dom, Rathaus, Thermalwasser und studentische Cafés ergeben eine ruhige, gut portionierbare Städtereise.',
                'bestFor' => 'Dom, Geschichte, Cafés, entspannte Grenzstadt-Atmosphäre',
                'duration' => '1-2 Tage',
                'accent' => '#ca8a04',
                'imageIndex' => 0,
                'neighborhoods' => array('Altstadt', 'Pontviertel', 'Burtscheid', 'Lousberg'),
                'spots' => array(
                    array('type' => 'Wahrzeichen', 'name' => 'Aachener Dom', 'area' => 'Altstadt', 'note' => 'UNESCO-Welterbe, Mosaike und Kaiserpfalzgefühl in sehr kompakter Form.', 'time' => '90 Min.'),
                    array('type' => 'Altstadt', 'name' => 'Rathaus und Katschhof', 'area' => 'Zentrum', 'note' => 'Der Platz zwischen Dom und Rathaus ist der klarste Einstieg in die Stadt.', 'time' => '45 Min.'),
                    array('type' => 'Thermal', 'name' => 'Carolus Thermen', 'area' => 'Monheimsallee', 'note' => 'Ein entspannter Kontrast zur Altstadt, besonders bei kühlerem Wetter.', 'time' => '2 Std.'),
                    array('type' => 'Aussicht', 'name' => 'Lousberg', 'area' => 'Nordstadt', 'note' => 'Kurzer Aufstieg mit Blick über Aachen und die hügelige Umgebung.', 'time' => '60 Min.'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Dom und Katschhof', 'detail' => 'Starte im historischen Zentrum und nimm dir Zeit für die Innenräume.'),
                    array('step' => 'Nachmittag', 'title' => 'Cafés oder Thermalwasser', 'detail' => 'Pontviertel für Alltag und Kaffee oder ein ruhiger Thermenbesuch.'),
                    array('step' => 'Abend', 'title' => 'Altstadt langsam', 'detail' => 'Kurzer Spaziergang durch beleuchtete Gassen und ein unkompliziertes Abendessen.'),
                ),
            ),
            array(
                'slug' => 'trier',
                'name' => 'Trier',
                'displayName' => 'Trier',
                'aliases' => array(),
                'region' => 'Römerstadt an der Mosel',
                'headline' => 'Porta Nigra, römische Spuren, Moselwege und Wein in der Altstadt.',
                'summary' => 'Trier ist klein genug für ein Wochenende und alt genug für viel Tiefe: Römische Bauten, Moselblick und Weinlokale liegen angenehm nah beieinander.',
                'bestFor' => 'Römische Geschichte, Mosel, Wein, kurze Kulturtrips',
                'duration' => '1-2 Tage',
                'accent' => '#7c2d12',
                'imageIndex' => 1,
                'neighborhoods' => array('Altstadt', 'Moselufer', 'Südallee', 'Petrisberg'),
                'spots' => array(
                    array('type' => 'Wahrzeichen', 'name' => 'Porta Nigra', 'area' => 'Altstadt', 'note' => 'Der stärkste Auftakt in die römische Geschichte der Stadt.', 'time' => '60 Min.'),
                    array('type' => 'Geschichte', 'name' => 'Kaiserthermen', 'area' => 'Zentrum', 'note' => 'Römische Ruinen, die gut zeigen, wie groß Trier einmal gedacht war.', 'time' => '75 Min.'),
                    array('type' => 'Platz', 'name' => 'Hauptmarkt', 'area' => 'Altstadt', 'note' => 'Bunte Fassaden, kurze Wege und ein guter Ort für eine Pause.', 'time' => '45 Min.'),
                    array('type' => 'Wasser', 'name' => 'Moselufer', 'area' => 'Mosel', 'note' => 'Ein ruhiger Spaziergang mit Weinregion-Gefühl direkt neben der Stadt.', 'time' => 'Sonnenuntergang'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Römischer Einstieg', 'detail' => 'Porta Nigra, Hauptmarkt und die ersten historischen Spuren zu Fuß verbinden.'),
                    array('step' => 'Nachmittag', 'title' => 'Thermen und Mosel', 'detail' => 'Kultur vertiefen und danach ans Wasser wechseln.'),
                    array('step' => 'Abend', 'title' => 'Wein und Altstadt', 'detail' => 'Ein langsamer Abend mit regionalem Wein und kurzen Wegen.'),
                ),
            ),
            array(
                'slug' => 'luebeck',
                'name' => 'Lübeck',
                'displayName' => 'Lübeck',
                'aliases' => array('Luebeck', 'Lubeck'),
                'region' => 'Backstein und Wasser',
                'headline' => 'Holstentor, Inselaltstadt, Gänge, Marzipan und ein schneller Weg zur Ostsee.',
                'summary' => 'Lübeck fühlt sich wie eine konzentrierte Hansestadt an: Backstein, Wasserarme, versteckte Höfe und die Nähe nach Travemünde machen den Trip sehr rund.',
                'bestFor' => 'Backsteinarchitektur, Wasser, Altstadtgassen, Ostsee-Ausflug',
                'duration' => '1-2 Tage',
                'accent' => '#be123c',
                'imageIndex' => 2,
                'neighborhoods' => array('Altstadtinsel', 'Museumshafen', 'St. Lorenz', 'Travemünde'),
                'spots' => array(
                    array('type' => 'Wahrzeichen', 'name' => 'Holstentor', 'area' => 'Altstadt', 'note' => 'Das klare Eingangsmotiv der Stadt mit Wasser und Backstein im Blick.', 'time' => '45 Min.'),
                    array('type' => 'Gassen', 'name' => 'Gänge und Höfe', 'area' => 'Altstadtinsel', 'note' => 'Kleine Durchgänge und ruhige Innenhöfe zeigen Lübeck von innen.', 'time' => '90 Min.'),
                    array('type' => 'Kultur', 'name' => 'Europäisches Hansemuseum', 'area' => 'Burgtor', 'note' => 'Ein moderner Einstieg in die Geschichte der Hanse.', 'time' => '2 Std.'),
                    array('type' => 'Küste', 'name' => 'Travemünde', 'area' => 'Ostsee', 'note' => 'Strand, Promenade und Hafenluft als leichter zweiter Teil des Trips.', 'time' => 'Halber Tag'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Holstentor und Altstadtinsel', 'detail' => 'Am Wasser starten und langsam durch die Backsteinstadt gehen.'),
                    array('step' => 'Nachmittag', 'title' => 'Höfe oder Hanse', 'detail' => 'Versteckte Gänge suchen oder die Geschichte im Museum vertiefen.'),
                    array('step' => 'Abend', 'title' => 'Wasser und Marzipan', 'detail' => 'Ein ruhiger Abschluss am Museumshafen oder in der Altstadt.'),
                ),
            ),
            array(
                'slug' => 'freiburg',
                'name' => 'Freiburg',
                'displayName' => 'Freiburg',
                'aliases' => array('Freiburg im Breisgau'),
                'region' => 'Altstadt und Schwarzwald',
                'headline' => 'Bächle, Münster, sonnige Plätze und schnelle Wege in die Hügel.',
                'summary' => 'Freiburg ist leicht, sonnig und sehr gut zu Fuß: Altstadt, Markt, kleine Wasserläufe und Schwarzwaldnähe ergeben einen entspannten Kurztrip.',
                'bestFor' => 'Altstadt, Markt, Schwarzwald, sonnige Wochenenden',
                'duration' => '2 Tage',
                'accent' => '#16a34a',
                'imageIndex' => 3,
                'neighborhoods' => array('Altstadt', 'Wiehre', 'Schlossberg', 'Vauban'),
                'spots' => array(
                    array('type' => 'Wahrzeichen', 'name' => 'Freiburger Münster', 'area' => 'Altstadt', 'note' => 'Der zentrale Orientierungspunkt mit Markt und viel Leben rundherum.', 'time' => '90 Min.'),
                    array('type' => 'Aussicht', 'name' => 'Schlossberg', 'area' => 'Osten', 'note' => 'Kurzer Aufstieg für Blick über Dächer, Münster und Schwarzwaldkante.', 'time' => '75 Min.'),
                    array('type' => 'Viertel', 'name' => 'Wiehre', 'area' => 'Südosten', 'note' => 'Ruhige Straßen, Cafés und ein weicherer lokaler Ton.', 'time' => 'Nachmittag'),
                    array('type' => 'Draußen', 'name' => 'Schauinsland', 'area' => 'Schwarzwald', 'note' => 'Ein schneller Naturwechsel, wenn der Trip mehr Luft bekommen soll.', 'time' => 'Halber Tag'),
                ),
                'route' => array(
                    array('step' => 'Morgen', 'title' => 'Münster und Markt', 'detail' => 'Altstadt, Marktstände und Bächle ohne Eile verbinden.'),
                    array('step' => 'Nachmittag', 'title' => 'Schlossberg oder Wiehre', 'detail' => 'Aussicht suchen oder ein ruhiges Viertel mit Cafés wählen.'),
                    array('step' => 'Abend', 'title' => 'Sonnenstadt ausklingen lassen', 'detail' => 'Essen in der Altstadt und ein letzter Weg über die Plätze.'),
                ),
            ),
        );
    }
}
