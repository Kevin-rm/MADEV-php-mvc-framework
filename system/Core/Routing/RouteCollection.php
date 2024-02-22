<?php

namespace MADEV\Core\Routing;

use InvalidArgumentException;

/**
 * Cette classe gère la collection des routes de l'application.
 * Elle permet d'ajouter et de stocker les routes pour une utilisation ultérieure par le routeur.
 *
 * @package MADEV\Core\Routing
 * @author  Kevin Ramarozatovo <kevinramarozatovo@gmail.com>
 */
class RouteCollection
{
    private static $routes = [];

    private function __construct() { }

    /**
     * @return array
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * Ajoute une nouvelle route dans la liste des routes.
     *
     * @param  string         $path
     * @param  string|array   $methods
     * @param  callable|array $callable
     * @return Route
     */
    public static function addRoute($path, $methods, $callable)
    {
        $newRoute = new Route($path, $methods, $callable);
        foreach (self::$routes as $route)
            if ($route->getPath() === $newRoute->getPath()) {
                $sameMethods = array_intersect($route->getMethods(), $newRoute->getMethods());
                if (!empty($sameMethods))
                    throw new InvalidArgumentException("La route que vous essayez d'ajouter (Path: $path, Methods: \"" . implode(', ', $sameMethods) . '") existe déjà dans la liste des routes');
            }

        self::$routes[] = $newRoute;

        return $newRoute;
    }

    /**
     * Ajoute une nouvelle route de type GET dans la liste des routes.
     *
     * @param  string         $path
     * @param  array|callable $callable
     * @return Route
     */
    public static function get($path, $callable)
    {
        return RouteCollection::addRoute($path, 'GET', $callable);
    }

    /**
     * Ajoute une nouvelle route de type POST dans la liste des routes.
     *
     * @param  string         $path
     * @param  array|callable $callable
     * @return Route
     */
    public static function post($path, $callable)
    {
        return RouteCollection::addRoute($path, 'POST', $callable);
    }
}
