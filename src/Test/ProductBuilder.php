<?php

namespace App\Test;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ProductBuilder
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function getSampleProductData(): array
    {
        return [
            'name' => $this->getRandomProductName(),
            'price' => $this->getRandomProductPrice(),
        ];
    }

    /**
     * @param array $data
     * @param bool $save
     *
     * @return Product
     */
    public function buildProduct(bool $save = true, array $data = null): Product
    {
        if ($data === null) {
            $data = $this->getSampleProductData();
        }

        $dataAccessor = PropertyAccess::createPropertyAccessor();

        $product = new Product();

        foreach ($data as $key => $value) {
            $dataAccessor->setValue($product, $key, $value);
        }

        if ($save) {
            $this->entityManager->persist($product);
            $this->entityManager->flush();
        }

        $this->entityManager->refresh($product);

        return $product;
    }

    /**
     * @return string
     */
    public function getRandomProductName(): string
    {
        return 'Product ' . uniqid();
    }

    /**
     * @return int
     */
    public function getRandomProductPrice(): int
    {
        return rand(100, 10000);
    }
}