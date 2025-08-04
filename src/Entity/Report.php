<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_DISMISSED = 'dismissed';
    public const STATUS_ACTION_TAKEN = 'action_taken';

    public const TYPE_CONVERSATION = 'conversation';
    public const TYPE_USER = 'user';
    public const TYPE_ANNONCE = 'annonce';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $type = self::TYPE_CONVERSATION;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $reason = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $reviewedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $adminComment = null;

    #[ORM\ManyToOne(inversedBy: 'reportsMade')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reporter = null;

    #[ORM\ManyToOne(inversedBy: 'reportsReceived')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reportedUser = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Annonce $annonce = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $reviewedBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->status = self::STATUS_PENDING;
        $this->type = self::TYPE_CONVERSATION;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
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

    public function getReviewedAt(): ?\DateTimeInterface
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?\DateTimeInterface $reviewedAt): static
    {
        $this->reviewedAt = $reviewedAt;
        return $this;
    }

    public function getAdminComment(): ?string
    {
        return $this->adminComment;
    }

    public function setAdminComment(?string $adminComment): static
    {
        $this->adminComment = $adminComment;
        return $this;
    }

    public function getReporter(): ?User
    {
        return $this->reporter;
    }

    public function setReporter(?User $reporter): static
    {
        $this->reporter = $reporter;
        return $this;
    }

    public function getReportedUser(): ?User
    {
        return $this->reportedUser;
    }

    public function setReportedUser(?User $reportedUser): static
    {
        $this->reportedUser = $reportedUser;
        return $this;
    }

    public function getAnnonce(): ?Annonce
    {
        return $this->annonce;
    }

    public function setAnnonce(?Annonce $annonce): static
    {
        $this->annonce = $annonce;
        return $this;
    }

    public function getReviewedBy(): ?User
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(?User $reviewedBy): static
    {
        $this->reviewedBy = $reviewedBy;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isReviewed(): bool
    {
        return $this->status === self::STATUS_REVIEWED;
    }

    public function markAsReviewed(User $admin, string $comment = null): static
    {
        $this->status = self::STATUS_REVIEWED;
        $this->reviewedAt = new \DateTime();
        $this->reviewedBy = $admin;
        if ($comment) {
            $this->adminComment = $comment;
        }
        return $this;
    }

    public function markAsActionTaken(User $admin, string $comment = null): static
    {
        $this->status = self::STATUS_ACTION_TAKEN;
        $this->reviewedAt = new \DateTime();
        $this->reviewedBy = $admin;
        if ($comment) {
            $this->adminComment = $comment;
        }
        return $this;
    }

    public function dismiss(User $admin, string $comment = null): static
    {
        $this->status = self::STATUS_DISMISSED;
        $this->reviewedAt = new \DateTime();
        $this->reviewedBy = $admin;
        if ($comment) {
            $this->adminComment = $comment;
        }
        return $this;
    }
}
