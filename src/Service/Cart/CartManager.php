<?php

namespace App\Service\Cart;

use App\Entity\Cart;
use App\Entity\Item;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Money\Money;

class CartManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Cart|null
     */
    private $cart = null;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Cart $cart
     */
    public function setCart(Cart $cart) {
        $this->cart = $cart;
    }

    /**
     * @param Product $product
     * @param int $quantity
     */
    public function addProductToCart(Product $product, int $quantity): void
    {
        $item = $this->getItemFromCart($product);

        if ($item) {
            $item->increaseQuantity($quantity);
        } elseif (count($this->cart->getItems()) < 3) {
            $item = new Item($product, $quantity);
            $this->cart->addItem($item);
        }
    }

    /**
     * @param Product $product
     */
    public function removeProductFromCart(Product $product): void
    {
        $item = $this->getItemFromCart($product);

        if ($item) {
            $this->cart->removeItem($item);
        }
    }

    /**
     * @return Money
     */
    public function getCartTotalPrice(): Money
    {
        $totalPrice = 0;

        foreach ($this->cart->getItems() as $item) {
            $totalPrice += $item->getProduct()->getPrice() * $item->getQuantity();
        }

        return Money::USD($totalPrice);
    }

    /**
     * @param Product $product
     * @return Item|null
     */
    public function getItemFromCart(Product $product): ?Item
    {
        foreach ($this->cart->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                return $item;
            }
        }

        return null;
    }
}