<?php

namespace App\Repository;

use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Place|null find($id, $lockMode = null, $lockVersion = null)
 * @method Place|null findOneBy(array $criteria, array $orderBy = null)
 * @method Place[]    findAll()
 * @method Place[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findCitiesData(): array
    {
        return $this->buildCitiesData($this->findCitySpotPlaces(null));
    }

    public function findCityData(string $value): ?array
    {
        $normalizedValue = $this->normalize($value);

        foreach ($this->findCitiesData() as $city) {
            if ($city['slug'] === $normalizedValue) {
                return $city;
            }

            $aliases = array_merge(array($city['name']), $city['aliases']);

            foreach ($aliases as $alias) {
                if ($this->normalize((string) $alias) === $normalizedValue) {
                    return $city;
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, array{city: Place, spots: array<int, Place>}>
     */
    public function findCitySpotGroups(): array
    {
        $groups = array();

        foreach ($this->findCitySpotPlaces(null) as $place) {
            $citySlug = (string) $place->getCitySlug();

            if (!isset($groups[$citySlug])) {
                $groups[$citySlug] = array(
                    'city' => $place,
                    'spots' => array(),
                );
            }

            $groups[$citySlug]['spots'][] = $place;
        }

        return array_values($groups);
    }

    public function findCityRepresentative(string $value): ?Place
    {
        $normalizedValue = $this->normalize($value);

        if ($normalizedValue === '') {
            return null;
        }

        foreach ($this->findCitySpotGroups() as $group) {
            /** @var Place $city */
            $city = $group['city'];
            $names = array_merge(
                array($city->getCitySlug(), $city->getCityName(), $city->getCityDisplayName()),
                $city->getCityAliases() ?? array()
            );

            foreach ($names as $name) {
                if ($this->normalize((string) $name) === $normalizedValue) {
                    return $city;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function findCoolPlacesData(string $citySlug): array
    {
        $city = $this->findCityData($citySlug);
        $places = $this->findCoolPlaces($city ? $city['slug'] : $citySlug);

        $firstPlace = isset($places[0]) ? $places[0] : null;

        if (!$city && $firstPlace instanceof Place) {
            $city = $this->cityDataFromPlace($firstPlace);
            $city['spots'] = array();
        }

        if (!$city) {
            $city = array(
                'slug' => $citySlug,
                'displayName' => $citySlug,
                'region' => '',
                'accent' => '#475569',
                'summary' => '',
            );
        }

        return array(
            'city' => array(
                'slug' => $city['slug'],
                'displayName' => $city['displayName'],
                'region' => $city['region'],
                'accent' => $city['accent'],
                'summary' => $city['summary'],
            ),
            'places' => array_map(array($this, 'coolPlaceDataFromPlace'), $places),
        );
    }

    /**
     * @return Place[]
     */
    public function findCoolPlaces(string $citySlug): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.citySlug = :citySlug')
            ->andWhere('p.coolPlace = :coolPlace')
            ->setParameter('citySlug', $citySlug)
            ->setParameter('coolPlace', true)
            ->orderBy('p.coolPlaceOrder', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAttractionItems(?string $citySlug, string $searchQuery): array
    {
        $normalizedSearchQuery = $this->normalize($searchQuery);
        $items = array();

        foreach ($this->findCitySpotPlaces($citySlug) as $place) {
            if ($normalizedSearchQuery !== '' && strpos($this->normalize($place->getSearchText()), $normalizedSearchQuery) === false) {
                continue;
            }

            $spritePosition = $this->getSpritePosition($place->getCityImageIndex() ?? 0);

            $items[] = array(
                'id' => $place->getCitySlug() . '-' . $place->getSlug(),
                'slug' => $this->normalize($place->getCitySlug() . '-' . ($place->getName() ?? $place->getSlug())),
                'city' => array(
                    'slug' => $place->getCitySlug(),
                    'displayName' => $place->getCityDisplayName(),
                    'region' => $place->getCityRegion() ?? '',
                    'accent' => $place->getCityAccent() ?? '#0b0c3f',
                    'spriteX' => $spritePosition['x'],
                    'spriteY' => $spritePosition['y'],
                ),
                'spot' => $this->spotDataFromPlace($place),
            );
        }

        return $items;
    }

    /**
     * @return Place[]
     */
    public function findAttractionPlaces(?string $citySlug, string $searchQuery): array
    {
        $normalizedSearchQuery = $this->normalize($searchQuery);
        $items = array();

        foreach ($this->findCitySpotPlaces($citySlug) as $place) {
            if ($normalizedSearchQuery !== '' && strpos($this->normalize($place->getSearchText()), $normalizedSearchQuery) === false) {
                continue;
            }

            $items[] = $place;
        }

        return $items;
    }

    /**
     * @return Place[]
     */
    public function findCitySpotPlaces(?string $citySlug = null): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->andWhere('p.citySpot = :citySpot')
            ->setParameter('citySpot', true)
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.id', 'ASC');

        if ($citySlug !== null) {
            $queryBuilder
                ->andWhere('p.citySlug = :citySlug')
                ->setParameter('citySlug', $citySlug);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Place[] $places
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildCitiesData(array $places): array
    {
        $cities = array();

        foreach ($places as $place) {
            $citySlug = (string) $place->getCitySlug();

            if (!isset($cities[$citySlug])) {
                $cities[$citySlug] = $this->cityDataFromPlace($place);
                $cities[$citySlug]['spots'] = array();
            }

            $cities[$citySlug]['spots'][] = $this->spotDataFromPlace($place);
        }

        return array_values($cities);
    }

    /**
     * @return array<string, mixed>
     */
    private function cityDataFromPlace(Place $place): array
    {
        return array(
            'slug' => $place->getCitySlug(),
            'name' => $place->getCityName(),
            'displayName' => $place->getCityDisplayName(),
            'aliases' => $place->getCityAliases() ?? array(),
            'region' => $place->getCityRegion() ?? '',
            'headline' => $place->getCityHeadline() ?? '',
            'summary' => $place->getCitySummary() ?? '',
            'bestFor' => $place->getCityBestFor() ?? '',
            'duration' => $place->getCityDuration() ?? '',
            'accent' => $place->getCityAccent() ?? '#0b0c3f',
            'imageIndex' => $place->getCityImageIndex() ?? 0,
            'neighborhoods' => $place->getCityNeighborhoods() ?? array(),
            'route' => $place->getCityRoute() ?? array(),
        );
    }

    /**
     * @return array<string, string>
     */
    private function spotDataFromPlace(Place $place): array
    {
        return array(
            'type' => $place->getType() ?? '',
            'name' => $place->getName() ?? '',
            'area' => $place->getArea() ?? '',
            'note' => $place->getNote() ?? '',
            'time' => $place->getTime() ?? '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function coolPlaceDataFromPlace(Place $place): array
    {
        return array(
            'slug' => $place->getSlug(),
            'name' => $place->getName(),
            'type' => $place->getType() ?? '',
            'area' => $place->getArea() ?? '',
            'duration' => $place->getDuration() ?? '',
            'bestTime' => $place->getBestTime() ?? '',
            'address' => $place->getAddress() ?? '',
            'intro' => $place->getIntro() ?? '',
            'why' => $place->getWhy() ?? '',
            'tip' => $place->getTip() ?? '',
            'facts' => $place->getFacts() ?? array(),
            'photos' => $place->getPhotos() ?? array(),
            'feedbacks' => $place->getFeedbacks() ?? array(),
        );
    }

    /**
     * @return array{x: float|int, y: float|int}
     */
    private function getSpritePosition(int $imageIndex): array
    {
        $column = $imageIndex % 4;
        $row = (int) floor($imageIndex / 4);

        return array(
            'x' => $column === 3 ? 100 : $column * 33.3333,
            'y' => $row === 3 ? 100 : $row * 33.3333,
        );
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

        return trim((string) $value, '-');
    }
}
