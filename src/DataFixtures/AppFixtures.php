<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\Product;
use App\Entity\ProductProperties;
use App\Entity\Storage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $storage = new Storage();
        $storage->setName('Raktár 1');
        $storage->setAddress('Budapest, Váci út 143');
        $storage->setCapacity(300);
        $storage->setAvailableSpace(300);
        $manager->persist($storage);

        $storage = new Storage();
        $storage->setName('Raktár 2');
        $storage->setAddress('Debrecen, Ipari park 2');
        $storage->setCapacity(100);
        $storage->setAvailableSpace(100);
        $manager->persist($storage);

        // $storage = new Storage();
        // $storage->setName('Raktár 3');
        // $storage->setAddress('Pécs, Fő út 17');
        // $storage->setCapacity(50);
        // $storage->setAvailableSpace(50);
        // $manager->persist($storage);

        // $storage = new Storage();
        // $storage->setName('Raktár 4');
        // $storage->setAddress('Szeged, Brüsszeli körút 76');
        // $storage->setCapacity(100);
        // $storage->setAvailableSpace(100);
        // $manager->persist($storage);

        $brand = new Brand();
        $brand->setName('Apple');
        $brand->setQualityCategory(5);
        $manager->persist($brand);

        $product = new Product();
        $product->setBrand($brand);
        $product->setName("iPhone 13 Max");
        $product->setClass("Telefon");
        $product->setPrice(520000);
        $product->setUnitSize(1);
        $manager->persist($product);

        $productProperty = new ProductProperties();
        $productProperty->setProduct($product);
        $productProperty->setPropertyName('Capacity');
        $productProperty->setValue('128GB');
        $manager->persist($productProperty);

        $productProperty = new ProductProperties();
        $productProperty->setProduct($product);
        $productProperty->setPropertyName('Color');
        $productProperty->setValue('Red');
        $manager->persist($productProperty);

        $product = new Product();
        $product->setBrand($brand);
        $product->setName("MacBook Pro M1");
        $product->setClass("Laptop");
        $product->setPrice(840000);
        $product->setUnitSize(1);
        $manager->persist($product);

        $productProperty = new ProductProperties();
        $productProperty->setProduct($product);
        $productProperty->setPropertyName('Capacity');
        $productProperty->setValue('256GB');
        $manager->persist($productProperty);

        $productProperty = new ProductProperties();
        $productProperty->setProduct($product);
        $productProperty->setPropertyName('Memory');
        $productProperty->setValue('16GB');
        $manager->persist($productProperty);

        $brand = new Brand();
        $brand->setName('Samsung');
        $brand->setQualityCategory(5);
        $manager->persist($brand);

        $product = new Product();
        $product->setBrand($brand);
        $product->setName("Galaxy Note 10");
        $product->setClass("Telefon");
        $product->setPrice(490000);
        $product->setUnitSize(1);
        $manager->persist($product);

        $productProperty = new ProductProperties();
        $productProperty->setProduct($product);
        $productProperty->setPropertyName('Capacity');
        $productProperty->setValue('128GB');
        $manager->persist($productProperty);

        $productProperty = new ProductProperties();
        $productProperty->setProduct($product);
        $productProperty->setPropertyName('Color');
        $productProperty->setValue('Red');
        $manager->persist($productProperty);

        $product = new Product();
        $product->setBrand($brand);
        $product->setName("Galaxy Watch 4");
        $product->setClass("Smartwatch");
        $product->setPrice(150000);
        $product->setUnitSize(1);
        $manager->persist($product);

        $productProperty = new ProductProperties();
        $productProperty->setProduct($product);
        $productProperty->setPropertyName('Size');
        $productProperty->setValue('44mm');
        $manager->persist($productProperty);
    
        $manager->flush();
    }
}
