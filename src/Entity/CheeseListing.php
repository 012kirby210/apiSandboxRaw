<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Repository\CheeseListingRepository;
use Doctrine\ORM\Mapping as ORM;
use Carbon\Carbon;

/**
 * @ApiResource(
 *   normalizationContext={"groups"={"api_read"}},
 *   denormalizationContext={"groups"={"api_write"}}
 * )
 * @ORM\Entity(repositoryClass=CheeseListingRepository::class)
 */
class CheeseListing
{
  /**
   * @ORM\Id()
   * @ORM\GeneratedValue()
   * @ORM\Column(type="integer")
   * @Groups({"api_read"})
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   * @Groups({"api_read","api_write"})
   */
  private $title;

  /**
   * @ORM\Column(type="text", nullable=true)
   * @Groups({"api_read"})
   */
  private $description;

  /**
   * The price of this delicious cheese in cents.
   * @ORM\Column(type="integer", nullable=true)
   * @Groups({"api_read","api_write"})
   *
   */
  private $price;

  /**
   * @ORM\Column(type="datetime")
   * @Groups({"api_read"})
   */
  private $createdAt;

  /**
   * @ORM\Column(type="boolean")
   */
  private $isPublished = false;

  public function __construct()
  {
    $this->createdAt = new \DateTimeImmutable();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getTitle(): ?string
  {
    return $this->title;
  }

  public function setTitle(string $title): self
  {
    $this->title = $title;

    return $this;
  }

  public function getDescription(): ?string
  {
    return $this->description;
  }

  /**
   * Transform a description into a line breaked description.
   * @param string|null $description
   * @return $this
   *
   * @Groups({"api_write"})
   */
  public function setTextDescription(?string $description): self
  {
    $this->description = nl2br($description);

    return $this;
  }

  public function setDescription(?string $description): self
  {
    $this->description = $description;

    return $this;
  }

  public function getPrice(): ?int
  {
    return $this->price;
  }

  public function setPrice(?int $price): self
  {
    $this->price = $price;

    return $this;
  }

  public function getCreatedAt(): ?\DateTimeInterface
  {
    return $this->createdAt;
  }

  /**
   * Get the time diff relative to now as a human understandable string.
   * @return string|null
   *
   * @Groups({"api_read"})
   */
  public function getCreatedAtAgo(): ?string
  {
    return Carbon::instance($this->getCreatedAt())->shortRelativeToNowDiffForHumans();
  }

  public function getIsPublished(): ?bool
  {
    return $this->isPublished;
  }

  public function setIsPublished(bool $isPublished): self
  {
    $this->isPublished = $isPublished;

    return $this;
  }
}
