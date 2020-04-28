<?php

namespace App\Controller\Web;

use App\Repository\ProductRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BaseWebController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @Template("base.html.twig")
     */
    public function indexAction(ProductRepository $productRepository)
    {
        $activeProducts = $productRepository->getAllActivePRoducts();

        return [
            'activeProducts' => $activeProducts
        ];
    }
}