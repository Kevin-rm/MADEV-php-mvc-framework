<?php

namespace MADEV\Core\Routing;

use Exception;
use InvalidArgumentException;
use MADEV\Core\Exceptions\RouteNotFoundException;
use MADEV\Core\Http\Request;
use MADEV\Core\Http\Response;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Le Routeur permet de trouver la route qui correspond à une requête HTTP
 * parmi toutes les routes de l'application.
 *
 * @package MADEV\Core\Routing
 * @author  Kevin Ramarozatovo <kevinramarozatovo@gmail.com>
 */
class Router
{
    /**
     * @throws Exception
     */
    public function __construct()
    {
        try {
            $this->loadRoutes();
        } catch (Exception $e) {
            throw new Exception('Exception lors de l\'instanciation du routeur : ' . $e->getMessage());
        }
    }

    /**
     * Dispatche la requête vers la route appropriée et appelle la fonction associée.
     *
     * @param  Request $request La requête entrante.
     * @return Response
     * @throws Exception
     */
    public function dispatch($request)
    {
        $matchedRoute = $this->resolve($request);
        if ($matchedRoute === null) throw new RouteNotFoundException($request->getPathInfo(), $request->getMethod());

        $callable               = $matchedRoute->getCallable();
        $possibleArguments      = array_merge($matchedRoute->getDynamicParameters(), ['request' => $request]);

        $reflectionMethod       = is_array($callable) ? new ReflectionMethod(...$callable) : new ReflectionFunction($callable);
        if ($reflectionMethod instanceof ReflectionMethod && !$reflectionMethod->isPublic())
            throw new Exception('La fonction à appeler doit être déclarée comme publique pour être accessible lors de la résolution de la route');

        $functionParameterNames = array_map(
            function($parameter) { return $parameter->getName(); },
            $reflectionMethod->getParameters()
        );

        /*
         * Les paramètres possibles pour chaque fonction associée à une route sont:
         * - Les paramètres dynamiques
         * - La requête
         *
         * Mais ils peuvent également être tous optionnels.
         */
        if (count($functionParameterNames) > count($possibleArguments))
            throw new Exception("Le nombre de paramètres de la fonction \"$reflectionMethod->name\" ne correspond pas au nombre de paramètres possible");

        $difference = array_diff($functionParameterNames, array_keys($possibleArguments));
        if (!empty($difference))
            throw new Exception("Paramètres inattendus pour la fonction \"$reflectionMethod->name\" : \"" . implode(', ', $difference) . '"');

        $matchedArguments = array_intersect_key($possibleArguments, array_flip($functionParameterNames));

        return new Response(
             call_user_func($callable, ...$matchedArguments),
            Response::HTTP_OK
        );
    }

    /**
     * Recherche de la route correspondant à l'URL fournie dans la requête.
     *
     * @param  Request $request
     * @return Route|null
     */
    private function resolve($request)
    {
        /*
         * Splitting de l'url demandé par l'utilisateur.
         *
         * On le divise en segments en utilisant le caractère "/" comme séparateur.
         * Le trim est utilisé pour supprimer les éventuels "/" au début et à la fin de l'URL.
         * Par exemple si l'URL est "/", le trim garantit que $explodedRequestPathInfo contient un tableau vide.
         *
         * Exemple :
         * - URL fournie : "/path/to/resource/"
         * - Après trim : "path/to/resource"
         * - Après explode : ["path", "to", "resource"]
         */
        $explodedRequestPathInfo = explode(
            "/",
            trim($request->getPathInfo(), "/")
        );
        $countExplodedRequestPathInfo = count($explodedRequestPathInfo);

        foreach (RouteCollection::getRoutes() as $route) {
            // Check si la route autorise la méthode HTTP actuelle
            if (!in_array($request->getMethod(), $route->getMethods())) continue;

            // Splitting du chemin de la route, similaire à la division de l'URL
            $explodedPath = explode(
                "/",
                trim($route->getPath(), "/")
            );

            if ($countExplodedRequestPathInfo !== count($explodedPath)) continue;

            for ($i = 0; $i < $countExplodedRequestPathInfo; $i++) {
                // Si le chemin est dynamique
                if (Route::isParameter($explodedPath[$i])) {
                    $parameterName = substr($explodedPath[$i], 1, -1);
                    if (!$route->matchConstraint($parameterName, $explodedRequestPathInfo[$i])) break;

                    $route->addDynamicParameter($parameterName, $explodedRequestPathInfo[$i]);
                }
                // Sinon, vérifiez simplement si les segments correspondent
                elseif ($explodedRequestPathInfo[$i] !== $explodedPath[$i]) break;
            }

            // Si on a parcouru tous les segments avec succès, c'est la route recherchée
            if ($i === $countExplodedRequestPathInfo) return $route;
        }
        return null;
    }

    /**
     * Charge les routes à partir du fichier de configuration des routes.
     *
     * @return void
     * @throws Exception
     */
    private function loadRoutes()
    {
        $routesJsonFilePath = CONFIG_PATH . 'routes/routes.json';
        $routesPhpFilePath  = CONFIG_PATH . 'routes/routes.php';
        if (!file_exists($routesJsonFilePath) || !file_exists($routesPhpFilePath)) {
            $missingFiles = [];
            if (!file_exists($routesJsonFilePath)) $missingFiles[] = " \"$routesJsonFilePath\"";
            if (!file_exists($routesPhpFilePath))  $missingFiles[] = " \"$routesPhpFilePath\"";
            $missingFilesList = implode(' et', $missingFiles);

            throw new InvalidArgumentException("Les fichiers de configuration des routes $missingFilesList sont introuvables");
        }

        $rawRoutes = json_decode(file_get_contents($routesJsonFilePath), true, 4);
        self::parseRawRoutes($rawRoutes);

        // Ajout des routes
        foreach ($rawRoutes as $rawRoute)
            RouteCollection::addRoute(
                     $rawRoute['path'],
                     $rawRoute['methods'],
                    [$rawRoute['controller'], $rawRoute['action']]
            )->name ($rawRoute['name'])
             ->where($rawRoute['constraints']);

        // Ajout des routes du fichier de configuration 'routes.php'
        require_once $routesPhpFilePath;

    }

    /**
     * Analyse les données de routes entrées par les utilisateurs
     * dans le fichier de configuration JSON dédié.
     *
     * @param  array $rawRoutes
     * @return void
     * @throws Exception
     */
    private static function parseRawRoutes($rawRoutes)
    {
        if ($rawRoutes === null)
            throw new InvalidArgumentException('Format invalide des routes dans le fichier de configuration JSON. Suivez et respectez la structure de données déjà établie');

        $expectedRoutesKeys = ['path', 'methods', 'controller', 'action', 'name', 'constraints'];
        foreach ($rawRoutes as $index => $element) {
            if (!is_int($index)) throw new InvalidArgumentException("Indice de route innatendu : \"$index\"");

            // Les clés de $element
            $actualKeys = array_keys($element);

            // Vérification des clés manquantes
            $missingKeys = array_diff($expectedRoutesKeys, $actualKeys);
            if (!empty($missingKeys))
                throw new Exception("Clés manquantes pour la route numéro \"$index\" : \"" . implode(', ', $missingKeys) . "\"");

            // Vérification des clés inattendues
            $unexpectedKeys = array_diff($actualKeys, $expectedRoutesKeys);
            if (!empty($unexpectedKeys))
                throw new Exception("Clés innattendues pour la route numéro \"$index\" : \"" . implode(', ', $unexpectedKeys) . "\"");
        }
    }
}
