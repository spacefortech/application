<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PlaceRepository::class)
 * @ORM\Table(
 *     name="places",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="UNIQ_PLACES_CITY_SLUG", columns={"city_slug", "slug"})
 *     },
     *     indexes={
     *         @ORM\Index(name="IDX_PLACES_CITY_SLUG", columns={"city_slug"}),
     *         @ORM\Index(name="IDX_PLACES_TYPE", columns={"type"}),
     *         @ORM\Index(name="IDX_PLACES_CITY_SPOT", columns={"is_city_spot"}),
     *         @ORM\Index(name="IDX_PLACES_COOL_PLACE", columns={"is_cool_place", "cool_place_order"}),
     *         @ORM\Index(name="IDX_PLACES_SORT_ORDER", columns={"sort_order"})
     *     }
     * )
 */
class Place
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="city_slug", type="string", length=120)
     */
    private $citySlug;

    /**
     * @ORM\Column(name="city_name", type="string", length=180, nullable=true)
     */
    private $cityName;

    /**
     * @ORM\Column(name="city_display_name", type="string", length=180)
     */
    private $cityDisplayName;

    /**
     * @ORM\Column(name="city_aliases", type="json", nullable=true)
     */
    private $cityAliases;

    /**
     * @ORM\Column(name="city_region", type="string", length=180, nullable=true)
     */
    private $cityRegion;

    /**
     * @ORM\Column(name="city_headline", type="text", nullable=true)
     */
    private $cityHeadline;

    /**
     * @ORM\Column(name="city_summary", type="text", nullable=true)
     */
    private $citySummary;

    /**
     * @ORM\Column(name="city_best_for", type="string", length=255, nullable=true)
     */
    private $cityBestFor;

    /**
     * @ORM\Column(name="city_duration", type="string", length=80, nullable=true)
     */
    private $cityDuration;

    /**
     * @ORM\Column(name="city_accent", type="string", length=20, nullable=true)
     */
    private $cityAccent;

    /**
     * @ORM\Column(name="city_image_index", type="integer", nullable=true)
     */
    private $cityImageIndex;

    /**
     * @ORM\Column(name="city_neighborhoods", type="json", nullable=true)
     */
    private $cityNeighborhoods;

    /**
     * @ORM\Column(name="city_route", type="json", nullable=true)
     */
    private $cityRoute;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $area;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     */
    private $time;

    /**
     * @ORM\Column(type="string", length=80, nullable=true)
     */
    private $duration;

    /**
     * @ORM\Column(name="best_time", type="string", length=180, nullable=true)
     */
    private $bestTime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $intro;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $why;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $tip;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $facts;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $photos;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $feedbacks;

    /**
     * @ORM\Column(name="is_city_spot", type="boolean", options={"default"=true})
     */
    private $citySpot = true;

    /**
     * @ORM\Column(name="is_cool_place", type="boolean", options={"default"=false})
     */
    private $coolPlace = false;

    /**
     * @ORM\Column(name="cool_place_order", type="integer", nullable=true)
     */
    private $coolPlaceOrder;

    /**
     * @ORM\Column(name="sort_order", type="integer", options={"default"=0})
     */
    private $sortOrder = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCitySlug(): ?string
    {
        return $this->citySlug;
    }

    public function setCitySlug(string $citySlug): self
    {
        $this->citySlug = $citySlug;

        return $this;
    }

    public function getCityName(): ?string
    {
        return $this->cityName;
    }

    public function setCityName(?string $cityName): self
    {
        $this->cityName = $cityName;

        return $this;
    }

    public function getCityDisplayName(): ?string
    {
        return $this->cityDisplayName;
    }

    public function setCityDisplayName(string $cityDisplayName): self
    {
        $this->cityDisplayName = $cityDisplayName;

        return $this;
    }

    public function getCityAliases(): ?array
    {
        return $this->cityAliases;
    }

    public function setCityAliases(?array $cityAliases): self
    {
        $this->cityAliases = $cityAliases;

        return $this;
    }

    public function getCityRegion(): ?string
    {
        return $this->cityRegion;
    }

    public function setCityRegion(?string $cityRegion): self
    {
        $this->cityRegion = $cityRegion;

        return $this;
    }

    public function getCityHeadline(): ?string
    {
        return $this->cityHeadline;
    }

    public function setCityHeadline(?string $cityHeadline): self
    {
        $this->cityHeadline = $cityHeadline;

        return $this;
    }

    public function getCitySummary(): ?string
    {
        return $this->citySummary;
    }

    public function setCitySummary(?string $citySummary): self
    {
        $this->citySummary = $citySummary;

        return $this;
    }

    public function getCityBestFor(): ?string
    {
        return $this->cityBestFor;
    }

    public function setCityBestFor(?string $cityBestFor): self
    {
        $this->cityBestFor = $cityBestFor;

        return $this;
    }

    public function getCityDuration(): ?string
    {
        return $this->cityDuration;
    }

    public function setCityDuration(?string $cityDuration): self
    {
        $this->cityDuration = $cityDuration;

        return $this;
    }

    public function getCityAccent(): ?string
    {
        return $this->cityAccent;
    }

    public function setCityAccent(?string $cityAccent): self
    {
        $this->cityAccent = $cityAccent;

        return $this;
    }

    public function getCityImageIndex(): ?int
    {
        return $this->cityImageIndex;
    }

    public function setCityImageIndex(?int $cityImageIndex): self
    {
        $this->cityImageIndex = $cityImageIndex;

        return $this;
    }

    public function getCityNeighborhoods(): ?array
    {
        return $this->cityNeighborhoods;
    }

    public function setCityNeighborhoods(?array $cityNeighborhoods): self
    {
        $this->cityNeighborhoods = $cityNeighborhoods;

        return $this;
    }

    public function getCityRoute(): ?array
    {
        return $this->cityRoute;
    }

    public function setCityRoute(?array $cityRoute): self
    {
        $this->cityRoute = $cityRoute;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function setArea(?string $area): self
    {
        $this->area = $area;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(?string $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getBestTime(): ?string
    {
        return $this->bestTime;
    }

    public function setBestTime(?string $bestTime): self
    {
        $this->bestTime = $bestTime;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setIntro(?string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }

    public function getWhy(): ?string
    {
        return $this->why;
    }

    public function setWhy(?string $why): self
    {
        $this->why = $why;

        return $this;
    }

    public function getTip(): ?string
    {
        return $this->tip;
    }

    public function setTip(?string $tip): self
    {
        $this->tip = $tip;

        return $this;
    }

    public function getFacts(): ?array
    {
        return $this->facts;
    }

    public function setFacts(?array $facts): self
    {
        $this->facts = $facts;

        return $this;
    }

    public function getPhotos(): ?array
    {
        return $this->photos;
    }

    public function setPhotos(?array $photos): self
    {
        $this->photos = $photos;

        return $this;
    }

    public function getFeedbacks(): ?array
    {
        return $this->feedbacks;
    }

    public function setFeedbacks(?array $feedbacks): self
    {
        $this->feedbacks = $feedbacks;

        return $this;
    }

    public function isCitySpot(): bool
    {
        return $this->citySpot;
    }

    public function setCitySpot(bool $citySpot): self
    {
        $this->citySpot = $citySpot;

        return $this;
    }

    public function isCoolPlace(): bool
    {
        return $this->coolPlace;
    }

    public function setCoolPlace(bool $coolPlace): self
    {
        $this->coolPlace = $coolPlace;

        return $this;
    }

    public function getCoolPlaceOrder(): ?int
    {
        return $this->coolPlaceOrder;
    }

    public function setCoolPlaceOrder(?int $coolPlaceOrder): self
    {
        $this->coolPlaceOrder = $coolPlaceOrder;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
