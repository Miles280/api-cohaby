<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Enum\BookingStatus;
use App\Repository\BookingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['booking:read']],
    denormalizationContext: ['groups' => ['booking:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['listing' => 'exact', 'user' => 'exact'])]
#[ORM\HasLifecycleCallbacks]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['booking:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['booking:read', 'booking:write'])]
    private ?\DateTime $beginningDate = null;

    #[ORM\Column]
    #[Groups(['booking:read', 'booking:write'])]
    private ?int $totalNights = null;

    #[ORM\Column(enumType: BookingStatus::class)]
    #[Groups(['booking:read', 'booking:write'])]
    private ?BookingStatus $status = null;

    #[ORM\Column]
    #[Groups(['booking:read', 'booking:write'])]
    private ?int $nbrGuests = null;

    #[ORM\Column]
    #[Groups(['booking:read', 'booking:write'])]
    private ?float $totalPrice = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['booking:read', 'booking:write'])]
    private ?Listing $listing = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['booking:read', 'booking:write'])]
    private ?User $user = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'booking', orphanRemoval: true)]
    #[Groups(['booking:read', 'booking:write'])]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBeginningDate(): ?\DateTime
    {
        return $this->beginningDate;
    }

    public function setBeginningDate(\DateTime $beginningDate): static
    {
        $this->beginningDate = $beginningDate;

        return $this;
    }

    public function getTotalNights(): ?int
    {
        return $this->totalNights;
    }

    public function setTotalNights(int $totalNights): static
    {
        $this->totalNights = $totalNights;

        return $this;
    }

    public function getStatus(): ?BookingStatus
    {
        return $this->status;
    }

    public function setStatus(BookingStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getNbrGuests(): ?int
    {
        return $this->nbrGuests;
    }

    public function setNbrGuests(int $nbrGuests): static
    {
        $this->nbrGuests = $nbrGuests;

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getListing(): ?Listing
    {
        return $this->listing;
    }

    public function setListing(?Listing $listing): static
    {
        $this->listing = $listing;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setBooking($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getBooking() === $this) {
                $comment->setBooking(null);
            }
        }

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function calculateTotalPrice(): void
    {
        if ($this->listing && $this->totalNights && $this->nbrGuests) {
            $this->totalPrice = $this->listing->getPricePerNight() * $this->totalNights * $this->nbrGuests;
        }
    }

}
