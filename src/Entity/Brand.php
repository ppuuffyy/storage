<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BrandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BrandRepository::class)]
#[ApiResource(
    normalizationContext: [
        "groups" => ["brand:read"]
    ],
    denormalizationContext: [
        "groups" => ["brand:write"]
    ],
)]
class Brand
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
        maxMessage: "A márka neve min 2, max 50 karakter legyen"
    )]      
    #[Groups(['brand:read', 'brand:write', 'product:read'])]
    private $name;

    /**
     * A minőség kategőria 1-5 között lehet
     */
    #[ORM\Column(type: 'smallint')]
    #[Assert\Choice([1, 2, 3, 4, 5])]
    #[Groups(['brand:read', 'brand:write', 'product:read'])]
    private $qualityCategory;

    #[ORM\OneToMany(mappedBy: 'brand', targetEntity: Product::class)]
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
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

    public function getQualityCategory(): ?int
    {
        return $this->qualityCategory;
    }

    public function setQualityCategory(int $qualityCategory): self
    {
        $this->qualityCategory = $qualityCategory;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setBrand($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getBrand() === $this) {
                $product->setBrand(null);
            }
        }

        return $this;
    }
}
