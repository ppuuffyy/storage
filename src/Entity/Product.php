<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    normalizationContext: [
        "groups" => ["product:read"]
    ],
    denormalizationContext: [
        "groups" => ["product:write"]
    ],
)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["product:read", "product:write",'storage:read'])]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["product:read", "product:write",'storage:read'])]
    private $class;

    #[ORM\Column(type: 'integer')]
    #[Groups(["product:read", "product:write",'storage:read'])]
    private $price;

    #[ORM\Column(type: 'integer')]
    #[Groups(["product:read", "product:write"])]
    private $unitSize;

    /**
     * Nem kötelező a márka megadása, lehetnek márkátlan termékek is,
     * de ha meg van adva, akkor az egy már létező márka IRI-jét kell tartalmazza
     */
    #[ORM\ManyToOne(targetEntity: Brand::class, inversedBy: 'products')]
    #[Groups(["product:read", "product:write"])]
    private $brand;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Stock::class)]
    #[Groups(["product:read"])]
    private $stocks;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: StockChange::class)]
    #[Groups(["product:read"])]
    private $stockChanges;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductProperties::class, orphanRemoval: true)]
    #[Groups(["product:read"])]
    private $productProperties;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
        $this->stockChanges = new ArrayCollection();
        $this->productProperties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getUnitSize(): ?int
    {
        return $this->unitSize;
    }

    public function setUnitSize(int $unitSize): self
    {
        $this->unitSize = $unitSize;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): self
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks[] = $stock;
            $stock->setProduct($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getProduct() === $this) {
                $stock->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StockChange>
     */
    public function getStockChanges(): Collection
    {
        return $this->stockChanges;
    }

    public function addStockChange(StockChange $stockChange): self
    {
        if (!$this->stockChanges->contains($stockChange)) {
            $this->stockChanges[] = $stockChange;
            $stockChange->setProduct($this);
        }

        return $this;
    }

    public function removeStockChange(StockChange $stockChange): self
    {
        if ($this->stockChanges->removeElement($stockChange)) {
            // set the owning side to null (unless already changed)
            if ($stockChange->getProduct() === $this) {
                $stockChange->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductProperties>
     */
    public function getProductProperties(): Collection
    {
        return $this->productProperties;
    }

    public function addProductProperty(ProductProperties $productProperty): self
    {
        if (!$this->productProperties->contains($productProperty)) {
            $this->productProperties[] = $productProperty;
            $productProperty->setProduct($this);
        }

        return $this;
    }

    public function removeProductProperty(ProductProperties $productProperty): self
    {
        if ($this->productProperties->removeElement($productProperty)) {
            // set the owning side to null (unless already changed)
            if ($productProperty->getProduct() === $this) {
                $productProperty->setProduct(null);
            }
        }

        return $this;
    }
}
