<?php

namespace App\Tests\Controller\Api;

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

        $this->productBuilder = new ProductBuilder($this->entityManager);
    }

    public function testGetProductAction()
    {
        $product = $this->productBuilder->buildProduct();

        $this->apiRequest('GET', $this->urlGenerator->generate('get_product', ['id' => $product->getId()]));

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $returnedData = json_decode($response->getContent(), true);

        $this->assertEquals(
            [
                'uri',
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

    public function testListProductsAction()
    {
        for ($i = 0; $i <= 6; $i++) {
            $this->productBuilder->buildProduct(true, ['name' => 'Product' . $i, 'price' => $i * 100]);
        }

        $this->apiRequest('GET', $this->urlGenerator->generate('list_products'));

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $returnedData = json_decode($response->getContent(), true);

        $this->assertCount(4, $returnedData);

        $this->assertEquals(
            [
                'items',
                'count',
                'total',
                '_links'
            ],
            array_keys($returnedData)
        );

        $this->assertEquals(3, $returnedData['count']);
        $this->assertEquals(7, $returnedData['total']);
        $this->assertCount(3, $returnedData['items']);
        $this->assertCount(4, $returnedData['_links']);

        $this->assertEquals('Product0', $returnedData['items'][0]['name']);
        $this->assertEquals('Product1', $returnedData['items'][1]['name']);

        $this->apiRequest('GET', $returnedData['_links']['next']);

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $returnedData = json_decode($response->getContent(), true);

        $this->assertCount(4, $returnedData);

        $this->assertEquals(3, $returnedData['count']);
        $this->assertEquals(7, $returnedData['total']);
        $this->assertCount(3, $returnedData['items']);
        $this->assertCount(5, $returnedData['_links']);

        $this->assertEquals('Product3', $returnedData['items'][0]['name']);
        $this->assertEquals('Product4', $returnedData['items'][1]['name']);

        $this->apiRequest('GET', $returnedData['_links']['last']);

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $returnedData = json_decode($response->getContent(), true);

        $this->assertCount(4, $returnedData);

        $this->assertEquals(1, $returnedData['count']);
        $this->assertEquals(7, $returnedData['total']);
        $this->assertCount(1, $returnedData['items']);
        $this->assertCount(4, $returnedData['_links']);

        $this->assertEquals('Product6', $returnedData['items'][0]['name']);
    }

    public function testCreateProductAction()
    {
        $productName = $this->productBuilder->getRandomProductName();
        $productPrice = $this->productBuilder->getRandomProductPrice();

        $data = [
            'name' => $productName,
            'price' => $productPrice,
        ];

        $this->apiRequest('POST', $this->urlGenerator->generate('create_product'), json_encode($data));

        $response = $this->client->getResponse();

        $this->assertEquals(201, $response->getStatusCode());

        $returnedData = json_decode($response->getContent(), true);

        $this->assertEquals(
            [
                'uri',
                'id',
                'name',
                'price'
            ],
            array_keys($returnedData)
        );

        $this->assertEquals($productName, $returnedData['name']);
        $this->assertEquals($productPrice, $returnedData['price']);
    }

    public function testInvalidCreateProductAction()
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

    public function testUpdateProductAction()
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

        $this->assertCount(4, $returnedData);

        $this->assertEquals(
            [
                'uri',
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

    public function testDeleteProductAction()
    {
        $product = $this->productBuilder->buildProduct();

        $this->apiRequest('DELETE', $this->urlGenerator->generate('delete_product', ['id' => $product->getId()]));

        $response = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode());

        $this->apiRequest('GET', $this->urlGenerator->generate('delete_product', ['id' => $product->getId()]));

        $response = $this->client->getResponse();

        $this->assertEquals(404, $response->getStatusCode());

        $this->entityManager->refresh($product);

        $this->assertEquals(true, $product->isDeleted());
    }
}