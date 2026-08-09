<?php

namespace App\Infrastructure\Repository;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineCustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function save(Customer $customer): void
    {
        $this->em->persist($customer);
        $this->em->flush();
    }

    public function findByName(string $name): ?Customer
    {
        return $this->em->getRepository(Customer::class)
            ->findOneBy(['name' => $name]);
    }
}
