<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\StockChange;
use App\Entity\Product;
use App\Entity\Stock;
use App\Entity\Storage;
use App\Exception\NotEnoughProductException;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockChangeDataPersister implements DataPersisterInterface
{
    private $decoratedDataPersister;
    private $entityManager;

    public function __construct(DataPersisterInterface $decoratedDataPersister, EntityManagerInterface $entityManager)
    {
        $this->decoratedDataPersister = $decoratedDataPersister;
        $this->entityManager = $entityManager;
    }

    public function supports($data): bool
    {
        return $data instanceof StockChange;
    }

    /**
     * @param StockChange $data
     */
    public function persist($data)
    {
        /**
         * Ez a végpont az ami tulajdonképpen kezeli a beérkező termék hozzáadást
         * vagy elvételt a raktárak készletéhez, ezért mentés előtt szükséges a 
         * raktár szabad kapacitását ellenőrizni hozzáadásnál, ha túl kevés akkor 
         * helyet keresni másik raktárban (akár több raktárban). Ha sikeres a hozzáadás
         * akkor ezt bejegyezni (vagy módosítani) a Stock táblába, ami az adott termék 
         * adott raktárban lévő mennyiségét tartja számon. Elvétel esetén ellenőrizni van-e
         * elég abban a raktárban, ha igen akkor a művelet után frissíteni a szabad kapacitását 
         * a raktárnak és a Stock-ot is aktualizálni.
         */
        
        /**
         * Megkeressük a terméket az adatbázisban, ha nincs akkor elutasítjuk a requestet
         */
        
        $product = $this->entityManager->getRepository(Product::class)->find($data->getProduct()->getId());
        if (!$product) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(404, 'Nincs ilyen  termék');
        }
        // $productSize = ($product->getName());
        // $productSpaceNeeded = $productSize * $data->getQuantity();
        
        /**
         * Megkeressük a raktárat az adatbázisban, ha nincs akkor elutasítjuk a requestet
         */       
        $storageRepository = $this->entityManager->getRepository(Storage::class);
        /** 
         * @var Storage $storage
         * 
         */        
        $storage = $storageRepository->find($data->getStorage()->getId());
        if (!$storage) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(404, 'Nincs ilyen azonósitóju raktár');
        }
        // $storageAvailableSpace = $storage->getAvailableSpace();

        $stockRepository = $this->entityManager->getRepository(Stock::class);
        
        /** 
         * @var Stock $stock
         * 
         */
        // $stock = $stockRepository->findOneBy([
        //     'storage_id' => $data->getStorage()->getId(),
        //     'product_id' => $data->getProduct()->getId(),
        // ]);
        $stock = $stockRepository->findOneBy([
            'storage' => $data->getStorage(),
            'product' => $data->getProduct()
        ]);

        if ($data->getQuantity() < 0) {
            /**
             * Termék elvétele a raktárból, vagy ha adott raktárban nincs, akkor mivel közösen kezeljük
             * a kapacitást ezért másik raktárakból próbálja meg elvenni
             */
            $totalQuantityToRetriev = abs($data->getQuantity());
            if ($stock && $stock->getQuantity() > $totalQuantityToRetriev) {
                $stock->setQuantity($stock->getQuantity() - $totalQuantityToRetriev);
                $storage->setAvailableSpace($storage->getAvailableSpace() + $totalQuantityToRetriev);
                $this->entityManager->flush();
                return $this->decoratedDataPersister->persist($data);
            } else if ($stock && $stock->getQuantity() == $totalQuantityToRetriev) {
                $this->entityManager->remove($stock);
                $storage->setAvailableSpace($storage->getAvailableSpace() + $totalQuantityToRetriev);
                $this->entityManager->flush();
                return $this->decoratedDataPersister->persist($data);
            }
            /** 
             * Az összes raktárban lévő mennyiség a keresett termékből
             */
            $allAvailableProductInStock = $stockRepository->getAllAvailableProductInStock($product);
            if ($allAvailableProductInStock < $totalQuantityToRetriev) {
                // throw new \Doctrine\ORM\EntityNotFoundException('Nincs a kért termékből a kért mennyiség a raktárakban ezért a kivét nem lehetséges.'); 
                throw new NotEnoughProductException('Nincs a kért termékből a kért mennyiség a raktárakban ezért a kivét nem lehetséges.');
                // return;
            }

            
            if ($stock) {
                $quatityToRetriev = $stock->getQuantity();
                $this->entityManager->remove($stock);
                $storage->setAvailableSpace($storage->getAvailableSpace() + $quatityToRetriev);
                $this->entityManager->flush();
                $totalQuantityToRetriev = $totalQuantityToRetriev - $quatityToRetriev;
            }
            
            /**
             * @var Stock[] $otherStocks
             */
            $otherStocks = $stockRepository->findAllStocksOfProduct($data->getProduct(), $data->getStorage());
            foreach ($otherStocks as $key => $otherStock) {
                $quatityToRetriev = $totalQuantityToRetriev > $otherStock->getQuantity() ? $otherStock->getQuantity() : $totalQuantityToRetriev;
                $otherStorage = $storageRepository->find($otherStock->getStorage()->getId());
                if ($quatityToRetriev == $otherStock->getQuantity()) {
                    $this->entityManager->remove($otherStock);
                    $otherStorage->setAvailableSpace($otherStorage->getAvailableSpace() + $quatityToRetriev);
                    $this->entityManager->flush();
                    $totalQuantityToRetriev = $totalQuantityToRetriev - $quatityToRetriev;
                } else {
                    $otherStock->setQuantity($otherStock->getQuantity() - $quatityToRetriev);
                    $otherStorage->setAvailableSpace($otherStorage->getAvailableSpace() + $quatityToRetriev);
                    $this->entityManager->flush();
                    return $this->decoratedDataPersister->persist($data);
                }
            }

        } else {
            /**
             * Termék hozzáadása a raktárhoz, vagy ha adott raktárban nincs elég szabad hely, 
             * akkor mivel közösen kezeljük a kapacitást ezért a többi raktárba próbálja meg berakni
             */
            $totalQuantityToStore = $data->getQuantity();
            $quatityToStore = $storage->getAvailableSpace() >= $totalQuantityToStore ? $totalQuantityToStore : $storage->getAvailableSpace();
            if ($quatityToStore > 0) {
                if ($stock) {
                    $stock->setQuantity($stock->getQuantity() + $quatityToStore);
                } else {
                    $stock = new Stock();
                    $stock->setProduct($product);
                    $stock->setStorage($storage);
                    $stock->setQuantity($quatityToStore);
                    $this->entityManager->persist($stock);
                }
                $storage->setAvailableSpace($storage->getAvailableSpace() - $quatityToStore);
                $this->entityManager->flush();
                $totalQuantityToStore = $totalQuantityToStore - $quatityToStore;
                if ($totalQuantityToStore <= 0){
                    return $this->decoratedDataPersister->persist($data);
                }
            }

            $otherStorages = $storageRepository->findAllStoragesWithFreeSpace();
            foreach ($otherStorages as $key => $otherStorage) {
                $quatityToStore = $otherStorage->getAvailableSpace() >= $totalQuantityToStore ? $totalQuantityToStore : $otherStorage->getAvailableSpace();
                $otherStock = $stockRepository->findOneBy([
                    'storage' => $otherStorage,
                    'product' => $data->getProduct(),
                ]);
                if ($otherStock) {
                    $otherStock->setQuantity($otherStock->getQuantity() + $quatityToStore);
                } else {
                    $stock = new Stock();
                    $stock->setProduct($product);
                    $stock->setStorage($otherStorage);
                    $stock->setQuantity($quatityToStore);
                    $this->entityManager->persist($stock);
                } 
                $otherStorage->setAvailableSpace($otherStorage->getAvailableSpace() - $quatityToStore);
                $this->entityManager->flush();
                $totalQuantityToStore = $totalQuantityToStore - $quatityToStore;
                if ($totalQuantityToStore <= 0){
                    return $this->decoratedDataPersister->persist($data);
                }                               
            }

                








        }


        return $this->decoratedDataPersister->persist($data);
    }


    public function remove($data)
    {
        $this->decoratedDataPersister->remove($data);
    }
}
