<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;

#[ApiResource(
    collectionOperations: ['get'],
    itemOperations: ['get'],
    paginationEnabled: false,
)]
class Dependency
{
    #[ApiProperty(
        identifier: true,
    )]
    private string $uuid;

    #[ApiProperty(
        description: 'Nom de la dépendance'
    )]
    private string $name;

    #[ApiProperty(
        description: 'Version de la dépendance',
        openapiContext: [
            'exemple' => '"5.2.*"',
        ]
    )]
    private string $version;

    public function __construct(string $uuid, string $name, string $version)
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}