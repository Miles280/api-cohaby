<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ListingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ListingRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(
            security: "is_granted('ROLE_OWNER')  or is_granted('ROLE_ADMIN')", 
            securityMessage: "Seuls les propriétaires peuvent créer un listing."
        ),
        new Put(
            security: "object.getOwner() == user  or is_granted('ROLE_ADMIN')",
            securityMessage: "Vous ne pouvez modifier que vos propres annonces."
        ),
        new Patch(
        security: "object.getOwner() == user  or is_granted('ROLE_ADMIN')",
        securityMessage: "Vous ne pouvez modifier que vos propres annonces."
        ),
        new Delete(
            security: "object.getOwner() == user or is_granted('ROLE_ADMIN')",
            securityMessage: "Vous ne pouvez supprimer que vos propres annonces ou si vous êtes admin."
        ),
    ],
    normalizationContext: ['groups' => ['listing:read'], 'enable_max_depth' => true],
    denormalizationContext: ['groups' => ['listing:write']],
    paginationEnabled: false
)]
#[ApiFilter(SearchFilter::class, properties: [
    'Address.city' => 'partial',   
    'Address.street' => 'partial', 
    'equipments.name' => 'exact', 
    'services.name' => 'exact',   
    'owner' => 'exact'
])]
#[ApiFilter(RangeFilter::class, properties: [
    'pricePerNight',
    'maxCapacity'
])]
class Listing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['listing:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['listing:read', 'listing:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['listing:read', 'listing:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['listing:read', 'listing:write'])]
    private ?float $pricePerNight = null;

    #[ORM\Column]
    #[Groups(['listing:read', 'listing:write'])]
    private ?int $maxCapacity = null;

    #[ORM\ManyToOne(inversedBy: 'listings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['listing:read', 'listing:write'])]
    private ?User $owner = null;

    /**
     * @var Collection<int, Service>
     */
    #[ORM\ManyToMany(targetEntity: Service::class, mappedBy: 'listings')]
    #[Groups(['listing:read', 'listing:write'])]
    private Collection $services;

    /**
     * @var Collection<int, Equipment>
     */
    #[ORM\ManyToMany(targetEntity: Equipment::class, mappedBy: 'listings')]
    #[Groups(['listing:read', 'listing:write'])]
    private Collection $equipments;

    /**
     * @var Collection<int, Picture>
     */
    #[ORM\OneToMany(targetEntity: Picture::class, mappedBy: 'listing',cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['listing:read', 'listing:write'])]
    private Collection $pictures;

    /**
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: 'listing')]
    private Collection $bookings;

    #[ORM\ManyToOne(inversedBy: 'listings', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['listing:read', 'listing:write'])]
    private ?Address $address = null;

    public function __construct()
    {
        $this->equipments = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPricePerNight(): ?float
    {
        return $this->pricePerNight;
    }

    public function setPricePerNight(float $pricePerNight): static
    {
        $this->pricePerNight = $pricePerNight;

        return $this;
    }

    public function getMaxCapacity(): ?int
    {
        return $this->maxCapacity;
    }

    public function setMaxCapacity(int $maxCapacity): static
    {
        $this->maxCapacity = $maxCapacity;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->addListing($this);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        if ($this->services->removeElement($service)) {
            $service->removeListing($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Equipment>
     */
    public function getEquipments(): Collection
    {
        return $this->equipments;
    }

    public function addEquipment(Equipment $equipment): static
    {
        if (!$this->equipments->contains($equipment)) {
            $this->equipments->add($equipment);
            $equipment->addListing($this);
        }

        return $this;
    }

    public function removeEquipment(Equipment $equipment): static
    {
        if ($this->equipments->removeElement($equipment)) {
            $equipment->removeListing($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Picture>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): static
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures->add($picture);
            $picture->setListing($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): static
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getListing() === $this) {
                $picture->setListing(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setListing($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getListing() === $this) {
                $booking->setListing(null);
            }
        }

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): static
    {
        $this->address = $address;

        return $this;
    }
}
