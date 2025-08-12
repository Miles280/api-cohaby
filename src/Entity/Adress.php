<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\AdressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdressRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch()
    ]
)]
class Adress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read'])]
    private ?string $street = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read'])]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read'])]
    private ?string $region = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read'])]
    private ?string $country = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?float $latitude = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?float $longitude = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'adress')]
    private Collection $users;

    /**
     * @var Collection<int, Listing>
     */
    #[ORM\OneToMany(targetEntity: Listing::class, mappedBy: 'adress')]
    private Collection $listings;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->listings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setAdress($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getAdress() === $this) {
                $user->setAdress(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Listing>
     */
    public function getListings(): Collection
    {
        return $this->listings;
    }

    public function addListing(Listing $listing): static
    {
        if (!$this->listings->contains($listing)) {
            $this->listings->add($listing);
            $listing->setAdress($this);
        }

        return $this;
    }

    public function removeListing(Listing $listing): static
    {
        if ($this->listings->removeElement($listing)) {
            // set the owning side to null (unless already changed)
            if ($listing->getAdress() === $this) {
                $listing->setAdress(null);
            }
        }

        return $this;
    }
}
