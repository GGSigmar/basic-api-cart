<?php

namespace App\Test;

use Doctrine\ORM\EntityManagerInterface;

class AbstractEntityBuilder
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }
}