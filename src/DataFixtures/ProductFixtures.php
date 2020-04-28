<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    const GAME_PRODUCTS = [
        'Fallout' => 199,
        'Don\'t Starve' => 299,
        'Baldur\s Gate' => 399,
        'Icewind Dale' => 499,
        'Bloodborne' => 599,
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::GAME_PRODUCTS as $name => $price) {
            $product = new Product();
            $product->setName($name);
            $product->setPrice($price);

            $manager->persist($product);
        }

        $manager->flush();
    }
}