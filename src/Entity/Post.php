<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\PostRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
#[
    ApiResource(
    collectionOperations: [
        'get',
        'post',
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['read:collection', 'read:item', 'read:post']]
        ],
        'put',
        'delete',
    ],
    denormalizationContext: ['groups' => ['write:Post']],
    normalizationContext: ['groups' => ['read:collection']],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 2,
    paginationMaximumItemsPerPage: 2,
    ),
    ApiFilter(
        SearchFilter::class,
        properties: ['id' => 'exact', 'title' => 'partial']
    )
]
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:collection'])]
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:collection', 'write:Post']),
        Length(min: 5)
    ]
    private ?string $title = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:collection', 'write:Post'])]
    private ?string $slug = null;

    /**
     * @ORM\Column(type="text")
     */
    #[Groups(['read:item', 'write:Post'])]
    private ?string $content = null;

    /**
     * @ORM\Column(type="datetime")
     */
    #[Groups(['read:item'])]
    private ?DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTimeInterface $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="posts", cascade={"persist", "remove"})
     */
    #[
        Groups(['read:item', 'write:Post']),
        Valid()
    ]
    private ?Category $category = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
