<?php

namespace App\Test;

use App\Entity\Cart;

class CartBuilder extends AbstractEntityBuilder
{
    /**
     * @param bool $save
     */
    public function buildCart(bool $save = true)
    {
        $cart = new Cart();

        if ($save) {
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        $this->entityManager->refresh($cart);

        return $cart;
    }
}