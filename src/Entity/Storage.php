<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\StorageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StorageRepository::class)]
#[ApiResource(
    normalizationContext: [
        "groups" => ["storage:read"]
    ],
    denormalizationContext: [
        "groups" => ["storage:write"]
    ],
)]
class Storage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(
        min : 2, 
        max : 50,
        maxMessage: "A raktár neve min 2, max 50 karakter legyen"
    )]    
    #[Groups(['storage:read', "storage:write"])]
    private $name;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank()]
    #[Groups(['storage:read', "storage:write"])]
    private $address;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank()]
    #[Assert\GreaterThan(0)]   
    #[Groups(['storage:read', "storage:write"])] 
    private $capacity;

    #[ORM\Column(type: 'integer')]
    #[Groups(['storage:read'])]
    private $availableSpace;

    #[ORM\OneToMany(mappedBy: 'storage', targetEntity: Stock::class)]
    #[Groups(['storage:read'])]
    private $stocks;

    #[ORM\OneToMany(mappedBy: 'storage', targetEntity: StockChange::class)]
    #[Groups(['storage:read'])]
    private $stockChanges;

    public function __construct()
    {
        $this->stocks = new ArrayCollection();
        $this->stockChanges = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getAvailableSpace(): ?int
    {
        return $this->availableSpace;
    }

    public function setAvailableSpace(int $availableSpace): self
    {
        $this->availableSpace = $availableSpace;

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
            $stock->setStorage($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): self
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getStorage() === $this) {
                $stock->setStorage(null);
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
            $stockChange->setStorage($this);
        }

        return $this;
    }

    public function removeStockChange(StockChange $stockChange): self
    {
        if ($this->stockChanges->removeElement($stockChange)) {
            // set the owning side to null (unless already changed)
            if ($stockChange->getStorage() === $this) {
                $stockChange->setStorage(null);
            }
        }

        return $this;
    }
}
