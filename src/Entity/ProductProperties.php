<?php

namespace App\Entity;

use App\Repository\ProductPropertiesRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductPropertiesRepository::class)]
#[ApiResource(
    normalizationContext: [
        "groups" => ["productProperty:read"]
    ],
    denormalizationContext: [
        "groups" => ["productProperty:write"]
    ],
)]
class ProductProperties
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["productProperty:read", "productProperty:write", "product:read"])]
    private $propertyName;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["productProperty:read", "productProperty:write", "product:read"])]
    private $value;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productProperties')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["productProperty:read", "productProperty:write"])]
    private $product;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPropertyName(): ?string
    {
        return $this->propertyName;
    }

    public function setPropertyName(string $propertyName): self
    {
        $this->propertyName = $propertyName;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
