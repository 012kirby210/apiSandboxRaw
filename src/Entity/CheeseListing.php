<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use App\Repository\CheeseListingRepository;
use Doctrine\ORM\Mapping as ORM;
use Carbon\Carbon;


/**
 * @ApiResource(
 *   normalizationContext={"groups"={"CheeseListing:api_read"}},
 *   denormalizationContext={"groups"={"CheeseListing:api_write"}},
 *   attributes={
 *    "pagination_items_per_page"=1,
 *    "formats"={"jsonld","json","html","jsonhal","csv"={"text/csv"}}
 *   },
 *   itemOperations={
 *    "get"={"normalization_context"={
 *     "groups"={"CheeseListing:api_read","CheeseListing:api_write","CheeseListing:get:api_read"}
 *    }},
 *    "delete"={"denormalization_context"={"groups"={"CheeseListing:api_write"}},
 *     "security"="is_granted('ROLE_ADMIN')"
 *    },
 *    "put"={"denormalization_context"={"groups"={"CheeseListing:api_write","CheeseListing:put:api_write"}},
 *     "security"="is_granted('EDIT',object)"
 *    }
 *   },
 *   collectionOperations={
 *    "get",
 *    "post"={"security"="is_granted('ROLE_USER')"}
 *   }
 * )
 * @ApiFilter(BooleanFilter::class, properties={"isPublished"})
 * @ApiFilter(SearchFilter::class, properties=
 *   {"title":"partial",
 *    "description":"partial",
 *    "owner":"exact",
 *    "owner.username":"partial"}))
 * @ORM\Entity(repositoryClass=CheeseListingRepository::class)
 */
class CheeseListing
{
  /**
   * @ORM\Id()
   * @ORM\GeneratedValue()
   * @ORM\Column(type="integer")
   * @Groups({"CheeseListing:api_read"})
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   * @Groups({"CheeseListing:api_read","CheeseListing:api_write","User:api_read","User:api_write"})
   * @Assert\NotBlank()
   * @Assert\Length(
   *   min=2,
   *   max=50,
   *   maxMessage="Décrivez votre fromage en moins de 50 charactères."
   * )
   */
  private $title;

  /**
   * @ORM\Column(type="text", nullable=true)
   * @Groups({"CheeseListing:api_read","User:api_read"})
   * @Assert\NotBlank()
   */
  private $description;

  /**
   * The price of this delicious cheese in cents.
   * @ORM\Column(type="integer", nullable=true)
   * @Groups({"CheeseListing:api_read","CheeseListing:api_write","User:api_read","User:api_write"})
   * @Assert\NotBlank()
   * @Assert\GreaterThan(
   *   value=0
   * )
   */
  private $price;

  /**
   * @ORM\Column(type="datetime")
   * @Groups({"CheeseListing:api_read"})
   */
  private $createdAt;

  /**
   * Define if the ressource is published.
   * @ORM\Column(type="boolean")
   */
  private $isPublished = false;

  /**
   * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cheeseListings")
   *
   * @Groups({"CheeseListing:api_read","CheeseListing:api_write","CheeseListing:put:api_write"})
   * @Assert\Valid()
   */
  private $owner;

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
   * Return a short description.
   * @return string|null
   *
   * @Groups({"CheeseListing:api_read"})
   */
  public function getShortDescription(): ?string
  {
    return (strlen($this->description) > 20)?
      substr($this->description,0,20).'...' : $this->description;
  }

  /**
   * Transform a description into a line breaked description.
   * @param string|null $description
   * @return $this
   *
   * @Groups({"CheeseListing:api_write","User:api_write"})
   * @SerializedName("description")
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
   * @Groups({"CheeseListing:api_read"})
   */
  public function getCreatedAtAgo(): ?string
  {
    return Carbon::instance($this->getCreatedAt())->shortRelativeToNowDiffForHumans();
  }

  public function getIsPublished(): ?bool
  {
    return $this->isPublished;
  }

  /**
   * @param bool $isPublished
   * @return $this
   *
   * @Groups({"CheeseListing:api_write"})
   */
  public function setIsPublished(bool $isPublished): self
  {
    $this->isPublished = $isPublished;

    return $this;
  }

  public function getOwner(): ?User
  {
      return $this->owner;
  }

  public function setOwner(?User $owner): self
  {
      $this->owner = $owner;

      return $this;
  }
}
