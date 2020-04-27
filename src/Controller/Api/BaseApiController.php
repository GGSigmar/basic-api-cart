<?php

namespace App\Controller\Api;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class BaseApiController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct()
    {
        $this->serializer = $this->getSerializer();
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer(): SerializerInterface
    {
        return SerializerBuilder::create()->build();
    }

    /**
     * @param $data
     * @param int $statusCode
     *
     * @return Response
     */
    protected function createApiResponse($data = [], int $statusCode = 200): Response
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $json = $this->serializer->serialize($data, 'json', $context);

        return new Response($json, $statusCode, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    protected function getErrorsFromForm(FormInterface $form)
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()] = $error->getMessage();
        }

        return $errors;
    }

    protected function createApiErrorResponse(string $errorMessage, int $statusCode = 500, $details = null): Response
    {
        $data = [
            'errorCode' => $statusCode,
            'errorMessage' => $errorMessage,
            'errorDetails' => $details
        ];

        return $this->createApiResponse($data, $statusCode);
    }
}