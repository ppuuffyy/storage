<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 *
 * @method Stock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stock[]    findAll()
 * @method Stock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    public function add(Stock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Stock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return int A teljes mennyiség ami adott termékből a raktárakban található
     */

     public function getAllAvailableProductInStock($product): int
     {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT sum(s.quantity)
            FROM App\Entity\Stock s
            WHERE s.product = :product'
        )->setParameter('product', $product);
            // dd();
        return $query->getResult()[0][1] ?? 0;
     }

    /**
    * @return Stock[] Megkeresi a terméket az összes raktárban, kivéve az aktuálisban
    */
    public function findAllStocksOfProduct($product, $storage): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.product = :product')
            ->setParameter('product', $product)
            ->andWhere('s.storage != :storage')
            ->setParameter('storage', $storage)
            ->orderBy('s.storage', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

}
