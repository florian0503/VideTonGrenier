<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column]
    private ?bool $isVerified = false;

    #[ORM\Column]
    private ?bool $isBanned = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $bannedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $banReason = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $bannedBy = null;

    /**
     * @var Collection<int, Annonce>
     */
    #[ORM\OneToMany(targetEntity: Annonce::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $annonces;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender', orphanRemoval: true)]
    private Collection $sentMessages;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'receiver', orphanRemoval: true)]
    private Collection $receivedMessages;

    /**
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'reporter', orphanRemoval: true)]
    private Collection $reportsMade;

    /**
     * @var Collection<int, Report>
     */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: 'reportedUser', orphanRemoval: true)]
    private Collection $reportsReceived;

    public function __construct()
    {
        $this->annonces = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
        $this->reportsMade = new ArrayCollection();
        $this->reportsReceived = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->isBanned = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    /**
     * @return Collection<int, Annonce>
     */
    public function getAnnonces(): Collection
    {
        return $this->annonces;
    }

    public function addAnnonce(Annonce $annonce): static
    {
        if (!$this->annonces->contains($annonce)) {
            $this->annonces->add($annonce);
            $annonce->setUser($this);
        }
        return $this;
    }

    public function removeAnnonce(Annonce $annonce): static
    {
        if ($this->annonces->removeElement($annonce)) {
            if ($annonce->getUser() === $this) {
                $annonce->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function addSentMessage(Message $sentMessage): static
    {
        if (!$this->sentMessages->contains($sentMessage)) {
            $this->sentMessages->add($sentMessage);
            $sentMessage->setSender($this);
        }
        return $this;
    }

    public function removeSentMessage(Message $sentMessage): static
    {
        if ($this->sentMessages->removeElement($sentMessage)) {
            if ($sentMessage->getSender() === $this) {
                $sentMessage->setSender(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function addReceivedMessage(Message $receivedMessage): static
    {
        if (!$this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages->add($receivedMessage);
            $receivedMessage->setReceiver($this);
        }
        return $this;
    }

    public function removeReceivedMessage(Message $receivedMessage): static
    {
        if ($this->receivedMessages->removeElement($receivedMessage)) {
            if ($receivedMessage->getReceiver() === $this) {
                $receivedMessage->setReceiver(null);
            }
        }
        return $this;
    }

    public function isBanned(): ?bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(bool $isBanned): static
    {
        $this->isBanned = $isBanned;
        return $this;
    }

    public function getBannedAt(): ?\DateTimeInterface
    {
        return $this->bannedAt;
    }

    public function setBannedAt(?\DateTimeInterface $bannedAt): static
    {
        $this->bannedAt = $bannedAt;
        return $this;
    }

    public function getBanReason(): ?string
    {
        return $this->banReason;
    }

    public function setBanReason(?string $banReason): static
    {
        $this->banReason = $banReason;
        return $this;
    }

    public function getBannedBy(): ?User
    {
        return $this->bannedBy;
    }

    public function setBannedBy(?User $bannedBy): static
    {
        $this->bannedBy = $bannedBy;
        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReportsMade(): Collection
    {
        return $this->reportsMade;
    }

    public function addReportsMade(Report $reportsMade): static
    {
        if (!$this->reportsMade->contains($reportsMade)) {
            $this->reportsMade->add($reportsMade);
            $reportsMade->setReporter($this);
        }
        return $this;
    }

    public function removeReportsMade(Report $reportsMade): static
    {
        if ($this->reportsMade->removeElement($reportsMade)) {
            if ($reportsMade->getReporter() === $this) {
                $reportsMade->setReporter(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReportsReceived(): Collection
    {
        return $this->reportsReceived;
    }

    public function addReportsReceived(Report $reportsReceived): static
    {
        if (!$this->reportsReceived->contains($reportsReceived)) {
            $this->reportsReceived->add($reportsReceived);
            $reportsReceived->setReportedUser($this);
        }
        return $this;
    }

    public function removeReportsReceived(Report $reportsReceived): static
    {
        if ($this->reportsReceived->removeElement($reportsReceived)) {
            if ($reportsReceived->getReportedUser() === $this) {
                $reportsReceived->setReportedUser(null);
            }
        }
        return $this;
    }

    public function ban(User $admin, string $reason): static
    {
        $this->isBanned = true;
        $this->bannedAt = new \DateTime();
        $this->banReason = $reason;
        $this->bannedBy = $admin;
        return $this;
    }

    public function unban(): static
    {
        $this->isBanned = false;
        $this->bannedAt = null;
        $this->banReason = null;
        $this->bannedBy = null;
        return $this;
    }
}
