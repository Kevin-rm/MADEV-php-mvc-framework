<?php

namespace MADEV\Core\Exceptions;

use Exception;

/**
 * Exception lancée lorsqu'une route n'est pas trouvée.
 *
 * @package MADEV\Core
 * @author  Kevin Ramarozatovo <kevinramarozatovo@gmail.com>
 */
class RouteNotFoundException extends Exception
{
    public function __construct($path, $method)
    {
        parent::__construct(
            "La route demandée \"$path\" avec la méthode \"$method\" n'a pas été trouvée",
            404,
            null
        );
    }
}
