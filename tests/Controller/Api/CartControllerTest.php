<?php

namespace App\Tests\Controller\Api;

use App\Entity\Item;
use App\Services\Cart\CartManager;
use App\Test\ApiTestCase;
use App\Test\CartBuilder;
use App\Test\ProductBuilder;

class CartControllerTest extends ApiTestCase
{
    /**
     * @var ProductBuilder
     */
    private $productBuilder;

    /**
     * @var CartBuilder
     */
    private $cartBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->productBuilder = new ProductBuilder($this->entityManager);
        $this->cartBuilder = new CartBuilder($this->entityManager);
    }

    public function testCreateCartAction()
    {
        $this->apiRequest('POST', $this->urlGenerator->generate('create_cart'));

        $response = $this->client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());

        $returnedData = json_decode($response->getContent(), true);

        $this->assertCount(3, $returnedData);
        $this->assertEquals(
            [
                'location',
                'id',
                'items',
            ],
            array_keys($returnedData)
        );
    }

    public function testGetCartAction()
    {
        $cart = $this->cartBuilder->buildCart();

        $this->apiRequest('GET', $this->urlGenerator->generate('get_cart', ['id' => $cart->getId()]));

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $returnedData = json_decode($response->getContent(), true);

        $this->assertCount(2, $returnedData);
        $this->assertEquals(
            [
                'cart',
                'totalPrice',
            ],
            array_keys($returnedData)
        );

        $this->assertEquals($cart->getId(), $returnedData['cart']['id']);
        $this->assertEquals('0 PLN', $returnedData['totalPrice']);
    }

    public function testAddProductToCartAction()
    {
        $product1 = $this->productBuilder->buildProduct();
        $cart = $this->cartBuilder->buildCart();

        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->getService(CartManager::class);
        $cartManager->setCart($cart);

        $data1 = [
            'product' => $product1->getId(),
        ];

        $this->apiRequest('PATCH', $this->urlGenerator->generate('add_product_to_cart', ['id' => $cart->getId()]), json_encode($data1));

        $this->entityManager->refresh($cart);

        $this->assertCount(1, $cart->getItems());

        $item1 = $cartManager->getItemFromCart($product1);

        $this->assertInstanceOf(Item::class, $item1);
        $this->assertEquals(1, $item1->getQuantity());

        $data2 = [
            'product' => $product1->getId(),
            'quantity' => 2
        ];

        $this->apiRequest('PATCH', $this->urlGenerator->generate('add_product_to_cart', ['id' => $cart->getId()]), json_encode($data2));

        $this->entityManager->refresh($cart);

        $this->assertCount(1, $cart->getItems());

        $this->entityManager->refresh($item1);

        $this->assertInstanceOf(Item::class, $item1);
        $this->assertEquals(3, $item1->getQuantity());

        $product2 = $this->productBuilder->buildProduct();

        $data3 = [
            'product' => $product2->getId(),
            'quantity' => 2,
        ];

        $this->apiRequest('PATCH', $this->urlGenerator->generate('add_product_to_cart', ['id' => $cart->getId()]), json_encode($data3));

        $this->entityManager->refresh($cart);

        $this->assertCount(2, $cart->getItems());

        $item2 = $cartManager->getItemFromCart($product2);

        $this->assertInstanceOf(Item::class, $item2);
        $this->assertEquals(2, $item2->getQuantity());
    }

    public function testRemoveProductFromCartAction()
    {
        $cart = $this->cartBuilder->buildCart();
        $product1 = $this->productBuilder->buildProduct();

        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->getService(CartManager::class);
        $cartManager->setCart($cart);

        $cartManager->addProductToCart($product1, 2);

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        $this->assertCount(1, $cart->getItems());

        $data = [
            'product' => $product1->getId(),
        ];

        $this->apiRequest('PATCH', $this->urlGenerator->generate('remove_product_from_cart', ['id' => $cart->getId()]), json_encode($data));

        $this->entityManager->refresh($cart);

        $this->assertCount(0, $cart->getItems());
    }
}