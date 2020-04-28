<?php

namespace App\Controller\Api;

use App\Entity\Cart;
use App\Entity\Product;
use App\Exception\InvalidRequestException;
use App\Exception\NotFoundException;
use App\Service\Cart\CartManager;
use App\Service\PriceFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends BaseApiController
{
    /**
     * @Route("/carts/{id}", methods={"GET"}, name="get_cart")
     */
    public function getCartAction(Cart $cart, CartManager $cartManager)
    {
        try {
            $cartManager->setCart($cart);

            $data = [
                'cart' => $cart,
                'totalPrice' => PriceFormatter::formatMoneyPrice($cartManager->getCartTotalPrice()),
            ];

            return $this->createApiResponse($data);
        } catch (\Exception $e) {
            return $this->createApiErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Route("/carts", methods={"POST"}, name="create_cart")
     */
    public function createCartAction()
    {
        try {
            $cart = new Cart();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cart);
            $entityManager->flush();

            $response =  $this->createApiResponse($cart, 201);
            $response->headers->set('Location', $this->generateUrl('get_cart', ['id' => $cart->getId()]));

            return $response;
        } catch (\Exception $e) {
            return $this->createApiErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Route("/carts/{id}/add-product", methods={"PATCH"}, name="add_product_to_cart")
     */
    public function addProductToCartAction(Request $request, Cart $cart, CartManager $cartManager, EntityManagerInterface $entityManager)
    {
        try {
            $data = json_decode($request->getContent(), true);

            $cartManager->setCart($cart);

            if (!isset($data['product'])) {
                throw new InvalidRequestException();
            }

            $product = $entityManager->getRepository(Product::class)->getActiveProductById($data['product']);

            if (!$product) {
                throw new NotFoundException('product');
            }

            if (isset($data['quantity'])) {
                $quantity = $data['quantity'];
            } else {
                $quantity = 1;
            }

            $cartManager->addProductToCart($product, $quantity);

            $entityManager->persist($cart);
            $entityManager->flush();

            return $this->createApiResponse($cart);
        } catch (\Exception $e) {
            return $this->createApiErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @Route("/carts/{id}/remove-product", methods={"PATCH"}, name="remove_product_from_cart")
     */
    public function removeProductFromCartAction(Request $request, Cart $cart, CartManager $cartManager, EntityManagerInterface $entityManager)
    {
        try {
            $data = json_decode($request->getContent(), true);

            $cartManager->setCart($cart);

            if (!isset($data['product'])) {
                throw new InvalidRequestException();
            }

            $product = $entityManager->getRepository(Product::class)->getActiveProductById($data['product']);

            if (!$product) {
                throw new NotFoundException('product');
            }

            $cartManager->removeProductFromCart($product);

            $entityManager->persist($cart);
            $entityManager->flush();

            return $this->createApiResponse($cart);
        } catch (\Exception $e) {
            return $this->createApiErrorResponse($e->getMessage(), $e->getCode());
        }
    }
}