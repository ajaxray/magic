<?php

namespace Ajaxray\Magic\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
    public function __construct($classOrIdentifier)
    {
        parent::__construct("$classOrIdentifier was not found as service id or class name");
    }

}