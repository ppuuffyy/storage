<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\StockRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StockRepository::class)]
#[ApiResource(
    collectionOperations: ["get"],
    itemOperations: ["get"],
    normalizationContext: [
        "groups" => ["stock:read"]
    ],
    denormalizationContext: [
        "groups" => ["stock:write"]
    ],
)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    #[Groups(["stock:read", "stock:write",'storage:read'])]
    private $quantity;

    #[ORM\ManyToOne(targetEntity: Storage::class, inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["stock:read", "stock:write"])]
    private $storage;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["stock:read", "stock:write",'storage:read'])]
    private $product;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getStorage(): ?Storage
    {
        return $this->storage;
    }

    public function setStorage(?Storage $storage): self
    {
        $this->storage = $storage;

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
