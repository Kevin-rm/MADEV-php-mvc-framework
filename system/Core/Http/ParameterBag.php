<?php

namespace MADEV\Core\Http;

use InvalidArgumentException;

/**
 * Cette classe représente un conteneur de paramètres utilisé pour stocker et manipuler des données de requête.
 *
 * Elle facilite la gestion des paramètres transmis dans les requêtes,
 * en s'attendant à ce que ces derniers soient des tableaux associatifs clé-valeur.
 *
 * @package MADEV\Core\Http
 * @author  Kevin Ramarozatovo <kevinramarozatovo@gmail.com>
 */
class ParameterBag
{
    private $parameters;

    public function __construct($parameters = [])
    {
        $this->setParameters($parameters);
    }

    /**
     * @param  array $parameters
     * @return void
     */
    private function setParameters($parameters)
    {
        $this->add($parameters);
    }

    /**
     * Récupère la valeur associée à une clé spécifique.
     *
     * @param  string $key     La clé du paramètre recherché.
     * @param  mixed  $default La valeur par défaut à retouner si la clé n'existe pas.
     * @return mixed|null      La valeur associée à la clé ou la valeur par défaut si la clé n'existe pas.
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->all()[$key] : $default;
    }

    /**
     * Définit la valeur associée à une clé spécifique.
     *
     * @param  string $key   La clé du paramètre à définir.
     * @param  mixed  $value La valeur à associer à la clé.
     * @return void
     */
    public function set($key, $value)
    {
        self::validateKey($key);
        if (!isset($value))
            throw new InvalidArgumentException("La valeur associée à la clé \"$key\" à définir ne doit pas être \"undefined\" ou \"null\"");

        $this->parameters[$key] = $value;
    }

    /**
     * Ajoute des paramètres à la collection.
     *
     * @param  array $parameters
     * @return void
     */
    public function add($parameters)
    {
        if (!is_associative_array($parameters))
            throw new InvalidArgumentException('Le tableau de paramètres doit être un tableau associatif (paires clé-valeur)');

        foreach ($parameters as $key => $value) $this->set($key, $value);
    }

    /**
     * Remplace les paramètres actuels par de nouveaux paramètres.
     *
     * @param  array $parameters Les nouveaux paramètres à utiliser.
     * @return void
     */
    public function replace($parameters)
    {
        $this->clear();
        $this->add($parameters);
    }

    /**
     * Vérifie si une clé spécifique existe dans le conteneur.
     *
     * @param  string $key La clé à vérifier.
     * @return bool        True si la clé existe, sinon false.
     */
    public function has($key)
    {
        self::validateKey($key);
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Récupère tous les paramètres du conteneur.
     *
     * @return array Un tableau associatif contenant tous les paramètres.
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Supprime un paramètre spécifique du conteneur.
     *
     * @param  string $key La clé du paramètre à supprimer.
     * @return void
     */
    public function remove($key)
    {
        self::validateKey($key);
        unset($this->parameters[$key]);
    }

    /**
     * Efface tous les paramètres du conteneur.
     *
     * @return void
     */
    public function clear()
    {
        $this->parameters = [];
    }

    /**
     * Renvoie le nombre de paramètres dans le conteneur.
     *
     * @return int Le nombre de paramètres.
     */
    public function count()
    {
        return count($this->parameters);
    }

    /**
     * Vérifie si la clé passée en argument est bien une chaîne de caractères non vide.
     *
     * @param mixed $key La clé à valider.
     */
    private static function validateKey($key)
    {
        if (empty($key) || !is_string($key))
            throw new InvalidArgumentException('La clé doit être une chaîne de caractères non vide');
    }
}
