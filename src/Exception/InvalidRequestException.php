<?php

namespace App\Exception;

class InvalidRequestException extends \Exception
{
    private const MESSAGE = "Content of the request was invalid!";

    /**
     * @param $entityType
     * @param \Throwable|null $previous
     */
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, 400, $previous);
    }
}