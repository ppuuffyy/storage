<?php

namespace App\Entity;

use App\Repository\StockChangeRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockChangeRepository::class)]
#[ApiResource(
    description: 'Ezen API végpont segítségével lehet műveletet végezni a raktárak készletében',
    collectionOperations: ["get", "post"],
    itemOperations: ["get"],
    normalizationContext: [
        "groups" => ["stockChange:read"]
    ],
    denormalizationContext: [
        "groups" => ["stockChange:write"]
    ],
)]
class StockChange
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    
    /**
     * Pozittív szám jelöli a készlethez hozzáadást, a negatív szám a készletből való kivétet
     */
    #[ORM\Column(type: 'integer')]
    #[Groups(["stockChange:read", "stockChange:write"])]
    #[Assert\NotEqualTo(0)]
    private $quantity;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(["stockChange:read"])]
    private $createdAt;

    /**
     * A raktár IRI azonosítója ahová a termék kerül vagy ahonnan elvételre kerül
     */
    #[ORM\ManyToOne(targetEntity: Storage::class, inversedBy: 'stockChanges')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["stockChange:read", "stockChange:write"])]
    private $storage;

    /**
     * A termék IRI azonosítója ami hozzáadásra vagy elvételre kerül
     */
    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'stockChanges')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["stockChange:read", "stockChange:write"])]
    private $product;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
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
