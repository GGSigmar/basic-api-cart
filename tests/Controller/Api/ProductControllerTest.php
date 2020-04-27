<?php

namespace App\Tests\Controller\Api;

use App\Entity\Product;
use App\Test\ApiTestCase;
use App\Test\ProductBuilder;

class ProductControllerTest extends ApiTestCase
{
    /**
     * @var ProductBuilder
     */
    private $productBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->productBuilder = new ProductBuilder($this->getEntityManager());
    }

    public function testGetProduct()
    {
        $product = $this->productBuilder->buildProduct();

        $this->apiRequest('GET', $this->urlGenerator->generate('get_product', ['id' => $product->getId()]));

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $returnedData = json_decode($response->getContent(), true);

        $this->assertEquals(
            [
                'id',
                'name',
                'price'
            ],
            array_keys($returnedData)
        );

        $this->assertEquals($product->getName(), $returnedData['name']);
        $this->assertEquals($product->getPrice(), $returnedData['price']);
    }

    public function testGetNonExistentProduct()
    {
        $this->apiRequest('GET', $this->urlGenerator->generate('get_product', ['id' => 0]));

        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testListProducts()
    {
        for ($i = 1; $i < 4; $i++) {
            $this->productBuilder->buildProduct();
        }

        $this->apiRequest('GET', $this->urlGenerator->generate('list_products'));

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $returnedData = json_decode($response->getContent(), true);

        $this->assertCount(2, $returnedData);

        $this->assertEquals(
            [
                'products',
                'count',
            ],
            array_keys($returnedData)
        );

        $this->assertEquals(3, $returnedData['count']);
        $this->assertCount(3, $returnedData['products']);
    }

    public function testCreateProduct()
    {
        $productName = $this->productBuilder->getRandomProductName();
        $productPrice = $this->productBuilder->getRandomProductPrice();

        $data = [
            'name' => $productName,
            'price' => $productPrice,
        ];

        $this->apiRequest('POST', 'products', json_encode($data));

        $response = $this->client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());

        $returnedData = json_decode($response->getContent(), true);

        $this->assertEquals(
            [
                'id',
                'name',
                'price'
            ],
            array_keys($returnedData)
        );

        $this->assertEquals($productName, $returnedData['name']);
        $this->assertEquals($productPrice, $returnedData['price']);
    }

    public function testInvalidCreateProduct()
    {
        $data = [
            'name' => $this->productBuilder->getRandomProductName(),
        ];

        $this->apiRequest('POST', 'products', json_encode($data));

        $response = $this->client->getResponse();

        $this->assertEquals(500, $response->getStatusCode());

        $returnedData = json_decode($response->getContent(), true);

        $this->assertCount(3, $returnedData);

        $this->assertEquals(
            [
                'errorCode',
                'errorMessage',
                'errorDetails'
            ],
            array_keys($returnedData)
        );

        $this->assertEquals('Error validating entity', $returnedData['errorMessage']);
        $this->assertEquals(1, count($returnedData['errorDetails']));
        $this->assertArrayHasKey('price', $returnedData['errorDetails']);
    }

    public function testUpdateProduct()
    {
        $product = $this->productBuilder->buildProduct();

        $this->apiRequest('GET', $this->urlGenerator->generate('get_product', ['id' => $product->getId()]));

        $initialResponse = $this->client->getResponse();
        $initialData = json_decode($initialResponse->getContent(), true);

        $data = [
            'name' => 'Excellent Updated Product',
            'price' => $product->getPrice() + 100,
        ];

        $this->apiRequest('PATCH', $this->urlGenerator->generate('update_product', ['id' => $product->getId()]), json_encode($data));

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $returnedData = json_decode($response->getContent(), true);

        $this->assertCount(3, $returnedData);

        $this->assertEquals(
            [
                'id',
                'name',
                'price'
            ],
            array_keys($returnedData)
        );

        $this->assertNotEquals($initialData['name'], $returnedData['name']);
        $this->assertNotEquals($initialData['price'], $returnedData['price']);
        $this->assertEquals($data['name'], $returnedData['name']);
        $this->assertEquals($data['price'], $returnedData['price']);
    }

    public function testDeleteProduct()
    {
        $product = $this->productBuilder->buildProduct();

        $this->apiRequest('DELETE', $this->urlGenerator->generate('delete_product', ['id' => $product->getId()]));

        $response = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode());

        $this->apiRequest('GET', $this->urlGenerator->generate('delete_product', ['id' => $product->getId()]));

        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());

        $this->getEntityManager()->refresh($product);

        $this->assertEquals(true, $product->isDeleted());
    }
}