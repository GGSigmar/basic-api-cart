<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Exception\NotFoundException;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends BaseApiController
{
    /**
     * @Route("products", methods={"GET"}, name="list_products")
     */
    public function listProductsAction(EntityManagerInterface $entityManager)
    {
        try {
            $activeProducts = $entityManager->getRepository(Product::class)->getAllActiveProducts();

            $data = [
                'products' => $activeProducts,
                'count' => count($activeProducts),
            ];

            return $this->createApiResponse($data);
        } catch (\Exception $e) {
            return $this->createApiErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Route("products/{id}", methods={"GET"}, name="get_product")
     */
    public function getProductAction(Product $product)
    {
        try {
            if (!$product || $product->getIsDeleted()) {
                throw new NotFoundException('Product was not found');
            }

            return $this->createApiResponse($product);
        } catch (\Exception $e) {
            return $this->createApiErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Route("/products", methods={"POST"}, name="create_product")
     */
    public function createProductAction(Request $request)
    {
        try {
            $data = json_decode($request->getContent(), true);

            $product = new Product();
            $form = $this->createForm(ProductType::class, $product);

            $form->submit($data);

            if ($form->isValid()) {
                $product = $form->getData();

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($product);
                $entityManager->flush();

                $response =  $this->createApiResponse($product, 201);
                $response->headers->set('Location', $this->generateUrl('get_product', ['id' => $product->getId()]));

                return $response;
            }

            return $this->createApiErrorResponse('Error validating entity', 500, $this->getErrorsFromForm($form));
        } catch (\Exception $e) {
            return $this->createApiErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Route("/products/{id}", methods={"PATCH"}, name="update_product")
     */
    public function updateProductAction(Request $request, Product $product)
    {
        try {
            if (!$product || $product->getIsDeleted()) {
                return $this->createApiErrorResponse('Product was not found');
            }

            $data = json_decode($request->getContent(), true);

            $form = $this->createForm(ProductType::class, $product);

            $form->submit($data);

            if ($form->isValid()) {
                $product = $form->getData();

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($product);
                $entityManager->flush();

                $response =  $this->createApiResponse($product, 200);
                $response->headers->set('Location', $this->generateUrl('get_product', ['id' => $product->getId()]));

                return $response;
            }

            return $this->createApiErrorResponse('Error validating entity', 500, $this->getErrorsFromForm($form));
        } catch (\Exception $e) {
            return $this->createApiErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Route("/products/{id}", methods={"DELETE"}, name="delete_product")
     */
    public function deleteProductAction(Request $request, Product $product)
    {
        try {
            if (!$product || $product->getIsDeleted()) {
                return $this->createApiErrorResponse('Product was not found');
            }

            $product->setIsDeleted(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->createApiResponse(null, 204);
        }  catch (\Exception $e) {
            return $this->createApiErrorResponse($e->getMessage(), $e->getCode());
        }
    }
}