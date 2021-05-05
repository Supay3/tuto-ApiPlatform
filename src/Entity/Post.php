<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Attribute\ApiAuthGroups;
use App\Controller\EmptyController;
use App\Controller\PostCountController;
use App\Controller\PostPublishController;
use App\Repository\PostRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Valid;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @Vich\Uploadable()
 */
#[
    ApiResource(
        collectionOperations: [
            'get' => [
                'openapi_context' => [
                    'security' => [['bearerAuth' => []]],
                ],
            ],
            'post',
            'count' => [
                'method' => 'GET',
                'path' => '/posts/count',
                'controller' => PostCountController::class,
                'read' => false,
                'pagination_enabled' => false,
                'filters' => [],
                'openapi_context' => [
                    'summary' => 'Récupère le nombre total d\'articles',
                    'parameters' => [
                        [
                            'in' => 'query',
                            'name' => 'online',
                            'schema' => [
                                'type' => 'integer',
                                'maximum' => 1,
                                'minimum' => 0,
                            ],
                            'description' => 'Filtre les articles en ligne',
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'OK',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'integer',
                                        'exemple' => 3,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        itemOperations: [
            'get' => [
                'normalization_context' => [
                    'groups' => ['read:collection', 'read:item', 'read:post'],
                    'openapi_definition_name' => 'Detail',
                ],
            ],
            'put',
            'delete',
            'publish' => [
                'method' => 'POST',
                'path' => '/posts/{id}/publish',
                'controller' => PostPublishController::class,
                'openapi_context' => [
                    'summary' => 'Permet de publier un article',
                    'requestBody' => [
                        'content' => [
                            'application/json' => [
                                'schema' => [],
                            ],
                        ],
                    ],
                ],
            ],
            'image' => [
                'method' => 'POST',
                'path' => '/posts/{id}/image',
                'controller' => EmptyController::class,
                'openapi_context' => [
                    'requestBody' => [
                        'content' => [
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'file' => [
                                            'type' => 'string',
                                            'format' => 'binary',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        denormalizationContext: ['groups' => ['write:Post']],
        normalizationContext: [
            'groups' => ['read:collection'],
            'openapi_definition_name' => 'Collection',
        ],
        paginationClientItemsPerPage: true,
    ),
    ApiFilter(
        SearchFilter::class,
        properties: ['id' => 'exact', 'title' => 'partial'],
    ),
    ApiAuthGroups([
        'CAN_EDIT' => ['read:collection:Owner'],
        'ROLE_USER' => ['read:collection:User']
    ])
]
class Post implements UserOwnedInterface
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
    #[Groups(['read:collection:User', 'write:Post'])]
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

    /**
     * @ORM\Column(type="boolean", options={"default": "0"})
     */
    #[
        Groups(['read:collection:Owner']),
        ApiProperty(openapiContext: ['type' => 'boolean', 'description' => 'En ligne ou pas ?'])
    ]
    private bool $online = false;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     */
    private ?User $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $filePath = null;

    /**
     * @var string|null
     */
    #[Groups(['read:collection'])]
    private ?string $fileUrl = null;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="post_image", fileNameProperty="filePath")
     */
    #[Groups(['write:Post'])]
    private ?File $file = null;

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

    public function getOnline(): ?bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): self
    {
        $this->online = $online;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File|null $file
     * @return Post
     */
    public function setFile(?File $file): Post
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    /**
     * @param string|null $fileUrl
     * @return Post
     */
    public function setFileUrl(?string $fileUrl): Post
    {
        $this->fileUrl = $fileUrl;
        return $this;
    }


}
