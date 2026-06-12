<?php

namespace App\Controller;

use App\Repository\PlaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GuideController extends AbstractController
{
    private $places;

    public function __construct(PlaceRepository $places)
    {
        $this->places = $places;
    }

    public function index(Request $request): Response
    {
        $cityGroups = $this->places->findCitySpotGroups();
        $cityQuery = trim((string) $request->query->get('city', ''));
        $selectedCity = $cityQuery !== '' ? $this->places->findCityRepresentative($cityQuery) : null;
        $selectedGroup = $selectedCity ? $this->findCityGroup($cityGroups, (string) $selectedCity->getCitySlug()) : null;
        $spotPlaces = $selectedCity
            ? $this->places->findCitySpotPlaces($selectedCity->getCitySlug())
            : $this->places->findCitySpotPlaces(null);

        return $this->render('guide/index.html.twig', array(
            'cityGroups' => $cityGroups,
            'visibleCityGroups' => array_slice($cityGroups, 0, 8),
            'packageGroups' => $selectedGroup ? array($selectedGroup) : array_slice($cityGroups, 0, 8),
            'spotPlaces' => array_slice($spotPlaces, 0, $selectedCity ? 9 : 12),
            'selectedCity' => $selectedCity,
            'citySearchQuery' => $cityQuery,
            'cityNotFound' => $cityQuery !== '' && !$selectedCity,
        ));
    }

    public function coolPlaces(Request $request): Response
    {
        $cityQuery = trim((string) $request->query->get('city', 'duisburg'));
        $city = $this->places->findCityRepresentative($cityQuery ?: 'duisburg');
        $citySlug = $city ? (string) $city->getCitySlug() : ($cityQuery ?: 'duisburg');
        $places = $this->places->findCoolPlaces($citySlug);
        $selectedPlace = isset($places[0]) ? $places[0] : null;
        $placeQuery = trim((string) $request->query->get('place', ''));

        foreach ($places as $place) {
            if ($place->getSlug() === $placeQuery) {
                $selectedPlace = $place;
                break;
            }
        }

        $photos = $selectedPlace ? ($selectedPlace->getPhotos() ?? array()) : array();
        $photoIndex = max(0, (int) $request->query->get('photo', 0));

        if ($photos) {
            $photoIndex = min($photoIndex, count($photos) - 1);
        } else {
            $photoIndex = 0;
        }

        return $this->render('guide/cool_places.html.twig', array(
            'city' => $city ?: $selectedPlace,
            'places' => $places,
            'selectedPlace' => $selectedPlace,
            'activePhotoIndex' => $photoIndex,
        ));
    }

    public function apiCities(): JsonResponse
    {
        return new JsonResponse($this->places->findCitiesData());
    }

    public function apiCity(string $slug): JsonResponse
    {
        $city = $this->places->findCityData($slug);

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
        $cityQuery = (string) $request->query->get('city', '');
        $searchQuery = trim((string) $request->query->get('q', ''));
        $selectedCity = $cityQuery !== '' ? $this->places->findCityRepresentative($cityQuery) : null;
        $items = $this->places->findAttractionPlaces(
            $selectedCity ? $selectedCity->getCitySlug() : null,
            $searchQuery
        );

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
            'searchQuery' => $searchQuery,
            'selectedCity' => $selectedCity,
            'cityQuery' => $cityQuery,
            'cityNotFound' => $cityQuery !== '' && !$selectedCity,
        ));
    }

    public function highlightsPaket(Request $request): Response
    {
        $cityGroups = $this->places->findCitySpotGroups();
        $cityQuery = trim((string) $request->query->get('city', ''));
        $selectedCity = $cityQuery !== '' ? $this->places->findCityRepresentative($cityQuery) : null;

        return $this->render('guide/highlights_paket.html.twig', array(
            'cityGroups' => $cityGroups,
            'visibleCityGroups' => array_slice($cityGroups, 0, 8),
            'selectedCity' => $selectedCity,
            'citySearchQuery' => $cityQuery,
            'cityNotFound' => $cityQuery !== '' && !$selectedCity,
        ));
    }

    private function findCityGroup(array $cityGroups, string $citySlug): ?array
    {
        foreach ($cityGroups as $group) {
            if ($group['city']->getCitySlug() === $citySlug) {
                return $group;
            }
        }

        return null;
    }
}
