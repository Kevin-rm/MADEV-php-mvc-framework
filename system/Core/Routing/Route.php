<?php

namespace MADEV\Core\Routing;

use InvalidArgumentException;
use MADEV\Core\Controllers\BaseController;

/**
 * La classe Route représente une route dans une application.
 *
 * Une route est généralement associée à une URL particulière qui encapsule
 * des informations importantes tel que le chemin, les méthodes HTTP autorisées,
 * l'action à exécuter et les paramètres.
 *
 * @package MADEV\Core\Routing
 * @author  Kevin Ramarozatovo <kevinramarozatovo@gmail.com>
 */
class Route
{
    private        $path;              // Le chemin associé à la route.
                                       // Il peut contenir des segments dynamiques entre accolades,
                                       // et doit commencer par un "/".
    private        $methods;           // Les méthodes HTTP
    private        $callable;          // L'action à exécuter lorsque la route est atteinte
    private        $name;              // Le nom de la route
    private        $dynamicParameters; // Les paramètres dynamiques extraits du chemins
    private        $constraints;       // Les contraintes pour les paramètres dynamiques (ce sont usuellement des expressions régulières)
    private static $validMethods = ['GET', 'DELETE', 'POST', 'PUT'];

    /**
     * @param string         $path
     * @param string|array   $methods
     * @param array|callable $callable
     * @param string         $name
     */
    public function __construct($path, $methods, $callable, $name = '')
    {
        $this->setPath           ($path);
        $this->setMethods        ($methods);
        $this->setCallable       ($callable);
        $this->setName           ($name);
        $this->dynamicParameters = [];
        $this->constraints       = [];
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return callable
     */
    public function getCallable()
    {
        return $this->callable;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getDynamicParameters()
    {
        return $this->dynamicParameters;
    }

    /**
     * @param  string $path
     * @return void
     */
    private function setPath($path)
    {
        if (empty($path))      throw new InvalidArgumentException('Le chemin associé à une route ne doit pas être vide');
        if (!is_string($path)) throw new InvalidArgumentException('Le chemin associé à une route doit être une chaîne de caractères');
        if (!string_starts_with($path, "/"))
            throw new InvalidArgumentException('Le chemin associé à une route doit commmencer par un "/"');

        $this->path = trim($path);
    }

    /**
     * @param  string|array $methods
     * @return void
     */
    private function setMethods($methods)
    {
        if (empty($methods))
            throw new InvalidArgumentException("Aucune méthode HTTP n'a été spécifiée pour la route associée à \"$this->path\"");
        if (is_string($methods)) {
            $this->setMethod($methods);
            return;
        }
        if (!is_array($methods) || get_array_depth($methods) !== 1 || is_associative_array($methods))
            throw new InvalidArgumentException("Les méthodes HTTP pour la route associée à \"$this->path\" doivent être sous forme de chaîne de caractères ou de tableau à une(1) dimension non associatif");

        $this->methods = [];
        foreach ($methods as $method) $this->setMethod($method);
    }

    /**
     * @param  string $method
     * @return void
     */
    private function setMethod($method)
    {
        if (empty($method)) throw new InvalidArgumentException('Une méthode HTTP ne doit pas être vide, "undefined" ou "null"');

        $method = strtoupper(trim($method));
        if (!in_array($method, self::$validMethods, true))
            throw new InvalidArgumentException("La méthode \"$method\" fournie n'est pas valide pour la route associée à \"$this->path\"");

        $this->methods[] = $method;
    }

    /**
     * @param  string $name
     * @return void
     */
    private function setName($name)
    {
        if (!isset($name))     throw new InvalidArgumentException('Le nom d\'une route ne doit pas être "undefined" ou "null"');
        if (!is_string($name)) throw new InvalidArgumentException('Le nom d\'une route doit être une chaîne de caractères');
        $this->name = trim($name);
    }

    /**
     * @param  array|callable $callable
     * @return void
     */
    private function setCallable($callable)
    {
        if (empty($callable)) throw new InvalidArgumentException('Le callable ne peut pas être vide');

        if (is_array($callable)) {
            if (count($callable) != 2)
                throw new InvalidArgumentException('Le tableau associé au callable doit avoir exactement 2 éléments : [controller, action]');

            list($controller, $action) = $callable;
            if (!is_string($controller) || !is_string($action))
                throw new InvalidArgumentException('Chaque élément du tableau associé au callable doit être une chaîne de caractères');

            $controller  = trim($controller);
            $action      = trim($action);
            if ($controller === '' || $action === '')
                throw new InvalidArgumentException('Le controller et l\'action associés à la route ne peuvent pas être vides');

            $controller      = new ("App\\Controllers\\$controller");
            $controllerClass = get_class($controller);
            if (!($controller instanceof BaseController))
                throw new InvalidArgumentException("\"$controllerClass\" n\'est pas un contrôleur");
            if (!method_exists($controller, $action))
                throw new InvalidArgumentException("La méthode \"$action\" n'existe pas pour le contrôleur \"$controllerClass\"");

            $this->callable = [$controller, $action];
        }
        elseif (is_callable($callable)) $this->callable = $callable;
        else throw new InvalidArgumentException('Le callable associé à une route doit être une fonction ou un tableau [controller, action]');
    }

    /**
     * @return array
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * Cette fonction sert à modifier le nom d'une route.
     *
     * @param  string $name
     * @return Route
     */
    public function name($name = '')
    {
        $this->setName($name);
        return $this;
    }

    /**
     * Précise les contraintes sur les paramètres dynamiques des routes.
     * Les contraintes doivent être des expressions régulières.
     *
     * @param array $constraints Tableau associatif des contraintes
     *                           où les clés sont les noms des paramètres
     *                           et les valeurs sont des expressions régulières.
     * @return Route
     */
    public function where($constraints)
    {
        if (empty($constraints)) return $this;
        if (!is_associative_array($constraints) || get_array_depth($constraints) !== 1)
            throw new InvalidArgumentException('Les contraintes des paramètres des routes doivent être sous forme de tableau associatif à une(1) dimension');

        // Valider que chaque contrainte est une expression régulière valide
        foreach ($constraints as $parameter => $constraint) {
            $escapedConstraint = '/' . str_replace('/', '\/', $constraint) . '/';
            if (@preg_match($escapedConstraint, null) === false)
                throw new InvalidArgumentException("Expression régulière non valide : \"$constraint\" sur les contraintes du paramètre \"$parameter\" de la route associée à \"$this->path\"");

            $this->constraints[$parameter] = $escapedConstraint;
        }

        return $this;
    }

    /**
     * Ajoute un paramètre dynamique à la liste des paramètres dynamiques de la route.
     *
     * @param  string $parameterName Le nom du paramètre dynamique.
     * @param  mixed  $value         La valeur à assigner au paramètre.
     * @return void
     */
    public function addDynamicParameter($parameterName, $value)
    {
        $this->dynamicParameters[$parameterName] = $value;
    }

    /**
     * Vérifie si une valeur respecte la contrainte d'un paramètre dynamique.
     *
     * @param string $parameterName Le nom du paramètre.
     * @param string $value         La valeur à vérifier.
     * @return bool                 True si la valeur respecte la contrainte, false sinon.
     */
    public function matchConstraint($parameterName, $value)
    {
        if (!isset($this->constraints[$parameterName])) return true;
        return preg_match($this->constraints[$parameterName], $value) === 1;
    }

    /**
     * Vérifie si une partie du chemin dans une route dynamique est un paramètre.
     *
     * Les routes dynamiques sont de la forme : /formations/{slug_formation}.
     * Les paramètres sont inclus entre une paire d'accolades "{}", et il ne doit pas y avoir
     * d'accolades supplémentaires à l'intérieur d'une paire.
     *
     * @param  string $pathPart La partie du chemin à vérifier.
     * @return bool             True si c'est un paramètre, false sinon.
     */
    public static function isParameter($pathPart)
    {
        if (
            !string_contains($pathPart, "{") &&
            !string_contains($pathPart, "}")
        ) return false;

        if (!self::hasBalancedBraces($pathPart))
            throw new InvalidArgumentException("Erreur au niveau des accolades : \"$pathPart\"");

        if (substr_count($pathPart, '{') === 1 && substr_count($pathPart, '}') === 1) {
            $insideBraces = substr($pathPart, 1, -1);

            if (is_numeric($insideBraces)) throw new InvalidArgumentException('Le nom d\'un paramètre doit être un "string"');
            if ($insideBraces === '')      throw new InvalidArgumentException('Aucun nom de paramètre trouvé à l\'intérieur des accolades');

            if (preg_match('/^\{(.+?)}$/', $pathPart) === 1) {
                /*
                 * Vérifier que le nom du paramètre ne contient que des caractères alphanumériques et
                 * éventuellement des traits de soulignement
                 */
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $insideBraces))
                    throw new InvalidArgumentException('Le nom d\'un paramètre ne peut contenir que des caractères alphanumériques et des traits de soulignement');

                   return true;
            } else return false;
        }
        else throw new InvalidArgumentException("Plus d'une paire d'accolades a été trouvé : \"$pathPart\"");
    }

    /**
     * Un helper qui vérifie l'équilibre des accolades dans une chaîne de caractères.
     *
     * @param  string $string La chaîne de caractères à analyser.
     * @return bool           True si les accolades sont équilibrées, false sinon.
     */
    private static function hasBalancedBraces($string)
    {
        $verif = 0;
        for ($i = 0; $i < strlen($string); $i++) {
            $char = $string[$i];

            if     ($char === '{') $verif++;
            elseif ($char === '}') $verif--;

            if ($verif < 0) return false;
        }
        return $verif === 0;
    }
}
