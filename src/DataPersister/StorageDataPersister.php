<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Storage;
use Doctrine\ORM\EntityManagerInterface;

class StorageDataPersister implements DataPersisterInterface
{
    private $decoratedDataPersister;

    public function __construct(DataPersisterInterface $decoratedDataPersister, EntityManagerInterface $entityManager)
    {
        $this->decoratedDataPersister = $decoratedDataPersister;
        $this->entityManager = $entityManager;
    }

    public function supports($data): bool
    {
        return $data instanceof Storage;
    }

    /**
     * @param Storage $data
     */
    public function persist($data)
    {
        /**
         * A raktár kezdeti szabad helye megegyezik a kapacitással
         */
        $data->setAvailableSpace($data->getCapacity());
        return $this->decoratedDataPersister->persist($data);
    }


    public function remove($data)
    {
        $this->decoratedDataPersister->remove($data);
    }
}
