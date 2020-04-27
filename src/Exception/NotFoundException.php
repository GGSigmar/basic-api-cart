<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class NotFoundException extends \Exception
{
    private const MESSAGE = "Desired entity of type '%s' not found, sorry!";

    /**
     * @param $entityType
     * @param Throwable|null $previous
     */
    public function __construct($entityType, Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $entityType), Response::HTTP_NOT_FOUND, $previous);
    }
}